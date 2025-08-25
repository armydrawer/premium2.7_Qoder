<?php
if (!defined('ABSPATH')) { exit(); }

/*
title: [en_US:]User filter[:en_US][ru_RU:]Фильтрация пользователей[:ru_RU]
description: [en_US:]User filter[:en_US][ru_RU:]Фильтрация пользователей[:ru_RU]
version: 2.7.0
category: [en_US:]Users[:en_US][ru_RU:]Пользователи[:ru_RU]
cat: user
*/

add_action('admin_menu', 'admin_menu_userfilters', 100);
function admin_menu_userfilters() {
	global $premiumbox;
	
	if (current_user_can('administrator')) {
		add_submenu_page('all_users', __('User filters', 'pn'), __('User filters', 'pn'), 'read', "pn_userfilters", array($premiumbox, 'admin_temp'));
	}
}

add_filter('pn_adminpage_title_pn_userfilters', 'def_adminpage_title_pn_userfilters');
function def_adminpage_title_pn_userfilters() {
	
	return __('User filters', 'pn');
}

add_action('pn_adminpage_content_pn_userfilters', 'def_adminpage_content_pn_userfilters');
function def_adminpage_content_pn_userfilters() {
	global $wpdb;
	
?>
<form action="<?php the_pn_link('userfilters_form', 'post'); ?>" class="finstats_form" method="post">
	<div class="finfiletrs">
					
		<div class="fin_list">
			<div class="fin_label"><?php _e('User type', 'pn'); ?></div>
			<select name="verify" autocomplete="off">
				<option value="0"><?php _e('All', 'pn'); ?></option>
				<option value="1"><?php _e('Verified', 'pn'); ?></option>
				<option value="2"><?php _e('Unverified', 'pn'); ?></option>
			</select>
		</div>

		<div class="fin_list">
			<div class="fin_label"><?php _e('Discount is more than', 'pn'); ?> ></div>
			<input type="text" name="discount" value="" />
		</div>		
			<div class="premium_clear"></div>		
				
		<input type="submit" name="submit" class="finstat_link" value="<?php _e('Display users', 'pn'); ?>" />
		<div class="finstat_ajax"></div>	
			<div class="premium_clear"></div>
	</div>
</form>

<div id="finres"></div>

<script type="text/javascript">
jQuery(function($) {
	
	$('.finstats_form').ajaxForm({
		dataType:  'json',
		beforeSubmit: function(a, f, o) {
			$('.finstat_link').prop('disabled', true);
			$('.finstat_ajax').show();
		},
		error: function(res, res2, res3) {
			<?php do_action('pn_js_error_response'); ?>
		},		
		success: function(res) {
				
			$('.finstat_link').prop('disabled', false);
			$('.finstat_ajax').hide();
				
			if (res['status'] == 'error') {
				<?php do_action('pn_js_alert_response'); ?>
			} else if (res['status'] == 'success') {
				$('#finres').html(res['table']);
			}
		}
	});
		
});
</script>		
<?php
}

add_action('premium_action_userfilters_form', 'pn_premium_action_userfilters_form');
function pn_premium_action_userfilters_form() {
	global $wpdb;

	_method('post');
	_json_head();
		
	$log = array();
	$log['status'] = 'success';
	$log['status_code'] = 0; 
	$log['status_text'] = '';	
		
	if (current_user_can('administrator')) {
			
		$where = '';
		$discount = is_sum(is_param_post('discount'));
		$verify = intval(is_param_post('verify'));
			
		$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "users LIKE 'user_verify'");
		if (1 == $query) {		
			if (1 == $verify) {
				$where .= " AND user_verify='1'";
			} elseif (2 == $verify) {
				$where .= " AND user_verify='0'";
			}
		}
		$users = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "users WHERE ID > 0 $where");
			
		$table = '
		<div class="finresults">';
			$r = 0;
			if (function_exists('get_user_discount')) {
				foreach ($users as $user) { 
					$user_id = $user->ID;
					$u_discount = get_user_discount($user_id, $user);
					if ($u_discount >= $discount) {
						$r++;
						$table .= '<div class="finline"><strong><a href="' . pn_edit_user_link($user_id) . '" target="_blank">' . is_user($user->user_login) . '</a></strong> | <strong>' . __('Discount', 'pn') . ':</strong> ' . $u_discount . '%</div>';
					}
				}
			}
			if (0 == $r) {
				$table .= '<div class="finline"><strong>' . __('No users', 'pn') . '</strong></div>';
			}
		$table .= '
		</div>		
		';
			
		$log['table'] = $table;
			
	} else {
		$log['status'] = 'error';
		$log['status_code'] = 1;
		$log['status_text'] = __('You do not have permission', 'pn');
	}	
		
	echo pn_json_encode($log);	
	exit;
}