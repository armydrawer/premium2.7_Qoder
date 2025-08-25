<?php
if (!defined('ABSPATH')) { exit(); }

if (!function_exists('def_adminpage_title_pn_moduls') and is_admin()) {
	
	add_filter('pn_adminpage_title_pn_moduls', 'def_adminpage_title_pn_moduls');
	function def_adminpage_title_pn_moduls($page) {
		
		return __('Modules', 'pn');
	}

	add_action('pn_adminpage_content_pn_moduls', 'def_pn_admin_content_pn_moduls');
	function def_pn_admin_content_pn_moduls() {
		premium_table_list();
	} 

}

if (!function_exists('def_premium_action_pn_moduls')) {
	add_action('premium_action_pn_moduls', 'def_premium_action_pn_moduls');
	function def_premium_action_pn_moduls() {	
	
		$plugin = get_plugin_class();

		_method('post');
		
		pn_only_caps(array('administrator'));

		$arrs = array(
			'paged' => intval(is_param_post('paged')),
		);
		$action = get_request_action();
		
		if (isset($_POST['id']) and is_array($_POST['id'])) {
			if ('active' == $action) {
							
				$extended = get_option('pn_extended');
				if (!is_array($extended)) { $extended = array(); }
							
				foreach ($_POST['id'] as $id) {
					$id = is_extension_name($id);
					if ($id) {
						if (!isset($extended['moduls'][$id])) {
							$extended['moduls'][$id] = $id;
							
							include_extanded($plugin, 'moduls', $id);
							
							do_action('all_moduls_active_' . $id);
							do_action('all_moduls_active', $id);
						}
					}	
				}
				
				update_option('pn_extended', $extended);
				
				$plugin->plugin_create_pages();
							
				$arrs['reply'] = 'true';	
			}
				
			if ('deactive' == $action) {
							
				$extended = get_option('pn_extended');
				if (!is_array($extended)) { $extended = array(); }
								
				foreach ($_POST['id'] as $id) {
					$id = is_extension_name($id);
					if ($id) {
						if (isset($extended['moduls'][$id])) {
							unset($extended['moduls'][$id]);
							
							do_action('all_moduls_deactive_' . $id);
							do_action('all_moduls_deactive', $id);
						}
					}	
				}
				
				update_option('pn_extended', $extended);
							
				$arrs['reply'] = 'true';		
			}				
		}
				
		$url = pn_admin_filter_data('', 'reply, paged');
		$url = add_query_args($arrs, $url);
		wp_redirect($url);
		exit;			
	} 

	add_action('premium_action_pn_moduls_activate', 'def_premium_action_pn_moduls_activate');
	function def_premium_action_pn_moduls_activate() {

		$plugin = get_plugin_class();

		pn_only_caps(array('administrator'));	
		
		$id = is_extension_name(is_param_get('key'));	
		if ($id) {
			
			$extended = get_option('pn_extended');
			if (!is_array($extended)) { $extended = array(); }
			
			if (!isset($extended['moduls'][$id])) {
				$extended['moduls'][$id] = $id;
					
				include_extanded($plugin, 'moduls', $id);
				
				do_action('all_moduls_active_' . $id);
				do_action('all_moduls_active', $id);
			}	

			update_option('pn_extended', $extended);
			
			$plugin->plugin_create_pages();
		}
		
		$url = pn_admin_filter_data(urldecode(is_param_get('_wp_http_referer')), 'reply') . '&reply=true';
		wp_redirect($url);
		exit;		
	}

	add_action('premium_action_pn_moduls_deactivate', 'def_premium_action_pn_moduls_deactivate');
	function def_premium_action_pn_moduls_deactivate() {	

		pn_only_caps(array('administrator'));	
		
		$id = is_extension_name(is_param_get('key'));	
		if ($id) {
			
			$extended = get_option('pn_extended');
			if (!is_array($extended)) { $extended = array(); }
			
			if (isset($extended['moduls'][$id])) {
				unset($extended['moduls'][$id]);
				
				do_action('all_moduls_deactive_' . $id);
				do_action('all_moduls_deactive', $id);
			}	

			update_option('pn_extended', $extended);
			
		}
		
		$url = pn_admin_filter_data(urldecode(is_param_get('_wp_http_referer')), 'reply') . '&reply=true';
		wp_redirect($url);
		exit;
	}	
}

