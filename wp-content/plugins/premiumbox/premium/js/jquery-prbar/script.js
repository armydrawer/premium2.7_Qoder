/*
version: 0.3
*/
jQuery(function($) {
	
    var defaults = { 
		trigger: '',
		start_title: 'we determine the number of requests...',
		end_title: 'number of requests defined',
		found_title: 'Found: %count% requests',
		perform_title: 'Perform:',
		step_title: 'Step:',
		run_title: 'Run',
		line_text: 'start step %now% of %max% step',
		end_progress: 'action is completed',
		line_success: 'step %now% is success',
		success: function(){ }
	};
 
    $.fn.PrBar = function(params) {
        var options = $.extend({}, defaults, options, params);
        var thet = $(this);
 
		var trigger = options['trigger'];
		var start_title = options['start_title'];
		var end_title = options['end_title'];
		var found_title = options['found_title'];
		found_title = found_title.replace('%count%', '<input type="text" name="" class="prbar_input prbar_num_count" value="0" />');
		var perform_title = options['perform_title'];
		var step_title = options['step_title'];
		var run_title = options['run_title'];
		var line_text = options['line_text'];
		var end_progress = options['end_progress'];
		var line_success = options['line_success'];
		var end_func = options['success'];
		var c_url = '';
		var r_url = '';
		var r_title = '';
		var count_request = 0;
		var count_item = 0;
		var max_request = 0;
		var now_obj = '';
		
		function create_log(log_text, log_class) {
			
			var date = new Date();
			var now = date.getHours() + ':' + date.getMinutes() + ':' + date.getSeconds();
			$('.prbar_log').after('<div class="prbar_line ' + log_class + '">[' + now + '] ' + log_text + '</div>');
			
		}
		
		function create_window(insert_div) {
			
			var creating_window = '<div class="prbar_shadow js_techwindow"></div>' +
			'<div class="prbar_wrap js_techwindow"><div class="prbar_wrap_ins"><div class="prbar_close"></div><div class="prbar_title"></div><div class="prbar_content">' +
			'<div class="prbar_num">' + found_title + '</div>' +
			'<div class="prbar_control">' +
				'<div class="prbar_input_wrap">' + perform_title + ' <input type="text" name="" class="prbar_input prbar_count" value="50" /></div>' +
				'<div class="prbar_input_wrap">' + step_title + ' <input type="text" name="" class="prbar_input prbar_step_num" value="1" /></div>' +
			'<div class="prbar_submit">' + run_title + '</div><div class="prbar_clear"></div></div>' +
			'<div class="prbar_ind"><div class="prbar_ind_abs"></div><div class="prbar_ind_text"></div></div>' +
			'<div class="prbar_log_wrap"><div class="prbar_log"></div></div>' + 
			'</div></div></div>';
			
			$(insert_div).append(creating_window);
			
		}
		
		$(document).on('click', trigger, function() {
			
			create_window('body');
			
			now_obj = $(this);
			
			var title = $.trim($(this).attr('data-title'));
			$('.prbar_title').html(title);
			$('.prbar_shadow, .prbar_wrap').show();
			$('.prbar_log_wrap .prbar_line').remove();
			$('.prbar_ind').hide();
			$('.prbar_ind_text').html('0%');
			$('.prbar_ind_abs').css({'width':'0px'});
			$('.prbar_num_count').val('0');
			$('.prbar_control, .prbar_num').hide();
			$('.prbar_wrap').addClass('deactive');
			
			c_url = $.trim($(this).attr('data-count-url'));
			
			if (c_url) {
				create_log(start_title, '');
				
				var param = 'action=progressbar';
				$.ajax({
					type: "POST",
					url: c_url,
					dataType: 'json',
					data: param,
					error: function(res, res2, res3) {
						$('.prbar_wrap').removeClass('deactive');
						create_log('error:'+ res2, 'color_red');
						for (key in res) {
							console.log(key + ' = ' + res[key]);
						}						
					},			
					success: function(res)
					{
						$('.prbar_wrap').removeClass('deactive');
						
						if (res['status'] == 'error') {
							create_log(res['status_text'], 'color_red');
						}
						if (res['status'] == 'success') {
							create_log(end_title, 'color_green');
							count_request = parseInt(res['count']);
							r_title = res['status_text'];
							$('.prbar_num_count').val(count_request);
							$('.prbar_num, .prbar_control').show();
							r_url = res['link'];
							$('.prbar_step_num').val('1');
						}						
					}
				});				
				
			} else {
				$('.prbar_wrap').removeClass('deactive');
				create_log('error data-count-url', 'color_red');
			}
			
			return false;
		});
		
		function set_progress(now) {
			
			var one_pers = max_request / 100;
			var now_pers = Math.ceil(now / one_pers);
			$('.prbar_ind').show();
			$('.prbar_ind_text').html(now_pers + '%');
			var wid_ind = Math.ceil(now_pers * $('.prbar_ind').width() / 100);
			$('.prbar_ind_abs').css({'width': wid_ind + 'px'});
			
		}
		
		function get_results_for(ind) {
			if (max_request >= ind) {
				
				var now_line_text = line_text.replace('%now%', ind).replace('%max%', max_request).replace('%text%', r_title);
				create_log(now_line_text, '');
				
				var param = 'limit=' + count_item + '&num_page='+ind;
				$.ajax({
					type: "POST",
					url: r_url,
					dataType: 'json',
					data: param,
					error: function(res, res2, res3) {
						$('.prbar_wrap').removeClass('deactive');
						create_log('error:' + res2, 'color_red');
						for (key in res) {
							console.log(key + ' = ' + res[key]);
						}						
					},			
					success: function(res)
					{
						if (res['status'] == 'error') {
							create_log(res['status_text'], 'color_red');
						}
						if (res['status'] == 'success') {
							var now_line_success = line_success.replace('%now%', ind).replace('%text%', res['status_text']);
							create_log(now_line_success, 'color_green');
						}	
						
						set_progress(ind);
						ind = ind + 1;
						get_results_for(ind);					
					}
				});				
				
			} else {
				$('.prbar_wrap').removeClass('deactive');				
				create_log(end_progress, 'color_green');
				end_func.apply(null, [now_obj]);
			}
		}
		
		$(document).on('click', '.prbar_wrap:not(.deactive) .prbar_submit', function() {
			$('.prbar_wrap').addClass('deactive');
			
			count_item = parseInt($('.prbar_count').val());
			if (count_item < 1) { count_item = 1; }
			count_request = parseInt($.trim($('.prbar_num_count').val()));
			if (count_request < 1) { count_request = 1; }
			max_request = Math.ceil(count_request / count_item);
			var num_page = parseInt($('.prbar_step_num').val());
			if (num_page < 1) { num_page = 1; }
			
			get_results_for(num_page);				
			
			return false;
		});		
		
		$(document).on('click', '.prbar_wrap:not(.deactive) .prbar_close', function() {
			$('.prbar_shadow, .prbar_wrap').remove();
			return false;
		});		
 
        return this;
    };
});