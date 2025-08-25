<?php
if (!defined('ABSPATH')) { exit(); }

if (!function_exists('_comment_label')) {
	function _comment_label($identifier, $id, $comment = '', $title = '') {
		global $csystem;
		
		$csystem = 1;	
		$comment = pn_strip_input($comment);
		$identifier = pn_string($identifier);
		$id = pn_strip_input($id);
		
		$title = pn_strip_input($title);
		if (strlen($title) < 1) { $title = __('Comment', 'premium'); }
		
		$class = '';
		if ($comment) { 
			$class = 'has_comment'; 
		}
		$temp = '<div class="column_comment_label js_csl ' . $identifier . ' ' . $identifier . '-' . $id . ' ' . $class . '" data-db="' . $identifier . '" data-id="' . $id . '" data-title="' . $title . '"></div>';
			
		return $temp;
	}
}
 
if (!function_exists('comment_system_admin_footer')) {
	add_action('admin_footer', 'comment_system_admin_footer');
	function comment_system_admin_footer() {
		global $csystem;
		
		$csystem = intval($csystem);
		if ($csystem) {		
		?>
<script type="text/javascript">
jQuery(function($) {
	
	$(document).on('click','.js_csl',function() {
		
		$(document).JsWindow('show', { 
			window_class: 'comment_window',
			title: 'loading...',
			content: '<form action="<?php the_pn_link('csl_add'); ?>" class="csl_form_action" method="post"><p><textarea id="csl_the_text" name="comment"></textarea></p><p><input type="submit" name="submit" class="button-primary" value="<?php _e('Save', 'premium'); ?>" /></p><input type="hidden" id="csl_the_id" name="id" value="" /><input type="hidden" id="csl_the_db" name="db" value="" /></form>',
			scrollContent: '<div id="csl_the_scroll" style="height: 80px;"></div>',
			shadow: 0,
			after: after_csl_form
		});		
			
		var id = $(this).attr('data-id');
		var db = $(this).attr('data-db');
		var title = $(this).attr('data-title');
		$('.standart_window_title_ins').html(title);
		$('#csl_the_id').val(id);
		$('#csl_the_db').val(db);
			
		csl_load_comments();
			
		return false;
	});
		
	$(document).on('click','.js_csl_del',function() {
		var id = $(this).attr('data-id');
		var db = $(this).attr('data-db');
		var param = 'id=' + id + '&db=' + db;
		$('.js_csl_del').hide();
			
		$.ajax({
			type: "POST",
			url: "<?php the_pn_link('csl_del');?>",
			dataType: 'json',
			data: param,
			error: function(res, res2, res3) {
				<?php do_action('pn_js_error_response', 'ajax'); ?>
			},			
			success: function(res)
			{		
				if (res['status'] == 'error') {
					<?php do_action('pn_js_alert_response'); ?>
				} else {
					csl_load_comments();
				}	
				$('.js_csl_del').show();
				<?php do_action('csl_del_jsresult'); ?>
			}
		});
	});	
	
	function after_csl_form() {
		
		$('.csl_form_action').ajaxForm({
			dataType:  'json',
			beforeSubmit: function(a, f, o) {
				$('.comment_window input[type=submit]').prop('disabled', true);
			},
			error: function(res, res2, res3) {
				<?php do_action('pn_js_error_response', 'form'); ?>
			},		
			success: function(res) {
				$('.comment_window input[type=submit]').prop('disabled', false);
				
				if (res['status'] && res['status'] == 'error') {
					<?php do_action('pn_js_alert_response'); ?>
				} 
				if (res['status'] && res['status'] == 'success') {
					csl_load_comments();
				}
				<?php do_action('csl_add_jsresult'); ?>
			}
		});	
		
	}	
		
	function csl_load_comments() {
		
		$('.comment_window input[type=submit]').prop('disabled', true);
		$('#csl_the_text').val('').prop('disabled', true);
		$('#csl_the_scroll').html('<center>loading...</center>');
		var id = $('#csl_the_id').val();
		var db = $('#csl_the_db').val();
		var js_button = $('.js_csl[data-id=' + id + '][data-db=' + db + ']');

		var param = 'id=' + id + '&db=' + db;
		$.ajax({
			type: "POST",
			url: "<?php the_pn_link('csl_get'); ?>",
			dataType: 'json',
			data: param,
			error: function(res, res2, res3) {
				<?php do_action('pn_js_error_response', 'ajax'); ?>
			},			
			success: function(res)
			{		
				$('.comment_window input[type=submit]').prop('disabled', false);
					
				if (res['status'] == 'error') {
					<?php do_action('pn_js_alert_response'); ?>
				} else {
					$('#csl_the_text').val(res['comment']);
					$('#csl_the_scroll').html(res['last']);
					if (res['count'] > 0) {
						js_button.addClass('has_comment');
					} else {
						js_button.removeClass('has_comment');
					}
				}	
					
				$('#csl_the_text').prop('disabled', false).focus();
				<?php do_action('csl_get_jsresult'); ?>
			}
		});	
	}
		
});
</script>		
		<?php
		}
	}
} 

