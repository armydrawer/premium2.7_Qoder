<?php
if( !defined( 'ABSPATH')){ exit(); }

add_action('admin_menu', 'admin_menu_theme_home');
function admin_menu_theme_home(){
	$plugin = get_plugin_class();

	add_submenu_page("themes.php", __('Homepage','pntheme'), __('Homepage','pntheme'), 'administrator', "pn_theme_home", array($plugin, 'admin_temp'));
}

add_filter('pn_adminpage_title_pn_theme_home', 'def_adminpage_title_pn_theme_home');
function def_adminpage_title_pn_theme_home($page){
	return __('Homepage','pntheme');
}

add_filter('pn_theme_home_option', 'def_pn_theme_home_option', 1);
function def_pn_theme_home_option($options){
global $wpdb;

	$change = get_option('ho_change');

    $options['banners'] = array(
        'view' => 'h3',
        'title' => __('Banners','pntheme'),
        'submit' => __('Save','pntheme'),
    );
    $options['banners_block'] = array(
        'view' => 'select',
        'title' => __('Banners block','pntheme'),
        'options' => array('0'=>__('hide','pntheme'), '1'=>__('show','pntheme')),
        'default' => is_isset($change,'banners_block'),
        'name' => 'banners_block',
        'work' => 'int',
    );
    //banner1
    $options['b1'] = array(
        'view' => 'h3',
        'title' => __('Banner 1','pntheme'),
        'submit' => __('Save','pntheme'),
    );
    $options['b1_title'] = array(
        'view' => 'inputbig',
        'title' => __('Title', 'pntheme'),
        'default' => is_isset($change,'b1_title'),
        'name' => 'b1_title',
        'work' => 'input',
        'ml' => 1,
    );
    $options['b1_text'] = array(
        'view' => 'inputbig',
        'title' => __('Text', 'pntheme'),
        'default' => is_isset($change,'b1_text'),
        'name' => 'b1_text',
        'work' => 'input',
        'ml' => 1,
    );
    $options['b1_link'] = array(
        'view' => 'inputbig',
        'title' => __('Banner link', 'pntheme'),
        'default' => is_isset($change,'b1_link'),
        'name' => 'b1_link',
        'work' => 'input',
        'ml' => 1,
    );
    $options['b1_img'] = array(
        'view' => 'uploader',
        'title' => __('Image', 'pn'),
        'default' => is_isset($change,'b1_img'),
        'name' => 'b1_img',
        'work' => 'input',
    );
    //banner1
    $options['b2'] = array(
        'view' => 'h3',
        'title' => __('Banner 2','pntheme'),
        'submit' => __('Save','pntheme'),
    );
    $options['b2_title'] = array(
        'view' => 'inputbig',
        'title' => __('Title', 'pntheme'),
        'default' => is_isset($change,'b2_title'),
        'name' => 'b2_title',
        'work' => 'input',
        'ml' => 1,
    );
    $options['b2_text'] = array(
        'view' => 'inputbig',
        'title' => __('Text', 'pntheme'),
        'default' => is_isset($change,'b2_text'),
        'name' => 'b2_text',
        'work' => 'input',
        'ml' => 1,
    );
    $options['b2_link'] = array(
        'view' => 'inputbig',
        'title' => __('Banner link', 'pntheme'),
        'default' => is_isset($change,'b2_link'),
        'name' => 'b2_link',
        'work' => 'input',
        'ml' => 1,
    );
    $options['b2_img'] = array(
        'view' => 'uploader',
        'title' => __('Image', 'pn'),
        'default' => is_isset($change,'b2_img'),
        'name' => 'b2_img',
        'work' => 'input',
    );
     $options['line1'] = array(
        'view' => 'line',
    );
	$options['top_title'] = array(
		'view' => 'h3',
		'title' => __('Information','pntheme'),
		'submit' => __('Save','pntheme'),
	);
	$options['wtitle'] = array(
		'view' => 'inputbig',
		'title' => __('Title', 'pntheme'),
		'default' => is_isset($change,'wtitle'),
		'name' => 'wtitle',
		'work' => 'input',
		'ml' => 1,
	);
	$options['wtext'] = array(
		'view' => 'editor',
		'title' => __('Text', 'pntheme'),
		'default' => is_isset($change,'wtext'),
		'name' => 'wtext',
		'work' => 'text',
		'rows' => '10',
		'media' => 1,
		'formatting_tags' => 1,
		'ml' => 1,
	);
	$options['center_title'] = array(
		'view' => 'h3',
		'title' => __('Welcome message','pntheme'),
		'submit' => __('Save','pntheme'),
	);
	$options['ititle'] = array(
		'view' => 'inputbig',
		'title' => __('Title', 'pntheme'),
		'default' => is_isset($change,'ititle'),
		'name' => 'ititle',
		'work' => 'input',
		'ml' => 1,
	);
	$options['itext'] = array(
		'view' => 'editor',
		'title' => __('Text', 'pntheme'),
		'default' => is_isset($change,'itext'),
		'name' => 'itext',
		'work' => 'text',
		'rows' => '10',
		'media' => 1,
		'formatting_tags' => 1,
		'ml' => 1,
	);
	$options['iimg'] = array(
        'view' => 'uploader',
        'title' => __('Image (size 336x426 px )', 'pntheme'),
        'default' => is_isset($change,'iimg'),
        'name' => 'iimg',
        'work' => 'input',
    );
	$options['line1'] = array(
		'view' => 'line',
	);
	$options['blocknews'] = array(
		'view' => 'select',
		'title' => __('News column','pntheme'),
		'options' => array('0'=>__('hide','pntheme'), '1'=>__('show','pntheme')),
		'default' => is_isset($change,'blocknews'),
		'name' => 'blocknews',
		'work' => 'int',
	);
	$categories = get_categories('hide_empty=0');
	$array = array();
	$array[0] = '--'.__('All','pntheme').'--';
	if(is_array($categories)){
		foreach($categories as $cat){
			$array[$cat->cat_ID] = ctv_ml($cat->name);
		}
	}
	$options['catnews'] = array(
		'view' => 'select',
		'title' => __('Category','pntheme'),
		'options' => $array,
		'default' => is_isset($change,'catnews'),
		'name' => 'catnews',
		'work' => 'int',
	);
	$options['line2'] = array(
		'view' => 'line',
	);
	$options['blocreviews'] = array(
		'view' => 'select',
		'title' => __('Reviews column','pntheme'),
		'options' => array('0'=>__('hide','pntheme'), '1'=>__('show','pntheme')),
		'default' => is_isset($change,'blocreviews'),
		'name' => 'blocreviews',
		'work' => 'int',
	);
	$options['line3'] = array(
		'view' => 'line',
	);
	$options['lastobmen'] = array(
		'view' => 'select',
		'title' => __('Last exchange','pntheme'),
		'options' => array('0'=>__('hide','pntheme'), '1'=>__('show','pntheme')),
		'default' => is_isset($change,'lastobmen'),
		'name' => 'lastobmen',
		'work' => 'int',
	);
	$options['partners'] = array(
		'view' => 'select',
		'title' => __('Partners','pntheme'),
		'options' => array('0'=>__('hide','pntheme'), '1'=>__('show','pntheme')),
		'default' => is_isset($change,'partners'),
		'name' => 'partners',
		'work' => 'int',
	);
	$options['line4'] = array(
		'view' => 'line',
	);
	$options['advantages'] = array(
		'view' => 'select',
		'title' => __('Advantages','pntheme'),
		'options' => array('0'=>__('hide','pntheme'), '1'=>__('show','pntheme')),
		'default' => is_isset($change,'advantages'),
		'name' => 'advantages',
		'work' => 'int',
	);
	$options['line5'] = array(
		'view' => 'line',
	);
	$options['reserve'] = array(
		'view' => 'select',
		'title' => __('Reserve','pntheme'),
		'options' => array('0'=>__('hide','pntheme'), '1'=>__('show','pntheme')),
		'default' => is_isset($change,'reserve'),
		'name' => 'reserve',
		'work' => 'int',
	);
	$options['hidecurr'] = array(
		'view' => 'user_func',
		'name' => 'hidecurr',
		'func_data' => $change,
		'func' => 'pn_theme_home_hidecurr',
		'work' => 'input_array',
	);
	if (function_exists('get_parser_list')) {

		$options['line_parsers'] = array(
			'view' => 'line',
		);
		$options['showparsers'] = array(
			'view' => 'user_func',
			'name' => 'showparsers',
			'func_data' => $change,
			'func' => 'pn_themehome_showparsers',
			'work' => 'input_array',
		);

	}

	return $options;
}

