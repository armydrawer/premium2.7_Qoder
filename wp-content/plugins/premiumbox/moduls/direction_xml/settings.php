<?php
if (!defined('ABSPATH')) { exit(); }

if (is_admin()) {

	add_action('admin_menu', 'admin_menu_txtxml', 500);
	function admin_menu_txtxml() {
		global $premiumbox;	
		
		add_submenu_page("pn_config", __('TXT and XML export settings', 'pn'), __('TXT and XML export settings', 'pn'), 'administrator', "pn_txtxml", array($premiumbox, 'admin_temp'));
		
	}
	
	add_filter('pn_adminpage_title_pn_txtxml', 'def_adminpage_title_pn_txtxml');
	function def_adminpage_title_pn_txtxml($page) {
		
		return __('TXT and XML export settings', 'pn');
	} 	
	 
	add_action('pn_adminpage_content_pn_txtxml','def_adminpage_content_pn_txtxml');
	function def_adminpage_content_pn_txtxml() {
		global $wpdb, $premiumbox;

		$form = new PremiumForm();

		$options = array();
		$options['top_title'] = array(
			'view' => 'h3',
			'title' => __('TXT and XML export settings', 'pn'),
			'submit' => __('Save', 'pn'),
		);
		$options['alias'] = array(
			'view' => 'input',
			'title' => __('Count alias', 'pn'),
			'default' => $premiumbox->get_option('txtxml', 'alias'),
			'name' => 'alias',
		);
		$options['line_alias'] = array(
			'view' => 'line',
		);
		$options['txt'] = array(
			'view' => 'select',
			'title' => __('TXT file', 'pn'),
			'options' => array('0' => __('No', 'pn'), '1' => __('Yes', 'pn')),
			'default' => $premiumbox->get_option('txtxml', 'txt'),
			'name' => 'txt',
		);
		$options['site_txt'] = array(
			'view' => 'select',
			'title' => __('Show link to TXT file on site', 'pn'),
			'options' => array('0' => __('No', 'pn'), '1' => __('Yes', 'pn')),
			'default' => $premiumbox->get_option('txtxml', 'site_txt'),
			'name' => 'site_txt',
		);	
		$options['line_txt'] = array(
			'view' => 'line',
		);
		$options['xml'] = array(
			'view' => 'select',
			'title' => __('XML file', 'pn'),
			'options' => array('0' => __('No', 'pn'), '1' => __('Yes', 'pn')),
			'default' => $premiumbox->get_option('txtxml', 'xml'),
			'name' => 'xml',
		);	
		$options['site_xml'] = array(
			'view' => 'select',
			'title' => __('Show link to XML file on site', 'pn'),
			'options' => array('0' => __('No', 'pn'), '1' => __('Yes', 'pn')),
			'default' => $premiumbox->get_option('txtxml', 'site_xml'),
			'name' => 'site_xml',
		);
		$options['line_xml'] = array(
			'view' => 'line',
		);
		$options['newxml'] = array(
			'view' => 'select',
			'title' => __('new XML file', 'pn'),
			'options' => array('0' => __('No', 'pn'), '1' => __('Yes', 'pn')),
			'default' => $premiumbox->get_option('txtxml', 'newxml'),
			'name' => 'newxml',
		);	
		$options['site_newxml'] = array(
			'view' => 'select',
			'title' => __('Show link to XML 2.0 file on site', 'pn'),
			'options' => array('0' => __('No', 'pn'), '1' => __('Yes', 'pn')),
			'default' => $premiumbox->get_option('txtxml', 'site_newxml'),
			'name' => 'site_newxml',
		);
		$options['line_newxml'] = array(
			'view' => 'line',
		);		
		$options['json'] = array(
			'view' => 'select',
			'title' => __('JSON file', 'pn'),
			'options' => array('0' => __('No', 'pn'), '1' => __('Yes', 'pn')),
			'default' => $premiumbox->get_option('txtxml', 'json'),
			'name' => 'json',
		);	
		$options['site_json'] = array(
			'view' => 'select',
			'title' => __('Show link to JSON file on site', 'pn'),
			'options' => array('0' => __('No', 'pn'), '1' => __('Yes', 'pn')),
			'default' => $premiumbox->get_option('txtxml', 'site_json'),
			'name' => 'site_json',
		);	
		$options['line_json'] = array(
			'view' => 'line',
		);	
		$options['filehash'] = array(
			'view' => 'inputbig',
			'title' => __('Add personal hash to URL of files with exchange rates', 'pn'),
			'default' => $premiumbox->get_option('txtxml', 'filehash'),
			'name' => 'filehash',
		);	
		$options['create'] = array(
			'view' => 'select',
			'title' => __('Static file with exchange rates', 'pn'),
			'options' => array('0'=> __('No', 'pn'), '1' => __('Yes', 'pn')),
			'default' => $premiumbox->get_option('txtxml', 'create'),
			'name' => 'create',
		);	
		$options['live_help'] = array(
			'view' => 'help',
			'title' => __('More info', 'pn'),
			'default' => __('File contains static data. File will be updated only when changing the characteristics of the exchange direction. For example, when changing the exchange rate or reserve and etc.', 'pn'),
		);	
		$options['line2'] = array(
			'view' => 'line',
		);	
		$options['fromfee'] = array(
			'view' => 'select',
			'title' => __('For fromfee parameter, pass value', 'pn'),
			'options' => array('0' => __('additional fee of exchange office charged from sender', 'pn'), '1' => __('payment system fee for wallet', 'pn'), '2' => __('payment system fee for a verified wallet', 'pn')),
			'default' => $premiumbox->get_option('txtxml', 'fromfee'),
			'name' => 'fromfee',
		);
		$options['tofee'] = array(
			'view' => 'select',
			'title' => __('For tofee parameter, pass value', 'pn'),
			'options' => array('0' => __('additional fee of exchange office charged from recipient', 'pn'), '1' => __('payment system fee for wallet', 'pn'), '2' => __('payment system fee for a verified wallet', 'pn')),
			'default' => $premiumbox->get_option('txtxml','tofee'),
			'name' => 'tofee',
		);
		$options['line3'] = array(
			'view' => 'line',
		);		
		$options['decimal_with'] = array(
			'view' => 'select',
			'title' => __('Number of simbols after comma', 'pn'),
			'options' => array('0' => __('depending on currency settings', 'pn'), '1' => __('depending on setting below', 'pn')),
			'default' => $premiumbox->get_option('txtxml', 'decimal_with'),
			'name' => 'decimal_with',
		);
		$options['decimal'] = array(
			'view' => 'input',
			'title' => __('Number of simbols after comma (forcibly)', 'pn'),
			'default' => $premiumbox->get_option('txtxml', 'decimal'),
			'name' => 'decimal',
		);
		$options['line_exclude'] = array(
			'view' => 'line',
		);	
		$options['exclude_currency_give'] = array(
			'view' => 'select',
			'title' => __('Exclude the currency I give from the fields if it matches the field.', 'pn'),
			'options' => array('0' => __('No', 'pn'), '1' => __('Yes', 'pn')),
			'default' => $premiumbox->get_option('txtxml', 'exclude_currency_give'),
			'name' => 'exclude_currency_give',
		);		
		$params_form = array(
			'filter' => 'pn_txtxml_option',
			'button_title' => __('Save', 'pn'),
		);
		$form->init_form($params_form, $options);
		
	} 
	
}	
	
