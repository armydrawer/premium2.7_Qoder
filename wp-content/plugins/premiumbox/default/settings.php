<?php
if (!defined('ABSPATH')) { exit(); }

if (!function_exists('standart_whitelist_options')) {
	
	add_filter('allowed_options', 'standart_allowed_options');
	function standart_allowed_options($options) {
		
		if (isset($options['general'])) {	
			$key = array_search('blogname', $options['general']);
			if (isset($options['general'][$key])) {
				unset($options['general'][$key]);
			}
			
			$key = array_search('blogdescription', $options['general']);
			if (isset($options['general'][$key])) {
				unset($options['general'][$key]);
			}	
			
			$key = array_search('new_admin_email', $options['general']);
			if (isset($options['general'][$key])) {
				unset($options['general'][$key]);
			}
		}
		
		return $options;
	}

	add_action('admin_footer', 'standart_admin_footer');
	function standart_admin_footer() {
		
		$screen = get_current_screen();
		if ('options-general' == $screen->id) {
			?>
			<script type="text/javascript">
			jQuery(function($) {
				
				$('#blogname').parents('tr').hide();
				$('#blogdescription').parents('tr').hide();
				$('#new_admin_email').parents('tr').hide();
				
			});
			</script>
			<?php
		}	
		
	}
	
	add_action('admin_menu', 'admin_menu_settings');
	function admin_menu_settings() {
		
		$plugin = get_plugin_class();	
		add_submenu_page("options-general.php", __('General settings', 'pn'), __('General settings', 'pn'), 'administrator', "all_settings", array($plugin, 'admin_temp'));
		
	}	

	add_filter('pn_adminpage_title_all_settings', 'def_adminpage_title_all_settings');
	function def_adminpage_title_all_settings($page) {
		
		return __('General settings', 'pn');
	} 

	add_filter('all_settings_option', 'def_all_settings_option', 1);
	function def_all_settings_option($options) {
		global $wpdb;	
		
		$options['top_title'] = array(
			'view' => 'h3',
			'title' => __('General settings', 'pn'),
			'submit' => __('Save', 'pn'),
		);				
		
		$row = $wpdb->get_row( "SELECT option_value FROM " . $wpdb->prefix . "options WHERE option_name = 'blogname'");
		$options['blogname'] = array(
			'view' => 'inputbig',
			'title' => __('Website Title', 'pn'),
			'default' => $row->option_value,
			'name' => 'blogname',
			'work' => 'input',
			'ml' => 1,
		);
		
		$row = $wpdb->get_row( "SELECT option_value FROM " . $wpdb->prefix . "options WHERE option_name = 'blogdescription'");
		$options['blogdescription'] = array(
			'view' => 'inputbig',
			'title' => __('Description', 'pn'),
			'default' => $row->option_value,
			'name' => 'blogdescription',
			'work' => 'input',
			'ml' => 1,
		);

		$options['admin_email'] = array(
			'view' => 'inputbig',
			'title' => __('Administrator email', 'pn'),
			'default' => get_option('admin_email'),
			'name' => 'admin_email',
			'work' => 'email',
		);		
		
		return $options;
	}
	
	add_action('pn_adminpage_content_all_settings', 'def_adminpage_content_all_settings');
	function def_adminpage_content_all_settings() {

		$form = new PremiumForm();
		$params_form = array(
			'filter' => 'all_settings_option',
			'button_title' => __('Save', 'pn'),
		);
		$form->init_form($params_form);
		
	} 

	add_action('premium_action_all_settings', 'def_premium_action_all_settings');
	function def_premium_action_all_settings() {	
	
		_method('post');
		
		$form = new PremiumForm();
		$form->send_header();
		
		pn_only_caps(array('administrator'));
		
		$data = $form->strip_options('all_settings_option', 'post');
		
		update_option('blogname', $data['blogname']);
		update_option('blogdescription', $data['blogdescription']);
		update_option('admin_email', $data['admin_email']);
		
		do_action('all_settings_option_post', $data);			
		
		$back_url = is_param_post('_wp_http_referer');
		$back_url = add_query_args(array('reply' => 'true'), $back_url);
				
		$form->answer_form($back_url);	
		
	}
	
}