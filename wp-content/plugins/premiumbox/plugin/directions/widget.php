<?php
if (!defined('ABSPATH')) { exit(); }

add_action('premium_js', 'premium_js_exchange_widget');
function premium_js_exchange_widget() {	
	$tbl = get_type_table();
?>	
jQuery(function($) { 

	if ($('#hexch_html').length > 0) {
		$(document).on('click', '.js_exchange_link', function() {

			$('.js_exchange_link').removeClass('active');
			$(this).addClass('active');
				
			var direction_id = $(this).attr('data-direction-id'); 
				
			$('.js_exchange_widget_abs').show();
				
			var tscroll = $('#hexch_html').offset().top - 100;
			$('body,html').animate({scrollTop : tscroll}, 500);
				
			var param = 'direction_id=' + direction_id;
			$.ajax({
				type: "POST",
				url: "<?php echo get_pn_action('exchange_widget'); ?>",
				dataType: 'json',
				data: param,
				error: function(res, res2, res3){
					<?php do_action('pn_js_error_response', 'ajax'); ?>
				},					
				success: function(res)
				{
					if (res['html']) {
						$('#hexch_html').html(res['html']);
					}
					if (res['status'] == 'error') {
						$('#hexch_html').html('<div class="resultfalse"><div class="resultclose"></div>' + res['status_text'] + '</div>');
					}
					<?php do_action('live_change_html'); ?>
					$('.js_exchange_widget_abs').hide();
						
					<?php if (!in_array($tbl, array('1', '4', '5'))) { ?>
						$('.js_exchange_link').removeClass('active');
					<?php } ?>							
				}
			});	
			
			return false;
		});
	}
	
});
<?php	
}

function the_exchange_widget() { 
	global $premiumbox, $widget_exchange;
	
	$widget_exchange = intval($widget_exchange);	
	$exch_method = intval($premiumbox->get_option('exchange', 'exch_method'));
	$exch_method = apply_filters('exch_method', $exch_method);
	if (1 == $exch_method and 0 == $widget_exchange) {
?>
<form method="post" class="ajax_post_bids" action="<?php echo get_pn_action('create_bid'); ?>">
	<div class="hexch_ajax_wrap">
		<div class="hexch_ajax_wrap_abs js_exchange_widget_abs"></div>
		<div id="hexch_html">
		<?php 
			$dir_id = apply_filters('table_exchange_widget', 0, 'widget');
			$dir_id = intval($dir_id);
			if ($dir_id) {
				echo get_exchange_widget($dir_id); 
			}
		?>
		</div>
	</div>
</form>
<?php
	}
}

function table_exchange_widget($def_cur_from = '', $def_cur_to = '', $def_direction_id = '') {
	global $widget_exchange, $premiumbox;

	$widget_exchange = 1;
	$temp = '';
	
	$dir_id = apply_filters('table_exchange_widget', $def_direction_id, 'intable', $def_cur_from, $def_cur_to);
	$dir_id = intval($dir_id);
	if ($dir_id) {
		$html = get_exchange_widget($dir_id);
	} else {
		$html = '<div class="htable_notwidget"><div class="htable_notwidget_ins">' . __('Select currency "Receive" to display exchange form', 'pn') . '</div></div>';
		$html = apply_filters('notexchange_widget', $html);
	}
	
	$temp = '
	<form method="post" class="ajax_post_bids" action="' . get_pn_action('create_bid') . '">
		<div class="htable_ajax_wrap">
			<div class="htable_ajax_wrap_abs js_exchange_widget_abs"></div>
			<div id="hexch_html">' . $html . '</div>
		</div>
	</form>	
	';
	
	return $temp;
}

add_action('premium_siteaction_exchange_widget', 'def_premium_siteaction_exchange_widget');
function def_premium_siteaction_exchange_widget() {
	global $premiumbox;
	
	_json_head(); 
	_method('post'); 
	
	$log = array();
	$log['status'] = '';
	$log['status_code'] = 0; 
	$log['status_text'] = __('Error','pn');	
	
	$premiumbox->up_mode('post');
	
	$direction_id = is_param_post('direction_id');

	$exch_method = intval($premiumbox->get_option('exchange', 'exch_method'));
	$exch_method = apply_filters('exch_method', $exch_method);
	if (1 == $exch_method or 5 == get_type_table()) {
		$log['status'] = 'success';
		$log['html'] = get_exchange_widget($direction_id);
	} else {
		$log['status'] = 'error';
		$log['status_code'] = 1; 		
	}
	
	echo pn_json_encode($log);
	exit;
}