if (!class_exists('pn_moduls_Table_List')) {
	class pn_moduls_Table_List extends PremiumTable {

		function __construct() { 
		
			parent::__construct();
				
			$this->primary_column = 'title';
			$this->save_button = 0;
			$this->count_items = 50;
			
		}
		
		function column_default($item, $column_name) {
			
			if ('descr' == $column_name) {
				$html = '
					<div>' . pn_strip_input(ctv_ml($item['description'])) . '</div>
					<div class="modul_vers">' . __('Version', 'pn') . ': ' . pn_strip_input($item['version']) . '</div>
				';
				return $html;
			} elseif ('title' == $column_name) {	
				return '<strong>' . pn_strip_input(ctv_ml($item['title'])) . '</strong>';			
			} elseif ('category' == $column_name) {	
				return '<a href="' . admin_url('admin.php?page=pn_moduls&cat=' . is_isset($item, 'cat')) . '&place=' . is_extension_name(is_param_get('place')) . '&filter=' . intval(is_param_get('filter')) . '">' . pn_strip_input(ctv_ml($item['category'])) . '</a>';
			} elseif ('place' == $column_name) {	
				$place = __('Plugin', 'pn');
				if ('theme' == is_isset($item, 'place')) {
					$place = __('Theme', 'pn');
				}
				return '<a href="' . admin_url('admin.php?page=pn_moduls&place=' . is_isset($item, 'place')) . '&cat=' . is_extension_name(is_param_get('cat')) . '&filter=' . intval(is_param_get('filter')) . '">' . $place . '</a>';		
			} elseif ('dependent' == $column_name) {	
				return pn_strip_input($item['dependent']);
			} elseif ('name' == $column_name) {	
				$name = is_extension_name($item['name']);
				$name = str_replace('_theme', '', $name);
				return $name;
			}
			
			return '';
		}	
		
		function column_cb($item) {
			return '<input type="checkbox" name="id[]" class="pntable-checkbox-single" autocomplete="off" value="' . $item['name'] . '" />';              
		}		

		function get_row_actions($item) {
			
			$actions = array();
			if (current_user_can('administrator')) {
				if ('active' == $item['status']) {
					$actions['deactive']  = '<a href="' . pn_link('pn_moduls_deactivate', 'get') . '&key=' . $item['name'] . '&_wp_http_referer=' . urlencode($_SERVER['REQUEST_URI']) . '">' . __('Deactivate', 'pn') . '</a>';
				} else {
					$actions['active']  = '<a href="' . pn_link('pn_moduls_activate', 'get') . '&key=' . $item['name'] . '&_wp_http_referer=' . urlencode($_SERVER['REQUEST_URI']) . '">' . __('Activate', 'pn') . '</a>';
				}
			}
			
			return $actions;
		}	
		
		function get_columns() {
			
			$columns = array(
				'cb'        => '',
				'title'     => __('Title', 'pn'),
				'descr'     => __('Description', 'pn'),
				'category'     => __('Category', 'pn'),
				'place'     => __('Location', 'pn'),
				'name'     => __('Folder name', 'pn'),
				'dependent'     => __('Dependent modules', 'pn'),
			);
			
			return $columns;
		}	
		
		function get_search() {
			
			$search = array();
			
			$list = pn_list_extended('moduls');
			
			$cats = array('0' => '--' . __('All categories', 'pn') . '--');
			foreach ($list as $data) {
				$c = is_extension_name($data['cat']);
				$n = pn_strip_input(ctv_ml($data['category']));
				if ($c and $n) {
					$cats[$c] = $n;
				}
			}
			asort($cats);
			
			$search['cat'] = array(
				'view' => 'select',
				'options' => $cats,
				'title' => __('Module categories', 'pn'),
				'default' => is_extension_name(is_param_get('cat')),
				'name' => 'cat',
			);
			
			$placed = array(
				'0' => '--' . __('All locations', 'pn') . '--',
				'plugin' => __('Plugin', 'pn'),
				'theme' => __('Theme', 'pn'),
			);
			$search['place'] = array(
				'view' => 'select',
				'options' => $placed,
				'title' => __('Module locations', 'pn'),
				'default' => is_extension_name(is_param_get('place')),
				'name' => 'place',
			);
			$search['title'] = array(
				'view' => 'input',
				'title' => __('Title', 'pn'),
				'default' => pn_strip_input(is_param_get('title')),
				'name' => 'title',
			);			
				
			return $search;
		}
			
		function get_submenu() { 
		
			$options = array();
			$options['filter'] = array(
				'options' => array(
					'1' => __('active modules', 'pn'),
					'2' => __('inactive modules', 'pn'),
					'3' => __('recently active modules', 'pn'),
					'4' => __('new modules', 'pn'),
				),
			);
			
			return $options;
		}

		function tr_class($tr_class, $item) {
			
			if (1 == $item['new']) {
				$tr_class[] = 'tr_green';
			} 			
			if ('active' != $item['status']) {
				$tr_class[] = 'tr_red';
			}	
			
			return $tr_class;
		}			

		function get_bulk_actions() {
			
			$actions = array();
			if (current_user_can('administrator')) {
				$actions = array(
					'active'    => __('Activate', 'pn'),
					'deactive'    => __('Deactivate', 'pn'),
				);
			}
			
			return $actions;
		}
		
		function prepare_items() {
			
			$per_page = $this->count_items();
			$current_page = $this->get_pagenum();
			$offset = $this->get_offset();

			$pn_extended_last = get_option('pn_extended_last');
			$extended_last = is_isset($pn_extended_last, 'moduls');

			$now_time = current_time('timestamp');
			$now_time_check = $now_time - (24 * 60 * 60);

			$list = pn_list_extended('moduls');
			
			$items = array();
			$filter = intval(is_param_get('filter'));
			$cat = is_extension_name(is_param_get('cat'));
			$place = is_extension_name(is_param_get('place'));
			$title = mb_strtolower(pn_strip_input(is_param_get('title')));
			
			foreach ($list as $list_key => $list_value) {
				$module_status = $list_value['status'];
				$module_category = is_extension_name($list_value['cat']);
				$module_place = $list_value['place'];
				$module_new = intval($list_value['new']);
				
				$time_deactive = extended_time_deactive($extended_last, $list_key, $list_value['old_names']);
				
				$show = 0;
				
				if (1 == $filter) {
					if ('active' == $module_status) {
						$show = 1;
					}
				} elseif (2 == $filter) {
					if ('deactive' == $module_status) {
						$show = 1;
					}	
				} elseif (3 == $filter) {
					if ('deactive' == $module_status and $time_deactive > $now_time_check) {
						$show = 1;
					}
				} elseif (4 == $filter) {
					if (1 == $module_new) {
						$show = 1;
					}	
				} else {
					$show = 1;
				}
				
				if (strlen($title) > 0) {
					$module_title = mb_strtolower(ctv_ml(is_isset($list_value, 'title')));
					$module_name = mb_strtolower(is_isset($list_value, 'name'));
					$modul_search = $module_title . ' ' . $module_name;
					if (!strstr($modul_search, $title)) {
						$show = 0;
					}
				}
				
				if ($cat) {
					if ($module_category != $cat) {
						$show = 0;
					}
				}

				if ($place) {
					if ($module_place != $place) {
						$show = 0;
					}
				}			
				
				if (1 == $show) {
					$items[] = $list_value;
				}
			}
			
			if ($this->navi) {
				$this->total_items = count($items);
			}
			
			$this->items = array_slice($items, $offset, $per_page);
		}	
	} 
}