<?php
if (!defined('ABSPATH')) { exit(); }

add_filter('pn_adminpage_title_pn_finstats_bid', 'def_adminpage_title_pn_finstats_bid');
function def_adminpage_title_pn_finstats_bid() {
	
	return __('Financial statistics', 'pn');
}

add_action('pn_adminpage_content_pn_finstats_bid', 'def_adminpage_content_pn_finstats_bid');
function def_adminpage_content_pn_finstats_bid() {
	global $wpdb;
?>
<form action="<?php the_pn_link('finstats_bid_form', 'post'); ?>" class="finstats_form" method="post">
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
		
		<?php
		$currency_codes = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "currency_codes ORDER BY currency_code_title ASC");
		?>		
		<div class="fin_list">
			<div class="fin_label"><?php _e('Convert to', 'pn'); ?></div>

			<select name="convert" autocomplete="off">
				<option value="0">--<?php _e('not to convert', 'pn'); ?>--</option>
				<?php foreach ($currency_codes as $item) { ?>
					<option value="<?php echo $item->id; ?>"><?php echo is_site_value($item->currency_code_title); ?><?php echo pn_item_basket($item); ?></option>
				<?php } ?>
			</select>
		</div>

		<div class="fin_list">
			<div class="fin_label"><?php _e('Individual Central Bank rate', 'pn'); ?></div>
			<input type="text" name="curs" value="" />
		</div>		
			<div class="premium_clear"></div>		
			
		<div class="fin_line"><label><input type="checkbox" name="share" autocomplete="off" value="1" /> <?php _e('multiplied by individual rate', 'pn'); ?></label></div>
		
		<?php
		$query = $wpdb->query("CHECK TABLE " . $wpdb->prefix . "user_payouts");
		if (1 == $query) {
		?>
		<div class="fin_line"><label><input type="checkbox" name="ppay" autocomplete="off" value="1" /> <?php _e('consider affiliate payouts', 'pn'); ?></label></div>
		<?php } ?>
		
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

add_action('premium_action_finstats_bid_form', 'pn_premium_action_finstats_bid_form');
function pn_premium_action_finstats_bid_form() {
	global $wpdb;

	_method('post');
	_json_head();
	
	$log = array();
	$log['status'] = 'success';
	$log['status_code'] = 0; 
	$log['status_text'] = '';	
	
	if (current_user_can('administrator') or current_user_can('pn_finstats')) {
		
		$where1 = $where2 = '';		
		
		$startdate = is_pn_date(is_param_post('startdate'));
		if ($startdate) {
			$startdate = get_pn_date($startdate, 'Y-m-d 00:00');
			$where1 .= " AND edit_date >= '$startdate'";
			$where2 .= " AND pay_date >= '$startdate'";
		}
		
		$enddate = is_pn_date(is_param_post('enddate'));
		if ($enddate) {
			$enddate = get_pn_date($enddate, 'Y-m-d 00:00');
			$where1 .= " AND edit_date <= '$enddate'";
			$where2 .= " AND pay_date <= '$enddate'";
		}	

		$vtype_convert = cur_type();

		$c_oper = 0;
		$profit = 0;		
		
		$c_oper = $wpdb->get_var("SELECT COUNT(id) FROM " . $wpdb->prefix . "exchange_bids WHERE status IN('success') $where1");
		
		$profit_c = $wpdb->get_var("SELECT SUM(profit) FROM " . $wpdb->prefix . "exchange_bids WHERE status IN('success') $where1");
		$profit = $profit + $profit_c;

		$ppay = intval(is_param_post('ppay'));
		if (1 == $ppay) {
			$query = $wpdb->query("CHECK TABLE " . $wpdb->prefix . "user_payouts");
			if (1 == $query) {
				$partn = $wpdb->get_var("SELECT SUM(pay_sum_or) FROM " . $wpdb->prefix . "user_payouts WHERE auto_status = '1' AND status = '1' $where2");
				$profit = $profit - $partn;
			}
		}			
		
		$convert = intval(is_param_post('convert'));
		$curs = is_sum(is_param_post('curs'));
		if ($convert) {
			$data = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "currency_codes WHERE id = '$convert'");
			if (isset($data->id)) {
				$profit = convert_sum($profit, $vtype_convert, $data->currency_code_title);
				$vtype_convert = $data->currency_code_title;
			}
		} elseif ($curs > 0) {
			$share = intval(is_param_post('share'));
			$profit = convert_bycourse($profit, $curs, $share);			
			$vtype_convert = 'S';
		}
		
		$profit = get_sum_color($profit);		
		
		$table = '
		<div class="finresults">
			<div class="finline"><strong>' . __('Exchange operations in Total', 'pn') . '</strong>: ' . $c_oper . '</div>
			<div class="finline"><strong>' . __('Profit', 'pn') . '</strong>: ' . $profit . ' ' . $vtype_convert . '</div>
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