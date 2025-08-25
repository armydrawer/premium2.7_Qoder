<?php
if (!defined('ABSPATH')) { exit(); }

add_filter('pn_adminpage_title_pn_finstats', 'def_adminpage_title_pn_finstats');
function def_adminpage_title_pn_finstats() {
	
	return __('Financial statistics', 'pn');
}

add_action('pn_adminpage_content_pn_finstats', 'def_pn_admin_content_pn_finstats');
function def_pn_admin_content_pn_finstats() {
	global $wpdb;
?>
<form action="<?php the_pn_link('finstats_form', 'post'); ?>" class="finstats_form" method="post">
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
		$currencies = list_currency(__('No item', 'pn'));		
		?>			
		<div class="fin_list">
			<div class="fin_label"><?php _e('Currency name', 'pn'); ?></div>
			<select name="currency_id" autocomplete="off">
				<?php foreach ($currencies as $key => $currency) { ?>
					<option value="<?php echo $key; ?>"><?php echo $currency; ?></option>
				<?php } ?>
			</select>
		</div>

		<?php
		$currency_codes = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "currency_codes ORDER BY currency_code_title ASC");
		?>		
		<div class="fin_list">
			<div class="fin_label"><?php _e('Currency code', 'pn'); ?></div>
			<select name="currency_code_id" autocomplete="off">
				<option value="0">--<?php _e('No item', 'pn'); ?>--</option>
				<?php foreach ($currency_codes as $item) { ?>
					<option value="<?php echo $item->id; ?>"><?php echo is_site_value($item->currency_code_title); ?><?php echo pn_item_basket($item); ?></option>
				<?php } ?>
			</select>
		</div>		
			<div class="premium_clear"></div>
		
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
		<div class="fin_line"><label><input type="checkbox" name="trans" autocomplete="off" value="1" /> <?php _e('consider corrections of reserve', 'pn'); ?></label></div>
			
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