add_action('pn_adminpage_content_pn_theme_home','def_pn_adminpage_content_pn_theme_home');
function def_pn_adminpage_content_pn_theme_home(){

	$form = new PremiumForm();
	$params_form = array(
		'filter' => 'pn_theme_home_option',
	);
	$form->init_form($params_form);

}

function pn_theme_home_hidecurr($change){
?>
	<div class="premium_standart_line">
		<div class="premium_stline_left"><div class="premium_stline_left_ins"><?php _e('Hide currency reserve in widget','pntheme'); ?></div></div>
		<div class="premium_stline_right"><div class="premium_stline_right_ins">
			<div class="premium_wrap_standart">
				<?php
				$scroll_lists = array();
				$hidecurr = explode(',',is_isset($change,'hidecurr'));
				$currencies = array();
				if(function_exists('list_view_currencies')){
					$currencies = list_view_currencies();
				}
				if(is_array($currencies)){
					foreach($currencies as $item){
						$checked = 0;
						if(in_array($item['id'], $hidecurr)){
							$checked = 1;
						}
						$scroll_lists[] = array(
							'title' => $item['title'],
							'checked' => $checked,
							'value' => $item['id'],
						);
					}
				}
				echo get_check_list($scroll_lists, 'hidecurr[]','','',1);
				?>
					<div class="premium_clear"></div>
			</div>
		</div></div>
			<div class="premium_clear"></div>
	</div>
<?php
}

