/*
version: 0.1
*/

jQuery(function($) {	

    var defaults = { 
		key: '',
		value: '',
		days: '7',
		domain: window.location.origin + '/',
	};
	
    $.fn.PHPCookie = function(method, params) {
        var options = $.extend({}, defaults, options, params);
        var now_obj = $(this);
 
		var c_key = $.trim(options['key']);
		var c_value = options['value'];
		var days = $.trim(options['days']);
		var domain = $.trim(options['domain']);
		
		if (method == 'set') {

			$.post(domain + "request-setc.html", {key: c_key, value: c_value, days: days}, function(theResponse) {
				
			});
							
		}
 
        return this;
    };
	
});	
