<?php
if (!defined('ABSPATH')) { exit(); }

add_action('tab_direction_tab7', 'tab_direction_tab_x19', 99, 2);
function tab_direction_tab_x19($data, $data_id) {
	?>
	<div class="add_tabs_line">
		<div class="add_tabs_single long">
			<div class="add_tabs_sublabel"><span><?php _e('X19', 'pn'); ?></span></div>
			<div class="premium_wrap_standart">
				<?php 
				$x19mod = intval(get_direction_meta($data_id, 'x19mod')); 
				?>									
				<select name="x19mod" autocomplete="off">
					<?php
					$array = list_x19();
					foreach ($array as $key => $arr) {
					?>
						<option value="<?php echo $key; ?>" <?php selected($x19mod, $key); ?>><?php echo $arr; ?></option>
					<?php } ?>
				</select>
			</div>
		</div>
	</div>		
	<?php
} 

add_action('item_direction_edit', 'item_direction_edit_x19'); 
add_action('item_direction_add', 'item_direction_edit_x19');
function item_direction_edit_x19($data_id) {
	
	$x19mod = intval(is_param_post('x19mod'));
	update_direction_meta($data_id, 'x19mod', $x19mod);
	
}

add_filter('error_bids', 'x19_error_bids', 350, 6);  
function x19_error_bids($error_bids, $direction, $vd1, $vd2, $cdata, $unmetas) {
	
	$x19mod = intval(is_isset($direction, 'x19mod'));
	
	if (0 == count($error_bids['error_text']) and 0 == count($error_bids['error_fields'])) {
		if ($x19mod > 0) {

			$account1 = $error_bids['bid']['account_give'];
			$account2 = $error_bids['bid']['account_get'];

			$arrwm1 = ind_x19();
			if (in_array($x19mod, $arrwm1)) {
				$wmkow = $account1;
				$wmkow2 = $account2;
				$wtype = 1;
			} else {
				$wmkow = $account2;
				$wmkow2 = $account1;
				$wtype = 2;
			}

			$pursetype = 'WM' . mb_strtoupper(mb_substr($wmkow, 0, 1));

			$object = WMXI_X19();
			if (is_object($object)) {	
				
				$darr = wmid_with_purse($object, $wmkow);
				$wmid = $darr['wmid'];
				
				if ($wmid) {
					if (20 == $x19mod) {
						
						$darr2 = wmid_with_purse($object, $wmkow2);
						$wmid2 = $darr2['wmid'];
							
						if ($wmid2) {
							if ($wmid != $wmid2) {
								$error_bids['error_fields']['account1'] = __('Wallet belongs to another WMID', 'pn');
								$error_bids['error_fields']['account2'] = __('Wallet belongs to another WMID', 'pn');
								$error_bids['error_text'][] = __('Wallet belongs to another WMID', 'pn');
							}
						} else {
							if (1 == $wtype) {
								$error_bids['error_fields']['account1'] = __('Wallets belong to different WMIDs', 'pn');
							} else {
								$error_bids['error_fields']['account2'] = __('Wallets belong to different WMIDs', 'pn');											
							}						
						}
						
					} else {					
						
						$amount = is_sum(is_isset($cdata, 'sum1dc')); 
						$fname = is_isset($error_bids['bid'], 'last_name');
						$iname = is_isset($error_bids['bid'], 'first_name');
						$passport = is_isset($error_bids['bid'], 'user_passport');

						$info = info_x19($x19mod, ctv_ml($vd1->psys_title), ctv_ml($vd2->psys_title), $account1, $account2);
						
						$retval = 101010;
						$res = array('retdesc' => 'no');
						
						try {
							$object = WMXI_X19();
							if (is_object($object)) {
								
								$res = $object->X19($info['type'], $info['dir'], $pursetype, $amount, $wmid, $passport, $fname, $iname, $info['bank_name'], $info['bank_account'], $info['card_number'], $info['emoney_name'], $info['emoney_id'], $info['phone'], $info['crypto_name'], $info['crypto_address'])->toArray();
								$retval = is_isset($res, 'retval');
								x19_create_log($direction->id, is_isset($res, 'retdesc'));
								
							} else {
								$retval = 1000;
								$res['retdesc'] = __('X19 interface error', 'pn');
							}
						} catch(Exception $e) {
							$retval = 1000;
							$res['retdesc'] = __('X19 interface error', 'pn');
						}
							
						if (0 == $retval) {
							/* 
								$error_bids['error_text'][] = $res['retdesc'];
							*/					
						} elseif (404 == $retval) {
							$error_bids['error_text'][] = $res['retdesc'];
						} else {
							$error_bids['error_text'][] = $res['retdesc'];
						}
					}
				} else {
					if (1 == $wtype) {
						$error_bids['error_fields']['account1'] = __('Invalid account Send', 'pn');
					} else {
						$error_bids['error_fields']['account2'] = __('Invalid account Receive', 'pn');				
					}
				}
			} else {
				$error_bids['error_text'][] = __('No access to X19 interface. Check settings', 'pn');
			}
		}
	}
		
	return $error_bids;
} 