add_action('premium_action_finstats_form', 'pn_premium_action_finstats_form');
function pn_premium_action_finstats_form() {
	global $wpdb;

	_method('post');
	_json_head();
	
	$log = array();
	$log['status'] = 'success';
	$log['status_code'] = 0; 
	$log['status_text'] = '';	
	
	if (current_user_can('administrator') or current_user_can('pn_finstats')) {
		
		$where1 = $where2 = $where3 = $where4 = '';		
		
		$startdate = is_pn_date(is_param_post('startdate'));
		if ($startdate) {
			$startdate = get_pn_date($startdate, 'Y-m-d 00:00');
			$where1 .= " AND edit_date >= '$startdate'";
			$where2 .= " AND edit_date >= '$startdate'";
			$where3 .= " AND pay_date >= '$startdate'";
			$where4 .= " AND create_date >= '$startdate'";
		}
		$enddate = is_pn_date(is_param_post('enddate'));
		if ($enddate) {
			$enddate = get_pn_date($enddate, 'Y-m-d 00:00');
			$where1 .= " AND edit_date <= '$enddate'";
			$where2 .= " AND edit_date <= '$enddate'";
			$where3 .= " AND pay_date <= '$enddate'";
			$where4 .= " AND create_date <= '$enddate'";
		}	

		$vtype_convert = '';
		
		$currency_id = intval(is_param_post('currency_id'));
		if ($currency_id) {
			$data = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "currency WHERE id = '$currency_id'");
			if (isset($data->id)) {
				$where1 .= " AND currency_id_give = '$currency_id'";
				$where2 .= " AND currency_id_get = '$currency_id'";
				$where3 .= " AND currency_id = '$currency_id'";
				$where4 .= " AND currency_id = '$currency_id'";	
				$vtype_convert = $data->currency_code_title;
			}
		}
		
		$currency_code_id = intval(is_param_post('currency_code_id'));
		if ($currency_code_id) {
			$data = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "currency_codes WHERE id = '$currency_code_id'");
			if (isset($data->id)) {			
				$where1 .= " AND currency_code_id_give = '$currency_code_id'";
				$where2 .= " AND currency_code_id_get = '$currency_code_id'";
				$where3 .= " AND currency_code_id = '$currency_code_id'";
				$where4 .= " AND currency_code_id = '$currency_code_id'";
				$vtype_convert = $data->currency_code_title;
			}
		}	
		
		if (!$vtype_convert) {
			$log['table'] = '<div class="finresults">' . __('Currency code is not chosen', 'pn') . '</div>';
			echo pn_json_encode($log);	
			exit;
		}

		$c_oper = 0;
		$bou = 0;
		$ac_bou = 0;
		$sol = 0;
		$ac_sol = 0;
		$profit = 0;		
		$ac_profit = 0;
		
		$c_oper = $wpdb->get_var("SELECT COUNT(id) FROM " . $wpdb->prefix . "exchange_bids WHERE status IN('payed','realpay','verify','coldsuccess','success') $where1 OR status IN('success') $where2");
		
		$bou_c = $wpdb->get_var("SELECT SUM(sum1r) FROM " . $wpdb->prefix . "exchange_bids WHERE status IN('payed','realpay','verify','coldsuccess','success') $where1");
		$bou = $bou + $bou_c;
		
		$ac_bou_c = $wpdb->get_var("SELECT SUM(sum1r) FROM " . $wpdb->prefix . "exchange_bids WHERE status IN('payed','realpay','verify','coldsuccess','success') $where1");
		$ac_bou = $ac_bou + $ac_bou_c;		
		
		$sol_c = $wpdb->get_var("SELECT SUM(sum2r) FROM " . $wpdb->prefix . "exchange_bids WHERE status = 'success' $where2");
		$sol = $sol + $sol_c;
		
		$ac_sol_c = $wpdb->get_var("SELECT SUM(sum2r) FROM " . $wpdb->prefix . "exchange_bids WHERE status = 'success' $where2");
		$ac_sol = $ac_sol + $ac_sol_c;

		$ppay = intval(is_param_post('ppay'));
		if (1 == $ppay) {
			$query = $wpdb->query("CHECK TABLE " . $wpdb->prefix . "user_payouts");
			if (1 == $query) {
				$partn = $wpdb->get_var("SELECT SUM(pay_sum) FROM " . $wpdb->prefix . "user_payouts WHERE auto_status = '1' AND status = '1' $where3");
				$bou = $bou - $partn;
				$ac_bou = $ac_bou - $partn;
			}
		}
		
		$trans = intval(is_param_post('trans'));
		if (1 == $trans) {
			$tr = $wpdb->get_var("SELECT SUM(trans_sum) FROM " . $wpdb->prefix . "currency_reserv WHERE auto_status = '1' AND id > 0 $where4");
			$bou = $bou + $tr;
			$ac_bou = $ac_bou + $tr;
		}			
		
		$convert = intval(is_param_post('convert'));
		$curs = is_sum(is_param_post('curs'));
		if ($convert) {
			$data = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "currency_codes WHERE id = '$convert'");
			if (isset($data->id)) {
				$bou = convert_sum($bou, $vtype_convert, $data->currency_code_title);
				$sol = convert_sum($sol, $vtype_convert, $data->currency_code_title);
				$ac_bou = convert_sum($ac_bou, $vtype_convert, $data->currency_code_title);
				$ac_sol = convert_sum($ac_sol, $vtype_convert, $data->currency_code_title);
				$vtype_convert = $data->currency_code_title;
			}
		} elseif ($curs > 0) {
			$share = intval(is_param_post('share'));
			$bou = convert_bycourse($bou, $curs, $share);
			$sol = convert_bycourse($sol, $curs, $share);
			$ac_bou = convert_bycourse($ac_bou, $curs, $share);
			$ac_sol = convert_bycourse($ac_sol, $curs, $share);			
			$vtype_convert = 'S';
		}
		
		$profit = $bou - $sol;
		$ac_profit = $ac_bou - $ac_sol;		
		
		$profit = get_sum_color($profit);		
		$ac_profit = get_sum_color($ac_profit);
		
		$table = '
		<div class="finresults">
			<div class="finline"><strong>' . __('Exchange operations in Total', 'pn') . '</strong>: ' . $c_oper . '</div>
			<div class="finline"><strong>' . __('Bought', 'pn') .'</strong>: ' . $bou . ' '. $vtype_convert . '</div>
			<div class="finline"><strong>' . __('Actually bought', 'pn') . '</strong>: ' . $ac_bou . ' ' . $vtype_convert . '</div>
			<div class="finline"><strong>' . __('Sold', 'pn') . '</strong>: ' . $sol . ' ' . $vtype_convert . '</div>
			<div class="finline"><strong>' . __('Actually sold', 'pn') . '</strong>: ' . $ac_sol . ' ' . $vtype_convert . '</div>
			<div class="finline"><strong>' . __('Profit', 'pn') . '</strong>: ' . $profit . ' ' . $vtype_convert . '</div>
			<div class="finline"><strong>' . __('Actuall profit', 'pn') . '</strong>: ' . $ac_profit . ' ' . $vtype_convert . '</div>
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