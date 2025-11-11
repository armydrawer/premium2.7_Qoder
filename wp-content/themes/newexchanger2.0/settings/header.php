<?php
if( !defined( 'ABSPATH')){ exit(); }

add_action('admin_menu', 'admin_menu_theme_header');
function admin_menu_theme_header(){
	$plugin = get_plugin_class();

	add_submenu_page("themes.php", __('Header','pntheme'), __('Header','pntheme'), 'administrator', "pn_theme_header", array($plugin, 'admin_temp'));
}

add_filter('pn_adminpage_title_pn_theme_header', 'def_adminpage_title_pn_theme_header');
function def_adminpage_title_pn_theme_header($page){
	return __('Header','pntheme');
}

add_filter('pn_theme_header_option', 'def_pn_theme_header_option', 1);
function def_pn_theme_header_option($options){

	$change = get_option('h_change');

	$options['top_title'] = array(
		'view' => 'h3',
		'title' => __('Header','pntheme'),
		'submit' => __('Save','pntheme'),
	);
	$options['switcher'] = array(
        'view' => 'select',
        'title' => __('Color scheme switcher','pntheme'),
        'options' => array('0'=>__('Only light','pntheme'), '1'=>__('Only dark','pntheme'), '2'=>__('Switcher','pntheme')),
        'default' => is_isset($change,'switcher'),
        'name' => 'switcher',
        'work' => 'int',
    );
	$options['fixheader'] = array(
		'view' => 'select',
		'title' => __('To fix','pntheme'),
		'options' => array('0'=>__('nothing','pntheme'), '1'=>__('header','pntheme')),
		'default' => is_isset($change,'fixheader'),
		'name' => 'fixheader',
		'work' => 'int',
	);
	$options['linkhead'] = array(
		'view' => 'select',
		'title' => __('Logo link','pntheme'),
		'options' => array('0'=>__('always','pntheme'), '1'=>__('with the exception of homepage','pntheme')),
		'default' => is_isset($change,'linkhead'),
		'name' => 'linkhead',
		'work' => 'int',
	);
	$options['hideloginbutton'] = array(
		'view' => 'select',
		'title' => __('Hide login/register link','pntheme'),
		'options' => array('0'=>__('No','pntheme'), '1'=>__('Yes','pntheme')),
		'default' => is_isset($change,'hideloginbutton'),
		'name' => 'hideloginbutton',
		'work' => 'int',
	);
	//$options['logo-mobile'] = array(
    //    'view' => 'uploader',
    //    'title' => __('Mobile logo', 'pntheme'),
    //    'default' => is_isset($change, 'logo-mobile'),
    //    'name' => 'logo-mobile',
    //    'work' => 'input',
    //);
	$options['line1'] = array(
		'view' => 'line',
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
	$options['line2'] = array(
		'view' => 'line',
	);
    $options['telegram__title1'] = array(
        'view' => 'h3',
        'title' => __('Telegram','pntheme'),
        'submit' => __('Save','pntheme'),
    );
    $options['telegram'] = array(
        'view' => 'inputbig',
        'title' => __('Telegram title', 'pntheme'),
        'default' => is_isset($change,'telegram'),
        'name' => 'telegram',
        'work' => 'input',
        'ml' => 1,
    );
    $options['telegram_link'] = array(
        'view' => 'inputbig',
        'title' => __('Telegram link', 'pntheme'),
        'default' => is_isset($change,'telegram_link'),
        'name' => 'telegram_link',
        'work' => 'input',
        'ml' => 1,
    );
    //$options['telegram__title2'] = array(
    //    'view' => 'h3',
    //    'title' => __('Telegram 2','pntheme'),
    //    'submit' => __('Save','pntheme'),
    //);
    //$options['telegram2'] = array(
    //    'view' => 'inputbig',
    //    'title' => __('Telegram title', 'pntheme'),
    //    'default' => is_isset($change,'telegram2'),
    //    'name' => 'telegram2',
    //    'work' => 'input',
    //    'ml' => 1,
    //);
    //$options['telegram_link2'] = array(
    //    'view' => 'inputbig',
    //    'title' => __('Telegram link', 'pntheme'),
    //    'default' => is_isset($change,'telegram_link2'),
    //    'name' => 'telegram_link2',
    //    'work' => 'input',
    //    'ml' => 1,
    //);
    //$options['telegram__title3'] = array(
    //    'view' => 'h3',
    //    'title' => __('Telegram 3','pntheme'),
    //    'submit' => __('Save','pntheme'),
    //);
    //$options['telegram3'] = array(
    //    'view' => 'inputbig',
    //    'title' => __('Telegram title', 'pntheme'),
    //    'default' => is_isset($change,'telegram3'),
    //    'name' => 'telegram3',
    //    'work' => 'input',
    //    'ml' => 1,
    //);
    //$options['telegram_link3'] = array(
    //    'view' => 'inputbig',
    //    'title' => __('Telegram link', 'pntheme'),
    //    'default' => is_isset($change,'telegram_link3'),
    //    'name' => 'telegram_link3',
    //    'work' => 'input',
    //    'ml' => 1,
    //);
    $options['line4'] = array(
        'view' => 'line',
    );
    $options['email__title'] = array(
        'view' => 'h3',
        'title' => __('E-mail','pntheme'),
        'submit' => __('Save','pntheme'),
    );
    //$options['email_text'] = array(
    //    'view' => 'inputbig',
    //    'title' => __('E-mail name', 'pntheme'),
    //    'default' => is_isset($change,'email_text'),
    //    'name' => 'email_text',
    //    'work' => 'input',
    //    'ml' => 1,
    //);
    $options['email'] = array(
        'view' => 'inputbig',
        'title' => __('E-mail', 'pntheme'),
        'default' => is_isset($change,'email'),
        'name' => 'email',
        'work' => 'input',
        'ml' => 1,
    );
    $options['line5'] = array(
        'view' => 'line',
    );
    $options['skype__title'] = array(
        'view' => 'h3',
        'title' => __('Skype','pntheme'),
        'submit' => __('Save','pntheme'),
    );
    $options['skype'] = array(
        'view' => 'inputbig',
        'title' => __('Skype', 'pntheme'),
        'default' => is_isset($change,'skype'),
        'name' => 'skype',
        'work' => 'input',
        'ml' => 1,
    );
    //$options['whatsapp__title'] = array(
    //    'view' => 'h3',
    //    'title' => __('Whatsapp','pntheme'),
    //    'submit' => __('Save','pntheme'),
    //);
    //$options['whatsapp'] = array(
    //    'view' => 'inputbig',
    //    'title' => __('Whatsapp', 'pntheme'),
    //    'default' => is_isset($change,'whatsapp'),
    //    'name' => 'whatsapp',
    //    'work' => 'input',
    //    'ml' => 1,
    //);


	return $options;
}

add_action('pn_adminpage_content_pn_theme_header','def_pn_adminpage_content_pn_theme_header');
function def_pn_adminpage_content_pn_theme_header(){

	$form = new PremiumForm();
	$params_form = array(
		'filter' => 'pn_theme_header_option',
	);
	$form->init_form($params_form);

}

add_action('premium_action_pn_theme_header','def_premium_action_pn_theme_header');
function def_premium_action_pn_theme_header(){

	_method('post');

	pn_only_caps(array('administrator'));

	$form = new PremiumForm();
	$form->send_header();

	$data = $form->strip_options('pn_theme_header_option', 'post');

	$change = get_option('h_change');
	if(!is_array($change)){ $change = array(); }

	$change['switcher'] = $data['switcher'];

	$change['fixheader'] = $data['fixheader'];
	$change['linkhead'] = $data['linkhead'];
	$change['hideloginbutton'] = $data['hideloginbutton'];
	$change['timetable'] = $data['timetable'];
	$change['logo-mobile'] = $data['logo-mobile'];

	$change['phone'] = $data['phone'];
	$change['icq'] = $data['icq'];
	$change['skype'] = $data['skype'];
	$change['email'] = $data['email'];
	$change['email_text'] = $data['email_text'];

	$change['support'] = $data['support'];
	$change['telegram'] = $data['telegram'];
	$change['telegram_link'] = str_replace('@','', $data['telegram_link']);
	$change['telegram2'] = $data['telegram2'];
	$change['telegram_link2'] = str_replace('@','', $data['telegram_link2']);
	$change['telegram3'] = $data['telegram3'];
	$change['telegram_link3'] = str_replace('@','', $data['telegram_link3']);

	$change['viber'] = $data['viber'];
	$change['whatsapp'] = $data['whatsapp'];
	$change['jabber'] = $data['jabber'];
	$change['inst'] = $data['inst'];

	//$change['matrix'] = $data['matrix'];

	update_option('h_change',$change);

	$back_url = is_param_post('_wp_http_referer');
	$back_url = add_query_args(array('reply'=>'true'), $back_url);

	$form->answer_form($back_url);

}