add_action('premium_action_pn_txtxml', 'premium_action_pn_txtxml');
function premium_action_pn_txtxml() {
	global $wpdb, $premiumbox;	

	_method('post');
		
	$form = new PremiumForm();
	$form->send_header();
		
	pn_only_caps(array('administrator'));
		
	$options = array('create', 'decimal_with', 'decimal', 'txt', 'xml', 'newxml', 'json', 'site_txt', 'site_xml', 'site_newxml', 'site_json', 'fromfee', 'tofee', 'alias', 'exclude_currency_give');		
	foreach ($options as $key) {
		$val = intval(is_param_post($key));
		$premiumbox->update_option('txtxml', $key, $val);
	}				
		
	$val = pn_strip_symbols(replace_cyr(is_param_post('filehash')));
	$premiumbox->update_option('txtxml', 'filehash', $val);

	do_action('pn_txtxml_option_post');
		
	$url = admin_url('admin.php?page=pn_txtxml&reply=true');
	$form->answer_form($url);
		
}

add_action('pn_adminpage_content_pn_txtxml', 'txtxml_pn_admin_content_pn_txtxml', 0);
function txtxml_pn_admin_content_pn_txtxml() {
	global $premiumbox;
		
	$form = new PremiumForm();

	$links = array();
	$show_files = apply_filters('show_txtxml_files', $premiumbox->get_option('txtxml', 'txt'), 'txt');
	if (1 == $show_files) {
		$links['txt'] = array(
			'url' => get_expfile_url('txt', 'txt'),
			'title' => 'TXT',
		);
	}
	$show_files = apply_filters('show_txtxml_files', $premiumbox->get_option('txtxml', 'xml'), 'xml');
	if (1 == $show_files) {	
		$links['xml'] = array(
			'url' => get_expfile_url('xml', 'xml'),
			'title' => 'XML',
		);
	}
	$show_files = apply_filters('show_txtxml_files', $premiumbox->get_option('txtxml', 'newxml'), 'newxml');
	if (1 == $show_files) {	
		$links['newxml'] = array(
			'url' => get_expfile_url('newxml', 'xml'),
			'title' => 'new XML',
		);
	}	
	$show_files = apply_filters('show_txtxml_files', $premiumbox->get_option('txtxml', 'json'), 'json');
	if (1 == $show_files) {	
		$links['json'] = array(
			'url' => get_expfile_url('json', 'json'),
			'title' => 'JSON',		
		);
	} 
	if (count($links) > 0) {
		$text = '<div>' . __('Links to files containing rates', 'pn') . ': </div>';
		foreach ($links as $link) {
			$text .= '<div><a href="' . is_isset($link, 'url') . '" target="_blank">' . is_isset($link, 'url') . '</a></div>';
		}
		$form->substrate($text);
	}

}

