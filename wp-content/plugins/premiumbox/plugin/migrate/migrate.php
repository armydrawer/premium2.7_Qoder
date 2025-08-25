<?php
if (!defined('ABSPATH')) { exit(); }

add_filter('pn_adminpage_title_pn_migrate', 'def_adminpage_title_pn_migrate');
function def_adminpage_title_pn_migrate($page) {
	
	return __('Migration', 'pn');
} 

add_action('pn_adminpage_content_pn_migrate', 'def_adminpage_content_pn_migrate');
function def_adminpage_content_pn_migrate() {
	
	$form = new PremiumForm();
	
	$up_list = array(
		'2.7' => array(
			'step' => 8,
			'step_key' => 27,
			'step_title' => sprintf(__('Migration (if version is lesser than %s)', 'pn'), '2.7'),
		),	
		'2.6' => array(
			'step' => 8,
			'step_key' => 26,
			'step_title' => sprintf(__('Migration (if version is lesser than %s)', 'pn'), '2.6'),
		),	
		'2.5' => array(
			'step' => 7,
			'step_key' => 25,
			'step_title' => sprintf(__('Migration (if version is lesser than %s)', 'pn'), '2.5'),
		),	
		'2.4' => array(
			'step' => 9,
			'step_key' => 24,
			'step_title' => sprintf(__('Migration (if version is lesser than %s)', 'pn'), '2.4'),
		),	
		'2.2' => array(
			'step' => 3,
			'step_key' => 22,
			'step_title' => sprintf(__('Migration (if version is lesser than %s)', 'pn'), '2.2'),
		),	
		'2.1' => array(
			'step' => 5,
			'step_key' => 21,
			'step_title' => sprintf(__('Migration (if version is lesser than %s)', 'pn'), '2.1'),
		),
		'2.0' => array(
			'step' => 29,
			'step_key' => 20,
			'step_title' => sprintf(__('Migration (if version is lesser than %s)', 'pn'), '2.0'),
		),	
		'1.6' => array(
			'step' => 13,
			'step_key' => 16,
			'step_title' => sprintf(__('Migration (if version is lesser than %s)', 'pn'), '1.6'),
		),		
		'speacial' => array(
			'step' => 2,
			'step_key' => 1,
			'step_title' => __('Special migration steps (do not use without instructions)', 'pn'),
		),		
	);
	$up_list = apply_filters('migration_uplist', $up_list);
	foreach ($up_list as $vers => $vers_data) {
?>
<div class="premium_body">
	<div class="premium_standart_div">
		<div style="padding: 0 0 10px 0;">
		<?php
		$form->h3(is_isset($vers_data, 'step_title'), '');
		?>
		</div>
		<?php
 		$r = 0;
		$step = intval(is_isset($vers_data, 'step'));
		$step_key = intval(is_isset($vers_data, 'step_key'));
		while ($r++ < $step) {
		?>		
		<div class="premium_standart_line">		 
			<input name="submit" type="submit" class="button prbar_button" data-count-url="<?php the_pn_link('migrate_step_count', 'post'); ?>&step=<?php echo $step_key; ?>_<?php echo $r; ?>" data-title="<?php printf(__('Step %s', 'pn'),$r); ?>" value="<?php printf(__('Step %s', 'pn'),$r); ?>" />	
			&nbsp;
			<input name="submit" type="submit" class="button prbar_button" data-count-url="<?php the_pn_link('migrate_step_count', 'post'); ?>&step=<?php echo $step_key; ?>_<?php echo $r; ?>&tech=1" data-title="<?php printf(__('Step %s', 'pn'),$r); ?>" value="<?php printf(__('Technical step %s', 'pn'),$r); ?>" />		
		</div>
		<?php 
		}
		?>
	</div>
</div>
	<?php } ?>

<script type="text/javascript">
jQuery(function($) {
	
	$(document).PrBar({ 
		trigger: '.prbar_button',
		start_title: '<?php _e('determining the number of requests', 'pn'); ?>...',
		end_title: '<?php _e('number of requests defined', 'pn'); ?>',
		found_title: '<?php _e('Found: %count% requests', 'pn'); ?>',
		perform_title: '<?php _e('Perform', 'pn'); ?>:',
		step_title: '<?php _e('Step', 'pn'); ?>:',
		run_title: '<?php _e('Run', 'pn'); ?>',
		line_text: '<?php _e('completed %now% of %max% steps', 'pn'); ?>',
		line_success: '<?php _e('step %now% is successful', 'pn'); ?>',
		end_progress: '<?php _e('action is completed', 'pn'); ?>',
		success: function(res) {
			res.prop('disabled', true);
		}
	});
	
});
</script>
<?php
}

add_action('premium_action_migrate_step_count', 'def_premium_action_migrate_step_count');
function def_premium_action_migrate_step_count() {
	global $wpdb;	

	_json_head();
	_method('post');

	$log = array();
	$log['status'] = '';
	$log['status_code'] = 0; 
	$log['status_text'] = '';
	$log['count'] = 0;
	$log['link'] = '';
	
	$step = is_param_get('step');
	$tech = intval(is_param_get('tech'));
	if (current_user_can('administrator')) {
		$count = 0;
		
		if (!$tech) {
			
			if ('1_1' == $step) {
				$count = 1;
			}

			if ('1_2' == $step) {
				$count = $wpdb->get_var("SELECT COUNT(id) FROM " . $wpdb->prefix . "exchange_bids");		
			}			

			if ('16_1' == $step) {
				$count = 1;
			}
			
			if ('16_2' == $step) {
				$count = 1;
			}

			if ('16_3' == $step) {
				$query = $wpdb->query("CHECK TABLE " . $wpdb->prefix . "currency_custom_fields");
				if (1 == $query) {
					$count = $wpdb->get_var("SELECT COUNT(id) FROM " . $wpdb->prefix . "currency_custom_fields WHERE auto_status = '1'");
				}				
			}

			if ('16_4' == $step) {
				
				$arr = array(
					array(
						'tbl' => 'currency_custom_fields',
						'row' => 'firstzn',
					),
					array(
						'tbl' => 'currency',
						'row' => 'firstzn',					
					),
					array(
						'tbl' => 'direction_custom_fields',
						'row' => 'firstzn',
					),					
				);
				$count = count($arr);
				
			}

			if ('16_7' == $step) {
				$query = $wpdb->query("CHECK TABLE " . $wpdb->prefix . "valuts_account");
				if (1 == $query) {				
					$count = $wpdb->get_var("SELECT COUNT(id) FROM " . $wpdb->prefix . "valuts_account");
				}
			}

			if ('16_8' == $step) {
				$query = $wpdb->query("CHECK TABLE " . $wpdb->prefix . "directions");
				if (1 == $query) {				
					$count = $wpdb->get_var("SELECT COUNT(id) FROM " . $wpdb->prefix . "directions");
				}
			}

			if ('16_9' == $step) {				
				$count = 1;
			}			

			if ('16_10' == $step) {
				$query = $wpdb->query("CHECK TABLE " . $wpdb->prefix . "uv_field");
				if (1 == $query) {				
					$count = $wpdb->get_var("SELECT COUNT(id) FROM " . $wpdb->prefix . "uv_field");
				}
			}			
			
			if ('16_11' == $step) {
				$query = $wpdb->query("CHECK TABLE " . $wpdb->prefix . "uv_field_user");
				if (1 == $query) {			
					$count = $wpdb->get_var("SELECT COUNT(id) FROM " . $wpdb->prefix . "uv_field_user");
				}
			}

			if ('16_12' == $step) {
				$query = $wpdb->query("CHECK TABLE " . $wpdb->prefix . "user_wallets");
				if (1 == $query) {				
					$count = $wpdb->get_var("SELECT COUNT(id) FROM " . $wpdb->prefix . "exchange_bids");
				}
			}

			if ('16_13' == $step) {
				$query = $wpdb->query("CHECK TABLE " . $wpdb->prefix . "uv_wallets_files");
				if (1 == $query) {				
					$count = $wpdb->get_var("SELECT COUNT(id) FROM " . $wpdb->prefix . "uv_wallets_files");
				}
			}

			if ('20_1' == $step) {
				$count = 1;
			}

			if ('20_2' == $step) {
				$count = 1;
			}
			
			if ('20_3' == $step) {
				$count = 1;
			}	

			if ('20_4' == $step) {
				$count = $wpdb->get_var("SELECT COUNT(ID) FROM " . $wpdb->prefix . "users");		
			}

			if ('20_5' == $step) {
				$count = 1;		
			}

			if ('20_6' == $step) { 
				$query = $wpdb->query("CHECK TABLE " . $wpdb->prefix . "uv_field");
				if (1 == $query) {				
					$count = $wpdb->get_var("SELECT COUNT(id) FROM " . $wpdb->prefix . "uv_field");
				}
			}

			if ('20_7' == $step) {
				$query = $wpdb->query("CHECK TABLE " . $wpdb->prefix . "geoip_blackip");
				if (1 == $query) {				
					$count = $wpdb->get_var("SELECT COUNT(id) FROM " . $wpdb->prefix . "geoip_blackip");
				}
			}

			if ('20_8' == $step) {
				$query = $wpdb->query("CHECK TABLE " . $wpdb->prefix . "geoip_whiteip");
				if (1 == $query) {				
					$count = $wpdb->get_var("SELECT COUNT(id) FROM " . $wpdb->prefix . "geoip_whiteip");
				}
			}

			if ('20_9' == $step) {
				$query = $wpdb->query("CHECK TABLE " . $wpdb->prefix . "geoip_country");
				if (1 == $query) {				
					$count = 1;
				}
			}

			if ('20_10' == $step) {
				$query = $wpdb->query("CHECK TABLE " . $wpdb->prefix . "directions");
				if (1 == $query) {				
					$count = $wpdb->get_var("SELECT COUNT(id) FROM " . $wpdb->prefix . "directions");
				}
			}

			if ('20_11' == $step) {
				$query = $wpdb->query("CHECK TABLE " . $wpdb->prefix . "exchange_bids");
				if (1 == $query) {				
					$count = $wpdb->get_var("SELECT COUNT(id) FROM " . $wpdb->prefix . "exchange_bids WHERE status = 'success'");
				}
			}

			if ('20_12' == $step) {
				$query = $wpdb->query("CHECK TABLE " . $wpdb->prefix . "archive_exchange_bids");
				if (1 == $query) {				
					$count = $wpdb->get_var("SELECT COUNT(id) FROM " . $wpdb->prefix . "archive_exchange_bids WHERE status = 'success'");
				}
			}			

			if ('20_13' == $step) {
				$query = $wpdb->query("CHECK TABLE " . $wpdb->prefix . "parser_pairs");
				if (1 == $query) {				
					$count = $wpdb->get_var("SELECT COUNT(id) FROM " . $wpdb->prefix . "parser_pairs");
				}
			}

			if ('20_16' == $step) {
				
				$arr = array(
					array(
						'tbl' => 'directions',
						'row' => 'm_in',
					),
					array(
						'tbl' => 'directions',
						'row' => 'm_out',					
					),					
				);
				$count = count($arr);
				
			} 

			if ('20_17' == $step) {
				$query = $wpdb->query("CHECK TABLE " . $wpdb->prefix . "directions");
				if (1 == $query) {				
					$count = $wpdb->get_var("SELECT COUNT(id) FROM " . $wpdb->prefix . "directions");
				}
			}

			if ($step == '20_18') {
				$query = $wpdb->query("CHECK TABLE " . $wpdb->prefix . "directions");
				if (1 == $query) {				
					$count = $wpdb->get_var("SELECT COUNT(id) FROM " . $wpdb->prefix . "directions");
				}
			}

			if ('20_19' == $step) {
				$query = $wpdb->query("CHECK TABLE " . $wpdb->prefix . "directions");
				if (1 == $query) {				
					$count = $wpdb->get_var("SELECT COUNT(id) FROM " . $wpdb->prefix . "directions");
				}
			}

			if ('20_21' == $step) {				
				$count = 1;
			}

			if ('20_22' == $step) {				
				$count = 1;
			}

			if ('20_23' == $step) {				
				$count = 1;
			}

			if ('20_24' == $step) {				
				$count = 1;
			}
			
			if ('20_25' == $step) {				
				$count = 1;
			}
			
			if ('20_26' == $step) {
				$query = $wpdb->query("CHECK TABLE " . $wpdb->prefix . "directions");
				if (1 == $query) {				
					$count = $wpdb->get_var("SELECT COUNT(id) FROM " . $wpdb->prefix . "directions");
				}
			}

			if ('20_27' == $step) {
				$query = $wpdb->query("CHECK TABLE " . $wpdb->prefix . "recalc_bids");
				if (1 == $query) {				
					$count = $wpdb->get_var("SELECT COUNT(id) FROM " . $wpdb->prefix . "recalc_bids");
				}
			}

			if ('20_28' == $step) {
				$query = $wpdb->query("CHECK TABLE " . $wpdb->prefix . "currency");
				if (1 == $query) {				
					$count = $wpdb->get_var("SELECT COUNT(id) FROM " . $wpdb->prefix . "currency");
				}
			}			

			if ('20_29' == $step) {
				$count = 1;
			}

			if ('21_1' == $step) {
				$count = $wpdb->get_var("SELECT COUNT(ID) FROM " . $wpdb->prefix . "users");		
			}

			if ('21_3' == $step) {
				$count = $wpdb->get_var("SELECT COUNT(id) FROM " . $wpdb->prefix . "exchange_bids");		
			}

			if ('21_4' == $step) {
				$query = $wpdb->query("CHECK TABLE " . $wpdb->prefix . "currency_accounts");
				if (1 == $query) {
					$count = $wpdb->get_var("SELECT COUNT(id) FROM " . $wpdb->prefix . "currency_accounts");	
				}	
			}

			if ('21_5' == $step) {
				$count = 1;
			}

			if ('22_1' == $step) {
				$query = $wpdb->query("CHECK TABLE " . $wpdb->prefix . "bidstatus");
				if (1 == $query) {
					$count = $wpdb->get_var("SELECT COUNT(id) FROM " . $wpdb->prefix . "bidstatus");	
				}	
			}

			if ('22_2' == $step) {
				$query = $wpdb->query("CHECK TABLE " . $wpdb->prefix . "recalcs");
				if (1 == $query) {				
					$count = $wpdb->get_var("SELECT COUNT(id) FROM " . $wpdb->prefix . "recalcs");
				}
			}

			if ('22_3' == $step) {
				$count = 1;		
			}

			if ('24_1' == $step) {
				$query = $wpdb->query("CHECK TABLE " . $wpdb->prefix . "auto_removal_bids");
				if (1 == $query) {				
					$count = $wpdb->get_var("SELECT COUNT(id) FROM " . $wpdb->prefix . "auto_removal_bids");
				}
			}

			if ('24_2' == $step) {
				$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "directions LIKE 'naps_lang'"); 
				if ($query) {				
					$count = $wpdb->get_var("SELECT COUNT(id) FROM " . $wpdb->prefix . "directions");
				}
			}

			if ('24_3' == $step) {
				$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "directions LIKE 'not_ip'"); 
				if ($query) {				
					$count = $wpdb->get_var("SELECT COUNT(id) FROM " . $wpdb->prefix . "directions");
				}
			}

			if ('24_4' == $step) { 
				$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "directions LIKE 'not_country'"); 
				if ($query) {				
					$count = $wpdb->get_var("SELECT COUNT(id) FROM " . $wpdb->prefix . "directions");
				}
			}

			if ('24_5' == $step) {
				$count = 1;		
			}

			if ('24_6' == $step) {
				$count = 1;		
			}

			if ('24_7' == $step) {
				$count = 1;		
			}	

			if ('24_8' == $step) {
				$count = 1;		
			}

			if ('24_9' == $step) {
				$count = $wpdb->get_var("SELECT COUNT(id) FROM " . $wpdb->prefix . "exchange_bids");
			}

			if ('25_1' == $step) {
				$count = 1;		
			}

			if ('25_2' == $step) {
				$count = 1;		
			}

			if ('25_3' == $step) {
				$query = $wpdb->query("CHECK TABLE " . $wpdb->prefix . "naps_dopsumcomis");
				if (1 == $query) {				
					$count = $wpdb->get_var("SELECT COUNT(id) FROM " . $wpdb->prefix . "naps_dopsumcomis");
				}
			}

			if ('25_4' == $step) {
				$query = $wpdb->query("CHECK TABLE " . $wpdb->prefix . "naps_reservcurs");
				if (1 == $query) {				
					$count = $wpdb->get_var("SELECT COUNT(id) FROM " . $wpdb->prefix . "naps_reservcurs");
				}
			}

			if ('25_5' == $step) {
				$query = $wpdb->query("CHECK TABLE " . $wpdb->prefix . "naps_sumcurs");
				if (1 == $query) {				
					$count = $wpdb->get_var("SELECT COUNT(id) FROM " . $wpdb->prefix . "naps_sumcurs");
				}
			} 

			if ('25_6' == $step) {
				$count = 1;
			}

			if ('26_1' == $step) {
				$count = 1;
			}

			if ('26_2' == $step) {
				$arr = array(
					array(
						'tbl' => 'currency',
						'row' => 'reserv_calc',
					),
					array(
						'tbl' => 'directions',
						'row' => 'reserv_calc',					
					),					
				);
				$count = count($arr);			
			} 

			if ('26_3' == $step) {
				$query = $wpdb->query("CHECK TABLE " . $wpdb->prefix . "bestchange_currency_codes");
				if (1 == $query) {				
					$count = $wpdb->get_var("SELECT COUNT(id) FROM " . $wpdb->prefix . "bestchange_currency_codes");
				}
			} 

			if ('26_4' == $step) {
				$query = $wpdb->query("CHECK TABLE " . $wpdb->prefix . "bestchange_cities");
				if (1 == $query) {				
					$count = $wpdb->get_var("SELECT COUNT(id) FROM " . $wpdb->prefix . "bestchange_cities");
				}
			} 

			if ('26_5' == $step) {
				$query = $wpdb->query("CHECK TABLE " . $wpdb->prefix . "bestchange_directions");
				if (1 == $query) {				
					$count = $wpdb->get_var("SELECT COUNT(id) FROM " . $wpdb->prefix . "bestchange_directions");
				}
			}

			if ('26_6' == $step) {
				$query = $wpdb->query("CHECK TABLE " . $wpdb->prefix . "directions");
				if (1 == $query) {				
					$count = $wpdb->get_var("SELECT COUNT(id) FROM " . $wpdb->prefix . "directions");
				}
			} 

			if ('26_7' == $step) {
				$count = $wpdb->get_var("SELECT COUNT(id) FROM ". $wpdb->prefix ."currency_codes");
			}	

			if ('26_8' == $step) {
				$count = $wpdb->get_var("SELECT COUNT(ID) FROM ". $wpdb->prefix ."users");
			}

			if ('27_1' == $step) {
				$count = 1;			
			}

			if ('27_2' == $step) {
				$count = 1;			
			}

			if ('27_3' == $step) {
				$count = 1;			
			}

			if ('27_4' == $step) {
				$count = 1;			
			}

			if ('27_5' == $step) {
				$count = 1;
			}

			if ('27_6' == $step) {
				$query = $wpdb->query("CHECK TABLE " . $wpdb->prefix . "directions");
				if (1 == $query) {				
					$count = $wpdb->get_var("SELECT COUNT(id) FROM " . $wpdb->prefix . "directions");
				}
			}

 			if ('27_7' == $step) {
				$query = $wpdb->query("CHECK TABLE " . $wpdb->prefix . "exchange_bids");
				if (1 == $query) {				
					$count = $wpdb->get_var("SELECT COUNT(id) FROM " . $wpdb->prefix . "exchange_bids");
				}
			}

			if ('27_8' == $step) {
				$query = $wpdb->query("CHECK TABLE " . $wpdb->prefix . "uv_wallets_files");
				if (1 == $query) {				
					$count = $wpdb->get_var("SELECT COUNT(id) FROM " . $wpdb->prefix . "uv_wallets_files");
				}
			}			

			$count = apply_filters('migration_count', $count, $step);
		}
		
		$log['status'] = 'success';
		$log['count'] = $count;
		$log['link'] = pn_link('migrate_step_request', 'post') . '&step=' . $step;
		$log['status_text'] = __('Ok!', 'pn');

	} else {
		$log['status'] = 'error';
		$log['status_code'] = 1; 
		$log['status_text'] = __('Error! Insufficient privileges', 'pn');
	}
	
	echo pn_json_encode($log);
	exit;	
}

