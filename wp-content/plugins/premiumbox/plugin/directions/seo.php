<?php
if (!defined('ABSPATH')) { exit(); }

add_action('admin_menu', 'pn_admin_menu_seo', 11);
function pn_admin_menu_seo() {
	
	$plugin = get_plugin_class();
	if (current_user_can('administrator') or current_user_can('pn_seo')) {
		if (is_extension_active('pn_extended', 'moduls', 'seo')) {
			add_submenu_page("all_seo", __('Exchange directions', 'pn'), __('Exchange directions', 'pn'), 'read', "seo_exchange_directions", array($plugin, 'admin_temp'));
		}
	}
	
}

add_filter('pn_adminpage_title_seo_exchange_directions', 'def_adminpage_title_seo_exchange_directions');
function def_adminpage_title_seo_exchange_directions() {
	
	return __('Exchange directions', 'pn');
}

add_action('pn_adminpage_content_seo_exchange_directions', 'def_adminpage_content_seo_exchange_directions');
function def_adminpage_content_seo_exchange_directions() {
	global $wpdb;

	$form = new PremiumForm();

	$direction_id = intval(is_param_get('direction_id'));
		
	$selects = array();
	$selects[] = array(
		'link' => admin_url("admin.php?page=seo_exchange_directions"),
		'title' => '--' . __('Choice', 'pn') . '--',
		'default' => '',
	);		
	$directions = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "directions WHERE auto_status = '1' ORDER BY id DESC");
	foreach ($directions as $direction) {
		$selects[] = array(
			'link' => admin_url("admin.php?page=seo_exchange_directions&direction_id=" . $direction->id),
			'title' => pn_strip_input($direction->tech_name),
			'default' => $direction->id,
		);
	}
		
	$form->select_box($direction_id, $selects, __('Setting up', 'pn'));	

	if ($direction_id > 0) {

		$seo = get_direction_meta($direction_id, 'seo');	

		$options = array();
		$options['hidden_block'] = array(
			'view' => 'hidden_input',
			'name' => 'direction_id',
			'default' => $direction_id,
		);	
		$options['top_title'] = array(
			'view' => 'h3',
			'title' => '',
			'submit' => __('Save', 'pn'),
		);
		$options['description_txt'] = array(
			'view' => 'editor',
			'title' => __('Exchange description', 'pn'),
			'default' => get_direction_meta($direction_id, 'description_txt'),
			'name' => 'description_txt',
			'rows' => '12',
			'formatting_tags' => 1,
			'other_tags' => 1,
			'tags' => apply_filters('direction_instruction_tags', array(), 'description_txt'),
			'ml' => 1,
		);
		$options['seo_title'] = array(
			'view' => 'input',
			'title' => __('Page title', 'pn'),
			'default' => is_isset($seo, 'seo_title'),
			'name' => 'seo_title',
			'ml' => 1,
			'atts' => array('class' => 'long_input'),
		);		
		$options['seo_exch_title'] = array(
			'view' => 'input',
			'title' => __('Exchange title (H1)', 'pn'),
			'default' => is_isset($seo, 'seo_exch_title'),
			'name' => 'seo_exch_title',
			'ml' => 1,
			'atts' => array('class' => 'long_input'),
		);	
		$options['seo_canonical'] = array(
			'view' => 'input',
			'title' => __('canonical url', 'pn'),
			'default' => is_isset($seo, 'seo_canonical'),
			'name' => 'seo_canonical',
			'ml' => 1,
			'atts' => array('class' => 'long_input'),
		);		
		$options['seo_key'] = array(
			'view' => 'textarea',
			'title' => __('Page keywords', 'pn'),
			'default' => is_isset($seo, 'seo_key'),
			'name' => 'seo_key',
			'rows' => '6',
			'word_count' => 1,
			'ml' => 1,
		);
		$options['seo_descr'] = array(
			'view' => 'textarea',
			'title' => __('Page description', 'pn'),
			'default' => is_isset($seo, 'seo_descr'),
			'name' => 'seo_descr',
			'rows' => '12',
			'word_count' => 1,
			'ml' => 1,
		);
		$options['ogp_title'] = array(
			'view' => 'input',
			'title' => __('OGP title', 'pn'),
			'default' => is_isset($seo, 'ogp_title'),
			'name' => 'ogp_title',
			'ml' => 1,
			'atts' => array('class' => 'long_input'),
		);
		$options['ogp_descr'] = array(
			'view' => 'textarea',
			'title' => __('OGP description', 'pn'),
			'default' => is_isset($seo, 'ogp_descr'),
			'name' => 'ogp_descr',
			'rows' => '12',
			'word_count' => 1,
			'ml' => 1,
		);			
		$params_form = array(
			'filter' => 'seo_exchange_directions_form',
			'data' => $seo,
		);
		$form->init_form($params_form, $options);
	}	
}