add_filter('account_list_pages', 'pn_account_list_pages');
function pn_account_list_pages($list_pages) {	
	global $wpdb, $premiumbox;	
		
	$lang_key = '';
	if (is_ml()) {
		$lang = get_locale();
		$lang_key = get_lang_key($lang);	
	}
		
	$show_files = apply_filters('show_txtxml_files', $premiumbox->get_option('txtxml', 'txt'), 'txt');
	if (1 == $show_files) {
		if (1 == $premiumbox->get_option('txtxml', 'site_txt')) {
			$list_pages['exporttxt'] = array(
				'title' => __('TXT file containing rates', 'pn'),
				'url' => get_expfile_url('txt', 'txt', 0, $lang_key),
				'type' => 'target_link',
			);
		}		
	}
	$show_files = apply_filters('show_txtxml_files', $premiumbox->get_option('txtxml', 'xml'), 'xml');
	if (1 == $show_files) {
		if (1 == $premiumbox->get_option('txtxml', 'site_xml')) {
			$list_pages['exportxml'] = array(
				'title' => __('XML file containing rates', 'pn'),
				'url' => get_expfile_url('xml', 'xml', 0, $lang_key),
				'type' => 'target_link',
			);
		}		
	}
	$show_files = apply_filters('show_txtxml_files', $premiumbox->get_option('txtxml', 'newxml'), 'newxml');
	if (1 == $show_files) {
		if (1 == $premiumbox->get_option('txtxml', 'site_newxml')) {
			$list_pages['exportnewxml'] = array(
				'title' => __('XML file containing rates', 'pn'),
				'url' => get_expfile_url('newxml', 'xml', 0, $lang_key),
				'type' => 'target_link',
			);
		}		
	}	
 	$show_files = apply_filters('show_txtxml_files', $premiumbox->get_option('txtxml', 'json'), 'json');
	if (1 == $show_files) {
		if (1 == $premiumbox->get_option('txtxml', 'site_json')) {
			$list_pages['exportjson'] = array(
				'title' => __('JSON file containing rates', 'pn'),
				'url' => get_expfile_url('json', 'json', 0, $lang_key),
				'type' => 'target_link',
			);
		}		
	}		
		
	return $list_pages;
}	