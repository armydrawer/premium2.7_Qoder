<?php
if (!defined('ABSPATH')) { exit(); }

add_filter('pn_adminpage_title_pn_bids', 'def_admin_title_pn_bids');
function def_admin_title_pn_bids() {
	
	return __('Orders', 'pn');
}

add_action('pn_adminpage_content_pn_bids', 'def_adminpage_content_pn_bids');
function def_adminpage_content_pn_bids() {
	global $wpdb, $premiumbox;
?>
<div class="head_bids_div">
	<div class="hb_button filter_change" data-name="changer_filter">
		<?php _e('Filters', 'pn'); ?>
	</div>
	<div class="hb_limit">
		<?php _e('Order ID', 'pn'); ?>	
		<input type="text" name="bid_id" id="fast_id" value="<?php echo pn_strip_input(is_param_get('bidid')); ?>" />
	</div>	
	<div class="hb_limit">
		<?php _e('Shown orders', 'pn'); ?>
		<?php
		$limit = list_bid_limit();	
		?>		
		<input type="text" name="limit" id="bid_limit_count" value="<?php echo $limit; ?>" />
	</div>	
	<div class="hb_limit">
		<a href="#" class="premium_button" id="bid_limit"><?php _e('Apply', 'pn'); ?></a>
			<div class="premium_clear"></div>
	</div>	
		<div class="premium_clear"></div>
</div>

<div class="head_bids_window">
	
	<!-- filter -->	
	<form action="<?php the_pn_link('bids_filter_change'); ?>" class="bids_filter_form" method="post">
	<div class="change_div" id="changer_filter">
		<div class="change_div_ins filters_this">	
			<?php
			$cats = array(
				'status' => __('Status of orders', 'pn'),
				'sum' => __('Amount and date', 'pn'),
				'currency' => __('Currency and codes', 'pn'),
				'user' => __('User filters', 'pn'),
				'other' => __('Other filters', 'pn'),
				'system' => '',
			);
			$lists = apply_filters('change_bids_filter_list', array());
			$lists = (array)$lists;
			
			foreach ($cats as $cat_name => $cat_title) {
				if (isset($lists[$cat_name]) and is_array($lists[$cat_name])) {
				?>
				<div class="change_div_one">
					<?php if ($cat_title) { ?>
						<div class="change_div_title"><?php echo $cat_title; ?></div>
					<?php } ?>
					
					<?php 
					foreach ($lists[$cat_name] as $data) { 
						$view = is_isset($data, 'view');
						$name = trim(is_isset($data, 'name'));
						$title = is_isset($data, 'title');
						$get = is_param_get($name);
						$options = is_isset($data, 'options');
						$work = trim(is_isset($data, 'work'));
						if ('input' == $work) {
							$def = pn_maxf_mb(pn_strip_input($get), 1000);
						} elseif ('int' == $work) {
							$def = intval($get);
						} elseif ('sum' == $work) {
							$def = is_sum($get);					
						} elseif ('options' == $work) {
							$en_options = array();
							if (is_array($options)) {
								foreach ($options as $k => $v) {
									$en_options[] = $k;
								}
							}
							if (is_array($get)) {
								$def = array();
								foreach ($get as $va) {
									if (in_array($va, $en_options)) {
										$def[] = $va;
									}
								}
							} else {
								$def = '';
								if (in_array($get, $en_options)) {
									$def = $get;
								}
							}
						}	
						if ('select' == $view and is_array($options)) {
						?>
							<div class="change_div_list">
								<div class="change_div_label"><?php echo $title; ?></div>
								<div class="change_div_input">
									<select name="<?php echo $name; ?>" class="bf_<?php echo $name; ?>" autocomplete="off">
										<?php foreach($options as $k => $v){ ?>
											<option value="<?php echo $k; ?>" <?php selected($k,$def); ?>><?php echo $v; ?></option>
										<?php } ?>
									</select>
								</div>
							</div>				
						<?php 
						} elseif ('multi' == $view and is_array($options)) {
							if (!is_array($def)) { $def=array(); }
						?>
							<div class="change_div_list multi" id="muarr_<?php echo $name; ?>">
								<div class="change_div_label"><?php echo $title; ?></div>
								<div class="change_div_input">
									<?php foreach ($options as $k => $t) { ?>
										<div><label><input type="checkbox" class="muarr_<?php echo $k; ?>" <?php if (in_array($k, $def)) { ?>checked="checked"<?php } ?> name="<?php echo $name; ?>[]" autocomplete="off" value="<?php echo $k; ?>" /> <?php echo $t; ?></label></div>
									<?php } ?>	
								</div>
							</div>
						<?php
						} elseif ('input' == $view) {
						?>
							<div class="change_div_list">
								<div class="change_div_label"><?php echo $title; ?></div>
								<div class="change_div_input">
									<input type="search" name="<?php echo $name; ?>" class="bf_<?php echo $name; ?>" value="<?php echo $def; ?>" autocomplete="off" />
								</div>
							</div>
						<?php
						} elseif ('date' == $view) {
						?>
							<div class="change_div_list">
								<div class="change_div_label"><?php echo $title; ?></div>
								<div class="change_div_input">
									<input type="search" name="<?php echo $name; ?>" autocomplete="off" class="js_datepicker bf_<?php echo $name; ?>" value="<?php echo $def; ?>" />
								</div>
							</div>
						<?php					
						} elseif ('clear' == $view) {
						?>
							<div class="premium_clear"></div>
						<?php
						}
					} ?>
						<div class="premium_clear"></div>
				</div>
				<?php
				}
			}
			?>	
		</div>
		<div class="change_close_wrap">
			<a href="#" class="filter_link f1 change_link_close"><?php _e('Hide', 'pn'); ?></a>
			<a href="#" id="go_filter" class="filter_link f2"><?php _e('Apply filters', 'pn'); ?></a>
			<a href="#" id="clear_filter" class="filter_link f3"><?php _e('Clear filters', 'pn'); ?></a>
			<a href="#" id="save_filter" class="filter_link f4"><?php _e('Save filters', 'pn'); ?></a>
			<a href="#" id="restore_filter" class="filter_link f5"><?php _e('Restore filters', 'pn'); ?></a>	
				<div class="premium_clear"></div>			
		</div>	
		<div class="premium_clear"></div>
	</div>	
	</form>
	<!-- end filter -->
	
</div>
	<div class="premium_clear"></div>	

<input type="hidden" name="visible_ids" id="visible_ids" value="" />	
	
<form action="<?php the_pn_link('bids_action_ajax', 'post'); ?>" class="bids_action_form" method="post">	
	<input type="hidden" name="_referrer" id="param_hide" style="width: 100%;" value="<?php echo pn_strip_input($_SERVER['REQUEST_URI']); ?>" />
	<div id="bids_html"><?php echo get_bids_html($_SERVER['REQUEST_URI'], 1); ?></div>	
</form>	
		
<script type="text/javascript">
jQuery(function($) {  	

	$(document).on('click', '.hb_button', function() {
		
		var block = $(this).attr('data-name');
		
		if ($(this).hasClass('open')) {
			$(this).removeClass('open');
			$('#' + block).stop(true, true).slideUp(300);
		} else {
			$('.hb_button').removeClass('open');
			$('.change_div').stop(true, true).slideUp(300);
			$(this).addClass('open');
			$('#' + block).stop(true,true).slideDown(300);
		}
		
		return false;
	});
	
	$(document).on('click', '.change_link_close', function() {
		
		$('.change_div').stop(true, true).slideUp(300);
		$('.hb_button').removeClass('open');
		load_html_bids(1);
		
		return false;
	});	
	
function assembly_bids_id() {
	
	var ids = '';
	$('.one_bids').each(function() {
		var now_id = $(this).attr('id').replace('bidid_', '');
		ids = ids + now_id + ',';
	});
	$('#visible_ids').val(ids);
	
}
assembly_bids_id();	

function load_html_bids(p) {
	
 	$('.filter_change, .filter_link').addClass('active');
	$('.apply_loader').show();
	
	var nurl = '<?php echo admin_url('admin.php?page=pn_bids'); ?>' + '&page_num=' + p;
	var param = 'page_num=' + p;	
	$('.filters_this input[type=text], .filters_this input[type=search], .filters_this select, .filters_this input[type=checkbox]:checked').each(function() {
		var vale = $(this).val();
		if (vale && vale !== '0') {
			nurl = nurl + '&' + encodeURIComponent($(this).attr('name')) + '=' + encodeURIComponent(vale);
			param = param + '&' + encodeURIComponent($(this).attr('name')) + '=' + encodeURIComponent(vale);
		}
	});	
			
	window.history.replaceState(null, null, nurl);

	$('#param_hide').val(param);

	$.ajax({
		type: "POST",
		url: "<?php the_pn_link('bids_filter_html', 'post'); ?>",
		dataType: 'json',
		data: 'url=' + encodeURIComponent(param),
		error: function(res, res2, res3) {
			<?php do_action('pn_js_error_response', 'ajax'); ?>
		},		
		success: function(res)
		{		
			$('.filter_change, .filter_link').removeClass('active');
			$('.apply_loader').hide();
			if (res['status'] == 'error') {
				<?php do_action('pn_js_alert_response'); ?>
			} else if (res['status'] == 'success') {
				$('#bids_html').html(res['html']);
				assembly_bids_id();
			}		
		}
	});

} 
	
function head_filter_action() {
	
	$('.filter_change').addClass('active');
		
	var thet = $(this);
	var param = 'count=' + $('#bid_limit_count').val();

	$.ajax({
		type: "POST",
		url: "<?php the_pn_link('bids_filter_count', 'post'); ?>",
		dataType: 'json',
		data: param,
		error: function(res, res2, res3) {
			<?php do_action('pn_js_error_response', 'ajax'); ?>
		},			
		success: function(res)
		{		
			$('.filter_change').removeClass('active');
						
			if (res['status'] == 'error') {
				<?php do_action('pn_js_alert_response'); ?>
			} else if (res['status'] == 'success') {
				load_html_bids(1);					
			}		
		}
	});
	
}	
	
	$(document).on('keydown', '#bid_limit_count, #fast_id', function(e) {
		if (e.which == '13') {
			head_filter_action();
		}
	});	
		
 	$(document).on('click', '#bid_limit', function() {
		
		head_filter_action();
		
		return false;
	});
	
 	$(document).on('change', '.bf_bidid', function() {
		
		var bidid = $(this).val();
		$('#fast_id').val(bidid);
		
		return false;
	});
	
 	$(document).on('keyup', '.bf_bidid', function() {
		
		var bidid = $(this).val();
		$('#fast_id').val(bidid);
		
		return false;
	});
	
 	$(document).on('change', '#fast_id', function() {
		
		var bidid = $(this).val();
		$('.bf_bidid').val(bidid);
		
		return false;
	});
	
 	$(document).on('keyup', '#fast_id', function() {
		
		var bidid = $(this).val();
		$('.bf_bidid').val(bidid);
		
		return false;
	});	
	
	$('.bids_filter_form').ajaxForm({
	    dataType:  'json',
        beforeSubmit: function(a, f, o) {
		    $('.filter_change, .filter_link').addClass('active');
        },
		error: function(res, res2, res3) {
			<?php do_action('pn_js_error_response', 'form'); ?>
		},			
        success: function(res) {
			$('.filter_change, .filter_link').removeClass('active');
			if (res['status'] == 'error') {
				<?php do_action('pn_js_alert_response'); ?>
			} 
        }
    });	
	
	$(document).on('click', '#save_filter', function() {
		
		$('.bids_filter_form').submit();
		load_html_bids(1);
		
		return false;
	});	
	
	$(document).on('click', '#restore_filter', function() {
		
		$('.filter_change, .filter_link').addClass('active');
		
		var param = 'id=1';
		$.ajax({
			type: "POST",
			url: "<?php the_pn_link('bids_filter_restore', 'post');?>",
			dataType: 'json',
			data: param,
			error: function(res, res2, res3) {
				<?php do_action('pn_js_error_response', 'ajax'); ?>
			},			
			success: function(res)
			{
					
				$('.filter_change, .filter_link').removeClass('active');	
				if (res['status'] == 'error') {
					<?php do_action('pn_js_alert_response'); ?>
				} else if (res['status'] == 'success') {		

					$('.filters_this input[type=checkbox]').prop('checked', false);			
					$.each(res['values'], function(index, value) {
						if ($.isArray(value)) {
							var par = $('#muarr_' + index);
							$.each(value, function(index, value) {
								par.find('.muarr_' + value).prop('checked', true);
							});
						} else {
							$('.filters_this input[type=text], .filters_this input[type=search], .filters_this select').each(function() {
								var nk = $(this).attr('name');
								if (nk == index) {
									$(this).val(decodeURIComponent(value));
								}
							});								
						}
					});	

					load_html_bids(1);
				}	
			}
		});		
		
		return false;
	});	

	$(document).on('click', '#clear_filter', function() {
						
		$('.filters_this input[type=text], .filters_this input[type=search]').val('');
					
		$('.filters_this select').each(function() {
			$(this).find('option:first').prop('selected', true);
		});
		
		$('.filters_this input[type=checkbox]').prop('checked', false);
		
		$('.change_div').stop(true,true).slideUp(300);
		$('.hb_button').removeClass('open');		
		
		load_html_bids(1);
		
		return false;
	});

	$(document).on('click', '#go_filter', function() {	
		
		$('.change_div').stop(true,true).slideUp(300);
		$('.hb_button').removeClass('open');		
		load_html_bids(1);
		
		return false;
	});	
	
	$(document).on('click', '.bids_pagenavi a', function() {
		
		var p = $(this).attr('data-page');
		load_html_bids(p);
		
		return false;
	});	
	
function go_ajax_action() {
	
	var vale = $('.sel_action').val();
	if (vale == '0') {
		alert('<?php _e('Error! Action is not selected!', 'pn'); ?>');
		$('.hotkey_tr').removeClass('active');		
	} else if (vale == 'realdelete') {
		<?php
		$ui = wp_get_current_user();
		$confirm_deletion = intval(is_isset($ui, 'confirm_deletion'));
		if (1 != $confirm_deletion) {
		?>
		if (confirm("<?php _e('Are you sure you want to delete these orders?', 'pn'); ?>")) {
			$('.bids_action_form').submit();
		} else {
			$('.hotkey_tr').removeClass('active');
		}
		<?php } else { ?>
			$('.bids_action_form').submit();
		<?php } ?>
	} else {			
		$('.bids_action_form').submit();			
	}
	
}

<?php do_action('change_bids_filter_js'); ?>

 	$('.bids_action_form').ajaxForm({
		dataType:  'json',
		beforeSubmit: function(a, f, o) {
			$('.filter_change, .filter_link').addClass('active');
			$('.apply_loader').show();
		},
		error: function(res, res2, res3) {
			<?php do_action('pn_js_error_response', 'form'); ?>
		},			
		success: function(res) { 
			$('.filter_change, .filter_link').removeClass('active');
			$('.apply_loader').hide();
			
			if (res['status'] == 'error') {
				<?php do_action('pn_js_alert_response'); ?>
			} else if (res['status'] == 'success') {
				$('#bids_html').html(res['html']);
				assembly_bids_id();
			}
			$('.hotkey_tr').removeClass('active');
		}
	});
		
	$(document).on('click', '.js_bids_action', function() {	
	
		go_ajax_action();
		
		return false;
	}); 	
	
  	$(document).on('change', '.check_all', function() {
		
		if ($(this).prop('checked')) {
			$('.check_all, .check_one').prop('checked', true);
			$('.check_one').parents('.one_bids').addClass('checked');
		} else {
			$('.check_all, .check_one').prop('checked', false);
			$('.check_one').parents('.one_bids').removeClass('checked');
		}
		
		return false;
	});
	
	$(document).on('change', '.check_one', function() {
		
		if ($(this).prop('checked')) {
			$(this).parents('.one_bids').addClass('checked');
		} else {
			$('.check_all').prop('checked', false);
			$(this).parents('.one_bids').removeClass('checked');
		}
		
		return false;
	});
	
	$(document).on('click', '.js_info', function() {
		
		$(this).parents('.one_bids').find('.js_info_block').stop(true, true).slideToggle(300);
		
		return false;
	});	
	
	$(document).on('change', '.sel_action', function() {
		
		var vale = $(this).val();
		$('.sel_action').val(vale);
		
		return false;
	});	 	

});
</script>
<?php
}