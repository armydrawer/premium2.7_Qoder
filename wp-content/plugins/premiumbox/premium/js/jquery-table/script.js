/* version: 0.5 */
jQuery(function($) {
	
    var default_params = {
		trigger: '.admintable table',
		interval: 500,
		min_width: 600,
	};
	
    $.fn.AdaptiveTable = function(params) {
        var options = $.extend({}, default_params, params);
		
		var trigger = options['trigger'];
		var resize_interval = options['interval'];
		var min_width = options['min_width'];
		
		function remove_adaptive(table) {
			
			table.removeClass('has_adaptive').addClass('not_adaptive');
			$('.not_adaptive_content').show();
			$('.has_adaptive_content').hide();
			
			table.find('thead').show();
			
			table.find('tbody tr').removeClass('one_item');
			table.find('tbody td').removeClass('one_item_line');
			table.find('tbody td .one_item_label').hide();
			
		}
		
		function add_adaptive(table) {
			
			table.addClass('has_adaptive').removeClass('not_adaptive');
			$('.not_adaptive_content').hide();
			$('.has_adaptive_content').show();			
			
			table.find('thead').hide();
			
			table.find('tbody tr').addClass('one_item');
			table.find('tbody td').addClass('one_item_line');
			table.find('tbody td .one_item_label').show();
			
			if (table.find('.one_item_label').length < 1) {
				var th = [];
				table.find('thead:first tr th').each(function(index) {
					th[index] = $(this).html();
				});
				
				table.find('tbody tr:not(.noitem)').each(function(tr_index) {
					var tds = $(this).find('td');
					tds.each(function(index) {				
						var title = th[index];
						$(this).wrapInner('<span class="one_item_content">');
						$(this).prepend('<span class="one_item_label">' + title + ':</span>');		
					});
				});				
			}
		}
		
		function set_table() {
			$(trigger).each(function() {
				if (!$(this).hasClass('has_adaptive_wrap')) {
					$(this).wrap('<div class="adaptive_wrap"></div>');
					$(this).addClass('has_adaptive_wrap');
				}	

				var table_height = $(this).height();
				$('.adaptive_wrap').css({'min-height': table_height+'px'});

				var doc_width = parseInt($(document).width());

				if (doc_width >= min_width) {
					remove_adaptive($(this));
				}
				
				var width_div = $(this).parents('.adaptive_wrap').width();
				var width_table = $(this).width();
				if (width_table > width_div) {
					add_adaptive($(this));
				} 
				
				$('.adaptive_wrap').css({'min-height': '100%'});
			});
		}
		
		var resize_ind = '';
		$(window).on('resize', function() {
			clearTimeout(resize_ind);
			resize_ind = setTimeout(set_table, resize_interval);
		});
		
		set_table();		
 
        return this;
    };	
});