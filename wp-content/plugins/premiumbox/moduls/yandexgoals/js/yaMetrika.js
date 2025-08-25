/* version: 0.1 */
jQuery(function($) { 

	var default_params = {  
		id: 0,
		test: 0,
		goal_1: '',
		goal_2: '',
		goal_3: '',
		goal_4: '',
		goal_5: '',
		goal_6: '',
		goal_7: '',
	};
	
    $.fn.PremiumYaMetrika = function(params) {
        var options = $.extend({}, default_params, params);
		
		var metrika_id = parseInt(options.id);
		var test = parseInt(options.test);
		
		var goal_1 = $.trim(options.goal_1);
		var goal_2 = $.trim(options.goal_2);
		var goal_3 = $.trim(options.goal_3);
		var goal_4 = $.trim(options.goal_4);
		var goal_5 = $.trim(options.goal_5);
		var goal_6 = $.trim(options.goal_6);
		var goal_7 = $.trim(options.goal_7);
		
		function reachGoal(goal) {
			
			if (goal.length > 0) {
				ym(metrika_id, 'reachGoal', goal);
			}
			
			if (test == 1) {
				console.log('metrika id: ' + metrika_id +', goal:' + goal);
			}
			
		}
		
		$(document).on('click', '.js_exchange_link:not(.active)', function() {
			reachGoal(goal_1);
		});
		
		$(document).on('click', '.ajax_post_bids input[type=submit]', function() {
			reachGoal(goal_4);
		});
		
		var calc_m = 0;
		function go_calc_m() {
			if (calc_m == 0) {
				calc_m = 1;
				
				reachGoal(goal_2);
			}
		}
		
		$(document).on('keyup', '.js_sum1, .js_sum2, .js_sum1c, .js_sum2c', function() {
			go_calc_m();
		});
		$(document).on('change', '.js_sum1, .js_sum2, .js_sum1c, .js_sum2c', function() {
			go_calc_m();
		});	

		var calc_d = 0;
		function go_exchange_data() {
			if (calc_d == 0) {
				calc_d = 1;
	
				reachGoal(goal_3);
			}
		}

		$(document).on('change', '.cache_data:not(.js_sum1)', function() {
			go_exchange_data();
		});
		$(document).on('keyup', '.cache_data:not(.js_sum1)', function() {
			go_exchange_data();
		});		
		
		$(document).on('click', '#check_rule_step_input', function() {
			reachGoal(goal_5);
		});
		
		$(document).on('click', '.cancel_paybutton', function() {
			reachGoal(goal_6);
		});		
		
		$(document).on('click', '.success_paybutton', function() {
			reachGoal(goal_7);
		});		
 
        return this;
    };
});