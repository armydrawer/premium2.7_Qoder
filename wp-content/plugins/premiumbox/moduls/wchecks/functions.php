<?php
if (!defined('ABSPATH')) { exit(); }

function wchecks_setting_list($data, $db_data, $place) {
	
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
	$options = apply_filters('_wchecks_options', $options, $db_data->ext_plugin, $data, $db_data->ext_key, $place);	
	
	return $options;
}

if (!class_exists('Ext_Wchecks_Premiumbox')) {
	class Ext_Wchecks_Premiumbox extends Ext_Premium {

		function __construct($file, $title)
		{
			global $premiumbox;
			parent::__construct($file, $title, 'wchecks', $premiumbox);

			add_filter('_wchecks_options', array($this, 'get_options'), 10, 5);
			add_filter('set_check_account_give', array($this, 'set_check_account'), 10, 3);
			add_filter('set_check_account_get', array($this, 'set_check_account'), 10, 3);
			add_filter('check_purse_text_give', array($this, 'check_purse_text'), 10, 2);
			add_filter('check_purse_text_get', array($this, 'check_purse_text'), 10, 2);
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
		
		public function check_purse_text($text, $check_id) {
			
			return $text;
		}	

		public function set_check_account($ind, $purse, $check_id) {
			
			return $ind;
		}	

	}
}