add_action('premium_action_seo_exchange_directions', 'def_premium_action_seo_exchange_directions');
function def_premium_action_seo_exchange_directions() {
	global $wpdb;	
			
	_method('post');
		
	$form = new PremiumForm();
	$form->send_header();
		
	pn_only_caps(array('administrator', 'pn_seo'));
			
	$direction_id = intval(is_param_post('direction_id')); 
	if ($direction_id > 0) {
		
		$description_txt = pn_strip_text(is_param_post_ml('description_txt'));
		$res = update_direction_meta($direction_id, 'description_txt', $description_txt);
				
		$seo = array();
		$seo['seo_exch_title'] = pn_strip_input(is_param_post_ml('seo_exch_title'));
		$seo['seo_title'] = pn_strip_input(is_param_post_ml('seo_title'));	
		$seo['seo_canonical'] = pn_strip_input(is_param_post_ml('seo_canonical'));
		$seo['seo_key'] = pn_strip_input(is_param_post_ml('seo_key'));
		$seo['seo_descr'] = pn_strip_input(is_param_post_ml('seo_descr'));								
		$seo['ogp_title'] = pn_strip_input(is_param_post_ml('ogp_title'));
		$seo['ogp_descr'] = pn_strip_input(is_param_post_ml('ogp_descr'));
		update_direction_meta($direction_id, 'seo', $seo);	
		
	}
		
	$url = admin_url('admin.php?page=seo_exchange_directions&direction_id=' . $direction_id . '&reply=true');
	$form->answer_form($url);
	
}

add_filter('list_tabs_direction','list_tabs_direction_seo');
function list_tabs_direction_seo($lists) {
	
	if (is_extension_active('pn_extended', 'moduls', 'seo')) {
		$lists['tabseo'] = __('SEO', 'pn');
	}
	
	return $lists;
}