add_action('premium_action_migrate_step_request', 'def_premium_action_migrate_step_request');
function def_premium_action_migrate_step_request() {
	global $wpdb, $premiumbox;	

	_json_head();
	_method('post');

	$log = array();
	$log['status'] = '';
	$log['status_code'] = 0; 
	$log['status_text'] = '';
	$log['count'] = 0;
	$log['link'] = '';
	
	$step = is_param_get('step');
	$num_page = intval(is_param_post('num_page'));
	$limit = intval(is_param_post('limit')); if ($limit < 1) { $limit = 1; }
	$offset = ($num_page - 1) * $limit;
	if (current_user_can('administrator')) {										

		if ('16_1' == $step) {	 /*****************/
			
			$premiumbox->update_option('checkpersdata', 'contactform', 1);
			$premiumbox->update_option('checkpersdata', 'reviewsform', 1);
			
			$reserv_out = get_option('reserv_out');
			if (is_array($reserv_out)) { 
				$premiumbox->update_option('reserv', 'out', $reserv_out);
				delete_option('reserv_out');
			}

			$reserv_in = get_option('reserv_in');
			if (is_array($reserv_in)) { 
				$premiumbox->update_option('reserv', 'in', $reserv_in);
				delete_option('reserv_in');
			}
			
			$reserv_auto = get_option('reserv_auto');
			if (is_array($reserv_auto)) { 
				$premiumbox->update_option('reserv', 'auto', $reserv_auto);
				delete_option('reserv_auto');
			}
			
			$premiumbox->delete_option('wchecks', '');
			
			$wp_upload_dir = wp_upload_dir();
			$path = $wp_upload_dir['basedir'];
			$dir = trailingslashit($path . '/captcha/');
			full_del_dir($dir);
			
		}

		if ('16_2' == $step) {	 /*****************/ 
		
			$tables = array(
				'warning_mess','head_mess', 'operator_schedules','change','term_meta','vtypes','login_check',
				'admin_captcha', 'admin_captcha_plus','standart_captcha','standart_captcha_plus','valuts_meta','valuts',
				'custom_fields_valut','custom_fields','cf_naps','masschange','user_accounts','uv_accounts_files',
				'naps_meta','naps_order','userverify','geoip_template','naps','autodel_bids_time','reserve_requests',
				'trans_reserv','archive_bids','payoutuser','valuts_fstats','vtypes_fstats','bids_fstats',
				'bcbroker_vtypes','bcbroker_naps',
			);
			foreach ($tables as $tbl) {
				$query = $wpdb->query("CHECK TABLE " . $wpdb->prefix . $tbl);
				if (1 == $query) {
					$wpdb->query("DROP TABLE " . $wpdb->prefix . $tbl);
				}	 
			}
			
		}

		if ('16_3' == $step) {	 /*****************/
			$query = $wpdb->query("CHECK TABLE " . $wpdb->prefix . "currency_custom_fields");
			if (1 == $query) {
				$datas = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "currency_custom_fields WHERE auto_status = '1' LIMIT {$offset},{$limit}");
				foreach ($datas as $data) {
					$id = $data->id;
					$array = array();
					
					$helps = pn_strip_input(is_isset($data, 'helps'));
					$array['helps_give'] = $helps;
					$array['helps_get'] = $helps;
					
					$wpdb->update($wpdb->prefix . "currency_custom_fields", $array, array('id' => $id));

					$currency_id = intval(is_isset($data, 'currency_id'));
					$place_id = intval(is_isset($data, 'place_id'));
					
					$cc_count = $wpdb->get_var("SELECT COUNT(id) FROM " . $wpdb->prefix . "cf_currency WHERE currency_id = '$currency_id' AND cf_id = '$id'");
					if (0 == $cc_count) {
						$arr = array();
						$arr['currency_id'] = $currency_id;
						$arr['cf_id'] = $id;
						if (0 == $place_id) {
							$arr['place_id'] = 1;
							$wpdb->insert($wpdb->prefix . "cf_currency", $arr);
							$arr['place_id'] = 2;
							$wpdb->insert($wpdb->prefix . "cf_currency", $arr);
						} else {
							$arr['place_id'] = $place_id;
							$wpdb->insert($wpdb->prefix . "cf_currency", $arr);
						}
					}
				}
			}
		}

		if ('16_4' == $step) {	 /*****************/

			$arr = array(
				array(
					'tbl' => 'currency_custom_fields',
					'row' => 'firstzn',
				),
				array(
					'tbl' => 'currency',
					'row' => 'firstzn',					
				),	
				array(
					'tbl' => 'direction_custom_fields',
					'row' => 'firstzn',
				),
			);
			$arr = array_slice($arr, $offset, $limit);
			
			foreach ($arr as $data) {
				$table = $wpdb->prefix . $data['tbl'];
				$query = $wpdb->query("CHECK TABLE {$table}");
				if (1 == $query) {
					$row = $data['row'];
					$que = $wpdb->query("SHOW COLUMNS FROM {$table} LIKE '{$row}'");
					if ($que) {
						$wpdb->query("ALTER TABLE {$table} CHANGE `{$row}` `{$row}` varchar(150) NOT NULL");
					}	
				}
			}	

			$in = intval($premiumbox->get_option('txtxml', 'txt'));
			if ($in) {
				$premiumbox->update_option('txtxml', 'site_txt', 1);
			}
			$in = intval($premiumbox->get_option('txtxml', 'xml'));
			if ($in) {
				$premiumbox->update_option('txtxml', 'site_xml', 1);
			}
			
		}

		if ('16_7' == $step) {	 /*****************/
			$query = $wpdb->query("CHECK TABLE " . $wpdb->prefix . "valuts_account");
			if (1 == $query) {
				$datas = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "valuts_account LIMIT {$offset},{$limit}");
				foreach ($datas as $data) {
					$id = $data->id;
					
					$array = array();
					$array['currency_id'] = is_isset($data, 'valut_id');
					$array['count_visit'] = is_isset($data, 'count_visit');
					$array['max_visit'] = is_isset($data, 'max_visit');
					$array['text_comment'] = is_isset($data, 'text_comment');
					$array['inday'] = is_isset($data, 'inday');
					$array['inmonth'] = is_isset($data, 'inmonth');
					$array['accountnum'] = is_isset($data, 'accountnum');
					$array['status'] = is_isset($data, 'status');
					
					$cc_count = $wpdb->get_var("SELECT COUNT(id) FROM " . $wpdb->prefix . "currency_accounts WHERE id = '$id'");
					if (0 == $cc_count) {
						$array['id'] = $id;
						$wpdb->insert($wpdb->prefix . "currency_accounts", $array);	
					} else {
						$wpdb->update($wpdb->prefix . "currency_accounts", $array, array('id' => $id));	
					}										
				}
			}
		}

		if ('16_8' == $step) {
			$query = $wpdb->query("CHECK TABLE " . $wpdb->prefix . "directions");
			if (1 == $query) {
				$datas = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "directions LIMIT {$offset},{$limit}");
				foreach ($datas as $data) {
					$data_id = $data->id;
					$array_pp_data = get_direction_meta($data_id, 'pp_data');
					if (!is_array($array_pp_data)) {
						$pp_data = array();
						$pp_data['enable'] = intval(get_direction_meta($data_id, 'p_enable'));
						$pp_data['ind_sum'] = get_direction_meta($data_id, 'p_ind_sum');
						$pp_data['min_sum'] = get_direction_meta($data_id, 'p_min_sum');
						$pp_data['max_sum'] = get_direction_meta($data_id, 'p_max_sum');
						$pp_data['pers'] = get_direction_meta($data_id, 'p_pers');
						$pp_data['max'] = get_direction_meta($data_id, 'p_max');
						update_direction_meta($data_id, 'pp_data', $pp_data);
					}
					
					delete_direction_meta($data_id, 'p_enable');
					delete_direction_meta($data_id, 'p_ind_sum');
					delete_direction_meta($data_id, 'p_min_sum');
					delete_direction_meta($data_id, 'p_max_sum');
					delete_direction_meta($data_id, 'p_pers');
					delete_direction_meta($data_id, 'p_max');
					
					$verify = intval(get_direction_meta($data_id, 'verify'));
					if ($verify) {
						$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "directions LIKE 'verify'");
						if (1 == $query) {
							$array = array();
							$array['verify'] = $verify;
							$wpdb->update($wpdb->prefix . "directions", $array, array('id' => $data_id));
						}
					}
					delete_direction_meta($data_id, 'verify');
				}				
			}
		}	

		if ('16_9' == $step) {
			$text = pn_strip_text($premiumbox->get_option('usve', 'text_notverify'));
			if ($text) {
				$premiumbox->update_option('naps_temp', 'notverify_text', $text);
				$premiumbox->update_option('naps_nodescr', 'notverify_text', 1);
				$premiumbox->delete_option('usve', 'text_notverify');
			}
			
			$text = pn_strip_text($premiumbox->get_option('usve','text_notverifysum'));
			if ($text) {
				$premiumbox->update_option('naps_temp', 'notverify_bysum', $text);
				$premiumbox->update_option('naps_nodescr', 'notverify_bysum', 1);
				$premiumbox->delete_option('usve', 'text_notverifysum');
			}			
		}
		
		if ('16_10' == $step) {
			$query = $wpdb->query("CHECK TABLE " . $wpdb->prefix . "uv_field");
			if (1 == $query) {
				$datas = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "uv_field LIMIT {$offset},{$limit}");
				foreach ($datas as $data) {
					$data_id = $data->id;
					$fieldvid = $data->fieldvid;
					
					$wpdb->query("UPDATE " . $wpdb->prefix . "uv_field_user SET fieldvid = '$fieldvid' WHERE uv_field = '$data_id'");
				}				
			}
		}

		if ('16_11' == $step) {
			$query = $wpdb->query("CHECK TABLE " . $wpdb->prefix . "uv_field_user");
			if (1 == $query) {
				$my_dir = wp_upload_dir();
				$datas = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "uv_field_user LIMIT {$offset},{$limit}");
				foreach ($datas as $data) {
					$old_file = $my_dir['basedir'] . '/userverify/' . $data->uv_id . '/' . $data->uv_data;
					if (is_file($old_file)) {
						$path = $premiumbox->upload_dir . '/';
						$path2 = $path . 'userverify/';
						$path3 = $path . 'userverify/' . $data->uv_id . '/';
						if (!is_dir($path)) { 
							@mkdir($path, 0777);
						}
						if (!is_dir($path2)) { 
							@mkdir($path2, 0777);
						}	
						if (!is_dir($path3)) { 
							@mkdir($path3, 0777);
						}
						
						$fdata = @file_get_contents($old_file);
						$fdata = str_replace('*', '%star%', $fdata);
						
						$file = $path3 . $data->id . '.php';
						
						$file_text = add_phpf_data($fdata);
						
						$file_open = @fopen($file, 'w');
						@fwrite($file_open, $file_text);
						@fclose($file_open);
						
						if (is_file($file)) {
							@unlink($old_file);
						}
					}
				}				
			}
		}

		if ('16_12' == $step) {
			$query = $wpdb->query("CHECK TABLE " . $wpdb->prefix . "user_wallets");
			if (1 == $query) {				
				$datas = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "exchange_bids LIMIT {$offset},{$limit}");
				foreach ($datas as $data) {
					$data_id = $data->id;
					$array = array();
					$user_id = $data->user_id;
					if ($user_id) {
						$account = $data->account_give;
						$cc = $wpdb->get_var("SELECT COUNT(id) FROM " . $wpdb->prefix . "user_wallets WHERE user_id = '$user_id' AND verify = '1' AND accountnum = '$account'");
						if ($cc > 0) {	
							$array['accv_give'] = 1;
						}
						$account = $data->account_get;
						$cc = $wpdb->get_var("SELECT COUNT(id) FROM " . $wpdb->prefix . "user_wallets WHERE user_id = '$user_id' AND verify = '1' AND accountnum = '$account'");
						if ($cc > 0) {	
							$array['accv_get'] = 1;
						}						
						if (count($array) > 0) {
							$wpdb->update($wpdb->prefix . "exchange_bids", $array, array('id' => $data_id)); 	
						}
					}
				}	
			}
		}

		if ('16_13' == $step) {
			$query = $wpdb->query("CHECK TABLE " . $wpdb->prefix . "uv_wallets_files");
			if (1 == $query) {
				$my_dir = wp_upload_dir();
				$datas = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "uv_wallets_files LIMIT {$offset},{$limit}");
				foreach ($datas as $data) {
					$old_file = $my_dir['basedir'] . '/accountverify/' . $data->uv_wallet_id . '/' . $data->uv_data;
					if (is_file($old_file)) {
						
						$path = $premiumbox->upload_dir . '/';
						$path2 = $path . 'accountverify/';
						$path3 = $path . 'accountverify/' . $data->uv_wallet_id . '/';
						if (!is_dir($path)) { 
							@mkdir($path , 0777);
						}
						if (!is_dir($path2)) { 
							@mkdir($path2 , 0777);
						}	
						if (!is_dir($path3)) { 
							@mkdir($path3 , 0777);
						}
						
						$fdata = @file_get_contents($old_file);
						$fdata = str_replace('*', '%star%', $fdata);
						
						$file = $path3 . $data->id . '.php';

						$file_text = add_phpf_data($fdata);
						
						$file_open = @fopen($file, 'w');
						@fwrite($file_open, $file_text);
						@fclose($file_open);
						if (is_file($file)) {
							@unlink($old_file);
						}
						
					}
				}				
			}
		}		

		if ('20_1' == $step) { 
		
			$lang = get_option('pn_lang');
			if (!is_array($lang)) { $lang = array(); } 
			
			if (!isset($lang['lang_redir'])) {
				$lr = $premiumbox->get_option('lang_redir');
				$lang['lang_redir'] = intval($lr);
				update_option('pn_lang', $lang);
				
				$premiumbox->delete_option('lang_redir');
			}
			
		}

		if ('20_2' == $step) { 
			$pn_notify = get_option('pn_notify');
			if (is_array($pn_notify)) {
				$email_notify = is_isset($pn_notify, 'email');
				update_option('pn_notify_email', $email_notify);
				$sms_notify = is_isset($pn_notify, 'sms');
				update_option('pn_notify_sms', $sms_notify);
				delete_option('pn_notify', $pn_notify);
			}
		}

		if ('20_3' == $step) {
			$mail_data = get_option('pn_mailtemp_modul');
			if (is_array($mail_data)) {
				$premiumbox->update_option('email', 'mail', is_isset($mail_data, 'mail'));
				$premiumbox->update_option('email', 'name', is_isset($mail_data, 'name'));
				delete_option('pn_mailtemp_modul');
			}
		}	

		if ('20_4' == $step) {	 /*****************/
			$datas = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "users LIMIT {$offset},{$limit}");
			foreach ($datas as $data) {
				$id = $data->ID;
				
				$array = array();
				$array['alogs_email'] = intval(is_isset($data, 'sec_login'));
				$wpdb->update($wpdb->prefix . "users", $array, array('ID' => $id));	

				$um_value = is_isset($data, 'user_url');
				update_user_meta($id, 'user_website', $um_value) or add_user_meta($id, 'user_website', $um_value, true);
			} 
		}

		if ('20_5' == $step) {	 /*****************/ 
			$arr = array('news_key', 'news_descr', 'ogp_news_img', 'ogp_news_title', 'ogp_news_descr');
			foreach ($arr as $k) {
				$nk = str_replace('news', 'post', $k);
				$wpdb->query("UPDATE " . $wpdb->prefix . "pn_options SET meta_key2 = '$nk' WHERE meta_key = 'seo' AND meta_key2 = '$k'");
			}
		}

		if ('20_6' == $step) {
			$query = $wpdb->query("CHECK TABLE " . $wpdb->prefix . "uv_field");
			if (1 == $query) {
				$datas = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "uv_field LIMIT {$offset},{$limit}");
				foreach ($datas as $data) {
					$id = $data->id;
					$country = is_isset($data, 'country');
					$uns = @unserialize($country);
					if (!is_array($uns)) {
						$countries = array();
						if (preg_match_all('/\[d](.*?)\[\/d]/s', $country, $match, PREG_PATTERN_ORDER)) {
							$countries = $match[1];
						}	
						$arr = array();
						if (count($countries) > 0) {
							$arr['country'] = serialize($countries);
						} else {
							$arr['country'] = '';
						}
						$wpdb->update($wpdb->prefix . "uv_field", $arr, array('id' => $id));
					}
				}				
			}
		}

		if ('20_7' == $step) {
			$query = $wpdb->query("CHECK TABLE " . $wpdb->prefix . "geoip_blackip");
			if (1 == $query) {
				$datas = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "geoip_blackip LIMIT {$offset},{$limit}");
				foreach ($datas as $data) {
					$arr = array();
					$arr['theip'] = $data->theip;
					$arr['thetype'] = 0;
					$wpdb->insert($wpdb->prefix . "geoip_ips", $arr);
				}				
			}
		}

		if ('20_8' == $step) {
			$query = $wpdb->query("CHECK TABLE " . $wpdb->prefix . "geoip_whiteip");
			if (1 == $query) {
				$datas = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "geoip_whiteip LIMIT {$offset},{$limit}");
				foreach ($datas as $data) {
					$arr = array();
					$arr['theip'] = $data->theip;
					$arr['thetype'] = 1;
					$wpdb->insert($wpdb->prefix . "geoip_ips", $arr);					
				}				
			}
		}

		if ('20_9' == $step) {
			$query = $wpdb->query("CHECK TABLE " . $wpdb->prefix . "geoip_country");
			if (1 == $query) {
				$array = get_option('geoip_country');
				if (!is_array($array)) {
					$datas = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "geoip_country");
					$arr = array();
					foreach ($datas as $data) {
						$arr[$data->attr] = $data->attr;
					}
					update_option('geoip_country', $arr);
				}
			}
		}		

		if ('20_10' == $step) {
			$query = $wpdb->query("CHECK TABLE " . $wpdb->prefix . "directions");
			if (1 == $query) {
				$datas = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "directions LIMIT {$offset},{$limit}");
				foreach ($datas as $data) {
					$data_id = $data->id;
					$seo = get_direction_meta($data_id, 'seo');
					if (!is_array($seo)) { 
						$seo = array();
						$seo['seo_exch_title'] = get_direction_meta($data_id, 'seo_exch_title');
						$seo['seo_title'] = get_direction_meta($data_id, 'seo_title');
						$seo['seo_key'] = get_direction_meta($data_id, 'seo_key'); 
						$seo['seo_descr'] = get_direction_meta($data_id, 'seo_descr');
						$seo['ogp_title'] = get_direction_meta($data_id, 'ogp_title'); 
						$seo['ogp_descr'] = get_direction_meta($data_id, 'ogp_descr');
						update_direction_meta($data_id, 'seo', $seo);
					}
				}
			}
		}
		
		if ('20_11' == $step) {
			$query = $wpdb->query("CHECK TABLE " . $wpdb->prefix . "users_old_data");
			if (1 == $query) {	
				$datas = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "exchange_bids WHERE status = 'success' LIMIT {$offset},{$limit}");
				foreach ($datas as $data) {
					$data_id = $data->id;
					$cc = $wpdb->get_var("SELECT COUNT(id) FROM " . $wpdb->prefix . "users_old_data WHERE bid_id = '$data_id'");
					if (0 == $cc) {
						$arr = array();
						$arr['bid_id'] = $data_id;
						$arr['account_give'] = $data->account_give;
						$arr['account_get'] = $data->account_get;
						$arr['user_phone'] = str_replace('+', '', $data->user_phone);
						$arr['user_skype'] = $data->user_skype;
						$arr['user_email'] = $data->user_email;
						$wpdb->insert($wpdb->prefix ."users_old_data", $arr);
					}
				}
			}
		}

		if ('20_12' == $step) {
			$query = $wpdb->query("CHECK TABLE " . $wpdb->prefix . "archive_exchange_bids");
			if (1 == $query) {	
				$datas = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "archive_exchange_bids WHERE status = 'success' LIMIT {$offset},{$limit}");
				foreach ($datas as $data) {
					$data_id = $data->id;
					$cc = $wpdb->get_var("SELECT COUNT(id) FROM " . $wpdb->prefix . "users_old_data WHERE bid_id = '$data_id'");
					if (0 == $cc) {
						$arr = array();
						$arr['bid_id'] = $data_id;
						$arr['account_give'] = $data->account_give;
						$arr['account_get'] = $data->account_get;
						$arr['user_phone'] = str_replace('+', '', $data->user_phone);
						$arr['user_skype'] = $data->user_skype;
						$arr['user_email'] = $data->user_email;
						$wpdb->insert($wpdb->prefix . "users_old_data", $arr);
					}
				}
			}
		}		
		
		if ('20_13' == $step) {
			$query = $wpdb->query("CHECK TABLE " . $wpdb->prefix . "parser_pairs");
			if (1 == $query) {
				$datas = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "parser_pairs LIMIT {$offset},{$limit}");
				foreach ($datas as $data) {
					$data_id = $data->id;
					$arr = array();
					$arr['title_birg'] = ctv_ml($data->title_birg);
					$wpdb->update($wpdb->prefix . "parser_pairs", $arr, array('id' => $data_id));
				}
			}
		}	

		if ('20_16' == $step) {	 /*****************/ 

			$arr = array(
				array(
					'tbl' => 'directions',
					'row' => 'm_in',
				),
				array(
					'tbl' => 'directions',
					'row' => 'm_out',					
				),	
			);
			$arr = array_slice($arr, $offset, $limit);
			foreach ($arr as $data) {
				$table = $wpdb->prefix . $data['tbl'];
				$query = $wpdb->query("CHECK TABLE {$table}");
				if (1 == $query) {
					$row = $data['row'];
					$que = $wpdb->query("SHOW COLUMNS FROM {$table} LIKE '{$row}'");
					if ($que) {
						$wpdb->query("ALTER TABLE {$table} CHANGE `{$row}` `{$row}` longtext NOT NULL");
					}	
				}
			}	
			
		}

		if ('20_17' == $step) {
			$query = $wpdb->query("CHECK TABLE " . $wpdb->prefix . "directions");
			if (1 == $query) {
				$datas = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "directions LIMIT {$offset},{$limit}");
				foreach ($datas as $data) {
					$data_id = $data->id;
					$array = array();
					$m_in = is_isset($data, 'm_in');
					$m_in_arr = @unserialize($m_in);
					if (!is_array($m_in_arr)) {
						if ($m_in) {
							$array['m_in'] = @serialize(array($m_in));
						} else {	
							$array['m_in'] = @serialize(array());
						}
					}
					$m_out = is_isset($data, 'm_out');
					$m_out_arr = @unserialize($m_out);
					if (!is_array($m_out_arr)) {
						if ($m_out) {
							$array['m_out'] = @serialize(array($m_out));
						} else {	
							$array['m_out'] = @serialize(array());
						}
					}
					if (count($array) > 0) {
						$wpdb->update($wpdb->prefix . "directions", $array, array('id' => $data_id));
					}
				}				
			}
		}

		if ('20_18' == $step) {
			$query = $wpdb->query("CHECK TABLE " . $wpdb->prefix . "directions");
			if (1 == $query) {
				$datas = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "directions LIMIT {$offset},{$limit}");
				foreach ($datas as $data) {
					$data_id = $data->id;
					$pp_data = get_direction_meta($data_id, 'pp_data');
					if (is_array($pp_data)) {
						$p_enable = intval(is_isset($pp_data, 'enable'));
						$p_disable = 0;
						if (0 == $p_enable) {
							$p_disable = 1;
						}
						$pp_data['disable'] = $p_disable;
						update_direction_meta($data_id, 'pp_data', $pp_data);
					}
				}				
			}
		}		

		if ('20_19' == $step) {
			$query = $wpdb->query("CHECK TABLE " . $wpdb->prefix . "directions");
			if (1 == $query) {
				$datas = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "directions LIMIT {$offset},{$limit}");
				foreach ($datas as $data) {
					
					$data_id = $data->id;
					$verify_account = get_direction_meta($data_id, 'verify_account');
					if (1 == $verify_account) {
						update_direction_meta($data_id, 'verify_acc1', 1);
					} elseif (2 == $verify_account) {
						update_direction_meta($data_id, 'verify_acc2', 1);
					} elseif (3 == $verify_account) {
						update_direction_meta($data_id, 'verify_acc1', 1);
						update_direction_meta($data_id, 'verify_acc2', 1);
					}
					delete_direction_meta($data_id, 'verify_account');
					
				}				
			}
		}	

		if ('20_21' == $step) {
			$extended = get_option('pn_extended');
			if (!is_array($extended)) { $extended = array(); }

			$merchants = get_option('smsgate');
			if (!is_array($merchants)) { $merchants = array(); }
			
			if (isset($extended['sms']) and is_array($extended['sms'])) {
				$scripts = list_extended($premiumbox, 'sms');
				$item = array();
				foreach ($extended['sms'] as $k => $v) { 
					$scr_data = is_isset($scripts, $k);
					$item[$k] = array(
						'title' => ctv_ml(is_isset($scr_data, 'title')) . ' (' . $v . ')',
						'script' => $k,
						'status' => intval(is_isset($merchants, $k)),
					);
				}
				unset($extended['sms']);
				update_option('extlist_sms', $item);
			}
			update_option('pn_extended', $extended);
		}
		
		if ('20_22' == $step) {
			$extended = get_option('pn_extended');
			if (!is_array($extended)) { $extended = array(); }

			$merchants = get_option('merchants');
			if (!is_array($merchants)) { $merchants = array(); }
			
			if (isset($extended['merchants']) and is_array($extended['merchants'])) {
				$scripts = list_extended($premiumbox, 'merchants');
				$item = array();
				foreach ($extended['merchants'] as $k => $v) {
					$scr_data = is_isset($scripts, $k);
					$item[$k] = array(
						'title' => ctv_ml(is_isset($scr_data, 'title')) . ' (' . $v . ')',
						'script' => $k,
						'status' => intval(is_isset($merchants, $k)),
					);
				}
				unset($extended['merchants']);
				update_option('extlist_merchants', $item);
			}
			update_option('pn_extended', $extended);
		}

		if ('20_23' == $step) {
			$extended = get_option('pn_extended');
			if (!is_array($extended)) { $extended = array(); }

			$merchants = get_option('paymerchants');
			if (!is_array($merchants)) { $merchants = array(); }
			
			if (isset($extended['paymerchants']) and is_array($extended['paymerchants'])) {
				$scripts = list_extended($premiumbox, 'paymerchants');
				$item = array();
				foreach ($extended['paymerchants'] as $k => $v) {
					$scr_data = is_isset($scripts, $k);
					$item[$k] = array(
						'title' => ctv_ml(is_isset($scr_data, 'title')) . ' ('. $v . ')',
						'script' => $k,
						'status' => intval(is_isset($merchants, $k)),
					);
				}
				unset($extended['paymerchants']);
				update_option('extlist_paymerchants', $item);
			}
			update_option('pn_extended', $extended);
		}

		if ('20_24' == $step) {
			$list = get_option('extlist_merchants');
			if (!is_array($list)) { $list = array(); }
			$ms = array();
			$ids = array();
			foreach ($list as $list_k => $list_v) {
				$ms[is_isset($list_v, 'script')] = $list_k;
				$ids[$list_k] = $list_k;
			}

			$m = get_option('merchants_data');
			if (!is_array($m)) {
				$merch_data = get_option('merch_data');
				if (!is_array($merch_data)) { $merch_data = array(); }

				foreach ($merch_data as $k => $v) {
					if (isset($ms[$k])) {
						$merch_data[$ms[$k]] = $v;
					} 
					if (!isset($ids[$k])) {
						unset($merch_data[$k]);
					}	
				}	
				update_option('merchants_data', $merch_data);
			}
		}
		
		if ('20_25' == $step) {
			$list = get_option('extlist_paymerchants');
			if (!is_array($list)) { $list = array(); }
			$ms = array();
			$ids = array();
			foreach ($list as $list_k => $list_v) {
				$ms[is_isset($list_v, 'script')] = $list_k;
				$ids[$list_k] = $list_k;
			}

			$m = get_option('paymerchants_data');
			if (!is_array($m)) {
				$merch_data = get_option('paymerch_data');
				if (!is_array($merch_data)) { $merch_data = array(); }

				foreach ($merch_data as $k => $v) {
					if (isset($ms[$k])) {
						$merch_data[$ms[$k]] = $v;
					} 
					if (!isset($ids[$k])) {
						unset($merch_data[$k]);
					}	
				}	
				update_option('paymerchants_data', $merch_data);
			}
		}

		if ('20_26' == $step) {
			$query = $wpdb->query("CHECK TABLE " . $wpdb->prefix . "directions");
			if (1 == $query) {
				
				$list = get_option('extlist_merchants');
				if (!is_array($list)) { $list = array(); }
				$m_ins = array();
				foreach ($list as $list_k => $list_v) {
					$m_ins[is_isset($list_v, 'script')] = $list_k;
				}
				
				$list = get_option('extlist_paymerchants');
				if (!is_array($list)) { $list = array(); }
				$m_outs = array();
				foreach ($list as $list_k => $list_v) {
					$m_outs[is_isset($list_v, 'script')] = $list_k;
				}				
				
				$datas = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "directions LIMIT {$offset},{$limit}");
				foreach ($datas as $data) {
					$data_id = $data->id;
					$array = array();
					$m_in = is_isset($data,'m_in');
					$m_in_arr = @unserialize($m_in);
					if (is_array($m_in_arr)) {
						$nm = array();
						foreach ($m_in_arr as $m) {
							if (isset($m_ins[$m])) {
								$nm[] = $m_ins[$m];
							}
						}
						$array['m_in'] = @serialize($nm);
					}
					$m_out = is_isset($data,'m_out');
					$m_out_arr = @unserialize($m_out);
					if (is_array($m_out_arr)) {
						$nm = array();
						foreach ($m_out_arr as $m) {
							if (isset($m_outs[$m])) {
								$nm[] = $m_outs[$m];
							}
						}
						$array['m_out'] = @serialize($nm);
					}
					
					$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "directions LIKE 'reserv_place'");
					if (1 == $query) {
						$array['reserv_place'] = str_replace('fres', 'dfilereserve', $data->reserv_place);
					}	
					
					if (count($array) > 0) {
						$wpdb->update($wpdb->prefix . "directions", $array, array('id' => $data_id));
					}
				}				
			}
		}

		if ('20_27' == $step) { /*****************/
			$query = $wpdb->query("CHECK TABLE " . $wpdb->prefix . "recalc_bids");
			if (1 == $query) {
				$datas = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "recalc_bids LIMIT {$offset},{$limit}");
				foreach ($datas as $data) {
					$id = $data->id;
					$array = array();

					$array['direction_id'] = is_isset($data, 'naps_id');
					$array['change_course'] = is_isset($data, 'enable_recalc');
					$array['course_minute'] = is_isset($data, 'cou_minute') + (is_isset($data, 'cou_hour') * 60);
					$array['sum_minute'] = is_isset($data, 'cou_minute') + (is_isset($data, 'cou_hour') * 60);
					$array['course_status'] = is_isset($data, 'statused');
					$array['sum_status'] = @serialize(array('techpay', 'coldpay', 'realpay', 'verify'));

					$cc_count = $wpdb->get_var("SELECT COUNT(id) FROM " . $wpdb->prefix . "recalculations WHERE id = '$id'");
					if (0 == $cc_count) {
						$array['id'] = $id;
						$wpdb->insert($wpdb->prefix . "recalculations", $array);	
					} else {
						$wpdb->update($wpdb->prefix . "recalculations", $array, array('id' => $id));	
					}			
				}
			}						
		}

		if ('20_28' == $step) {
			$query = $wpdb->query("CHECK TABLE " . $wpdb->prefix . "currency");
			if (1 == $query) {
				$datas = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "currency LIMIT {$offset},{$limit}");
				foreach ($datas as $data) {
					$data_id = $data->id;
					
					$array = array();
					$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "currency LIKE 'reserv_place'");
					if (1 == $query) {
						$array['reserv_place'] = str_replace('fres', 'cfilereserve', $data->reserv_place);
					}	
	
					if (count($array) > 0) {
						$wpdb->update($wpdb->prefix . "currency", $array, array('id' => $data_id));
					}
				}				
			}
		}		
		
		if ('20_29' == $step) {	 /*****************/ 
			$tables = array(
				'adminpanelcaptcha','captcha','uv_accounts','bids','geoip_blackip','geoip_whiteip','geoip_country','geoip_iplist',
				'course_logs','blackbrokers_naps','recalc_bids','valuts_account',
			);
			foreach ($tables as $tbl) {
				$query = $wpdb->query("CHECK TABLE " . $wpdb->prefix . $tbl);
				if (1 == $query) {
					$wpdb->query("DROP TABLE " . $wpdb->prefix . $tbl);
				}	
			}
		}

		if ('21_1' == $step) {	 /*****************/
			$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "users LIKE 'admin_comment'");
			if (1 == $query) {
				$datas = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "users LIMIT {$offset},{$limit}");
				foreach ($datas as $data) {
					$id = $data->ID;
					
					$admin_comment = pn_strip_input(is_isset($data, 'admin_comment'));
					if (strlen($admin_comment) > 0) {
						$arr = array();
						$arr['comment_date'] = current_time('mysql');
						$arr['user_id'] = $data->ID;
						$arr['user_login'] = pn_strip_input($data->user_login);
						$arr['text_comment'] = $admin_comment;
						$arr['itemtype'] = 'user';
						$arr['item_id'] = $data->ID;
						$wpdb->insert($wpdb->prefix . 'comment_system', $arr);
						
						$array = array();
						$array['admin_comment'] = '';
						$wpdb->update($wpdb->prefix . "users", $array, array('ID' => $id));	
					}
				} 
			}
		}

		if ('21_3' == $step) {	 /*****************/
			$datas = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "exchange_bids LIMIT {$offset},{$limit}");
			foreach ($datas as $data) {
				$id = $data->id;
					
				$comment_user = pn_strip_text(get_bids_meta($id, 'comment_user'));
				if (strlen($comment_user) > 0) {
					$arr = array();
					$arr['comment_date'] = current_time('mysql');
					$arr['user_id'] = $data->user_id;
					$arr['user_login'] = pn_strip_input($data->user_login);
					$arr['text_comment'] = $comment_user;
					$arr['itemtype'] = 'user_bid';
					$arr['item_id'] = $data->id;
					$wpdb->insert($wpdb->prefix . 'comment_system', $arr);
					delete_bids_meta($id,'comment_user');
				}
				
				$comment_admin = pn_strip_text(get_bids_meta($id, 'comment_admin'));	
				if (strlen($comment_admin) > 0) {
					$arr = array();
					$arr['comment_date'] = current_time('mysql');
					$arr['user_id'] = $data->user_id;
					$arr['user_login'] = pn_strip_input($data->user_login);
					$arr['text_comment'] = $comment_admin;
					$arr['itemtype'] = 'admin_bid';
					$arr['item_id'] = $data->id;
					$wpdb->insert($wpdb->prefix . 'comment_system', $arr);
					delete_bids_meta($id,'comment_admin');
				}		
			} 
		}

		if ('21_4' == $step) {
			$query = $wpdb->query("CHECK TABLE " . $wpdb->prefix . "currency_accounts");
			if (1 == $query) {
				$datas = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "currency_accounts LIMIT {$offset},{$limit}");
				foreach ($datas as $data) {
					$id = $data->id;	
					$text_comment = pn_strip_input(is_isset($data, 'text_comment'));
					if (strlen($text_comment) > 0) {
						$arr = array();
						$arr['comment_date'] = current_time('mysql');
						$arr['text_comment'] = $text_comment;
						$arr['itemtype'] = 'curracc';
						$arr['item_id'] = $data->id;
						$wpdb->insert($wpdb->prefix . 'comment_system', $arr);	
					}
				} 
			}
		}	
		
		if ('21_5' == $step) {
			$opts = array('pn_bcparser_courses','pn_bestchange_courses','pn_parser_pairs','pn_curs_parser','blackbrokers_courses','pn_directions_filedata','pn_fcourse_courses');
			foreach ($opts as $option_name) {
				$parts = get_option($option_name . '_parts');
				$parts = intval($parts);
				$s = 0;
				while ($s++ < $parts) {
					delete_option($option_name . '_p' . $s);
				}
				delete_option($option_name . '_parts');
			}
		}		
		
		if ('22_1' == $step) {
			$query = $wpdb->query("CHECK TABLE " . $wpdb->prefix . "bidstatus");
			if (1 == $query) {
				$colors = array('#ff3c00', '#fc6d41', '#dbdd0a', '#31dd0a', '#0adddb', '#810add');
				$datas = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "bidstatus LIMIT {$offset},{$limit}");
				foreach ($datas as $data) {
					$id = $data->id;	
					$bg_color = $data->bg_color;
					if (!strstr($bg_color, '#')) {
						$arr = array();
						$arr['bg_color'] = is_isset($colors, $bg_color);
						$wpdb->update($wpdb->prefix . "bidstatus", $arr, array('id' => $id));
					}
				} 
			}
		}

		if ('22_2' == $step) { /*****************/
			$query = $wpdb->query("CHECK TABLE " . $wpdb->prefix . "recalcs");
			if (1 == $query) {
				$datas = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "recalcs LIMIT {$offset},{$limit}");
				foreach ($datas as $data) {
					$id = $data->id;
					$array = array();

					$array['direction_id'] = is_isset($data, 'direction_id');
					$array['change_course'] = is_isset($data, 'change_course');
					$array['change_sum'] = is_isset($data, 'change_sum');
					$array['course_minute'] = is_isset($data, 'cou_minute') + (is_isset($data, 'cou_hour') * 60);
					$array['sum_minute'] = is_isset($data, 'cou_minute') + (is_isset($data, 'cou_hour') * 60);
					$array['course_status'] = is_isset($data, 'statused');
					$array['sum_status'] = @serialize(array('techpay', 'coldpay', 'realpay', 'verify'));

					$cc_count = $wpdb->get_var("SELECT COUNT(id) FROM " . $wpdb->prefix . "recalculations WHERE id = '$id'");
					if (0 == $cc_count) {
						$array['id'] = $id;
						$wpdb->insert($wpdb->prefix . "recalculations", $array);	
					} else {
						$wpdb->update($wpdb->prefix . "recalculations", $array, array('id' => $id));	
					}			
				}
			}						
		}	
		
		if ('22_3' == $step) {	 /*****************/
			$tables = array(
				'currency_codes_course_logs', 'direction_course_logs', 'blackbrokers', 'direction_blackbroker',
			);
			foreach ($tables as $tbl) {
				$query = $wpdb->query("CHECK TABLE " . $wpdb->prefix . $tbl);
				if (1 == $query) {
					$wpdb->query("DROP TABLE " . $wpdb->prefix . $tbl);
				}	
			}
		}		

		if ('1_1' == $step) {	 /*****************/
			$result = get_curl_parser('https://premiumexchanger.com/migrate/step35.xml', array(), 'migration');
			if (!$result['err']) {
				$out = $result['output'];
				if (is_string($out)) {
					if (strstr($out, '<?xml')) {
						$res = @simplexml_load_string($out);
						if (is_object($res)) {
							foreach ($res->item as $item) {
								$arr = (array)$item;
								if (isset($arr['id'])) {
									unset($arr['id']);
								}
								if (isset($arr['title_birg'])) {
									$arr['title_birg'] = ctv_ml($arr['title_birg']);
								}	
								$wpdb->insert($wpdb->prefix . 'parser_pairs', $arr);
							}
						}
					}
				}
			}			
		}

		if ('1_2' == $step) { /*****************/
			$datas = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "exchange_bids LIMIT {$offset},{$limit}");
			foreach ($datas as $data) {
				$id = $data->id;
				bid_hashdata($id, $data, '');
			}						
		}

		if ('24_1' == $step) {
			$query = $wpdb->query("CHECK TABLE " . $wpdb->prefix . "auto_removal_bids");
			if (1 == $query) {				
				$datas = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "auto_removal_bids LIMIT {$offset},{$limit}");
				foreach ($datas as $data) {
					$id = $data->id;
					$string = trim(is_isset($data, 'statused'));
					$def = array();
					if (preg_match_all('/\[d](.*?)\[\/d]/s', $string, $match, PREG_PATTERN_ORDER)) {
						$def = $match[1];
					}
					if (is_array($def) and count($def) > 0) {
						$arr = array();
						$arr['statused'] = pn_json_encode($def);
						$wpdb->update($wpdb->prefix . 'auto_removal_bids', $arr, array('id' => $id));	
					}
				}
			}
		}

		if ('24_2' == $step) {
			$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "directions LIKE 'naps_lang'"); 
			if ($query) {				
				$datas = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "directions LIMIT {$offset},{$limit}");
				foreach ($datas as $data) {
					$id = $data->id;
					$string = trim(is_isset($data, 'naps_lang'));
					$def = array();
					if (preg_match_all('/\[d](.*?)\[\/d]/s', $string, $match, PREG_PATTERN_ORDER)) {
						$def = $match[1];
					}
					if (is_array($def) and count($def) > 0) {
						$arr = array();
						$arr['naps_lang'] = pn_json_encode($def);
						$wpdb->update($wpdb->prefix . 'directions', $arr, array('id' => $id));	
					}
				}
			}
		}

		if ('24_3' == $step) {
			$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "directions LIKE 'not_ip'"); 
			if ($query) {				
				$datas = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "directions LIMIT {$offset},{$limit}");
				foreach ($datas as $data) {
					$id = $data->id;
					$string = trim(is_isset($data,'not_ip'));
					$def = array();
					if (preg_match_all('/\[d](.*?)\[\/d]/s', $string, $match, PREG_PATTERN_ORDER)) {
						$def = $match[1];
					}
					if (is_array($def) and count($def) > 0) {
						$arr = array();
						$arr['not_ip'] = pn_json_encode($def);
						$wpdb->update($wpdb->prefix . 'directions', $arr, array('id' => $id));	
					}
				}
			}
		}

		if ('24_4' == $step) {
			$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "directions LIKE 'not_country'"); 
			if ($query) {				
				$datas = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "directions LIMIT {$offset},{$limit}");
				foreach ($datas as $data) {
					$id = $data->id;
					$string = trim(is_isset($data, 'not_country'));
					$def1 = array();
					if (preg_match_all('/\[d](.*?)\[\/d]/s', $string, $match, PREG_PATTERN_ORDER)) {
						$def1 = $match[1];
					}
					$string = trim(is_isset($data, 'only_country'));
					$def2 = array();
					if (preg_match_all('/\[d](.*?)\[\/d]/s', $string, $match, PREG_PATTERN_ORDER)) {
						$def2 = $match[1];
					}

					$arr = array();
					$arr['not_country'] = pn_json_encode($def1);
					$arr['only_country'] = pn_json_encode($def2);
					$wpdb->update($wpdb->prefix . 'directions', $arr, array('id' => $id));	
				}
			}
		}

		if ('24_5' == $step) {
			
			$tables = array(
				'paymerchant_logs', 'merchant_logs', 'autobroker_lite',
			);
			foreach ($tables as $tbl) {
				$query = $wpdb->query("CHECK TABLE " . $wpdb->prefix . $tbl);
				if (1 == $query) {
					$wpdb->query("DROP TABLE " . $wpdb->prefix . $tbl);
				}	
			}			
			
			$path = $premiumbox->upload_dir . '/usveshow/';
			full_del_dir($path);
			
			$path = $premiumbox->upload_dir . '/usacshow/';
			full_del_dir($path);

			$oldname = $premiumbox->upload_dir . '/smsgate/';
			$newname = $premiumbox->upload_dir . '/sms/';
			if (is_dir($oldname)) {
				@rename($oldname, $newname);
			}
			
		}

		if ('24_6' == $step) {

			$list = get_option('extlist_sms');
			if (!is_array($list)) { $list = array(); }
			$data = get_option('smsgate_data');
			if (!is_array($data)) { $data = array(); }
			
			foreach ($list as $item_key => $item_data) {
				$cc = $wpdb->get_var("SELECT COUNT(id) FROM " . $wpdb->prefix . "exts WHERE ext_type = 'sms' AND ext_key = '$item_key'");
				if (0 == $cc) {
					$arr = array();
					$arr['ext_type'] = 'sms';
					$arr['ext_title'] = is_isset($item_data, 'title');
					$arr['ext_plugin'] = is_isset($item_data, 'script');
					$arr['ext_status'] = is_isset($item_data, 'status');
					$arr['ext_key'] = $item_key;
					$arr['ext_options'] = pn_json_encode(is_isset($data, $item_key));
					$wpdb->insert($wpdb->prefix . 'exts', $arr);
				}
			}

		}

		if ('24_7' == $step) {

			$list = get_option('extlist_merchants');
			if (!is_array($list)) { $list = array(); }
			$data = get_option('merchants_data');
			if (!is_array($data)) { $data = array(); }

			foreach ($list as $item_key => $item_data) {
				$cc = $wpdb->get_var("SELECT COUNT(id) FROM " . $wpdb->prefix . "exts WHERE ext_type = 'merchants' AND ext_key = '$item_key'");
				if (0 == $cc) {
					$arr = array();
					$arr['ext_type'] = 'merchants';
					$arr['ext_title'] = is_isset($item_data, 'title');
					$arr['ext_plugin'] = is_isset($item_data, 'script');
					$arr['ext_status'] = is_isset($item_data, 'status');
					$arr['ext_key'] = $item_key;
					$options = is_isset($data, $item_key);
					if (is_array($options)) { $options['cronhash'] = is_isset($options, 'resulturl'); }
					$arr['ext_options'] = pn_json_encode($options);
					$wpdb->insert($wpdb->prefix . 'exts', $arr);
				}
			}

		}

		if ('24_8' == $step) {

			$list = get_option('extlist_paymerchants');
			if (!is_array($list)) { $list = array(); }
			$data = get_option('paymerchants_data');
			if (!is_array($data)) { $data = array(); }

			foreach ($list as $item_key => $item_data) {
				$cc = $wpdb->get_var("SELECT COUNT(id) FROM " . $wpdb->prefix . "exts WHERE ext_type = 'paymerchants' AND ext_key = '$item_key'");
				if (0 == $cc) {
					$arr = array();
					$arr['ext_type'] = 'paymerchants';
					$arr['ext_title'] = is_isset($item_data, 'title');
					$arr['ext_plugin'] = is_isset($item_data, 'script');
					$arr['ext_status'] = is_isset($item_data, 'status');
					$arr['ext_key'] = $item_key;
					$options = is_isset($data, $item_key);
					if (is_array($options)) { $options['cronhash'] = is_isset($options, 'resulturl'); }
					$arr['ext_options'] = pn_json_encode($options);
					$wpdb->insert($wpdb->prefix . 'exts', $arr);
				}
			}

		}

		if ('24_9' == $step) {

			$datas = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "exchange_bids ORDER BY id DESC LIMIT {$offset},{$limit}");
			foreach ($datas as $data) {
				$id = $data->id;
				$dest_tag = get_bids_meta($id, 'dest_tag');	
				$arr = array();
				$arr['dest_tag'] = $dest_tag;
				$arr['out_sum'] = $data->sum2c;
				$wpdb->update($wpdb->prefix . 'exchange_bids', $arr, array('id' => $id));		
			}

		}		
		
		if ('25_1' == $step) {
			
			$pn_notify = get_option('pn_notify_email');
			if (is_array($pn_notify)) {
				foreach ($pn_notify as $pn_notify_place => $pn_notify_data) {
					
					$pn_notify_data['subject'] = $pn_notify_data['title'];
					unset($pn_notify_data['title']);
					
					$pn_notify_data['to'] = $pn_notify_data['tomail'];
					unset($pn_notify_data['tomail']);		
			
					update_notify_data('email', $pn_notify_place, $pn_notify_data);
				}
				update_option('pn_notify_email', '');
			}

			$pn_notify = get_option('pn_notify_sms');
			if (is_array($pn_notify)) {
				foreach ($pn_notify as $pn_notify_place => $pn_notify_data) {
					update_notify_data('sms', $pn_notify_place, $pn_notify_data);
				}
				update_option('pn_notify_sms', '');
			}

			$pn_notify = get_option('pn_notify_telegram');
			if (is_array($pn_notify)) {
				foreach ($pn_notify as $pn_notify_place => $pn_notify_data) {
					update_notify_data('telegram', $pn_notify_place, $pn_notify_data);
				}
				update_option('pn_notify_telegram', '');
			}			
			
		}	
		
		if ('25_2' == $step) {
			
			$premiumbox->update_option('toslink', '', '[ru_RU:]/tos/[:ru_RU][en_US:]/en/tos/[:en_US]');
			
			$premiumbox->update_option('bidsfile', '', 1);
			
			$persdislink = $premiumbox->get_option('persdislink');
			if (!is_array($persdislink)) { 
				$persdislink = array('payed', 'realpay', 'verify'); 
				$premiumbox->update_option('persdislink', '', $persdislink);	
			}
			
			$pindexes = get_option('parser_indexes');
			if(is_array($pindexes)){ 
				update_array_option($premiumbox, 'parser_indexes', $pindexes);
				delete_option('parser_indexes');
			}
			
			$persdislink = $premiumbox->get_option('persdislink');
			if (is_array($persdislink)) { 
				$premiumbox->update_option('bidsind', '', $persdislink);	
				$premiumbox->delete_option('persdislink', '');
			}
			
			$premiumbox->update_option('exchange', 'dependenceminmax', 1);
			
		}
		
		if ('25_3' == $step) {
			$query = $wpdb->query("CHECK TABLE " . $wpdb->prefix . "naps_dopsumcomis");
			if (1 == $query) {				
				$datas = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "naps_dopsumcomis LIMIT {$offset},{$limit}");
				foreach ($datas as $data) {
					$id = $data->id;
					$dir_id = $data->naps_id;
					$cc = $wpdb->get_var("SELECT COUNT(id) FROM " . $wpdb->prefix . "constructs WHERE itemtype = 'dcombysum' AND item_id = '$dir_id'");
					if (0 == $cc) {
						$arr = array();
						$arr['itemtype'] = 'dcombysum';
						$arr['item_id'] = $dir_id;
						$arr['amount'] = $data->sum_val;
						$options = array(
							'com_box_sum1' => $data->com_box_summ1,
							'com_box_pers1' => $data->com_box_pers1,
							'com_box_sum2' => $data->com_box_summ2,
							'com_box_pers2' => $data->com_box_pers2,
						);
						$arr['itemsettings'] = pn_json_encode($options);
						$wpdb->insert($wpdb->prefix . 'constructs', $arr);
					}					
				}
			}
		}

		if ('25_4' == $step) {
			$query = $wpdb->query("CHECK TABLE " . $wpdb->prefix . "naps_reservcurs");
			if (1 == $query) {				
				$datas = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "naps_reservcurs LIMIT {$offset},{$limit}");
				foreach ($datas as $data) {
					$id = $data->id;
					$dir_id = $data->naps_id;
					$cc = $wpdb->get_var("SELECT COUNT(id) FROM " . $wpdb->prefix . "constructs WHERE itemtype = 'coursebyreserve' AND item_id = '$dir_id'");
					if (0 == $cc) {
						$arr = array();
						$arr['itemtype'] = 'coursebyreserve';
						$arr['item_id'] = $dir_id;
						$arr['amount'] = $data->sum_val;
						$options = array(
							'course1' => $data->curs1,
							'course2' => $data->curs2,
						);
						$arr['itemsettings'] = pn_json_encode($options);
						$wpdb->insert($wpdb->prefix . 'constructs', $arr);
					}					
				}
			}
		}

		if ('25_5' == $step) {
			$query = $wpdb->query("CHECK TABLE " . $wpdb->prefix . "naps_sumcurs");
			if (1 == $query) {				
				$datas = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "naps_sumcurs LIMIT {$offset},{$limit}");
				foreach ($datas as $data) {
					$id = $data->id;
					$dir_id = $data->naps_id;
					$cc = $wpdb->get_var("SELECT COUNT(id) FROM " . $wpdb->prefix . "constructs WHERE itemtype = 'coursebysum' AND item_id = '$dir_id'");
					if (0 == $cc) {
						$arr = array();
						$arr['itemtype'] = 'coursebysum';
						$arr['item_id'] = $dir_id;
						$arr['amount'] = $data->sum_val;
						$options = array(
							'course1' => $data->curs1,
							'course2' => $data->curs2,
						);
						$arr['itemsettings'] = pn_json_encode($options);
						$wpdb->insert($wpdb->prefix . 'constructs', $arr);
					}					
				}
			}
		}

		if ('25_6' == $step) { /*****************/
		
			$que = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "exchange_bids LIKE 'card_issuer'");
			if ($que) {
 				$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "exchange_bids LIKE 'card_issuer_get'");
				if (0 == $query) {
					$wpdb->query("ALTER TABLE " . $wpdb->prefix . "exchange_bids ADD `card_issuer_get` varchar(500) NOT NULL");
				}				
				$wpdb->query("UPDATE " . $wpdb->prefix . "exchange_bids SET card_issuer_get = card_issuer");
				$wpdb->query("ALTER TABLE " . $wpdb->prefix . "exchange_bids DROP `card_issuer`");
			}
			$que = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "exchange_bids LIKE 'card_country'");
			if ($que) {
				$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "exchange_bids LIKE 'card_country_get'");
				if (0 == $query) {
					$wpdb->query("ALTER TABLE " . $wpdb->prefix . "exchange_bids ADD `card_country_get` varchar(250) NOT NULL");
				}				
				$wpdb->query("UPDATE " . $wpdb->prefix . "exchange_bids SET card_country_get = card_country");
				$wpdb->query("ALTER TABLE " . $wpdb->prefix . "exchange_bids DROP `card_country`");
			}
			$que = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "exchange_bids LIKE 'card_scheme'");
			if ($que) {
				$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "exchange_bids LIKE 'card_scheme_get'");
				if (0 == $query) {
					$wpdb->query("ALTER TABLE " . $wpdb->prefix . "exchange_bids ADD `card_scheme_get` varchar(500) NOT NULL");
				} 				
				$wpdb->query("UPDATE " . $wpdb->prefix . "exchange_bids SET card_scheme_get = card_scheme");
				$wpdb->query("ALTER TABLE " . $wpdb->prefix . "exchange_bids DROP `card_scheme`");
			}						
			
		}

		if ('26_1' == $step) {	 /*****************/ 
			$tables = array(
				'sitecaptcha_images', 'sitecaptcha_user',
			);
			foreach ($tables as $tbl) {
				$query = $wpdb->query("CHECK TABLE " . $wpdb->prefix . $tbl);
				if (1 == $query) {
					$wpdb->query("DROP TABLE " . $wpdb->prefix . $tbl);
				}	
			}
		}

		if ('26_2' == $step) {	 /*****************/ 

			$arr = array(
				array(
					'tbl' => 'currency',
					'row' => 'reserv_calc',
				),
				array(
					'tbl' => 'directions',
					'row' => 'reserv_calc',					
				),	
			);
			$arr = array_slice($arr, $offset, $limit);
			foreach ($arr as $data) {
				$table = $wpdb->prefix . $data['tbl'];
				$query = $wpdb->query("CHECK TABLE {$table}");
				if (1 == $query) {
					$row = $data['row'];
					$que = $wpdb->query("SHOW COLUMNS FROM {$table} LIKE '{$row}'");
					if ($que) {
						$wpdb->query("ALTER TABLE {$table} CHANGE `{$row}` `{$row}` longtext NOT NULL");
					}	
				}
			}	
			
		}	

		if ('26_3' == $step) { /*****************/
			$query = $wpdb->query("CHECK TABLE " . $wpdb->prefix . "bestchange_currency_codes");
			if (1 == $query) {
				$query = $wpdb->query("CHECK TABLE " . $wpdb->prefix . "bestchangeapi_currency_codes");
				if (1 == $query) {
					$datas = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "bestchange_currency_codes LIMIT {$offset},{$limit}");
					foreach ($datas as $data) {
						$array = array();
						foreach ($data as $data_k => $data_v) {
							if ('id' != $data_k) {
								$array[$data_k] = $data_v;
							}
						}
						$item = $wpdb->get_row("SELECT COUNT(id) FROM " . $wpdb->prefix . "bestchangeapi_currency_codes WHERE currency_code_id = '{$array['currency_code_id']}'");
						if (!isset($item->id)) {
							$wpdb->insert($wpdb->prefix . "bestchangeapi_currency_codes", $array);	
						} else {
							$wpdb->update($wpdb->prefix . "bestchangeapi_currency_codes", $array, array('id' => $item->id));	
						}			
					}
				}
			}						
		}

		if ('26_4' == $step) { /*****************/
			$query = $wpdb->query("CHECK TABLE " . $wpdb->prefix . "bestchange_cities");
			if (1 == $query) {
				$query = $wpdb->query("CHECK TABLE " . $wpdb->prefix . "bestchangeapi_cities");
				if (1 == $query) {
					$datas = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "bestchange_cities LIMIT {$offset},{$limit}");
					foreach ($datas as $data) {
						$array = array();
						foreach ($data as $data_k => $data_v) {
							if ('id' != $data_k) {
								$array[$data_k] = $data_v;
							}
						}
						$item = $wpdb->get_row("SELECT COUNT(id) FROM " . $wpdb->prefix . "bestchangeapi_cities WHERE city_id = '{$array['city_id']}'");
						if (!isset($item->id)) {
							$wpdb->insert($wpdb->prefix . "bestchangeapi_cities", $array);	
						} else {
							$wpdb->update($wpdb->prefix . "bestchangeapi_cities", $array, array('id' => $item->id));	
						}			
					}
				}
			}						
		}

		if ('26_5' == $step) { /*****************/
			$query = $wpdb->query("CHECK TABLE " . $wpdb->prefix . "bestchange_directions");
			if (1 == $query) {
				$query = $wpdb->query("CHECK TABLE " . $wpdb->prefix . "bestchangeapi_directions");
				if (1 == $query) {
					$datas = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "bestchange_directions LIMIT {$offset},{$limit}");
					foreach ($datas as $data) {
						$array = array();
						foreach ($data as $data_k => $data_v) {
							if ('id' != $data_k) {
								$array[$data_k] = $data_v;
							}
						}
						$array['status'] = 0;
						$item = $wpdb->get_row("SELECT COUNT(id) FROM " . $wpdb->prefix . "bestchangeapi_directions WHERE direction_id = '{$array['direction_id']}'");
						if (!isset($item->id)) {
							$wpdb->insert($wpdb->prefix . "bestchangeapi_directions", $array);	
						} else {
							$wpdb->update($wpdb->prefix . "bestchangeapi_directions", $array, array('id' => $item->id));	
						}			
					}
				}
			}						
		}

		if ('26_6' == $step) {
			$query = $wpdb->query("CHECK TABLE " . $wpdb->prefix . "directions");
			if (1 == $query) {
				$datas = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "directions LIMIT {$offset},{$limit}");
				foreach ($datas as $data) {
					$data_id = $data->id;
					$array = array();
					
					$que = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "directions LIKE 'aml'");
					if ($que) {

						$options = pn_json_decode(is_isset($data, 'aml'));
						if (!is_array($options)) { $options = array(); }
						
						if (!isset($options['give_error'])) {
						
							$give = intval(is_isset($options, 'aml_give'));
							$options['give_error'] = 0;
							if (1 == $give) {
								$options['give'] = 1;
							}
							if (2 == $give) {
								$options['give'] = 1;
								$options['give_error'] = 1;
							}							
							$get = intval(is_isset($options, 'aml_get'));
							$options['get_error'] = 0;
							if (1 == $get) {
								$options['get'] = 1;
							}
							if (2 == $get) {
								$options['get'] = 1;
								$options['get_error'] = 1;
							}							
							$merch = intval(is_isset($options, 'aml_merch'));
							$options['merch_error'] = 0;
							if (1 == $merch) {
								$options['merch'] = 1;
							}
							if (2 == $merch) {
								$options['merch'] = 2;
								$options['merch_error'] = 1;
							}							
							$options['give_sum'] = is_sum(is_isset($options, 'aml_give_sum'));
							$options['get_sum'] = is_sum(is_isset($options, 'aml_get_sum'));
							$options['merch_sum'] = is_sum(is_isset($options, 'aml_merch_sum'));
						
						}
						
						$array['aml'] = pn_json_encode($options);
					}
					
					$que = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "directions LIKE 'getblock'");
					if ($que) {
						
						$options = pn_json_decode(is_isset($data, 'getblock'));
						if (!is_array($options)) { $options = array(); }
						
						if (!isset($options['give_error'])) {
						
							$give = intval(is_isset($options, 'give'));
							$options['give_error'] = 0;
							if (1 == $give) {
								$options['give'] = 1;
							}
							if (2 == $give) {
								$options['give'] = 1;
								$options['give_error'] = 1;
							}							
							$get = intval(is_isset($options, 'get'));
							$options['get_error'] = 0;
							if (1 == $get) {
								$options['get'] = 1;
							}
							if (2 == $get) {
								$options['get'] = 1;
								$options['get_error'] = 1;
							}							
							$merch = intval(is_isset($options, 'merch'));
							$options['merch_error'] = 0;
							if (1 == $merch) {
								$options['merch'] = 1;
							}
							if (2 == $merch) {
								$options['merch'] = 2;
								$options['merch_error'] = 1;
							}							
							$options['give_sum'] = is_sum(is_isset($options, 'give_sum'));
							$options['get_sum'] = is_sum(is_isset($options, 'get_sum'));
							$options['merch_sum'] = is_sum(is_isset($options, 'merch_sum'));
						
						}						
						
						$array['getblock'] = pn_json_encode($options);
					}
					
					if (count($array) > 0) {
						$wpdb->update($wpdb->prefix . "directions", $array, array('id' => $data_id));
					}
				}				
			}
		}		

		if ('26_7' == $step) {
			$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "currency_codes LIKE 'iac_enable'");
			if ($query) {				
				$datas = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "currency_codes LIMIT {$offset},{$limit}");
				foreach ($datas as $data) {
					$id = $data->id;
					$domacc = intval(is_isset($data, 'domacc'));
					$arr = array();
					$arr['domacc'] = $domacc;
					$arr['iac_enable'] = $domacc;
					$wpdb->update($wpdb->prefix . 'currency_codes', $arr, array('id' => $id));					
				}
			}
		}

		if ('26_8' == $step) {
			$query = $wpdb->query("CHECK TABLE " . $wpdb->prefix . "iac");
			if (1 == $query and function_exists('get_user_domacc')) {				
				$datas = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "users LIMIT {$offset},{$limit}");
				foreach ($datas as $data) {
					$user_id = $data->ID;
					$title = 'migrate old version';
					$items = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "currency_codes");
					foreach ($items as $item) {
						$currency_code_id = $item->id;
						$amount = get_user_domacc($user_id, $currency_code_id);
						if ($amount != 0) {
							$cc = $wpdb->get_var("SELECT COUNT(id) FROM " . $wpdb->prefix . "iac WHERE title = '$title' AND user_id = '$user_id' AND currency_code_id = '$currency_code_id'");
							if (0 == $cc) {
								$arr = array();
								$arr['create_date'] = current_time('mysql');
								$arr['title'] = $title;
								$arr['amount'] = $amount;
								$arr['user_id'] = $user_id;
								$arr['currency_code_id'] = $currency_code_id;
								$arr['status'] = 1;
								$wpdb->insert($wpdb->prefix . 'iac', $arr);
							}
						}
					}
				}
			}
		}

		if ('27_1' == $step) {	 /*****************/ 

			$arr = array(
				array(
					'tbl' => 'currency_codes',
					'row' => 'internal_rate',
					'why' => 'longtext NOT NULL',
				),
				array(
					'tbl' => 'archive_data',
					'row' => 'meta_value',
					'why' => 'varchar(150) NOT NULL',
				),				
			);
			foreach ($arr as $data) {
				$table = $wpdb->prefix . $data['tbl'];
				$query = $wpdb->query("CHECK TABLE {$table}");
				if (1 == $query) {
					$row = $data['row'];
					$que = $wpdb->query("SHOW COLUMNS FROM {$table} LIKE '{$row}'");
					if ($que) {
						$why = $data['why'];
						$wpdb->query("ALTER TABLE {$table} CHANGE `{$row}` `{$row}` {$why}");
					}	
				}
			}			
			
		}

		if ('27_2' == $step) {
			$query = $wpdb->query("CHECK TABLE " . $wpdb->prefix . "directions_order");
			if (1 == $query) {
				
				$sorting = array();
				
				$dir_orders = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "directions_order");
				foreach ($dir_orders as $dir_order) {
					$sorting[$dir_order->c_id][$dir_order->direction_id] = intval($dir_order->order1);
				}
				
				foreach ($sorting as $sor_id => $sor) {
					$sor = pn_json_encode($sor);
					update_option('directions_order_' . $sor_id, $sor);
				}
				
			}			
		}

		if ('27_3' == $step) {
			
			$pindexes = get_array_option($premiumbox, 'parser_indexes');
			if (!is_array($pindexes)) { $pindexes = array(); }
			
			$query = $wpdb->query("CHECK TABLE " . $wpdb->prefix . "indxs");
			if (1 == $query) {			
			
				foreach ($pindexes as $pindex_name => $pindex_sum) {
					
					$comment = '';
					$items = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "comment_system WHERE item_id = '$pindex_name' AND itemtype = 'pindex' ORDER BY comment_date DESC");
					foreach ($items as $item) { 
						$comment .= pn_strip_input($item->text_comment) . "\n";
					}
					
					$pindex_name = is_inxs('index_' . $pindex_name);
					
					$cc = $wpdb->get_var("SELECT COUNT(id) FROM " . $wpdb->prefix . "indxs WHERE indx_name = '$pindex_name'");
					if (0 == $cc) {
						$arr = array();
						$arr['cat_id'] = '0';
						$arr['indx_name'] = $pindex_name;
						$arr['indx_value'] = pn_strip_input($pindex_sum);
						$arr['indx_type'] = 1;
						$arr['indx_comment'] = $comment;
						$wpdb->insert($wpdb->prefix . 'indxs', $arr);
					}						
					
				}
				
			}	
			
		}
		
		if ('27_4' == $step) {

			$que = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "exchange_bids LIKE 'courses'");
			if ($que) {
				$wpdb->query("ALTER TABLE " . $wpdb->prefix . "exchange_bids DROP `courses`");
			}

		}

		if ('27_5' == $step) {
			
			$arrs = array('amlbot', 'bitok', 'coinkyt', 'getblock');
			foreach ($arrs as $aml) {
					
				$array = array();
				$array['ext_type'] = 'amlcheck';
				$array['ext_title'] = $aml;
				$array['ext_plugin'] = $aml;
				$array['ext_key'] = $aml;
				$array['ext_status'] = 1;
				
				$merch_data = array();
				
				if ('amlbot' == $aml) {
					$merch_data['apierror_score'] = $premiumbox->get_option('amlbot', 'apierror_score');
					$premiumbox->delete_option('amlbot', 'apierror_score');
					$merch_data['addr_max'] = $premiumbox->get_option('amlbot', 'error_score2');
					$premiumbox->delete_option('amlbot', 'error_score2');
					$merch_data['txid_max'] = $premiumbox->get_option('amlbot', 'error_score');
					$premiumbox->delete_option('amlbot', 'error_score');
					$arrs = array(
						'ransom' => 'Ransom',
						'dark_service' => 'Dark Service',
						'other' => 'Other',
						'stolen_coins' => 'Stolen coins',
						'infrastructure_as_a_service' => 'infrastructure as a service',
						'gambling' => 'Gambling',
						'scam' => 'Scam',
						'exchange_mlrisk_veryhigh' => 'exchange mlrisk veryhigh',
						'miner' => 'Miner',
						'token_smart_contract' => 'token smart contract',
						'exchange_mlrisk_low' => 'exchange mlrisk low',
						'illicit_actor_org' => 'illicit actor org',
						'ico' => 'Ico',
						'exchange_fraudulent' => 'Exchange Fraudulent',
						'p2p_exchange_mlrisk_low' => 'p2p exchange mlrisk low',
						'p2p_exchange' => 'p2p exchange',
						'dark_market' => 'Dark Market',
						'illegal_service' => 'Illegal Service',
						'payment' => 'Payment',
						'atm' => 'Atm',
						'exchange_mlrisk_high' => 'exchange mlrisk high',
						'lending_contract' => 'lending contract',
						'risky_exchange' => 'risky exchange',
						'child_exploitation' => 'child_exploitation',
						'wallet' => 'wallet',
						'marketplace' => 'marketplace',
						'exchange_mlrisk_moderate' => 'exchange mlrisk moderate',
						'p2p_exchange_mlrisk_high' => 'p2p exchange mlrisk high',
						'decentralized_exchange_contract' => 'decentralized exchange contract',
						'fraud_shop' => 'fraud shop',
						'enforcement_action' => 'enforcement action',   
						'protocol_privacy' => 'protocol privacy',
						'unnamed_service' => 'unnamed service',
						'seized_assets' => 'seized assets',		
						'mixer' => 'Mixer',
						'liquidity_pools' => 'liquidity pools',
						'terrorism_financing' => 'terrorism financing',
						'exchange' => 'exchange',
						'smart_contract' => 'smart contract',
						'sanctions' => 'sanctions',
						'high_risk_jurisdiction' => 'high risk jurisdiction',
						'merchant_services' => 'merchant services',	
					);	
					foreach ($arrs as $arr_k => $arr_t) {
						$merch_data['addr_max_' . $arr_k] = $premiumbox->get_option('amlbot', 'apierror_opt2' . $arr_k);
						$premiumbox->delete_option('amlbot', 'apierror_opt2' . $arr_k);
						$merch_data['txid_max_' . $arr_k] = $premiumbox->get_option('amlbot', 'apierror_opt' . $arr_k);
						$premiumbox->delete_option('amlbot', 'apierror_opt' . $arr_k);
					}
				}
				if ('bitok' == $aml) {	
					$merch_data['api_timeout'] = $premiumbox->get_option('bitok', 'api_timeout');
					$premiumbox->delete_option('bitok', 'api_timeout');
					$merch_data['apierror_score'] = $premiumbox->get_option('bitok', 'apierror_score');
					$premiumbox->delete_option('bitok', 'apierror_score');
					$merch_data['addr_max'] = $premiumbox->get_option('bitok', 'error_score2');
					$premiumbox->delete_option('bitok', 'error_score2');
					$merch_data['txid_max'] = $premiumbox->get_option('bitok', 'error_score');
					$premiumbox->delete_option('bitok', 'error_score');	
					$arrs = array(
						'seized_funds' => 'seized_funds',
						'iaas' => 'iaas',
						'personal_wallet' => 'personal_wallet',
						'custodial_wallet' => 'custodial_wallet',
						'lending' => 'lending',
						'bridge' => 'bridge',
						'ico' => 'ico',
						'token_contract' => 'token_contract',
						'smart_contract' => 'smart_contract',
						'nft_marketplace' => 'nft_marketplace',
						'privacy_protocol' => 'privacy_protocol',
						'fraud_shop' => 'fraud_shop',
						'online_pharmacy' => 'online_pharmacy',
						'unnamed_service' => 'unnamed_service',
						'unnamed_wallet' => 'unnamed_wallet',
						'dust' => 'dust',
						'undefined' => 'undefined',
						'marketplace' => 'marketplace',
						'dex' => 'dex',
						'atm' => 'atm',
						'gambling' => 'gambling',
						'high_risk_jurisdiction' => 'high_risk_jurisdiction',    
						'mixer' => 'mixer',
						'enforcement_action' => 'enforcement_action',
						'darknet_market' => 'darknet_market',
						'illegal_service' => 'illegal_service',     
						'scam' => 'scam',
						'stolen_funds' => 'stolen_funds',
						'terrorist_financing' => 'terrorist_financing',
						'child_abuse_material' => 'child_abuse_material',
						'sanctions' => 'sanctions',
						'ransomware' => 'ransomware',
						'other' => 'other',
						'exchange' => 'exchange',
						'p2p_exchange' => 'p2p_exchange',
						'high_risk_exchange' => 'high_risk_exchange',
						'mining' => 'mining',
						'mining_pool' => 'mining_pool',
						'payment_service_provider' => 'payment_service_provider',	
					);	
					foreach ($arrs as $arr_k => $arr_t) {
						$merch_data['addr_max_' . $arr_k] = $premiumbox->get_option('bitok', 'apierror_opt2' . $arr_k);
						$premiumbox->delete_option('bitok', 'apierror_opt2' . $arr_k);
						$merch_data['txid_max_' . $arr_k] = $premiumbox->get_option('bitok', 'apierror_opt' . $arr_k);
						$premiumbox->delete_option('bitok', 'apierror_opt' . $arr_k);
					}					
				}
				if ('coinkyt' == $aml) {	
					$merch_data['api_timeout'] = $premiumbox->get_option('coinkyt', 'api_timeout');
					$premiumbox->delete_option('coinkyt', 'api_timeout');
					$merch_data['apierror_score'] = $premiumbox->get_option('coinkyt', 'apierror_score');
					$premiumbox->delete_option('coinkyt', 'apierror_score');
					$merch_data['addr_max'] = $premiumbox->get_option('coinkyt', 'error_score2');
					$premiumbox->delete_option('coinkyt', 'error_score2');
					$merch_data['txid_max'] = $premiumbox->get_option('coinkyt', 'error_score');
					$premiumbox->delete_option('coinkyt', 'error_score');
					$arrs = array(
						'p2p_exchange_unlicensed' => 'P2P Exchange unlicensed',
						'exchange_unlicensed' => 'Exchange unlicensed',
						'atm' => 'ATM',
						'decentralized_exchange' => 'Decentralized exchange',
						'p2p_exchange_licensed' => 'P2P Exchange licensed',
						'exchange_licensed' => 'Exchange licensed',
						'other' => 'Other',
						'unknown_owner' => 'Unknown owner',
						'rewards/fees' => 'Rewards/Fees',
						'miner' => 'Miner',
						'online_marketplace' => 'Online marketplace',
						'online_wallet' => 'Online wallet',
						'payment_systеm' => 'Payment system',
						'scam_crypto_exchange' => 'Scam crypto exchange',
						'darknet_marketplace' => 'Darknet marketplace',
						'darknet_service' => 'Darknet service',
						'scam' => 'Scam',
						'gambling' => 'Gambling',
						'stolen_assets' => 'Stolen assets',
						'mixing_service' => 'Mixing service',
						'ransom' => 'Ransom',
						'sanctions' => 'Sanctions',
						'terrorism_financing' => 'Terrorism financing',
						'illegal_service' => 'Illegal service',	
					);	
					foreach ($arrs as $arr_k => $arr_t) {
						$merch_data['addr_max_' . $arr_k] = $premiumbox->get_option('coinkyt', 'apierror_opt2' . $arr_k);
						$premiumbox->delete_option('coinkyt', 'apierror_opt2' . $arr_k);
						$merch_data['txid_max_' . $arr_k] = $premiumbox->get_option('coinkyt', 'apierror_opt' . $arr_k);
						$premiumbox->delete_option('coinkyt', 'apierror_opt' . $arr_k);
					}					
				}
				if ('getblock' == $aml) {
					$merch_data['api_timeout'] = $premiumbox->get_option('getblock', 'api_timeout');
					$premiumbox->delete_option('getblock', 'api_timeout');
					$merch_data['apierror_score'] = $premiumbox->get_option('getblock', 'apierror_score');
					$premiumbox->delete_option('getblock', 'apierror_score');	
					$merch_data['addr_max'] = $premiumbox->get_option('getblock', 'error_score2');
					$premiumbox->delete_option('getblock', 'error_score2');
					$merch_data['txid_max'] = $premiumbox->get_option('getblock', 'error_score');
					$premiumbox->delete_option('getblock', 'error_score');
					$arrs = array(
						'exchange_licensed' => 'exchange licensed',
						'p2p_exchange_licensed' => 'p2p exchange licensed',
						'seized_assets' => 'seized_assets',
						'other' => 'other',
						'transparent' => 'transparent',
						'atm' => 'Atm',
						'exchange_unlicensed' => 'exchange_unlicensed',
						'p2p_exchange_unlicensed' => 'p2p_exchange_unlicensed',
						'liquidity_pools' => 'liquidity_pools',
						'dark_service' => 'Dark Service',
						'dark_market' => 'Dark Market',
						'enforcement_action' => 'enforcement_action',
						'exchange_fraudulent' => 'Exchange Fraudulent',
						'exchange_mlrisk_high' => 'Exchange Mlrisk high',
						'exchange_mlrisk_low' => 'Exchange Mlrisk low',
						'exchange_mlrisk_moderate' => 'Exchange Mlrisk moderate',
						'exchange_mlrisk_veryhigh' => 'Exchange Mlrisk veryhigh',
						'gambling' => 'Gambling',
						'illegal_service' => 'Illegal Service',
						'marketplace' => 'Marketplace',
						'miner' => 'Miner',
						'mixer' => 'Mixer',    
						'payment' => 'Payment',
						'wallet' => 'Wallet',
						'p2p_exchange_mlrisk_high' => 'p2p_exchange_mlrisk_high',
						'p2p_exchange_mlrisk_low' => 'p2p exchange mlrisk low',     
						'stolen_coins' => 'Stolen coins',
						'ransom' => 'Ransom',
						'scam' => 'Scam',
						'child_exploitation' => 'child_exploitation',
						'sanctions' => 'sanctions',
						'terrorism_financing' => 'terrorism_financing',
					);	
					foreach ($arrs as $arr_k => $arr_t) {
						$merch_data['addr_max_' . $arr_k] = $premiumbox->get_option('getblock', 'apierror_opt2' . $arr_k);
						$premiumbox->delete_option('getblock', 'apierror_opt2' . $arr_k);
						$merch_data['txid_max_' . $arr_k] = $premiumbox->get_option('getblock', 'apierror_opt' . $arr_k);
						$premiumbox->delete_option('getblock', 'apierror_opt' . $arr_k);
					}					
				}				
				
				$array['ext_options'] = pn_json_encode($merch_data);

				$last_data = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "exts WHERE ext_type = 'amlcheck' AND ext_plugin = '$aml' AND ext_key = '$aml'");
				if (!isset($last_data->id)) {
					
					$wpdb->insert($wpdb->prefix . 'exts', $array);
					
					$posts = array();
					
					if ('amlbot' == $aml) {
						$posts['access_id'] = $premiumbox->get_option('amlbot', 'access_id');
						$premiumbox->delete_option('amlbot', 'access_id');
						$posts['access_key'] = $premiumbox->get_option('amlbot', 'access_key');
						$premiumbox->delete_option('amlbot', 'access_key');
					}
					if ('bitok' == $aml) {
						$posts['api_key'] = $premiumbox->get_option('bitok', 'api_key');
						$premiumbox->delete_option('bitok', 'api_key');
						$posts['api_secret'] = $premiumbox->get_option('bitok', 'api_secret');
						$premiumbox->delete_option('bitok', 'api_secret');			
					}
					if ('coinkyt' == $aml) {
						$posts['api_key'] = $premiumbox->get_option('coinkyt', 'api_key');
						$premiumbox->delete_option('coinkyt', 'api_key');	
					}
					if ('getblock' == $aml) {
						$posts['api_key'] = $premiumbox->get_option('getblock', 'api_key');
						$premiumbox->delete_option('getblock', 'api_key');	
					}				
					
					update_fdata('amlcheck', $aml, $posts);					
					
				}		
			}			
					
		}

		if ('27_6' == $step) {
			$query = $wpdb->query("CHECK TABLE " . $wpdb->prefix . "directions");
			if (1 == $query) {
				
				$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "directions LIKE 'amlcheck'");
				if (0 == $query) { 
					$wpdb->query("ALTER TABLE " . $wpdb->prefix . "directions ADD `amlcheck` varchar(150) NOT NULL");
				}
				
				$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "directions LIKE 'amlcheck_opts'");
				if (0 == $query) { 
					$wpdb->query("ALTER TABLE " . $wpdb->prefix . "directions ADD `amlcheck_opts` longtext NOT NULL");
				}				
				
				$datas = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "directions LIMIT {$offset},{$limit}");
				foreach ($datas as $data) {
					$data_id = $data->id;
					$array = array();
					
					$que = $wpdb->query("SHOW COLUMNS FROM ". $wpdb->prefix ."directions LIKE 'aml'");
					if ($que) {
						$options = pn_json_decode(is_isset($data, 'aml'));
						if (!is_array($options)) { $options = array(); }
						$give = intval(is_isset($options, 'give'));
						$get = intval(is_isset($options, 'get'));
						$merch = intval(is_isset($options, 'merch'));
						if ($give > 0 or $get > 0 or $merch > 0) {
							$array['amlcheck_opts'] = pn_json_encode($options);
							$array['amlcheck'] = 'amlbot';
						}
					}
					
					$que = $wpdb->query("SHOW COLUMNS FROM ". $wpdb->prefix ."directions LIKE 'bitok'");
					if ($que) {
						$options = pn_json_decode(is_isset($data, 'bitok'));
						if (!is_array($options)) { $options = array(); }
						$give = intval(is_isset($options, 'give'));
						$get = intval(is_isset($options, 'get'));
						$merch = intval(is_isset($options, 'merch'));
						if ($give > 0 or $get > 0 or $merch > 0) {
							$array['amlcheck_opts'] = pn_json_encode($options);
							$array['amlcheck'] = 'bitok';
						}
					}
					
					$que = $wpdb->query("SHOW COLUMNS FROM ". $wpdb->prefix ."directions LIKE 'coinkyt'");
					if ($que) {						
						$options = pn_json_decode(is_isset($data, 'coinkyt'));
						if (!is_array($options)) { $options = array(); }
						$give = intval(is_isset($options, 'give'));
						$get = intval(is_isset($options, 'get'));
						$merch = intval(is_isset($options, 'merch'));
						if ($give > 0 or $get > 0 or $merch > 0) {
							$array['amlcheck_opts'] = pn_json_encode($options);
							$array['amlcheck'] = 'coinkyt';
						}
					}

					$que = $wpdb->query("SHOW COLUMNS FROM ". $wpdb->prefix ."directions LIKE 'getblock'");
					if ($que) {
						$options = pn_json_decode(is_isset($data, 'getblock'));
						if (!is_array($options)) { $options = array(); }
						$give = intval(is_isset($options, 'give'));
						$get = intval(is_isset($options, 'get'));
						$merch = intval(is_isset($options, 'merch'));
						if ($give > 0 or $get > 0 or $merch > 0) {
							$array['amlcheck_opts'] = pn_json_encode($options);
							$array['amlcheck'] = 'getblock';
						}
					}		
					
					if (count($array) > 0) {
						$wpdb->update($wpdb->prefix . "directions", $array, array('id' => $data_id));
					}
				}				
			}
		}

		if ('27_7' == $step) {
			$query = $wpdb->query("CHECK TABLE " . $wpdb->prefix . "exchange_bids");
			if (1 == $query) {
				
				$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "exchange_bids LIKE 'aml_give'");
				if (0 == $query) { 
					$wpdb->query("ALTER TABLE " . $wpdb->prefix . "exchange_bids ADD `aml_give` longtext NOT NULL");
				}
				
				$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "exchange_bids LIKE 'aml_get'");
				if (0 == $query) { 
					$wpdb->query("ALTER TABLE " . $wpdb->prefix . "exchange_bids ADD `aml_get` longtext NOT NULL");
				}
				
				$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "exchange_bids LIKE 'aml_merch'");
				if (0 == $query) { 
					$wpdb->query("ALTER TABLE " . $wpdb->prefix . "exchange_bids ADD `aml_merch` longtext NOT NULL");
				}				
				
				$datas = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "exchange_bids LIMIT {$offset},{$limit}");
				foreach ($datas as $data) {
					
					$data_id = $data->id;
					$array = array();
					
					/****/
					$aml_give = pn_json_decode(is_isset($data, 'aml_give'));
					$aml_get = pn_json_decode(is_isset($data, 'aml_get'));
					$aml_merch = pn_json_decode(is_isset($data, 'aml_merch'));
					if (isset($aml_give['status']) and !isset($aml_give['nd'])) {
						$aml_give['nd'] = 1;
						$aml_give['status'] = str_replace(array('0', '2'), '3', is_isset($aml_give, 'status'));
						$array['aml_give'] = pn_json_encode($aml_give);
					}
					if (isset($aml_get['status']) and !isset($aml_get['nd'])) {
						$aml_get['nd'] = 1;
						$aml_get['status'] = str_replace(array('0', '2'), '3', is_isset($aml_get, 'status'));
						$array['aml_get'] = pn_json_encode($aml_get);
					}
					if (isset($aml_merch['status']) and !isset($aml_merch['nd'])) {
						$aml_merch['nd'] = 1;
						$aml_merch['status'] = str_replace(array('0', '2'), '3', is_isset($aml_merch, 'status'));
						$array['aml_merch'] = pn_json_encode($aml_merch);
					}	
					/****/
					
					/****/
					$aml_give = pn_json_decode(is_isset($data, 'bitok_give'));
					$aml_get = pn_json_decode(is_isset($data, 'bitok_get'));
					$aml_merch = pn_json_decode(is_isset($data, 'bitok_merch'));
					if (isset($aml_give['status']) and !isset($aml_give['nd'])) {
						$aml_give['nd'] = 1;
						$aml_give['status'] = str_replace(array('0', '2'), '3', is_isset($aml_give, 'status'));
						$array['aml_give'] = pn_json_encode($aml_give);
					}
					if (isset($aml_get['status']) and !isset($aml_get['nd'])) {
						$aml_get['nd'] = 1;
						$aml_get['status'] = str_replace(array('0', '2'), '3', is_isset($aml_get, 'status'));
						$array['aml_get'] = pn_json_encode($aml_get);
					}
					if (isset($aml_merch['status']) and !isset($aml_merch['nd'])) {
						$aml_merch['nd'] = 1;
						$aml_merch['status'] = str_replace(array('0', '2'), '3', is_isset($aml_merch, 'status'));
						$array['aml_merch'] = pn_json_encode($aml_merch);
					}	
					/****/

					/****/
					$aml_give = pn_json_decode(is_isset($data, 'coinkyt_give'));
					$aml_get = pn_json_decode(is_isset($data, 'coinkyt_get'));
					$aml_merch = pn_json_decode(is_isset($data, 'coinkyt_merch'));
					if (isset($aml_give['status']) and !isset($aml_give['nd'])) {
						$aml_give['nd'] = 1;
						$aml_give['status'] = str_replace(array('0', '2'), '3', is_isset($aml_give, 'status'));
						$array['aml_give'] = pn_json_encode($aml_give);
					}
					if (isset($aml_get['status']) and !isset($aml_get['nd'])) {
						$aml_get['nd'] = 1;
						$aml_get['status'] = str_replace(array('0', '2'), '3', is_isset($aml_get, 'status'));
						$array['aml_get'] = pn_json_encode($aml_get);
					}
					if (isset($aml_merch['status']) and !isset($aml_merch['nd'])) {
						$aml_merch['nd'] = 1;
						$aml_merch['status'] = str_replace(array('0', '2'), '3', is_isset($aml_merch, 'status'));
						$array['aml_merch'] = pn_json_encode($aml_merch);
					}	
					/****/

					/****/
					$aml_give = pn_json_decode(is_isset($data, 'getblock_give'));
					$aml_get = pn_json_decode(is_isset($data, 'getblock_get'));
					$aml_merch = pn_json_decode(is_isset($data, 'getblock_merch'));
					if (isset($aml_give['status']) and !isset($aml_give['nd'])) {
						$aml_give['nd'] = 1;
						$aml_give['status'] = str_replace(array('0', '2'), '3', is_isset($aml_give, 'status'));
						$array['aml_give'] = pn_json_encode($aml_give);
					}
					if (isset($aml_get['status']) and !isset($aml_get['nd'])) {
						$aml_get['nd'] = 1;
						$aml_get['status'] = str_replace(array('0', '2'), '3', is_isset($aml_get, 'status'));
						$array['aml_get'] = pn_json_encode($aml_get);
					}
					if (isset($aml_merch['status']) and !isset($aml_merch['nd'])) {
						$aml_merch['nd'] = 1;
						$aml_merch['status'] = str_replace(array('0', '2'), '3', is_isset($aml_merch, 'status'));
						$array['aml_merch'] = pn_json_encode($aml_merch);
					}	
					/****/					
					
					if (count($array) > 0) {
						$wpdb->update($wpdb->prefix . "exchange_bids", $array, array('id' => $data_id));
					}
				}				
			}
		}

		if ('27_8' == $step) {
			$query = $wpdb->query("CHECK TABLE " . $wpdb->prefix . "uv_wallets_files");
			if (1 == $query) {
				$path = $premiumbox->upload_dir . '/';
				$datas = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "uv_wallets_files LIMIT {$offset},{$limit}");
				foreach ($datas as $data) {
					$data_id = $data->id;
					
					$cc = $wpdb->get_var("SELECT COUNT(id) FROM " . $wpdb->prefix . "hidefiles WHERE id = '$data_id'");
					if (0 == $cc) {
						$arr = array();
						$arr['id'] = $data_id;
						$arr['itemtype'] = 'accountverify';
						$arr['item_id'] = $data->uv_wallet_id;
						$arr['file_name'] = $data->uv_data;
						$arr['file_ext'] = get_hf_ext($data->uv_data);
						$wpdb->insert($wpdb->prefix . "hidefiles", $arr);
					}					
					
					$old_file = $path . 'accountverify/' . $data->uv_wallet_id . '/' . $data->id . '.php';
					if (is_file($old_file)) {
						
						$new_file = $premiumbox->upload_dir . '/accountverify/' . $data->id . '.php';
						
						$file_text = @file_get_contents($old_file);

						$file_open = @fopen($new_file, 'w');
						@fwrite($file_open, $file_text);
						@fclose($file_open);
						if (is_file($old_file)) {
							@unlink($old_file);
						}
						
					}
				}				
			}
		}		
		
		do_action('migration_action', $step, $offset, $limit);
		
		$log['status'] = 'success';	
		$log['status_text'] = __('Ok!', 'pn');		
		
	} else {
		$log['status'] = 'error';
		$log['status_code'] = 1; 
		$log['status_text'] = __('Error! Insufficient privileges', 'pn');
	}
	
	echo pn_json_encode($log);
	exit;	
}