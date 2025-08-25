<?php
if (!defined('ABSPATH')) { exit(); }

add_filter('pn_adminpage_title_pn_finstats_all', 'def_adminpage_title_pn_finstats_all');
function def_adminpage_title_pn_finstats_all() {
	
	return __('General statistics', 'pn');
}

add_action('pn_adminpage_content_pn_finstats_all', 'def_pn_admin_content_pn_finstats_all');
function def_pn_admin_content_pn_finstats_all() {
	global $wpdb;
?>
<form action="<?php the_pn_link('finstats_all_form', 'post'); ?>" class="finstats_form" method="post">
	<div class="finfiletrs">
		<div class="fin_list">
			<div class="fin_label"><?php _e('Start date', 'pn'); ?></div>
			<input type="search" name="startdate" autocomplete="off" class="js_datepicker" value="" />
		</div>
		<div class="fin_list">
			<div class="fin_label"><?php _e('End date', 'pn'); ?></div>
			<input type="search" name="enddate" autocomplete="off" class="js_datepicker" value="" />
		</div>		
			<div class="premium_clear"></div>

		<input type="submit" name="submit" class="finstat_link" value="<?php _e('Display statistics', 'pn'); ?>" />
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

add_action('premium_action_finstats_all_form', 'pn_premium_action_finstats_all_form');
function pn_premium_action_finstats_all_form() {
	global $wpdb;

	_method('post');
	_json_head();
	
	$log = array();
	$log['status'] = 'success';
	$log['status_code'] = 0; 
	$log['status_text'] = '';	
	
	if (current_user_can('administrator') or current_user_can('pn_finstats')) {
		
		$start_date = '';
		$startdate = pn_strip_input(is_param_post('startdate'));
		if ($startdate) {
			$start_date = get_pn_date($startdate, 'Y-m-d H:i:s');
		}
		
		$end_date = '';
		$enddate = pn_strip_input(is_param_post('enddate'));
		if ($enddate) {
			$end_date = get_pn_date($enddate, 'Y-m-d H:i:s');
		}
		
		$table = '
		<div class="finresults">
			<div class="finline"><strong>' . __('Total users', 'pn') . '</strong>: ' . is_out_sum(get_user_for_site($start_date, $end_date), 0, 'all') . '</div>
			<div class="finline"><strong>' . __('Number of exchanges', 'pn') . '</strong>: ' . get_count_exchanges($start_date, $end_date) . '</div>
			<div class="finline"><strong>' . __('Amount of exchanges', 'pn') . '</strong>: ' . get_sum_exchanges($start_date, $end_date) . ' USD</div>
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