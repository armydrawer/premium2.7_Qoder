/* version: 0.2 */
jQuery(function($) {
	
    var default_params = {};
    $.fn.JcheckboxInit = function(params) {
        var options = $.extend({}, default_params, params);
		
		$('label input[type=checkbox]:not(.jcheckbox, .not_jcheckbox)').each(function() {
			var this_item = $(this);
			this_item.addClass('jcheckbox');
			this_item.parents('label').wrap('<div class="checkbox">');
			if ($(this).prop('checked')) {
				this_item.parents('.checkbox').addClass('checked');
			}
			this_item.hide();
		});	
 
        return this;
    };
	
	
    var default_params2 = {};
    $.fn.Jcheckbox = function(params) {
        var options = $.extend({}, default_params2, params);
		
		$(document).on('click', '.checkbox label a', function(event) {
			event.stopPropagation();
		});	
		
		$(document).on('click', '.checkbox label', function(event) {
			event.stopPropagation();
		});		
		
		$(document).on('change', '.checkbox input', function() {
			var parent_div = $(this).parents('.checkbox');
			if (parent_div.hasClass('checked')) {
				parent_div.removeClass('checked');
			} else {
				parent_div.addClass('checked');
			}
		});			
		
		$(document).on('click', '.checkbox', function() {
			if ($(this).find('input').prop('checked')) {
				$(this).find('input').prop('checked', false);
			} else {
				$(this).find('input').prop('checked', true);
			}
			$(this).find('input').trigger("change");
		});			
 
        return this;
    };	
	
});