add_action('tab_direction_tabseo', 'seo_tab_direction_tabseo', 99, 2);
function seo_tab_direction_tabseo($data, $data_id) {
				
	$form = new PremiumForm();
				
	$seo = get_direction_meta($data_id, 'seo');	

	$atts_input = array();
	$atts_input['class'] = 'long_input';
	?>
	<div class="add_tabs_line">
		<div class="add_tabs_single long">
			<div class="add_tabs_sublabel"><span><?php _e('Page title', 'pn'); ?></span></div>
			<?php $form->input('seo_title' , is_isset($seo,'seo_title'), $atts_input, 1); ?>
		</div>
	</div>	
	<div class="add_tabs_line">
		<div class="add_tabs_single long">
			<div class="add_tabs_sublabel"><span><?php _e('Exchange title (H1)', 'pn'); ?></span></div>
			<?php $form->input('seo_exch_title' , is_isset($seo,'seo_exch_title'), $atts_input, 1); ?>			
		</div>
	</div>
	<div class="add_tabs_line">
		<div class="add_tabs_single long">
			<div class="add_tabs_sublabel"><span><?php _e('canonical url', 'pn'); ?></span></div>
			<?php $form->input('seo_canonical' , is_isset($data,'seo_canonical'), $atts_input, 1); ?>
		</div>
	</div>	
	<div class="add_tabs_line">
		<div class="add_tabs_single long">
			<div class="add_tabs_sublabel"><span><?php _e('Page keywords', 'pn'); ?></span></div>
			<?php $form->editor('seo_key', is_isset($seo,'seo_key'), '6', '', 1, 1); ?>
		</div>
	</div>
	<div class="add_tabs_line">
		<div class="add_tabs_single long">
			<div class="add_tabs_sublabel"><span><?php _e('Page description', 'pn'); ?></span></div>
			<?php $form->editor('seo_descr', is_isset($seo,'seo_descr'), '12', '', 1, 1); ?>
		</div>
	</div>
	<div class="add_tabs_line">
		<div class="add_tabs_single long">
			<div class="add_tabs_sublabel"><span><?php _e('OGP title', 'pn'); ?></span></div>
			<?php $form->input('ogp_title' , is_isset($seo,'ogp_title'), $atts_input, 1); ?>
		</div>
	</div>
	<div class="add_tabs_line">
		<div class="add_tabs_single long">
			<div class="add_tabs_sublabel"><span><?php _e('OGP description', 'pn'); ?></span></div>
			<?php $form->editor('ogp_descr', is_isset($seo,'ogp_descr'), '12', '', 1, 1); ?>
		</div>
	</div>	
	<?php						
}

add_action('item_direction_edit', 'seo_item_direction_edit'); 
add_action('item_direction_add', 'seo_item_direction_edit');
function seo_item_direction_edit($data_id) {
	
	if (is_extension_active('pn_extended', 'moduls', 'seo')) {
		
		$seo = array();
		$seo['seo_exch_title'] = pn_strip_input(is_param_post_ml('seo_exch_title'));
		$seo['seo_title'] = pn_strip_input(is_param_post_ml('seo_title'));
		$seo['seo_canonical'] = pn_strip_input(is_param_post_ml('seo_canonical'));
		$seo['seo_key'] = pn_strip_input(is_param_post_ml('seo_key'));
		$seo['seo_descr'] = pn_strip_input(is_param_post_ml('seo_descr'));								
		$seo['ogp_title'] = pn_strip_input(is_param_post_ml('ogp_title'));
		$seo['ogp_descr'] = pn_strip_input(is_param_post_ml('ogp_descr'));
		update_direction_meta($data_id, 'seo', $seo);
		
	}
	
}

add_filter('selects_all_seo', 'dir_selects_all_seo');
function dir_selects_all_seo($selects) {
	
	$selects[] = array(
		'link' => admin_url("admin.php?page=all_seo&place=exchange"),
		'title' => __('Exchange form', 'pn'),
		'default' => 'exchange',
	);	
	
	return $selects;				
}

