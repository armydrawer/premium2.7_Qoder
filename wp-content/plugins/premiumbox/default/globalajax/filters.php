<?php
if (!defined('ABSPATH')) { exit(); }

function get_ga_time($place) {
	
	$plugin = get_plugin_class();
	$timer = intval($plugin->get_option('ga', $place . '_time'));
	if ($timer < 1) { $timer = 10; }
	
	return $timer;
}

add_action('premium_action_globalajax_action', 'def_premium_action_globalajax_action');
function def_premium_action_globalajax_action() {
	
	$plugin = get_plugin_class();
 	if (1 == $plugin->get_option('ga', 'ga_admin')) {	
	
		_method('post');
		_json_head();
	
		$log = array();
		$log['status'] = 'success';
		$log['status_code'] = 0;
		$log['status_text'] = '';	
	
		$plugin->up_mode('post');
	
		if (current_user_can('read')) {
			$log = apply_filters('globalajax_admin_data', $log);
		}
	
		echo pn_json_encode($log);
		exit;
	}
	
} 

add_action('premium_siteaction_globalajax_action', 'def_premium_siteaction_globalajax_action');
function def_premium_siteaction_globalajax_action() {
	
	$plugin = get_plugin_class();
	if (1 == $plugin->get_option('ga', 'ga_site')) {
		
		_method('post');
		_json_head();
	
		$log = array();
		$log['status'] = 'success';
		$log['status_code'] = 0;
		$log['status_text'] = '';

		$plugin->up_mode('post');
	
		$log = apply_filters('globalajax_site_data', $log);
	
		echo pn_json_encode($log);	
		exit;
	}
	
}

add_action('admin_footer', 'ga_admin_footer');
function ga_admin_footer() {
	ga_init_js('admin');
}

add_action('wp_footer', 'ga_wp_footer');
function ga_wp_footer() {
	ga_init_js('site');
} 

function ga_init_js($place) {
	$place = trim($place);

	$enable_name = 'ga_site';
	$globalajax_timer = get_ga_time('site') * 1000;
	$g_filter = 'globalajax_site_request';
	$action = get_pn_action('globalajax_action');
	$before = 'globalajax_site_before';
	$result = 'globalajax_site_result';
	
	if ('admin' == $place) {
		$enable_name = 'ga_admin';
		$globalajax_timer = get_ga_time('admin') * 1000;
		$g_filter = 'globalajax_admin_request';
		$action = pn_link('globalajax_action', 'post');
		$before = 'globalajax_admin_before';
		$result = 'globalajax_admin_result';
	} 
	
	$page = pn_strip_input(is_param_get('page'));
	
	$ga_test = 0;
	if (WP_DEBUG and isset($_GET['ga_test'])) {
		$ga_test = 1;
	}	
	
	$plugin = get_plugin_class();
	$enable = intval($plugin->get_option('ga', $enable_name));
	if ($enable) {
		$http_params = apply_filters($g_filter, "'set=1'", $page);
		?>
<script type="text/javascript">
jQuery(function($) {
	
	var auto_load = 1;
	
	function globalajax_timer() {
		if (auto_load == 1) {
			auto_load = 0;
			
			var param = <?php echo $http_params; ?>;
			<?php if (1 == $ga_test) { ?>
				console.log(param);
			<?php } ?>		
			$('.globalajax_ind').addClass('active');
			$.ajax({
				type: "POST",
				url: "<?php echo $action;?>",
				dataType: 'json',
				data: param,
				error: function(res, res2, res3) {
					<?php do_action('pn_js_error_response', 'ajax'); ?>
				},
				beforeSend: function(res, res2, res3) {
					<?php do_action($before); ?>
				},			
				success: function(res)
				{		
					<?php if (1 == $ga_test) { ?>
						console.log(res);
					<?php } ?>			
					if (res['status'] == 'success') { 
						auto_load = 1;						
						<?php do_action($result); ?>
					}	
					$('.globalajax_ind').removeClass('active');
				}
			});
		}
	}	
	
	setInterval(globalajax_timer, <?php echo $globalajax_timer; ?>);
	<?php if (1 == $ga_test) { ?>
	globalajax_timer();
	<?php } ?>	
});	
</script>		
		<?php
	} 
}