function pn_themehome_showparsers($change){
?>
	<div class="premium_standart_line">
		<div class="premium_stline_left"><div class="premium_stline_left_ins"><?php _e('Show parsers','pntheme'); ?></div></div>
		<div class="premium_stline_right"><div class="premium_stline_right_ins">
			<div class="premium_wrap_standart">
				<?php
				$scroll_lists = array();
				$output = explode(',',is_isset($change,'showparsers'));
				if (!is_array($output)) { $output = array(); }

				$lists = get_parser_list();

				if (is_array($lists)) {
					foreach ($lists as $item) {
						$checked = 0;
						if (in_array($item->id, $output)) {
							$checked = 1;
						}
						$scroll_lists[] = array(
							'title' => get_parser_title($item),
							'checked' => $checked,
							'value' => $item->id,
						);
					}
				}
				echo get_check_list($scroll_lists, 'showparsers[]','','',1);
				?>
					<div class="premium_clear"></div>
			</div>
		</div></div>
			<div class="premium_clear"></div>
	</div>
<?php
}

add_action('premium_action_pn_theme_home','def_premium_action_pn_theme_home');
function def_premium_action_pn_theme_home(){
global $wpdb;

	_method('post');
	pn_only_caps(array('administrator'));

	$form = new PremiumForm();
	$form->send_header();

	$data = $form->strip_options('pn_theme_home_option', 'post');

	$change = get_option('ho_change');
	if(!is_array($change)){ $change = array(); }

	$change['banners_block'] = $data['banners_block'];
    $change['b1_title'] = $data['b1_title'];
    $change['b1_text'] = $data['b1_text'];
    $change['b1_link'] = $data['b1_link'];
    $change['b1_img'] = pn_strip_input(is_param_post('b1_img'));

    $change['b2_title'] = $data['b2_title'];
    $change['b2_text'] = $data['b2_text'];
    $change['b2_link'] = $data['b2_link'];
    $change['b2_img'] = pn_strip_input(is_param_post('b2_img'));

	$change['blocknews'] = $data['blocknews'];
	$change['catnews'] = $data['catnews'];

	$change['lastobmen'] = $data['lastobmen'];

	$change['blocreviews'] = $data['blocreviews'];
	$change['partners'] = $data['partners'];
	$change['advantages'] = $data['advantages'];

	$change['wtitle'] = $data['wtitle'];
	$change['ititle'] = $data['ititle'];
    $change['iimg'] = pn_strip_input(is_param_post('iimg'));

	$change['wtext'] = $data['wtext'];
	$change['itext'] = $data['itext'];

	$change['reserve'] = $data['reserve'];
	$change['hidecurr'] = implode(',', $data['hidecurr']);

	$showparsers = '';
	$post_showparsers = is_param_post('showparsers');
	if (is_array($post_showparsers)) {
		$showparsers = pn_strip_input_array($post_showparsers);
		$showparsers = implode(',',$showparsers);
	}
	$change['showparsers'] = $showparsers;

	update_option('ho_change',$change);

	$back_url = is_param_post('_wp_http_referer');
	$back_url = add_query_args(array('reply'=>'true'), $back_url);

	$form->answer_form($back_url);
}
