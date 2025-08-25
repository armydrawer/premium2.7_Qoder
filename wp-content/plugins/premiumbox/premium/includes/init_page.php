<?php 
if (!defined('ABSPATH')) { exit(); }

if (!function_exists('set_premium_pointers')) {
	add_action('plugins_loaded', 'set_premium_pointers', 5);
	function set_premium_pointers() { 
		global $pn_query;
		
		if (!is_array($pn_query)) { $pn_query = array(); }
		
		$dir = get_request_dir();
		$page = ltrim(get_request_query(), '/');
		$matches = '';
		$check_hash_cron = 1;
		if (function_exists('check_hash_cron')) {
			$check_hash_cron = check_hash_cron();
		}
		if ($page and preg_match("/^cron-([a-zA-Z0-9\_]+).html$/", $page, $matches) and $check_hash_cron or 'cron.html' == $page and $check_hash_cron) {
			$action = pn_maxf(is_isset($matches, 1), 250);
			if (!$action) { $action = 'all'; }
			$pn_query['is_cron'] = $action;
		} elseif ($page and preg_match("/^merchant-([a-zA-Z0-9\_]+).html$/", $page, $matches)) {
			$action = pn_maxf(is_isset($matches, 1), 250);
			$pn_query['is_merch'] = $action;
		} elseif ($dir and 'api' == $dir) {
			if (!$page) { $page = 'index.php'; }
			$pn_query['is_api'] = $page;
		} elseif ($page and 'premium_script.js' == $page) {	
			$pn_query['is_script'] = 1;
		} elseif ($page and 'premium_quicktags.js' == $page) {	
			$pn_query['is_quicktags'] = 1;			
		} elseif ($page and preg_match("/^request-([a-zA-Z0-9\_]+).(php|txt|html|xml|js|json)$/", $page, $matches)) {	
			$action = pn_maxf(is_isset($matches, 1), 250);
			$pn_query['is_request'] = $action;
		} elseif ($page and preg_match("/^premium_admin_action-([a-zA-Z0-9\_]+).html$/", $page, $matches)) {
			$action = pn_maxf(is_isset($matches, 1), 250);		
			$pn_query['is_adminaction'] = $action;	
		} elseif ($page and preg_match("/^premium_site_action-([a-zA-Z0-9\_]+).html$/", $page, $matches)) {
			$action = pn_maxf(is_isset($matches, 1), 250);		
			$pn_query['is_action'] = $action;
		} elseif (is_admin()) {
			$pn_query['is_admin'] = 1;
		} else {
			$pn_query['is_site'] = 1;
		}
	}
}

if (!function_exists('_is')) {
	function _is($is_query) {
		global $pn_query;	
		
		$is_query = pn_strip_symbols($is_query, '_');
		if (isset($pn_query[$is_query])) {
			return $pn_query[$is_query];
		}	
		
		return 0;
	}
}

