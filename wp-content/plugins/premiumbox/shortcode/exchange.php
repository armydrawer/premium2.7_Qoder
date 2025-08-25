<?php
if (!defined('ABSPATH')) { exit(); } 
 
add_action('exchange_direction_is_404', 'default_exchange_direction_is_404');
function default_exchange_direction_is_404() {
	global $wp_query;
	
	status_header(404);
	$wp_query->set_404();
	
}
 
add_action('template_redirect', 'direction_initialization', 10);
function direction_initialization() {
	global $wpdb, $premiumbox, $direction_data;
	
	if (is_pn_page('exchange')) {
		$is_404 = 1;
		$pnhash = is_direction_name(get_query_var('pnhash'));
		if ($pnhash) {
			set_directions_data('exchange', 1, 0, $pnhash);
			if (isset($direction_data->direction_id)) {
				$is_404 = 0;
			}
		}
		if ($is_404) {
			do_action('exchange_direction_is_404', $pnhash);	
		}
	} 
	
}

function get_exchange_title($place = 'breadcrumb') {
	global $direction_data;	
	
	$place = trim($place);
	if (isset($direction_data->item_give) and isset($direction_data->item_get)) {
		$item_title1 = pn_strip_input($direction_data->item_give);
		$item_title2 = pn_strip_input($direction_data->item_get);	
		$title = sprintf(__('Exchange %1$s to %2$s', 'pn'), $item_title1, $item_title2);
		
		return apply_filters('get_exchange_title', $title, $item_title1, $item_title2, $place);
	} else {
		return __('Error 404', 'pn');
	}
}

add_filter('wp_title', 'direction_wp_title', 100);
function direction_wp_title($title) {
	
	if (is_pn_page('exchange')) {
		return get_exchange_title('title');
	} 
	
	return $title;			
}

add_filter('get_exchange_title', 'get_exchange_title_seo', 10, 4);
function get_exchange_title_seo($title, $item_title1, $item_title2, $place) {
	global $exchange_seo;
	
	if (is_extension_active('pn_extended', 'moduls', 'seo')) {
		global $direction_data;
		
		$direction_id = intval($direction_data->direction_id);
		
		if (!is_array($exchange_seo)) {
			$exchange_seo = (array)get_direction_meta($direction_id, 'seo');	
		}
			
		$plugin = get_plugin_class();
		if ('title' == $place) {
			$pl1 = 'seo_title';
			$pl2 = 'exch_temp';
		} else {
			$pl1 = 'seo_exch_title';
			$pl2 = 'exch_temp2';		
		}	
		
		$seo_title = pn_strip_input(ctv_ml(is_isset($exchange_seo, $pl1)));
		if (strlen($seo_title) > 0) {
			return replace_exchange_seo($seo_title, $direction_data);
		}	
		
		$seo_title = pn_strip_input(ctv_ml($plugin->get_option('seo', $pl2)));
		if (strlen($seo_title) > 0) {
			return replace_exchange_seo($seo_title, $direction_data);
		}	

	}
	
	return $title;
}

function replace_exchange_seo($text, $item) {
	
	$sitename = pn_site_name();
	$title1 = pn_strip_input($item->item_give);
	$code_give = is_site_value($item->vd1->currency_code_title);
	$xml_value_give = is_xml_value($item->vd1->xml_value);	
	$title2 = pn_strip_input($item->item_get);
	$code_get = is_site_value($item->vd2->currency_code_title);
	$xml_value_get = is_xml_value($item->vd2->xml_value);
	
	$array = array(
		'[title1]' => $title1,
		'[curr_title1]' => $code_give,
		'[xml_title1]' => $xml_value_give,
		'[title2]' => $title2,
		'[curr_title2]' => $code_get,
		'[xml_title2]' => $xml_value_get,		
		'[sitename]' => $sitename,
	);	
	$array = apply_filters('replace_direction_seo', $array, $item);
	$text = replace_tags($array, $text);	
	
	return $text;
}

add_action('wp_before_admin_bar_render', 'wp_before_admin_bar_render_direction', 1);
function wp_before_admin_bar_render_direction() {
	global $wp_admin_bar, $direction_data;
	
	if (current_user_can('administrator') or current_user_can('pn_directions')) {
		if (is_pn_page('exchange')) {
			if (isset($direction_data->direction_id)) {
				
				$wp_admin_bar->add_menu( array(
					'id'     => 'edit_directions',
					'href' => admin_url('admin.php?page=pn_add_directions&item_id=' . $direction_data->direction_id),
					'title'  => __('Edit direction exchange', 'pn'),	
				));	
				if (current_user_can('administrator') or current_user_can('pn_currency')) {
					$wp_admin_bar->add_menu( array(
						'id'     => 'edit_currency_give',
						'parent' => 'edit_directions',
						'href' => admin_url('admin.php?page=pn_add_currency&item_id=' . $direction_data->currency_id_give),
						'title'  => sprintf(__('Edit "%s"', 'pn'), $direction_data->item_give),	
					));
					$wp_admin_bar->add_menu( array(
						'id'     => 'edit_currency_get',
						'parent' => 'edit_directions',
						'href' => admin_url('admin.php?page=pn_add_currency&item_id=' . $direction_data->currency_id_get),
						'title'  => sprintf(__('Edit "%s"', 'pn'), $direction_data->item_get),	
					));	
				}
				
			}
		}
	}
}
 
