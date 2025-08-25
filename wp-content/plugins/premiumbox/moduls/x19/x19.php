<?php
if (!defined('ABSPATH')) { exit(); }

if (is_admin()) {

	add_filter('pn_adminpage_title_pn_x19_test', 'def_adminpage_title_pn_x19_test');
	function def_adminpage_title_pn_x19_test($page) {
		
		return __('X19', 'pn');
	} 

	add_action('pn_adminpage_content_pn_x19_test', 'def_adminpage_content_pn_x19_test');
	function def_adminpage_content_pn_x19_test() {	
		global $premiumbox;

		$form = new PremiumForm();

		$options = array();
		$options['top_title'] = array(
			'view' => 'h3',
			'title' => __('WMID ownership verification', 'pn'),
			'submit' => __('Test', 'pn'),
		);
		$options['purse'] = array(
			'view' => 'inputbig',
			'title' => __('Webmoney account', 'pn'),
			'default' => '',
			'name' => 'purse',
		);	
		$params_form = array(
			'filter' => 'pn_x19_config_test',
			'form_link' => pn_link('x19_test_wmid', 'post'),
			'button_title' => __('Test', 'pn'),
		);
		$form->init_form($params_form, $options);	

		$options = array();
		$options['top_title'] = array(
			'view' => 'h3',
			'title' => __('Test X19', 'pn'),
			'submit' => __('Test', 'pn'),
		);
		$options['account1'] = array(
			'view' => 'inputbig',
			'title' => __('Account To send', 'pn'),
			'default' => '',
			'name' => 'account1',
		);
		$options['account2'] = array(
			'view' => 'inputbig',
			'title' => __('Account To receive', 'pn'),
			'default' => '',
			'name' => 'account2',
		);
		$options['last_name'] = array(
			'view' => 'inputbig',
			'title' => __('Last name', 'pn'),
			'default' => '',
			'name' => 'last_name',
		);
		$options['first_name'] = array(
			'view' => 'inputbig',
			'title' => __('First name', 'pn'),
			'default' => '',
			'name' => 'first_name',
		);
		$options['passport'] = array(
			'view' => 'inputbig',
			'title' => __('Passport number', 'pn'),
			'default' => '',
			'name' => 'passport',
		);
		$array = list_x19();	
		$options['mode'] = array(
			'view' => 'select',
			'title' => __('Mode', 'pn'),
			'options' => $array,
			'default' => '',
			'name' => 'mode',
		);	
		$params_form = array(
			'filter' => 'pn_x19_config_test_mod',
			'form_link' => pn_link('x19_test_mod', 'post'),
			'button_title' => __('Test', 'pn'),
		);
		$form->init_form($params_form, $options);

	} 

}	

add_action('premium_action_x19_test_mod', 'def_premium_action_x19_test_mod');
function def_premium_action_x19_test_mod() {
	global $wpdb;	

	_method('post');
		
	$form = new PremiumForm();
	$form->send_header();
		
	pn_only_caps(array('administrator'));

	$x19mod = intval(is_param_post('mode'));
	$passport = pn_maxf_mb(pn_strip_input(is_param_post('passport')), 250);
	if (1 == $x19mod or 6 == $x19mod) {
		if (!$passport) {
			$form->error_form(__('You have not specified passport data', 'pn'));
		}
	}
			
	$account1 = pn_maxf_mb(pn_strip_input(is_param_post('account1')), 250);
	if (!$account1) {
		$form->error_form(__('You have not specified your account Send', 'pn'));
	}
				
	$account2 = pn_maxf_mb(pn_strip_input(is_param_post('account2')), 250);
	if (!$account2) {
		$form->error_form(__('You have not specified your account Receive', 'pn'));
	}
				
	$fname = pn_maxf_mb(pn_strip_input(is_param_post('last_name')), 250);
	$iname = pn_maxf_mb(pn_strip_input(is_param_post('first_name')), 250);
			
	if ($x19mod > 0) {
		$arrwm1 = ind_x19();
					
		if (in_array($x19mod, $arrwm1)) {
			$wmkow = $account1;
			$wmkow2 = $account2;
		} else {
			$wmkow = $account2;
			$wmkow2 = $account1;
		}
					
		$pursetype = 'WM' . mb_strtoupper(mb_substr($account1, 0, 1));
			
		$object = WMXI_X19();
		if (is_object($object)) {
				
			$darr = wmid_with_purse($object, $wmkow);
			$wmid = $darr['wmid'];
				
			if ($wmid) {
		
				if (20 == $x19mod) {
					
					$darr2 = wmid_with_purse($object, $wmkow2);
					$wmid2 = $darr2['wmid'];
					if ($wmid2) {
						if ($wmid != $wmid2) {
							$form->error_form(__('Owner own several accounts', 'pn'));
						} else {
							$form->error_form(__('OK', 'pn'));
						}
					} else {
						$form->error_form($darr2['result']);
					}
						
				} else {	

					$amount = 100; 
					
					$info = info_x19($x19mod, 'Sberbank RF', 'Sberbank RF', $account1, $account2);
							
					try {
						
						$res = $object->X19($info['type'], $info['dir'], $pursetype, $amount, $wmid, $passport, $fname, $iname, $info['bank_name'], $info['bank_account'], $info['card_number'], $info['emoney_name'], $info['emoney_id'], $info['phone'], $info['crypto_name'], $info['crypto_address'])->toArray();
						x19_create_log(0, is_isset($res, 'retdesc'));
						$form->error_form(print_r($res, true));	
						
					} catch(Exception $e){
						$form->error_form(print_r($e->getMessage(), true));
					}	
				}	
			} else {
				$form->error_form($darr['result']);
			}
		} else {
			$form->error_form(__('No access to X19 interface. Check settings', 'pn'));
		}				
	} else {
		$form->error_form('No mode');
	}		
}

add_action('premium_action_x19_test_wmid', 'def_premium_action_x19_test_wmid');
function def_premium_action_x19_test_wmid() {
	global $wpdb;	
	
	_method('post');
		
	$form = new PremiumForm();
	$form->send_header();
		
	pn_only_caps(array('administrator'));
		
	$object = WMXI_X19();
	if (is_object($object)) {
		$purse = pn_maxf_mb(pn_strip_input(is_param_post('purse')), 250);
		$darr = wmid_with_purse($object, $purse);
		$wmid = $darr['wmid'];
		if ($wmid) {
			$form->error_form($wmid, 1);
		} else {
			$form->error_form($darr['result']);
		}
	} else {
		$form->error_form(__('No access to X19 interface. Check settings', 'pn'));
	}	
	
}		