if (!function_exists('set_premium_page')) {
	add_action('init', 'set_premium_page', 200);
	function set_premium_page() {
		
		if (_is('is_cron')) {
		
			header('Content-Type: text/html; charset=' . get_charset());
			xframe_sameorigin();
			xrobots_noindex();
			status_header(200);
			
			$action = _is('is_cron');
			if ('all' == $action) {
				pn_cron_init('file');
			} else {
				go_pn_cron_func($action, 'file', 1);
			}
			
			_e('Done', 'premium');
			exit;
			
		} elseif (_is('is_merch')) {
			
			_pn_debug();
			header('Content-Type: text/html; charset=' . get_charset());
			xframe_sameorigin();
			xrobots_noindex();
			status_header(501);	
			
			do_action('premium_merchants');	
		
			$action	= _is('is_merch');
		
			if ($action and has_action('premium_merchant_' . $action)) {
				status_header(200);
				do_action('premium_merchant_' . $action);
			}
			exit;

		} elseif (_is('is_action')) {
		
			_pn_debug();
			header('Content-Type: text/html; charset=' . get_charset());
			status_header(501);		
			nocache_headers();	
			xframe_sameorigin();
			xrobots_noindex();
			
			do_action('premium_post', 'action');	
								
			$action = _is('is_action');
			if ($action and has_action('premium_siteaction_'. $action)) {
				status_header(200);
				do_action('premium_siteaction_'. $action);
			}

			exit;			
		
		} elseif (_is('is_adminaction')) {	
		
			_pn_debug();
			header('Content-Type: text/html; charset=' . get_charset());
			status_header(501);
			nocache_headers();
			xframe_sameorigin();
			xrobots_noindex();			
				
			do_action('premium_post', 'post');			

			$action = _is('is_adminaction');
			if ($action and has_action('premium_action_' . $action)) {
				status_header(200);
				do_action('premium_action_' . $action);
			}

			exit;

		} elseif (_is('is_request')) {
			
			_pn_debug();
			header('Content-Type: text/html; charset=' . get_charset());
			xframe_sameorigin();
			
			$action = _is('is_request');
			if ($action and has_action('premium_request_' . $action)) {
				status_header(200);
				do_action('premium_request_' . $action);
			}	
			exit;

		} elseif (_is('is_script')) {

			_pn_debug();
			header('Content-Type: application/x-javascript; charset=' . get_charset());
			xframe_sameorigin();
			xrobots_noindex();
			do_action('premium_post', 'js');

			if (function_exists('set_premium_default_js')) {
				set_premium_default_js('site');
			}
			
			do_action('premium_js');
			exit;

		} elseif (_is('is_quicktags')) {

			_pn_debug();
			header('Content-Type: application/x-javascript; charset=' . get_charset());
			xframe_sameorigin();
			xrobots_noindex();
			
			if (current_user_can('read')) {
				$place = pn_maxf(pn_strip_input(is_param_get('place')), 500);
				if(has_filter('pn_adminpage_quicktags_' . $place) or has_filter('pn_adminpage_quicktags')){
					do_action('pn_adminpage_quicktags_' . $place);
					do_action('pn_adminpage_quicktags');
				}			
			}
			
			exit;			
		
		} elseif (_is('is_api')) {
		
			_pn_debug();
			header('Content-Type: application/json; charset=' . get_charset());
			xframe_alloworigin();
			status_header(200);
		
			$api_arr = explode('/', _is('is_api'));
			$module = pn_strip_input(is_isset($api_arr, 1));
			$version = pn_strip_input(is_isset($api_arr, 2));
			$endpoint = pn_strip_input(is_isset($api_arr, 3));
		
			do_action('pn_api_init');
			do_action('pn_api_page', $module, $version, $endpoint);
				
			$json = array(
				'error' => 1,
				'error_text' => 'Api disabled',
			);			
			echo pn_json_encode($json);
			exit;		
		
		}
		
	}
}	
 
if (!function_exists('set_premium_default_js')) {
	function set_premium_default_js($place = '') { 
		$place = trim($place); if (!$place) { $place = 'site'; }
?>
jQuery(function($) {

 	$('.ajax_post_form').ajaxForm({
		dataType: 'json',
		beforeSubmit: function(a, f, o) {
			f.addClass('thisactive');
			$('.thisactive input[type=submit], .thisactive input[type=button]').attr('disabled',true);
			$('.thisactive').find('.ajax_submit_ind').show();
		},
		error: function(res, res2, res3) {
			<?php do_action('pn_js_error_response', 'form', $place); ?>
		},
		success: function(res) {
					
			if (res['status'] == 'error') {
				if (res['status_text']) {
					$('.thisactive .resultgo').html('<div class="resultfalse"><div class="resultclose"></div>' + res['status_text'] + '</div>');
				}
			}	
			
			if (res['status'] == 'success') {
				if (res['status_text']) {
					$('.thisactive .resultgo').html('<div class="resulttrue"><div class="resultclose"></div>' + res['status_text'] + '</div>');
				}
			}
			
			if (res['clear']) {
				$('.thisactive input[type=text]:not(.notclear), .thisactive input[type=password]:not(.notclear), .thisactive textarea:not(.notclear)').val('');
			}

			if (res['show_hidden']) {
				$('.thisactive .hidden_line').show();
			}			
					
			if (res['url']) {
				window.location.href = res['url']; 
			}
						
			<?php do_action('ajax_post_form_result', $place); ?>
					
			$('.thisactive input[type=submit], .thisactive input[type=button]').attr('disabled', false);
			$('.thisactive').find('.ajax_submit_ind').hide();
			$('.thisactive').removeClass('thisactive');	
			
		}
	});
	
	if (self != top && window.parent.frames.length > 0) {
		$('.not_frame').remove();
	}  
	
	<?php do_action('premium_js_inside', $place); ?>
});		
<?php
	} 
} 