add_action('premium_js', 'premium_js_exchange_stepselect');
function premium_js_exchange_stepselect() {
?>	
jQuery(function($) { 

 	function get_exchange_step1(id) {
		
		var id1 = $('#select_give').val();
		var id2 = $('#select_get').val();
		$('.exch_ajax_wrap_abs').show();	
		var param = 'id=' + id + '&id1=' + id1 + '&id2=' + id2;
		$.ajax({
			type: "POST",
			url: "<?php echo get_pn_action('exchange_stepselect'); ?>",
			dataType: 'json',
			data: param,
			error: function(res, res2, res3) {
				<?php do_action('pn_js_error_response', 'ajax'); ?>
			},			
			success: function(res)
			{	
				$('.exch_ajax_wrap_abs').hide();
				
				if (res['status'] == 'success') {
					
					$('#exch_html').html(res['html']);	

					if ($('#the_title_page').length > 0) {
						$('#the_title_page, .direction_title').html(res['titlepage']);
					}	
					
					$('title').html(res['title']);
					
					if ($('meta[name=keywords]').length > 0) {
						$('meta[name=keywords]').attr('content', res['keywords']);
					}
					if ($('meta[name=description]').length > 0) {
						$('meta[name=description]').attr('content', res['description']);
					}
					
					if (res['url']) {
						window.history.replaceState(null, null, res['url']);
					}				
					
					<?php do_action('live_change_html'); ?>
				} else {
					<?php do_action('pn_js_alert_response'); ?>
				}	
			}
		});		
		
	}
	$(document).on('change', '#select_give', function() {
		
		get_exchange_step1(1);
		
	});
	$(document).on('change', '#select_get', function() {
		
		get_exchange_step1(2);
		
	});	
	
});	
<?php	
}

add_action('premium_siteaction_exchange_stepselect', 'def_premium_siteaction_exchange_stepselect');
function def_premium_siteaction_exchange_stepselect() {
	global $wpdb, $premiumbox, $direction_data;	
	
	_json_head();
	_method('post'); 
	
	$log = array();
	$log['status'] = '';
	$log['status_code'] = 0; 
	$log['status_text'] = __('Error','pn');		
	
	$premiumbox->up_mode('post');
	
	$show_data = pn_exchanges_output('exchange');
	
	if (1 == $show_data['show']) {
	
		$id = intval(is_param_post('id'));
		$id1 = intval(is_param_post('id1')); if ($id1 < 0) { $id1 = 0; }
		$id2 = intval(is_param_post('id2')); if ($id2 < 0) { $id2 = 0; }

		set_directions_data('exchange', 0, 0, '', $id1, $id2, $id);
		
		if (isset($direction_data->direction_id) and $direction_data->direction_id > 0) {				
							
			$log['status'] = 'success';
			$log['url'] = get_exchange_link($direction_data->direction->direction_name);
			$log['html'] = get_exchange_html($direction_data->direction_id, $id);			
			$log['title'] = get_exchange_title('title');
			$log['titlepage'] = get_exchange_title();
			$log['keywords'] = '';
			$log['description'] = '';
			$log = apply_filters('exchange_step_meta', $log);
				
		} else {			
			$log['status'] = 'error';
			$log['status_code'] = 1;
			$log['status_text'] = __('Error! The direction do not exist', 'pn');
		}
		
	} else {
		$log['status'] = 'error';
		$log['status_code'] = 1;
		$log['status_text'] = $show_data['text'];
	}
	
	echo pn_json_encode($log);
	exit;
}