add_filter('all_seo_option', 'pn_all_seo_option', 10, 2);
function pn_all_seo_option($options, $place = '') {
	
	$plugin = get_plugin_class();

	if ($place and 'exchange' == $place) {

		$options = pn_array_unset($options, array('title_line', 'line2', 'exchange_title', 'exchange_key', 'exchange_descr', 'ogp_exchange_title', 'ogp_exchange_descr', 'ogp_exchange_img', 'exchange_temp'));

		$tags = array();
		$tags['sitename'] = array(
			'title' => __('Site name', 'pn'),
			'start' => '[sitename]',
		);	
		$tags['title1'] = array(
			'title' => sprintf(__('Currency title %s', 'pn'), '1'),
			'start' => '[title1]',
		);
		$tags['title2'] = array(
			'title' => sprintf(__('Currency title %s', 'pn'), '2'),
			'start' => '[title2]',
		);
		$tags['curr_title1'] = array(
			'title' => sprintf(__('Currency code %s', 'pn'), '1'),
			'start' => '[curr_title1]',
		);
		$tags['curr_title2'] = array(
			'title' => sprintf(__('Currency code %s', 'pn'), '2'),
			'start' => '[curr_title2]',
		);		
		$tags['xml_title1'] = array(
			'title' => sprintf(__('Currency XML name %s', 'pn'), '1'),
			'start' => '[xml_title1]',
		);
		$tags['xml_title2'] = array(
			'title' => sprintf(__('Currency XML name %s', 'pn'), '2'),
			'start' => '[xml_title2]',
		);		
		$tags = apply_filters('list_direction_seotags', $tags);
		$options['exch_temp'] = array(
			'view' => 'editor',
			'title' => __('Title template', 'pn'),
			'default' => $plugin->get_option('seo', 'exch_temp'),
			'tags' => $tags,
			'rows' => '3',
			'word_count' => 1,
			'name' => 'exch_temp',
			'work' => 'input',
			'ml' => 1,
		);						
		$options['exch_temp2'] = array(
			'view' => 'editor',
			'title' => __('Exchange page title template (H1)', 'pn'),
			'default' => $plugin->get_option('seo', 'exch_temp2'),
			'tags' => $tags,
			'rows' => '3',
			'name' => 'exch_temp2',
			'work' => 'input',
			'ml' => 1,
		);
		$options['exch_key'] = array(
			'view' => 'editor',
			'title' => __('Keywords', 'pn'),
			'default' => $plugin->get_option('seo', 'exch_key'),
			'tags' => $tags,
			'rows' => '3',
			'word_count' => 1,
			'name' => 'exch_key',
			'work' => 'input',
			'ml' => 1,
		);		
		$options['exch_descr'] = array(
			'view' => 'editor',
			'title' => __('Description', 'pn'),
			'default' => $plugin->get_option('seo', 'exch_descr'),
			'tags' => $tags,
			'rows' => '5',
			'word_count' => 1,
			'name' => 'exch_descr',
			'work' => 'input',
			'ml' => 1,
		);
		$options['ogp_exch_title'] = array(
			'view' => 'editor',
			'title' => __('OGP title', 'pn'),
			'default' => $plugin->get_option('seo', 'ogp_exch_title'),
			'tags' => $tags,
			'rows' => '3',
			'word_count' => 1,
			'name' => 'ogp_exch_title',
			'work' => 'input',
			'ml' => 1,			
		);	
		$options['ogp_exch_descr'] = array( 
			'view' => 'editor',
			'title' => __('OGP description', 'pn'),
			'default' => $plugin->get_option('seo', 'ogp_exch_descr'),
			'tags' => $tags,
			'name' => 'ogp_exch_descr',
			'work' => 'input',
			'rows' => '6',
			'word_count' => 1,
			'ml' => 1,
		);	
		$options['ogp_exch_img'] = array(
			'view' => 'uploader',
			'title' => __('OGP image', 'pn'),
			'default' => $plugin->get_option('seo', 'ogp_exch_img'),
			'name' => 'ogp_exch_img',
			'work' => 'input',
			'ml' => 1,
		);		
	
	}
	
	return $options;
}

add_filter('exchange_step_meta', 'seo_exchange_step_meta');
function seo_exchange_step_meta($log) {
	global $direction_data, $exchange_seo;
	
	if (isset($direction_data->direction_id)) {
		if (is_extension_active('pn_extended', 'moduls', 'seo')) {
			
			$direction_id = intval($direction_data->direction_id);
			if (!is_array($exchange_seo)) {
				$exchange_seo = (array)get_direction_meta($direction_id, 'seo');
			}
			$plugin = get_plugin_class();
			
			$seo_keywords = pn_strip_input(ctv_ml(is_isset($exchange_seo, 'seo_key')));
			if (strlen($seo_keywords) < 1) {
				$seo_keywords = pn_strip_input(ctv_ml($plugin->get_option('seo', 'exch_key')));
			}		
		
			$seo_descr = pn_strip_input(ctv_ml(is_isset($exchange_seo, 'seo_descr')));
			if (strlen($seo_descr) < 1) {
				$seo_descr = pn_strip_input(ctv_ml($plugin->get_option('seo', 'exch_descr')));
			}
			if (strlen($seo_descr) < 1) {
				$seo_descr = get_exchange_title('title');
			}		
		
			$log['keywords'] = replace_exchange_seo($seo_keywords, $direction_data);
			$log['description'] = replace_exchange_seo($seo_descr, $direction_data);
			
		}
	}
	
	return $log;
}

