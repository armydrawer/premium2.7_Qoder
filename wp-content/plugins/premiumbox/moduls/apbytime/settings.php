<?php
if (!defined('ABSPATH')) { exit(); }

if (!function_exists('admin_menu_apbytime')) {

	add_action('admin_menu', 'admin_menu_apbytime', 1000);
	function admin_menu_apbytime() {
		
		$plugin = get_plugin_class();
		add_submenu_page("all_users", __('Access to control panel by time', 'pn'), __('Access to control panel by time', 'pn'), 'administrator', "all_apbytime", array($plugin, 'admin_temp'));	
		
	}

	add_filter('pn_adminpage_title_all_apbytime', 'def_adminpage_title_all_apbytime');
	function def_adminpage_title_all_apbytime() {
		
		return __('Access to control panel by time', 'pn');
	}

	add_action('pn_adminpage_content_all_apbytime', 'def_pn_adminpage_content_all_apbytime');
	function def_pn_adminpage_content_all_apbytime() {
		global $wpdb;

		$plugin = get_plugin_class();

		$form = new PremiumForm();

		$prefix = $wpdb->prefix;
		
		global $wp_roles;
		if (!isset($wp_roles)) {
			$wp_roles = new WP_Roles();
		}
		
		$selects = array();
		$selects[] = array(
			'link' => admin_url("admin.php?page=all_apbytime"),
			'title' => '--' . __('Make a choice', 'pn') . '--',
			'default' => '',
		);		
		
		$places = array();
		$place = is_param_get('place');
		$title_roles = array();
		if (isset($wp_roles)) {
			foreach ($wp_roles->role_names as $role => $name) {
				if ('administrator' != $role and 'users' != $role) {
					$title_roles[$role] = $name;
					$places[] = $role;
					$selects[] = array(
						'link' => admin_url("admin.php?page=all_apbytime&place=" . $role),
						'title' => $name,
						'default' => $role,
					);				
				}	
			}
		}	
		
		$form->select_box($place, $selects, __('Setting up', 'pn'));

		if (in_array($place,$places)) {
			
			$data = $plugin->get_option('apbytime', $place);

			$options = array();
			$options['top_title'] = array(
				'view' => 'h3',
				'title' => is_isset($title_roles, $place),
				'submit' => __('Save', 'pn'),
			);
			$options['hidden'] = array(
				'view' => 'hidden_input',
				'name' => 'role',
				'default' => $place,
			);
			$options['status'] = array(
				'view' => 'select',
				'title' => __('Status', 'pn'),
				'options' => array('0' => __('Anytime', 'pn'), '1' => __('On schedule', 'pn')),
				'default' => is_isset($data, 'status'),
				'name' => 'status',
				'work' => 'int',
			);		
			$options['datetime'] = array(
				'view' => 'user_func',
				'func_data' => $data,
				'func' => 'all_apbytime_datetime',
				'work' => 'input_array',
			);		
			
			$params_form = array(
				'filter' => 'all_apbytime_options',
				'data' => $data,
				'button_title' => __('Save', 'pn'),
			);
			$form->init_form($params_form, $options);		
		}
	} 

	function all_apbytime_datetime($data) {
		
		$days = array(
			'd1' => __('monday', 'pn'),
			'd2' => __('tuesday', 'pn'),
			'd3' => __('wednesday', 'pn'),
			'd4' => __('thursday', 'pn'),
			'd5' => __('friday', 'pn'),
			'd6' => '<span class="bred">' . __('saturday', 'pn') . '</span>',
			'd7' => '<span class="bred">' . __('sunday', 'pn') . '</span>',
		);	
	?>
		<div class="premium_standart_line">
			<div class="premium_stline_left"><div class="premium_stline_left_ins"><?php _e('Work time', 'pn'); ?></div></div>
			<div class="premium_stline_right"><div class="premium_stline_right_ins">
				<div class="premium_wrap_standart">
					<select name="h1" style="width: 50px;" autocomplete="off">	
						<?php
						$r = -1;
						while ($r++ < 23) {
						?>
						<option value="<?php echo $r; ?>" <?php selected(intval(is_isset($data, 'h1')), $r);?>><?php echo zeroise($r, 2); ?></option>
						<?php } ?>
					</select>
						:
					<select name="m1" style="width: 50px;" autocomplete="off">	
						<?php
						$r = -1;
						while ($r++ < 59) {
						?>
						<option value="<?php echo $r; ?>" <?php selected(intval(is_isset($data, 'm1')), $r);?>><?php echo zeroise($r, 2); ?></option>
						<?php } ?>
					</select>							
					-
								
					<select name="h2" style="width: 50px;" autocomplete="off">	
						<?php
						$r = -1;
						while ($r++ < 23) {
						?>
						<option value="<?php echo $r; ?>" <?php selected(intval(is_isset($data, 'h2')), $r);?>><?php echo zeroise($r, 2); ?></option>
						<?php } ?>
					</select>	
					:
					<select name="m2" style="width: 50px;" autocomplete="off">	
						<?php
						$r = -1;
						while ($r++ < 59) {
						?>
						<option value="<?php echo $r; ?>" <?php selected(intval(is_isset($data, 'm2')), $r);?>><?php echo zeroise($r, 2); ?></option>
						<?php } ?>
					</select>							
						<div class="premium_clear"></div>
				</div>
			</div></div>
				<div class="premium_clear"></div>
		</div>									
	<?php
	}

 	add_action('premium_action_all_apbytime', 'def_premium_action_all_apbytime');
	function def_premium_action_all_apbytime() {
		
		_method('post');
		
		$form = new PremiumForm();
		$form->send_header();
		
		pn_only_caps(array('administrator'));
		
		$plugin = get_plugin_class();
			
		$role = pn_strip_input(is_param_post('role'));
				
		if ($role) {
			$array = array();
			$array['status'] = intval(is_param_post('status'));
			$array['h1'] = zeroise(intval(is_param_post('h1')), 2);
			$array['h2'] = zeroise(intval(is_param_post('h2')), 2);
			$array['m1'] = zeroise(intval(is_param_post('m1')), 2);
			$array['m2'] = zeroise(intval(is_param_post('m2')), 2);
			$plugin->update_option('apbytime', $role, $array);			
		}
		
		$back_url = is_param_post('_wp_http_referer');
		$back_url = add_query_args(array('reply' => 'true'), $back_url);
		$form->answer_form($back_url);	
		
	}	
}