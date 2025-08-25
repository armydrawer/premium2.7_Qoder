<?php
if (!defined('ABSPATH')) { exit(); }

if (!function_exists('admin_menu_lang')) {

	add_action('admin_menu', 'admin_menu_lang');
	function admin_menu_lang() {
		
		$plugin = get_plugin_class();	
		add_submenu_page("options-general.php", __('Language settings', 'pn'), __('Language settings', 'pn'), 'administrator', "all_lang", array($plugin, 'admin_temp'));
		
	}

	add_filter('pn_adminpage_title_all_lang', 'def_pn_adminpage_title_all_lang');
	function def_pn_adminpage_title_all_lang($page) {
		
		return __('Language settings', 'pn');
	}

	add_filter('allowed_options', 'lang_allowed_options' );
	function lang_allowed_options($options) {
		
		if (isset($options['general'])) {	
			$key = array_search('WPLANG', $options['general']);
			if (isset($options['general'][$key])) {
				unset($options['general'][$key]);
			}		
		}
		
		return $options;
	}	
	
  	add_action('admin_footer', 'lang_admin_lang_footer');
	function lang_admin_lang_footer() {
		
		$screen = get_current_screen();
		if ('options-general' == $screen->id) {
			?>
			<script type="text/javascript">
			jQuery(function($) {
				
				$('#WPLANG').parents('tr').hide();
				
			});
			</script>
			<?php
		}	
		
	}	

	add_filter('all_lang_option', 'def_all_lang_option', 1);
	function def_all_lang_option($options) {	
		
		$langs = get_site_langs();
		
		$lang = get_option('pn_lang');
		if (!is_array($lang)) { $lang = array(); }		
		
		$admin_lang = is_isset($lang, 'admin_lang');
		if (!$admin_lang) {
			$admin_lang = get_locale();
		}		
		
		$site_lang = is_isset($lang, 'site_lang');
		if (!$site_lang) {
			$site_lang = get_locale();
		}		
		
		$multisite_lang = array();
		if (isset($lang['multisite_lang'])) {
			$multisite_lang = $lang['multisite_lang'];
		}
		if (!is_array($multisite_lang)) { $multisite_lang = array(); }		
		
		$options['top_title'] = array(
			'view' => 'h3',
			'title' => __('Language settings', 'pn'),
			'submit' => __('Save', 'pn'),
		);	
		$options['lang_redir'] = array(
			'view' => 'select',
			'title' => __('User language auto detecting', 'pn'),
			'options' => array('0' => __('No', 'pn'), '1' => __('Yes', 'pn')),
			'default' => is_isset($lang, 'lang_redir'),
			'name' => 'lang_redir',
			'work' => 'int',
		);
		$options[] = array(
			'view' => 'line',
		);
		$options['admin_lang'] = array(
			'view' => 'select',
			'title' => __('Admin-panel language', 'pn'),
			'options' => $langs,
			'default' => $admin_lang,
			'name' => 'admin_lang',
			'work' => 'input',
		);
		$options['site_lang'] = array(
			'view' => 'select',
			'title' => __('Website language', 'pn'),
			'options' => $langs,
			'default' => $site_lang,
			'name' => 'site_lang',
			'work' => 'input',
		);		
		$options[] = array(
			'view' => 'line',
		);							
		$options['multisite_lang'] = array(
			'view' => 'user_func',
			'func_data' => array(
				'langs' => $langs,
				'multisite_lang' => $multisite_lang,
				'site_lang' => $site_lang,
			),
			'func' => 'pn_multisite_lang_option',
			'name' => 'multisite_lang',
			'work' => 'input_array',
		);	
		
		return $options;
	}	
	
  	add_action('pn_adminpage_content_all_lang', 'def_pn_adminpage_content_all_lang');
	function def_pn_adminpage_content_all_lang() {
		
		$form = new PremiumForm();
		$params_form = array(
			'filter' => 'all_lang_option',
			'button_title' => __('Save', 'pn'),
		);
		$form->init_form($params_form);		
	?>
	<script type="text/javascript">
	jQuery(function($) {
		
		$('#pn_site_lang').on('change', function() {
			var vale = $(this).val();
			$('.multisite_lang').find('input').prop('checked', false);
			$('#multisite_lang_' + vale).find('input').prop('checked', true);
		});	
		
	});
	</script>		
	<?php  
	} 

	function pn_multisite_lang_option($data) {
		
		$langs = $data['langs'];			
		$temp = '
		<div class="premium_standart_line">
			<div class="premium_stline_left"><div class="premium_stline_left_ins">' . __('Multilingualism', 'pn') . '</div></div>
			<div class="premium_stline_right"><div class="premium_stline_right_ins">
				<div class="premium_wrap_standart">';
				
					foreach ($langs as $lang_key => $lang_data) {  
						$checked = '';
						if (in_array($lang_key, $data['multisite_lang']) or $data['site_lang'] == $lang_key) { 
							$checked = 'checked="checked"';
						}							
						$temp .= '<div id="multisite_lang_' . $lang_key . '" class="multisite_lang"><label><input type="checkbox" name="multisite_lang[]" ' . $checked . ' autocomplete="off" value="' . $lang_key . '" /> ' . $lang_data . '</label></div>';
					}
					
					$temp .= '	
				</div>
			</div></div>
				<div class="premium_clear"></div>
		</div>';	
		
		echo $temp;	
	}

	add_action('premium_action_all_lang', 'def_premium_action_all_lang');
	function def_premium_action_all_lang() {
		
		_method('post');
		
		$form = new PremiumForm();
		$form->send_header();
		
		pn_only_caps(array('administrator'));

		$data = $form->strip_options('all_lang_option', 'post');
				
		$lang = get_option('pn_lang');
		$lang['admin_lang'] = $data['admin_lang'];
		$lang['site_lang'] = $data['site_lang'];
		$lang['multisite_lang'] = is_param_post('multisite_lang');
		$lang['lang_redir'] = is_param_post('lang_redir');
		update_option('pn_lang', $lang);
				
		do_action('all_lang_option_post', $data);			
				
		$back_url = is_param_post('_wp_http_referer');
		$back_url = add_query_args(array('reply' => 'true'), $back_url);
				
		$form->answer_form($back_url);
		
	} 	
}