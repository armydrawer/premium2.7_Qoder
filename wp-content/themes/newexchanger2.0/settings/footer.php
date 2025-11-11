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
	$options['timetable'] = array(
		'view' => 'textarea',
		'title' => __('Timetable','pntheme'),
		'default' => is_isset($change,'timetable'),
		'name' => 'timetable',
		'rows' => '8',
		'work' => 'text',
		'ml' => 1,
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
	//$options['f_logo'] = array(
    //    'view' => 'uploader',
    //    'title' => __('Footer logo', 'pntheme'),
    //    'default' => is_isset($change,'f_logo'),
    //    'name' => 'f_logo',
    //    'work' => 'input',
    //);
	$options['line1'] = array(
		'view' => 'line',
	);
	$options['contacts'] = array(
        'view' => 'h3',
        'title' => __('Contacts','pntheme'),
        'submit' => __('Save','pntheme'),
    );
	$options['tm'] = array(
        'view' => 'inputbig',
        'title' => __('Telegram title', 'pntheme'),
        'default' => is_isset($change,'tm'),
        'name' => 'tm',
        'work' => 'input',
		'ml' => 1,
    );
	$options['tm_link'] = array(
        'view' => 'inputbig',
        'title' => __('Telegram link','pntheme'),
        'default' => is_isset($change,'tm_link'),
        'name' => 'tm_link',
        'work' => 'input',
    );
	$options['tm2'] = array(
        'view' => 'inputbig',
        'title' => __('Telegram title 2', 'pntheme'),
        'default' => is_isset($change,'tm2'),
        'name' => 'tm2',
        'work' => 'input',
		'ml' => 1,
    );
	$options['tm2_link'] = array(
        'view' => 'inputbig',
        'title' => __('Telegram link 2','pntheme'),
        'default' => is_isset($change,'tm2_link'),
        'name' => 'tm2_link',
        'work' => 'input',
    );
    //$options['tm3'] = array(
    //    'view' => 'inputbig',
    //    'title' => __('Telegram title 3', 'pntheme'),
    //    'default' => is_isset($change,'tm3'),
    //    'name' => 'tm3',
    //    'work' => 'input',
    //    'ml' => 1,
    //);
    //$options['tm3_link'] = array(
    //    'view' => 'inputbig',
    //    'title' => __('Telegram link 3','pntheme'),
    //    'default' => is_isset($change,'tm3_link'),
    //    'name' => 'tm3_link',
    //    'work' => 'input',
    //);
    $options['email'] = array(
        'view' => 'inputbig',
        'title' => __('E-mail', 'pntheme'),
        'default' => is_isset($change,'email'),
        'name' => 'email',
        'work' => 'input',
        'ml' => 1,
    );
    //$options['dzen'] = array(
    //    'view' => 'inputbig',
    //    'title' => __('Dzen', 'pntheme'),
    //    'default' => is_isset($change,'dzen'),
    //    'name' => 'dzen',
    //    'work' => 'input',
    //    'ml' => 1,
    //);
    //$options['dzen_link'] = array(
    //    'view' => 'inputbig',
    //    'title' => __('Dzen link', 'pntheme'),
    //    'default' => is_isset($change,'dzen_link'),
    //    'name' => 'dzen_link',
    //    'work' => 'input',
    //    'ml' => 1,
    //);
    // $options['jabber'] = array(
    //     'view' => 'inputbig',
    //     'title' => __('Jabber', 'pntheme'),
    //     'default' => is_isset($change,'jabber'),
    //     'name' => 'jabber',
    //     'work' => 'input',
    //     'ml' => 1,
    // );
    //$options['phone'] = array(
    //    'view' => 'inputbig',
    //    'title' => __('Phone', 'pntheme'),
    //    'default' => is_isset($change,'phone'),
    //    'name' => 'phone',
    //    'work' => 'input',
    //    'ml' => 1,
    //);
    //$options['icq'] = array(
    //    'view' => 'inputbig',
    //    'title' => __('ICQ', 'pntheme'),
    //    'default' => is_isset($change,'icq'),
    //    'name' => 'icq',
    //    'work' => 'input',
    //    'ml' => 1,
    //);
    $options['skype'] = array(
        'view' => 'inputbig',
        'title' => __('Skype', 'pntheme'),
        'default' => is_isset($change,'skype'),
        'name' => 'skype',
        'work' => 'input',
        'ml' => 1,
    );
    //$options['viber'] = array(
    //    'view' => 'inputbig',
    //    'title' => __('Viber', 'pntheme'),
    //    'default' => is_isset($change,'viber'),
    //    'name' => 'viber',
    //    'work' => 'input',
    //    'ml' => 1,
    //);
    //$options['whatsapp'] = array(
    //    'view' => 'inputbig',
    //    'title' => __('WhatsApp', 'pntheme'),
    //    'default' => is_isset($change,'whatsapp'),
    //    'name' => 'whatsapp',
    //    'work' => 'input',
    //    'ml' => 1,
    //);
    $options['line2'] = array(
        'view' => 'line',
    );
	$options['social'] = array(
        'view' => 'h3',
        'title' => __('Social media','pntheme'),
        'submit' => __('Save','pntheme'),
    );
    //$options['in'] = array(
    //    'view' => 'inputbig',
    //    'title' => __('LinkedIn','pntheme'),
    //    'default' => is_isset($change,'in'),
    //    'name' => 'in',
    //    'work' => 'input',
    //);
	//$options['yt'] = array(
	//	'view' => 'inputbig',
	//	'title' => __('YouTube', 'pntheme'),
	//	'default' => is_isset($change,'YouTube'),
	//	'name' => 'yt',
	//	'work' => 'input',
	//	'ml' => 1,
	//);
	$options['tg'] = array(
		'view' => 'inputbig',
		'title' => __('Telegram','pntheme'),
		'default' => is_isset($change,'tg'),
		'name' => 'tg',
		'work' => 'input',
	);
	$options['vk'] = array(
		'view' => 'inputbig',
		'title' => __('Vk', 'pntheme'),
		'default' => is_isset($change,'vk'),
		'name' => 'vk',
		'work' => 'input',
		'ml' => 1,
	);
	$options['fb'] = array(
		'view' => 'inputbig',
		'title' => __('Facebook', 'pntheme'),
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
	$options['ins'] = array(
		'view' => 'inputbig',
		'title' => __('Instagram', 'pntheme'),
		'default' => is_isset($change,'ins'),
		'name' => 'ins',
		'work' => 'input',
		'ml' => 1,
	);

	$help = '
	<p>'. __('If you plan to use links as social buttons, use the following shortcode','pntheme') .'</p>
	<p><input type="text" name="" value="[soc_link]" onclick="this.select()" /></p>';
	$options['newpanel_help'] = array(
		'view' => 'help',
		'title' => __('Info','pntheme'),
		'default' => $help,
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
	$change['tm'] = $data['tm'];
	$change['tm2'] = $data['tm2'];
	$change['tm3'] = $data['tm3'];
	$change['tm_link'] = $data['tm_link'];
	$change['tm2_link'] = $data['tm2_link'];
	$change['tm3_link'] = $data['tm3_link'];
	$change['email'] = $data['email'];
	$change['phone'] = $data['phone'];
	$change['jabber'] = $data['jabber'];
	$change['icq'] = $data['icq'];
	$change['skype'] = $data['skype'];
	$change['viber'] = $data['viber'];
	$change['whatsapp'] = $data['whatsapp'];

	$change['in'] = $data['in'];
	$change['yt'] = $data['yt'];
	$change['ins'] = $data['ins'];
	$change['fb'] = $data['fb'];
	$change['tw'] = $data['tw'];
	$change['tg'] = $data['tg'];
	$change['vk'] = $data['vk'];

	$change['dzen'] = $data['dzen'];
	$change['dzen_link'] = $data['dzen_link'];

    $change['f_logo'] = $data['f_logo'];





	update_option('f_change',$change);

	$back_url = is_param_post('_wp_http_referer');
	$back_url = add_query_args(array('reply'=>'true'), $back_url);

	$form->answer_form($back_url);
}