if (!function_exists('pn_premium_action_csl_get')) {
	add_action('premium_action_csl_get', 'pn_premium_action_csl_get');
	function pn_premium_action_csl_get() {
		
		_method('post');
		_json_head();

		$log = array();
		$log['status'] = '';
		$log['status_code'] = 0; 
		$log['status_text'] = __('Error', 'premium');	
		$log['count'] = 0;
		$log['last'] = '';

		$id = pn_strip_input(is_param_post('id'));
		$db = pn_strip_input(is_param_post('db'));
		if (current_user_can('read')) {
			$log = apply_filters('csl_get_' . $db, $log, $id);	
		}
		
		echo pn_json_encode($log);
		exit;
	}
}

if (!function_exists('pn_premium_action_csl_add')) {
	add_action('premium_action_csl_add', 'pn_premium_action_csl_add');
	function pn_premium_action_csl_add() {
		
		_method('post');
		_json_head();

		$log = array();
		$log['status'] = '';
		$log['status_code'] = 0; 
		$log['status_text'] = __('Error', 'premium');
		
		$id = pn_strip_input(is_param_post('id'));
		$db = pn_strip_input(is_param_post('db'));
		if (current_user_can('read')) {
			$log = apply_filters('csl_add_' . $db, $log, $id);	
		}
		
		echo pn_json_encode($log);	
		exit;
	}
}

if (!function_exists('pn_premium_action_csl_del')) {
	add_action('premium_action_csl_del', 'pn_premium_action_csl_del');
	function pn_premium_action_csl_del() {
		
		_method('post');
		_json_head();

		$log = array();
		$log['status'] = '';
		$log['status_code'] = 0; 
		$log['status_text'] = __('Error', 'premium');
		
		$id = pn_strip_input(is_param_post('id'));
		$db = pn_strip_input(is_param_post('db'));
		if (current_user_can('read')) {
			$log = apply_filters('csl_del_' . $db, $log, $id);	
		}
		
		echo pn_json_encode($log);	
		exit;
	}
}

function del_syscomments() {
	global $wpdb;
	
	$plugin = get_plugin_class();
	if (!$plugin->is_up_mode()) {
		
		$count_day = get_logs_sett('delete_comments_day');
		$time = current_time('timestamp') - ($count_day * DAY_IN_SECONDS); 
		$ldate = date('Y-m-d H:i:s', $time);
		$items = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "comment_system WHERE comment_date < '$ldate'");
		foreach ($items as $item) {
			$item_id = $item->id;
			$res = apply_filters('item_syscomments_delete_before', pn_ind(), $item_id, $item);
			if ($res['ind']) {
				$result = $wpdb->query("DELETE FROM " . $wpdb->prefix . "comment_system WHERE id = '$item_id'");
				do_action('item_syscomments_delete', $item_id, $item, $result);
			}
		}
		
	}	
}
	 
add_filter('list_cron_func', 'del_syscomments_cron');
function del_syscomments_cron($filters) {
	
	$filters['del_syscomments'] = array(
		'title' => __('Deleting system comments', 'premium'),
		'site' => 'none',
		'file' => 'none',
	);
	
	return $filters;
}

add_filter('list_logs_settings', 'syscomments_list_logs_settings');
function syscomments_list_logs_settings($filters) {	

	$filters['delete_comments_day'] = array(
		'title' => __('Deleting system comments', 'premium'). ' (' . __('days', 'premium') . ')',
		'count' => 10,
		'minimum' => 2,
	);	
	
	return $filters;
}