/* vers: 0.3 */
jQuery(function($) {
	
    var default_params = { 
		trigger: '.js_timer',
	};
    $.fn.JTimer = function(params) {
		var options = $.extend({}, default_params, params);

		var trigger = options['trigger'];
		var spans = $(trigger);
		
		function zeroise(num, zero) {
			
			if (zero == 1) {
				if (num < 10) {
					num = '0' + num;
				}
			}
			
			return num;
		}
		
		function set_timer(second, dy, dm, dd, dh, dmi, ds, zero) {
			if (second > 0) {
			
				var years = 0;
				if (second > 31536000) {
					years = parseInt(second / 31536000);
					second -= years * 31536000;
				}
				var month = 0;
				if (second > 2592000) {
					month = parseInt(second / 2592000);
					second -= month * 2592000;
				}
				var days = 0;
				if (second > 86400) {
					days = parseInt(second / 86400);
					second -= days * 86400;
				}
				var hour = 0;
				if (second > 3600) {
					hour = parseInt(second / 3600);
					second -= hour * 3600;
				}
				var min = 0;
				if (second > 60) {
					min = parseInt(second / 60);
					second -= min * 60;
				}
				var y = '<span class="jt_y">' + zeroise(years, zero) + '<span class="jt_count">' + dy + '</span></span> ';
				var m = '<span class="jt_m">' + zeroise(month, zero) + '<span class="jt_count">' + dm + '</span></span> ';
				var d = '<span class="jt_d">' + zeroise(days, zero) + '<span class="jt_count">' + dd + '</span></span> ';
				var h = '<span class="jt_h">' + zeroise(hour, zero) + '<span class="jt_count">' + dh + '</span></span> ';
				var mi = '<span class="jt_min">' + zeroise(min, zero) + '<span class="jt_count">' + dmi + '</span></span> ';
				var s = '<span class="jt_s">' + zeroise(second, zero) + '<span class="jt_count">' + ds + '</span></span> ';
			
				if (years > 0) {
					return y+m+d+h;
				} else if (month > 0) {
					return m+d+h+mi;
				} else if (days > 0) {
					return d+h+mi+s;
				} else if (hour > 0) {
					return h+mi+s;					
				} else {	
					return mi+s;
				}	
				
			} else {
				return '---';
			}  
		} 
	 
		function pn_timer() {
			spans.each(function() {
				var time = parseInt($(this).attr('end-time'));
				var y = $(this).attr('data-y');
				var m = $(this).attr('data-m');
				var d = $(this).attr('data-d');
				var h = $(this).attr('data-h');
				var mi = $(this).attr('data-mi');
				var s = $(this).attr('data-s');
				var zero = parseInt($(this).attr('data-zero'));

				if (!isNaN(time)) {
					var time2 = time-1;
					$(this).attr('end-time',time2);
					if (time2 > 0) {
						$(this).html(set_timer(time2, y, m, d, h, mi, s, zero));				
					} else {
						$(this).addClass('ending').html('---');		
					}
				}
			});
		} 
	 
		if (spans.length > 0) {
			setInterval(pn_timer, 1000);
		}
 
        return this;
    };
	
	$(document).JTimer();
});