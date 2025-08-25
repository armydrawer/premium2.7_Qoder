<?php
if (!defined('ABSPATH')) { exit(); }

if (!class_exists('Ext_Premium')) {
	#[AllowDynamicProperties]
	class Ext_Premium {
		
		public $name = "";
		public $title = "";
		public $clear_title = "";
		public $map = "";
		public $place = '';
		private $salt = '';
		public $plugin = '';
		
		function __construct($file, $title, $place, $plugin, $salt = '')
		{
			$path = get_extension_file($file);
			$name = get_extension_name($path);

			file_safe_include($path . '/class');	

			$map = array();
			$maps = $this->get_map();
			foreach ($maps as $map_key => $map_value) {
				$map[] = $map_key;
			}
			
			$this->name = trim($name);
			$this->title = $title . ' (' . $this->name . ')';
			$this->clear_title = $title;
			$this->map = $map;
			$this->place = trim($place);
			$this->salt = trim($salt);
			if (strlen($this->salt) < 1) {
				if (defined('EXT_SALT')) {
					$this->salt = EXT_SALT;
				}
			}
			
			$this->plugin = $plugin;
			
			add_filter('ext_' . $this->place . '_data', array($this, 'ext_data'), 10, 3);
			add_filter('ext_' . $this->place . '_data_post', array($this, 'ext_data_post'), 10, 3);
			add_action('ext_' . $this->place . '_delete', array($this, 'delete_ext'), 10, 2);
			add_filter($this->place .'_settingtext_' . $this->name, array($this, 'settingtext'), 10, 2);
		}

		function settingtext($text, $id) {
			$data = $this->get_file_data($id);
		
			$error = 1;
			$arrs = $this->settings_list();
			if (count($arrs) > 0) {
				foreach ($arrs as $arr) {
					$arr_now = (array)$arr;
					$n_error = 0;
					foreach ($arr_now as $arr_key) {
						$d = is_isset($data, $arr_key);
						if (strlen($d) < 1) {
							$n_error = 1;
						}
					}
					if (1 != $n_error) {
						$error = 0;
					}
				}
			} else {
				$error = 0;
			}				
				
			if (1 == $error) {	
				$text = '<span class="bred">' . __('Config file is not configured', 'premium') . '</span>';
			}
			
			return $text;
		}

		public function get_map() {
			
			return array();
		}

		function settings_list() {
			
			$arrs = array();
			
			return $arrs;
		}

		function get_file_data($id) {
			
			$m_data = array();

			$data = array();
			$file = $this->plugin->upload_dir . '/' . $this->place . '/' . $id . '.php';
			if (is_file($file)) {
				$fdata = get_fdata($file);
				$data = check_array_map($fdata, $this->map);
			} 
			foreach ($data as $data_key => $data_value) {
				$m_data[$data_key] = premium_decrypt($data_value, $this->salt);
			}
			
			return $m_data;
		}

		function ext_data($options, $script, $id) {
			if ($script == $this->name) {
				
				$file_data = $this->get_file_data($id);
				$maps = $this->get_map();
				foreach ($maps as $map_key => $map_value) {
					$view = trim(is_isset($map_value, 'view'));
					$opts = is_isset($map_value, 'options');
					$title = trim(ctv_ml(is_isset($map_value, 'title')));
					$hidden = intval(is_isset($map_value, 'hidden'));
					$default = '';
					if (isset($file_data[$map_key])) {
						if (strlen($file_data[$map_key]) > 0) {
							$default = $file_data[$map_key];
						}
					}
					$placeholder = '';
					if (strlen($default) > 0 and $hidden) {
						$placeholder = '***' . __('parameter already set', 'premium') . '***';
					}	
					$show_text = '';
					if (1 != $hidden) {
						$show_text = $default;
					}					
					if ('input' == $view) {
						$options['map_' . $map_key] = array(
							'view' => 'inputbig',
							'title' => $title,
							'default' => apply_filters('show_secret_files', $show_text, $default),
							'name' => 'map_' . $map_key,
							'atts' => array('placeholder' => $placeholder, 'autocomplete' => 'off'),
						);					
					} elseif ('textarea' == $view) {
						$options['map_' . $map_key] = array(
							'view' => 'textarea',
							'title' => $title,
							'default' => apply_filters('show_secret_files', $show_text, $default),
							'name' => 'map_' . $map_key,
							'atts' => array('placeholder' => $placeholder, 'autocomplete' => 'off'),
							'rows' => '10',
						);	
					} elseif ('select' == $view) {
						$options['map_' . $map_key] = array(
							'view' => 'select',
							'title' => $title,
							'options' => $opts,
							'default' => $default,
							'name' => 'map_' . $map_key,
						);
					} elseif ('warning' == $view) {
						$options['map_' . $map_key] = array(
							'view' => 'warning',
							'title' => $title,
							'default' => $default,
						);						
					} elseif ('help' == $view) {	
						$options['map_' . $map_key] = array(
							'view' => 'help',
							'title' => $title,
							'default' => $default,
						);	
					}
				}	
			}
			
			return $options;
		}

		function ext_data_post($ind, $script, $id) {	
			if (1 != $ind and $script == $this->name) {
				$posts = array();

				$file_data = $this->get_file_data($id);
					
				$maps = $this->get_map();
				foreach ($maps as $map_key => $map_value) {
					$value = stripslashes(is_param_post('map_' . $map_key));
					$hidden = intval(is_isset($map_value, 'hidden'));
					if (strlen($value) < 1 and 1 == $hidden) {
						$value = is_isset($file_data, $map_key); 
					}
					if ('*' == $value) {
						$value = '';
					}					
					$posts[$map_key] = premium_encrypt($value, $this->salt);
				}		
					
				return update_fdata($this->place, $id, $posts);
			}
			
			return $ind;
		}

		function get_ids($name = '', $script = '', $set_status = 1) {
			
			$ids = array();
			$script = trim($script);
			$name = trim($name);
			$set_status = intval($set_status);
			if ($script) {
				$list = get_extandeds(); 
				foreach ($list as $ext) {
					$ext_type = trim(is_isset($ext, 'ext_type'));
					$mscr = trim(is_isset($ext, 'ext_plugin'));
					$status = intval(is_isset($ext, 'ext_status'));
					if ($mscr and $mscr == $script and $status == $set_status and $ext_type == $name) {
						$ids[] = is_isset($ext, 'ext_key');
					}
				}
			} 	
			
			return $ids;
		}

		function delete_ext($script, $id) {
			if ($script == $this->name) {
				
				$file = $this->plugin->upload_dir . '/' . $this->place . '/' . $id . '.php';
				if (is_file($file)) {
					@unlink($file);
				}	

			}
		}		
	}
}