add_filter('page_seo_data', 'direction_page_seo_data');
function direction_page_seo_data($page_seo_data) {
	global $direction_data, $exchange_seo;
	
	if (isset($direction_data->direction_id)) {

		$plugin = get_plugin_class();

		$page_seo_data['seo_enable'] = 1;
		
		if (!is_array($exchange_seo)) {
			$exchange_seo = (array)get_direction_meta($direction_data->direction_id, 'seo');	
		}			
		
		$seo_keywords = pn_strip_input(ctv_ml(is_isset($exchange_seo, 'seo_key')));
		if (strlen($seo_keywords) < 1) {
			$seo_keywords = pn_strip_input(ctv_ml($plugin->get_option('seo', 'exch_key')));
		}
		$page_seo_data['seo_keywords'] = replace_exchange_seo($seo_keywords, $direction_data);
		
		$seo_descr = pn_strip_input(ctv_ml(is_isset($exchange_seo, 'seo_descr')));
		if (strlen($seo_descr) < 1) {
			$seo_descr = pn_strip_input(ctv_ml($plugin->get_option('seo', 'exch_descr')));
		}
		if (strlen($seo_descr) < 1) {
			$seo_descr = get_exchange_title('title');
		}		
		$page_seo_data['seo_descr'] = replace_exchange_seo($seo_descr, $direction_data);
				
		$ogp_image = ctv_ml(is_isset($exchange_seo, 'ogp_image'));	
		if (strlen($ogp_image) < 1) {
			$ogp_image = ctv_ml($plugin->get_option('seo', 'ogp_exch_img'));
		}		
		if ($ogp_image) {
			$page_seo_data['ogp_image'] = $ogp_image;
		}		
			
		$ogp_title = pn_strip_input(ctv_ml(is_isset($exchange_seo, 'ogp_title')));
		if (strlen($ogp_title) < 1) {
			$ogp_title = pn_strip_input(ctv_ml($plugin->get_option('seo', 'ogp_exch_title')));
		}
		if (strlen($ogp_title) < 1) {
			$ogp_title = get_exchange_title('title');
		}
		$page_seo_data['ogp_title'] = replace_exchange_seo($ogp_title, $direction_data);	
		
		$ogp_descr = pn_strip_input(ctv_ml(is_isset($exchange_seo, 'ogp_descr')));
		if (strlen($ogp_descr) < 1) {
			$ogp_descr = pn_strip_input(ctv_ml($plugin->get_option('seo', 'ogp_exch_descr')));
		}		
		if (strlen($ogp_descr) < 1) {
			$ogp_descr = get_exchange_title('title');
		}	
		$page_seo_data['ogp_descr'] = replace_exchange_seo($ogp_descr, $direction_data);
	
	} 
	
	return $page_seo_data;
}

add_filter('canonical_url', 'direction_canonical_url');
function direction_canonical_url($link) {
	global $direction_data, $exchange_seo;

	if (isset($direction_data->direction, $direction_data->direction->direction_name)) {
		if (is_extension_active('pn_extended', 'moduls', 'seo')) {
		
			if (!is_array($exchange_seo)) {
				$exchange_seo = (array)get_direction_meta($direction_data->direction_id, 'seo');	
			}	
			$canonical_url = pn_strip_input(ctv_ml(is_isset($exchange_seo, 'seo_canonical')));
			if ($canonical_url) {
				return $canonical_url;
			}
		
			return get_exchange_link($direction_data->direction->direction_name);
		}
	}
	
	return $link;
}