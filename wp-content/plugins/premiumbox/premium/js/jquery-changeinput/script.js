/* version: 0.1 */
jQuery(function($){ 

	var default_params = {  
		trigger: '.changeinput',
		changetime: 800,
		success: function(){ }
	};
    $.fn.ChangeInput = function(params) {
        var options = $.extend({}, default_params, params);
		var thet = $(this);
		
		var trigger = options['trigger'];
		var changetime = parseInt(options['changetime']);
		var end_func = options['success'];
		
		var values = [];
		var goed = [];
		var un = '';
		var vl = '';	
		var space_id = 0;
		
		function get_uniq(thet) {
			if (thet.attr('js-change-input-uniq') !== undefined) {
				return thet.attr('js-change-input-uniq');
			} else {
				space_id = space_id + 1;
				thet.attr('js-change-input-uniq', space_id);
				return space_id;
			}
		}
		
		function changed_input(now_obj) {
			end_func.apply(null, [now_obj]);
		}
		
		$(document).on('search', trigger, function() { 
			un = get_uniq($(this));
			vl = $(this).val();
			if (values[un] !== vl) {
				changed_input($(this));
				goed[un] = vl;
			}
			values[un] = vl;
		});	
		
		$(document).on('change', trigger, function() {
			un = get_uniq($(this));
			vl = $(this).val();
			if (values[un] !== vl) {
				changed_input($(this));
				goed[un] = vl;
			}
			values[un] = vl;
		});	
		
		function ChangeTimeOut(id, val, now_obj) {
			if (values[id] == val) {
				if (values[id] !== goed[id]) {
					changed_input(now_obj);
					goed[id] = val;
				}
			}
		}		
		
		$(document).on('keyup', trigger, function(e) {
			un = get_uniq($(this));
			vl = $(this).val();
			values[un] = vl;
			setTimeout(ChangeTimeOut, changetime, un, vl, $(this));
		});		
 
        return this;
    };
});