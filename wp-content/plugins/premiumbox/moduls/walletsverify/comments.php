<?php
if (!defined('ABSPATH')) { exit(); }

add_action('csl_get_walletsverify', 'def_csl_get_walletsverify', 10, 2);
function def_csl_get_walletsverify($log, $id) {
	global $wpdb;
	
	if (current_user_can('administrator') or current_user_can('pn_userwallets')) {
		$id = intval($id);
		$item = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "uv_wallets WHERE id = '$id'");
		$comment = pn_strip_text(is_isset($item, 'comment'));
		$log['status'] = 'success';
		$log['comment'] = $comment;
		$log['last'] = '
		<div class="one_comment">
			<div class="one_comment_text">
				'. $comment .'
			</div>
		</div>
		';
		if (strlen($comment) > 0) {
			$log['count'] = 1;
		} else {
			$log['count'] = 0;
		}
	} else {
		$log['status'] = 'error';
		$log['status_code'] = 1; 
		$log['status_text'] = __('Authorisation Error', 'pn');
	}	
		
	return $log;
}

add_action('csl_add_walletsverify', 'def_csl_add_walletsverify', 10, 2);
function def_csl_add_walletsverify($log, $id) {
	global $wpdb;

	if (current_user_can('administrator') or current_user_can('pn_userwallets')) {
		$id = intval($id);
		$text = pn_strip_input(is_param_post('comment'));
		$wpdb->update($wpdb->prefix . 'uv_wallets', array('comment' => $text), array('id' => $id));
		$log['status'] = 'success';
	} else {
		$log['status'] = 'error';
		$log['status_code'] = 1; 
		$log['status_text'] = __('Authorisation Error', 'pn');
	}	
		
	return $log;
}	