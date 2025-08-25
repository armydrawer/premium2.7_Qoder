<?php
if( !defined( 'ABSPATH')){ exit(); }

add_action('admin_menu', 'admin_menu_theme_footer');
function admin_menu_theme_footer(){
	$plugin = get_plugin_class();

	add_submenu_page("themes.php", __('Footer','pntheme'), __('Footer','pntheme'), 'administrator', "pn_theme_footer", array($plugin, 'admin_temp'));
}

add_filter('pn_adminpage_title_pn_theme_footer', 'def_adminpage_title_pn_theme_footer');
function def_adminpage_title_pn_theme_footer($page){
	return __('Footer','pntheme');
}

add_filter('pn_theme_footer_option', 'def_pn_theme_footer_option', 1);
function def_pn_theme_footer_option($options){
global $wpdb;

	$change = get_option('f_change');

	$options['top_title'] = array(
		'view' => 'h3',
		'title' => __('Footer','pntheme'),
		'submit' => __('Save','pntheme'),
	);
	$options['ctext'] = array(
		'view' => 'textarea',
		'title' => __('Copywriting','pntheme'),
		'default' => is_isset($change,'ctext'),
		'name' => 'ctext',
		'rows' => '8',
		'work' => 'text',
		'ml' => 1,
	);
	$options['timetable'] = array(
		'view' => 'textarea',
		'title' => __('Timetable','pntheme'),
		'default' => is_isset($change,'timetable'),
		'name' => 'timetable',
		'rows' => '8',
		'work' => 'text',
		'ml' => 1,
	);
	$options['line1'] = array(
		'view' => 'line',
	);
	$options['contacts'] = array(
        'view' => 'h3',
        'title' => __('Contacts','pntheme'),
        'submit' => __('Save','pntheme'),
    );
    $options['tm_name'] = array(
        'view' => 'inputbig',
        'title' => __('Telegram name','pntheme'),
        'default' => is_isset($change,'tm_name'),
        'name' => 'tm_name',
        'work' => 'input',
        'ml' => 1,
    );
	$options['tm'] = array(
		'view' => 'inputbig',
		'title' => sprintf(__('Link to %s','pntheme'), 'Telegram'),
		'default' => is_isset($change,'tm'),
		'name' => 'tm',
		'work' => 'input',
		'ml' => 1,
	);
	$options['email'] = array(
		'view' => 'inputbig',
		'title' => __('E-mail', 'pntheme'),
		'default' => is_isset($change,'email'),
		'name' => 'email',
		'work' => 'input',
		'ml' => 1,
	);
	$options['jabber'] = array(
		'view' => 'inputbig',
		'title' => __('Jabber', 'pntheme'),
		'default' => is_isset($change,'jabber'),
		'name' => 'jabber',
		'work' => 'input',
		'ml' => 1,
	);
	$options['phone'] = array(
		'view' => 'inputbig',
		'title' => __('Phone', 'pntheme'),
		'default' => is_isset($change,'phone'),
		'name' => 'phone',
		'work' => 'input',
		'ml' => 1,
	);
	$options['icq'] = array(
		'view' => 'inputbig',
		'title' => __('ICQ', 'pntheme'),
		'default' => is_isset($change,'icq'),
		'name' => 'icq',
		'work' => 'input',
		'ml' => 1,
	);
	$options['skype'] = array(
		'view' => 'inputbig',
		'title' => __('Skype', 'pntheme'),
		'default' => is_isset($change,'skype'),
		'name' => 'skype',
		'work' => 'input',
		'ml' => 1,
	);
	$options['viber'] = array(
		'view' => 'inputbig',
		'title' => __('Viber', 'pntheme'),
		'default' => is_isset($change,'viber'),
		'name' => 'viber',
		'work' => 'input',
		'ml' => 1,
	);
	$options['whatsapp'] = array(
		'view' => 'inputbig',
		'title' => __('WhatsApp', 'pntheme'),
		'default' => is_isset($change,'whatsapp'),
		'name' => 'whatsapp',
		'work' => 'input',
		'ml' => 1,
	);
    $options['line2'] = array(
        'view' => 'line',
    );
	$options['social'] = array(
        'view' => 'h3',
        'title' => __('Social media','pntheme'),
        'submit' => __('Save','pntheme'),
    );
     $options['tm_soc'] = array(
        'view' => 'inputbig',
        'title' => __('Telegram','pntheme'),
        'default' => is_isset($change,'tm_soc'),
        'name' => 'tm_soc',
        'work' => 'input',
        'ml' => 1,
    );
	$options['vk'] = array(
		'view' => 'inputbig',
		'title' => __('VK', 'pntheme'),
		'default' => is_isset($change,'vk'),
		'name' => 'vk',
		'work' => 'input',
		'ml' => 1,
	);
	$options['ins'] = array(
		'view' => 'inputbig',
		'title' => __('IG', 'pntheme'),
		'default' => is_isset($change,'ins'),
		'name' => 'ins',
		'work' => 'input',
		'ml' => 1,
	);
	$options['fb'] = array(
		'view' => 'inputbig',
		'title' => __('FB', 'pntheme'),
		'default' => is_isset($change,'fb'),
		'name' => 'fb',
		'work' => 'input',
		'ml' => 1,
	);
	$options['tw'] = array(
		'view' => 'inputbig',
		'title' => __('X', 'pntheme'),
		'default' => is_isset($change,'tw'),
		'name' => 'tw',
		'work' => 'input',
		'ml' => 1,
	);

	return $options;
}

add_action('pn_adminpage_content_pn_theme_footer','def_pn_adminpage_content_pn_theme_footer');
function def_pn_adminpage_content_pn_theme_footer(){

	$form = new PremiumForm();
	$params_form = array(
		'filter' => 'pn_theme_footer_option',
	);
	$form->init_form($params_form);

}

add_action('premium_action_pn_theme_footer','def_premium_action_pn_theme_footer');
function def_premium_action_pn_theme_footer(){
global $wpdb;

	_method('post');
	pn_only_caps(array('administrator'));

	$form = new PremiumForm();
	$form->send_header();

	$data = $form->strip_options('pn_theme_footer_option', 'post');

	$change = get_option('f_change');
	if(!is_array($change)){ $change = array(); }

	$change['ctext'] = $data['ctext'];
	$change['timetable'] = $data['timetable'];
	$change['tm_name'] = $data['tm_name'];
	$change['tm'] = $data['tm'];
	$change['email'] = $data['email'];
	$change['jabber'] = $data['jabber'];
	$change['phone'] = $data['phone'];
	$change['icq'] = $data['icq'];
	$change['skype'] = $data['skype'];
	$change['viber'] = $data['viber'];
	$change['whatsapp'] = $data['whatsapp'];

    $change['tm_soc'] = $data['tm_soc'];
	$change['vk'] = $data['vk'];
	$change['ins'] = $data['ins'];
	$change['fb'] = $data['fb'];
	$change['tw'] = $data['tw'];






	update_option('f_change',$change);

	$back_url = is_param_post('_wp_http_referer');
	$back_url = add_query_args(array('reply'=>'true'), $back_url);

	$form->answer_form($back_url);
}