function get_exchange_html($id = '', $side_id = '') {
	global $wpdb, $premiumbox, $direction_data;
	
	$temp = ' ';
	
	$id = intval($id);
	
	$side_id = intval($side_id);
	if ($side_id != 2) { $side_id = 1; }
	
	if (!isset($direction_data->direction_id)) {
		set_directions_data('exchange', 0, $id);
	}	
	
	if (isset($direction_data->direction_id) and $direction_data->direction_id > 0) {
		
		$temp .= apply_filters('before_exchange_table', '');
		
		$temp .= '
		<input type="hidden" name="direction_id" class="js_direction_id" value="' . $direction_data->direction_id . '" />
		';
			
		$array = set_exchange_shortcode('exchange_html_list', $side_id);		

		$html = '
		[window]
		[frozen]
		[timeline]
		[other_filter]
		
		<div class="xchange_div">
			<div class="xchange_div_ins">
				<div class="xchange_div_cols">
					<div class="xchange_div_col_give">
						<div class="xchange_data_title give">
							<div class="xchange_data_title_ins">
								<span>' . __('Send', 'pn') . '</span>
							</div>	
						</div>
						<div class="xchange_data_div">
							<div class="xchange_data_ins">
								<div class="xchange_data_left">
									[meta1d]
								</div>	
								<div class="xchange_data_right">
									[meta1]
								</div>
									<div class="clear"></div>							
							
								<div class="xchange_data_left">
									<div class="xchange_select">
										[select_give]						
									</div>
								</div>
								<div class="xchange_data_right">
									<div class="xchange_sum_line">
										<div class="xchange_sum_label">
											' . __('Amount', 'pn') . '<span class="req">*</span>:
										</div>
										[input_give]
											<div class="clear"></div>
									</div>	
								</div>
									<div class="clear"></div>
								<div class="xchange_data_left js_viv_com1" [com_class_give]>
									[com_give_text]
								</div>	
								<div class="xchange_data_right js_viv_com1" [com_class_give]>
									<div class="xchange_sum_line">
										<div class="xchange_sum_label">
											' . __('With fees', 'pn') . '<span class="req">*</span>:
										</div>
										[com_give]
											<div class="clear"></div>
									</div>
								</div>
									<div class="clear"></div>								
								<div class="xchange_data_left">
								
									[give_field]
									
								</div>

								<div class="clear"></div>
							</div>
						</div>	
					</div>
					<div class="xchange_div_col_get">
						<div class="xchange_data_title get">
							<div class="xchange_data_title_ins">
								<span>' . __('Receive', 'pn') . '</span>
							</div>	
						</div>
						<div class="xchange_data_div">
							<div class="xchange_data_ins">
								<div class="xchange_data_left">
									[meta2d]
								</div>
								<div class="xchange_data_right">
									[meta2]
								</div>
									<div class="clear"></div>							
	
								<div class="xchange_data_left">
									<div class="xchange_select">
										[select_get]						
									</div>									
								</div>		
								<div class="xchange_data_right">
									<div class="xchange_sum_line">
										<div class="xchange_sum_label">
											' . __('Amount', 'pn') . '<span class="req">*</span>:
										</div>
										[input_get]
											<div class="clear"></div>
									</div>	
								</div>
									<div class="clear"></div>
								<div class="xchange_data_left js_viv_com2" [com_class_get]>
									[com_get_text]
								</div>
								<div class="xchange_data_right js_viv_com2" [com_class_get]>
									<div class="xchange_sum_line">
										<div class="xchange_sum_label">
											' . __('With fees', 'pn') . '<span class="req">*</span>:
										</div>
										[com_get]
											<div class="clear"></div>
									</div>									
								</div>
									<div class="clear"></div>
									
								<div class="xchange_data_left">	
									[get_field]
								</div>

								<div class="clear"></div>
							</div>
						</div>
					</div>
				</div>
				
				[direction_field]		

					<div class="clear"></div>
				[filters]
				[submit]

				[check]
				[remember]
				[result]
			</div>
		</div>
		
		[description]
		[otherdir]
		';

		$html = apply_filters('exchange_html', $html);			
		$temp .= replace_tags($array, $html);	
	
		$temp .= apply_filters('after_exchange_table', '');
	} else {
		$temp .= '<div class="exch_error"><div class="exch_error_ins">' . __('Error! The direction do not exist', 'pn') . '</div></div>';
	}
	
	return $temp;
}

function exchange_page_shortcode($atts = '', $content = '') {
	
	$temp = '';
	
	$show_data = pn_exchanges_output('exchange');
	
	if (strlen($show_data['text']) > 0) {
		$temp .= '<div class="resultfalse exch_error"><div class="exch_error_ins">' . $show_data['text'] . '</div></div>';
	}	
	
	if (1 == $show_data['show']) {
	
		$temp .= apply_filters('before_exchange_page', '');
		
		$temp .= '
		<form method="post" class="ajax_post_bids" action="' . get_pn_action('create_bid') . '">
			<div class="exch_ajax_wrap">
				<div class="exch_ajax_wrap_abs"></div>
				<div id="exch_html">' . get_exchange_html(is_isset($atts, 'direction_id')) . '</div>
			</div>
		</form>
		';
		
		$temp .= apply_filters('after_exchange_page', '');
	
	}
	
	return $temp;
}
add_shortcode('exchange', 'exchange_page_shortcode');