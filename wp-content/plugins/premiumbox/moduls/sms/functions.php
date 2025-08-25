<?php
if (!defined('ABSPATH')) { exit(); }

function sms_setting_list($data, $db_data, $place) {
	
	$options = array();	
	$options['top_title'] = array(
		'view' => 'h3',
		'title' => __('Settings', 'pn'),
		'submit' => __('Save', 'pn'),
	);
	$options['hidden_block'] = array(
		'view' => 'hidden_input',
		'name' => 'item_id',
		'default' => $db_data->id,
	);
	$options = apply_filters('_sms_options', $options, $db_data->ext_plugin, $data, $db_data->ext_key, $place);	
	
	return $options;
}

if (!class_exists('Ext_SmsGate_Premiumbox')) {
	class Ext_SmsGate_Premiumbox extends Ext_Premium {
		
		function __construct($file, $title)
		{
			global $premiumbox;
			parent::__construct($file, $title, 'sms', $premiumbox);
			
			add_filter('_sms_options', array($this, 'get_options'), 10, 5);
			add_filter('pn_sms_send', array($this, 'send_sms'), 10, 3);
		}

		function get_options($options, $name, $data, $id, $place) {
			
			if ($name == $this->name) {
				$options = $this->options($options, $data, $id, $place);
			}
			
			return $options;
		}
		
		function options($options, $data, $id, $place) {
			
			return $options;
		}

		public function send($data, $html, $to) {
			
			return 0;
		}

		public function send_sms($send, $html, $to) {
			
			if (1 != $send) {
				$ids = $this->get_ids('sms', $this->name);
				foreach ($ids as $id) {
					$file_data = $this->get_file_data($id);
					$res = $this->send($file_data, $html, $to);
					if (1 == $res) {
						return 1;
						break;
					}
				}	
			}
			
			return $send;
		}
	}
}	