function get_exchange_widget($id) {
	global $wpdb, $premiumbox, $direction_data;
	
	$temp =' ';
	
	$id = intval($id);		
	
	$show_data = pn_exchanges_output('home');
	if (strlen($show_data['text']) > 0) {
		$temp .= '<div class="exch_error resultfalse"><div class="exch_error_ins">' . $show_data['text'] . '</div></div>';
	}
	if (1 == $show_data['show']) {
	
		set_directions_data('home', 1, $id);	
		
		if (isset($direction_data->direction_id) and $direction_data->direction_id > 0) {
					
			$temp .= apply_filters('before_exchange_widget', '');
			
			$temp .= '
			<input type="hidden" name="direction_id" class="js_direction_id" value="' . $direction_data->direction_id . '" />
			';
					
			$array = set_exchange_shortcode('exchange_html_list_ajax');		
		
			$html = '
			<div class="hexch_widget">
			
				[window]
				[frozen]
				[timeline]
				[other_filter]
								
				<div class="hexch_div">
					<div class="hexch_div_ins">
					
						<!-- defore infoblock -->
					
						<div class="hexch_bigtitle">' . __('Data input', 'pn') . '</div>
						<div class="hexch_information">
							<div class="hexch_information_line"><span class="hexh_line_label">' . __('Exchange rate', 'pn') . '</span>: [course]</div>
							<div class="hexch_information_line"><span class="hexh_line_label">' . __('Reserve', 'pn') . '</span>: [reserve]</div>
							[user_discount_html]
						</div>

						<!-- after infoblock -->
						
						<div class="hexch_cols">
							<div class="hexch_left">
							
								<div class="hexch_title">
									<div class="hexch_title_ins">
										<div class="hexch_title_logo currency_logo" style="background-image: url([currency_logo_give]);"></div>
										<span class="hexch_psys">[psys_give] [currency_code_give]</span>
									</div>
								</div>
								
								<!-- before give meta -->
								[meta1]
								<!-- end give meta -->

								<div class="hexch_curs_line">
									<div class="hexch_curs_label">
										<div class="hexch_curs_label_ins">
											' . __('Amount', 'pn') . '<span class="req">*</span>:
										</div>
									</div>											
			
									[input_give]
						
									<div class="clear"></div>
								</div>

								<div class="hexch_curs_line js_viv_com1" [com_class_give]>
									<div class="hexch_curs_label">
										<div class="hexch_curs_label_ins">
											' . __('With fees', 'pn') . '<span class="req">*</span>:
										</div>
									</div>
									[com_give]
				
									<div class="clear"></div>
								</div>

								[com_give_text]
								
								[give_field]
	
							</div>
							<div class="hexch_right">
							
								<div class="hexch_title">
									<div class="hexch_title_ins">
										<div class="hexch_title_logo currency_logo" style="background-image: url([currency_logo_get]);"></div>
										<span class="hexch_psys">[psys_get] [currency_code_get]</span>
									</div>
								</div>
								
								<!-- before get meta -->
								[meta2]
								<!-- after get meta -->

								<div class="hexch_curs_line">
									<div class="hexch_curs_label">
										<div class="hexch_curs_label_ins">
											'. __('Amount', 'pn') .'<span class="req">*</span>:
										</div>
									</div>
					
									[input_get]	
					
									<div class="clear"></div>
								</div>																
				
								<div class="hexch_curs_line js_viv_com2" [com_class_get]>
									<div class="hexch_curs_label">
										<div class="hexch_curs_label_ins">
											' . __('With fees', 'pn') . '<span class="req">*</span>:
										</div>
									</div>
									[com_get]
					
									<div class="clear"></div>
								</div>		

								[com_get_text]
					
								[get_field]

							</div>
						</div>	
							
						<!-- before dirfields -->	
						[direction_field]
							<div class="clear"></div>
						<!-- after dirfields -->		
							
						[filters]
						[submit]
						
						[check]
						[remember]
						[result]
							
					</div>
				</div>	
			</div>
			';		
		
			$html = apply_filters('exchange_html_ajax', $html);			
			$temp .= replace_tags($array, $html);
					
			$temp .= apply_filters('after_exchange_widget', '');
 		}		
	}
	
	return $temp;
}