<?php
if (!defined('ABSPATH')) { exit(); }

/*
title: [en_US:]Captcha for website[:en_US][ru_RU:]Капча для сайта[:ru_RU]
description: [en_US:]Captcha for website[:en_US][ru_RU:]Капча для сайта[:ru_RU]
version: 2.7.0
category: [en_US:]Security[:en_US][ru_RU:]Безопасность[:ru_RU]
cat: secur
*/

$path = get_extension_file(__FILE__);
$name = get_extension_name($path);

add_action('pn_plugin_activate', 'all_moduls_active_captcha');
add_action('all_moduls_active_' . $name, 'all_moduls_active_captcha');
function all_moduls_active_captcha() {
	global $wpdb;	

	$table_name = $wpdb->prefix . "captch_site";
	$sql = "CREATE TABLE IF NOT EXISTS $table_name(
		`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
		`createdate` varchar(15) NOT NULL default '0',
		`num1` varchar(10) NOT NULL default '0',
		`num1h` varchar(10) NOT NULL default '0',
		`num2` varchar(10) NOT NULL default '0',
		`num2h` varchar(10) NOT NULL default '0',
		`symbol` varchar(10) NOT NULL default '0',
		`value` varchar(10) NOT NULL default '0',
		`sess_hash` varchar(150) NOT NULL,
		PRIMARY KEY (`id`),
		INDEX (`createdate`),
		INDEX (`sess_hash`)
	) ENGINE=InnoDB  DEFAULT CHARSET={$wpdb->charset} AUTO_INCREMENT=1;"; 
	$wpdb->query($sql);	

}

function captcha_generate($word, $title, $size = 50) {
	$plugin = get_plugin_class();

	$word = pn_strip_input($word);
	$title = pn_strip_input($title);
	$size = intval($size);
	if ($size < 1) { $size = 50; }

	$font = $plugin->plugin_dir . '/moduls/captcha/fonts/font.ttf';

	$url = $plugin->upload_url . 'captcha/';
	$dir = $plugin->upload_dir . '/captcha/';	
	if (!realpath($dir)) {
		@mkdir($dir, 0777);
	}
		
	$image_dir = $dir . $title . '.png';
	$image_url = $url . $title . '.png';
	if (file_exists($image_dir)) {
		return $image_url;
	}	
		
	$bgs_dir = $plugin->plugin_dir . '/moduls/captcha/bg/';
	$bgs_arr = glob("$bgs_dir*.png");
	if (is_array($bgs_arr)) { 
		shuffle($bgs_arr);
	}
	$bg_to = trim(is_isset($bgs_arr, 0));
	$bg_to = apply_filters('captcha_bg', $bg_to, 'captcha');
		
	if ($im = imagecreatetruecolor($size, $size)) {
			
		$bg_color = apply_filters('pn_sc_bgcolor', array('255', '255', '255'));
		$bg_color = imagecolorallocate($im, $bg_color[0], $bg_color[1], $bg_color[2]);
			
		$f_color = apply_filters('pn_sc_color', array('0', '0', '0'));
		$f_color = imagecolorallocate($im, $f_color[0], $f_color[1], $f_color[2]);
			
		imagefill($im, 0, 0, $bg_color);
			
		if ($bg_to) {
			$bg_im = imagecreatefrompng($bg_to);
			imagecopy($im, $bg_im, 0, 0, 0, 0, $size, $size);
		} 

		imagettftext($im, 30, 0, mt_rand(0,30), mt_rand(30,40) , $f_color, $font, $word);

		imagepng($im, $image_dir);
		imagedestroy($im);	
		return $image_url;
		
	}

	return get_premium_url() . 'images/gd_error.png';
}

function captcha_del_img($sess_hash = '') {	
	global $wpdb;
	
	$plugin = get_plugin_class();
	if (!$plugin->is_up_mode()) {
			
		$del_ims = array();
 		$time = current_time('timestamp') - (HOUR_IN_SECONDS * 4);
		$items = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "captch_site WHERE createdate < $time OR sess_hash = '$sess_hash'");
		foreach ($items as $item) {
			$del_ims[] = $item->num1h;
			$del_ims[] = $item->num2h;
		}
			
		$del_ims = array_unique($del_ims);
			
		$wpdb->query("DELETE FROM ". $wpdb->prefix ."captch_site WHERE createdate < $time OR sess_hash = '$sess_hash'");
			
		$dir = $plugin->upload_dir . '/captcha/';
			
		foreach ($del_ims as $im_title) {
			$file = $dir . $im_title . '.png';
			if (is_file($file)) {
				@unlink($file);
			}
		} 
			
	}
}

add_filter('list_cron_func', 'captcha_list_cron_func');
function captcha_list_cron_func($filters) {	

	$filters['captcha_del_img'] = array(
		'title' => __('Removing captcha sessions', 'pn'),
		'site' => '10min',
		'file' => 'none',
	);
	
	return $filters;
}

add_action('go_exchange_calc_js_response', 'ajax_post_form_result_captcha');
add_action('ajax_post_form_result', 'ajax_post_form_result_captcha');
function ajax_post_form_result_captcha($place = '') {
	
	$place = trim($place);
	if (!$place) { $place = 'site'; }
	if ('site' == $place) {
?>
		if (res['ncapt1']) {
			$('.captcha1').attr('src', res['ncapt1']);
		}
		
		if (res['ncapt2']) {
			$('.captcha2').attr('src', res['ncapt2']);
		}
		
		if (res['nsymb']) {
			$('.captcha_sym').html(res['nsymb']);
		}	
		
		$('.captcha_value').val('');
<?php	
	}
} 

add_action('premium_js', 'premium_js_captcha');
function premium_js_captcha() {
?>
jQuery(function($) { 

	$(document).on('click', '.captcha_reload', function() {
		var thet = $(this);
		thet.addClass('act');
		var param ='have=reload';
		$.ajax({
			type: "POST",
			url: "<?php echo get_pn_action('captcha_reload'); ?>",
			dataType: 'json',
			data: param,
			error: function(res, res2, res3) {
				<?php do_action('pn_js_error_response', 'ajax'); ?>
			},		
			success: function(res)
			{
				if (res['ncapt1']) {
					$('.captcha1').attr('src', res['ncapt1']);
				}
				
				if (res['ncapt2']) {
					$('.captcha2').attr('src', res['ncapt2']);
				}
				
				if (res['nsymb']) {
					$('.captcha_sym').html(res['nsymb']);
				}
				
				$('.captcha_value').val('');
				thet.removeClass('act');
			}
		});
			
		return false;
	});
	
});	
<?php	
} 

function captcha_reload($replace = 0) {
	global $wpdb;
		
	$data = '';
		
	$replace = intval($replace);
	$sess_hash = get_session_id();
	$plugin = get_plugin_class();
	$site_captcha = intval($plugin->get_option('site_captcha'));
		
	if ($replace) {
		captcha_del_img($sess_hash);
	} else {
		$data = $wpdb->get_row("SELECT * FROM ". $wpdb->prefix ."captch_site WHERE sess_hash = '$sess_hash'");
	}
		
	if (!isset($data->id)) {
		$array = array();
		$array['createdate'] = current_time('timestamp');
		$array['sess_hash'] = $sess_hash;
		$array['num1'] = $num1 = mt_rand(5, 8);
		$array['num2'] = $num2 = mt_rand(1, 9);
		if (1 == $site_captcha) {
			$array['symbol'] = $symbol = mt_rand(0, 2);
		} else {
			$array['symbol'] = $symbol = 0;
		}	
		if (1 == $symbol) {
			if ($num1 < $num2) {
				$array['num1'] = $num2;
				$array['num2'] = $num1;
			} elseif ($num1 == $num2) {
				$array['num1'] = $num1 + mt_rand(1, 3);
				$array['num2'] = $num2;						
			}
		}		
		$array['num1h'] = wp_generate_password(8, false, false);
		$array['num2h'] = wp_generate_password(8, false, false);
		$value = 0;
		if (1 == $symbol) {
			$value = $array['num1'] - $array['num2'];
		} elseif (2 == $symbol) {
			$value = $array['num2'] * $array['num1'];
		} else {
			$value = $array['num2'] + $array['num1'];
		}
		$array['value'] = $value;
		$wpdb->insert($wpdb->prefix ."captch_site", $array);
		$array['id'] = $wpdb->insert_id;
		
		return (object)$array;
	}	
		
	return $data;	
}

add_action('template_redirect', 'captcha_init', 20);
function captcha_init() {
	global $pn_captcha;
	
	$pn_captcha = captcha_reload(0); 			
}	

add_action('premium_siteaction_captcha_reload', 'def_premium_siteaction_captcha_reload');
function def_premium_siteaction_captcha_reload() {	

	$plugin = get_plugin_class();

	_method('post');
	_json_head();
		
	$log = array();
	$log['status'] = 'success';
	$log['status_text'] = '';
	$log['status_code'] = 0;
		
	$plugin->up_mode('post');
		
	$data = captcha_reload(1);
	$cd = create_captcha($data);
	$log = array_merge($log, $cd);	

	echo pn_json_encode($log);
	exit;
}

function create_captcha($data) {
		
	$array = array();	
		
	if (isset($data->id)) {
		$sumbols = array('+', '-', 'x');
		if (isset($data->id)) {
			$img1 = captcha_generate($data->num1, $data->num1h);
			$img2 = captcha_generate($data->num2, $data->num2h);
			$symb = is_isset($sumbols, $data->symbol);		
		} else {
			$img1 = captcha_generate(0, 0);
			$img2 = captcha_generate(0, 0);
			$symb = '+';		
		}
			
		$array['ncapt1'] = $img1;
		$array['ncapt2'] = $img2;
		$array['nsymb'] = $symb;			
	} 
		
	return $array;	
}

add_filter('get_form_filelds', 'get_form_filelds_captcha', 990, 2);
function get_form_filelds_captcha($items, $name) {
	
	$plugin = get_plugin_class();	
	if (is_captcha($name)) {
		$items['captcha'] = array(
			'type' => 'captcha',
		);
	}
	
	return $items;
}

add_filter('form_field_line', 'form_field_line_captcha', 10, 3);
function form_field_line_captcha($line, $filter, $data) {
	global $pn_captcha;	
		
	$type = trim(is_isset($data, 'type'));
	if ('captcha' == $type) {
		$cd = create_captcha($pn_captcha);
		$line = get_captcha_temp($cd);	
	}
		
	return $line;
}

add_action('comment_form', 'comment_form_captcha', 990);
function comment_form_captcha() {
	global $pn_captcha;	
	
	$plugin = get_plugin_class();
	if (is_captcha('commentform')) {
		$cd = create_captcha($pn_captcha);
		$line = get_captcha_temp($cd);
		
		echo $line;
	}	
}

add_filter('exchange_step1', 'exchange_form_captcha', 990);
function exchange_form_captcha($line) { 
	global $pn_captcha;
	
	if (is_captcha('exchangeform')) {
	
		if (!isset($pn_captcha->id)) {
			$pn_captcha = captcha_reload(0);
		}
	
		$cd = create_captcha($pn_captcha);
		$line .= get_captcha_temp($cd);
		
	}
	
	return $line;	
}

function get_captcha_temp($cd) {
	
	$temp = '
	<div class="captcha_div">
		<div class="captcha_title">
			'. __('Type your answer','pn') .'
		</div>
		<div class="captcha_body">
			<div class="captcha_divimg">
				<img src="' . is_isset($cd, 'ncapt1') . '" class="captcha1" alt="" />
			</div>
			<div class="captcha_divznak">
				<span class="captcha_sym">' . is_isset($cd, 'nsymb') . '</span>
			</div>	
			<div class="captcha_divimg">
				<img src="' . is_isset($cd, 'ncapt2') . '" class="captcha2" alt="" />
			</div>
			<div class="captcha_divznak">
				=
			</div>
			<input type="text" class="captcha_divpole captcha_value" name="number" maxlength="4" autocomplete="off" value="" />
			<a href="#" class="captcha_reload" title="' . __('replace task', 'pn') . '"></a>
				<div class="clear"></div>
		</div>
	</div>		
	';
		
	$temp = apply_filters('get_captcha_temp', $temp, is_isset($cd, 'ncapt1'), is_isset($cd, 'ncapt2'), is_isset($cd, 'nsymb'));
	
	return $temp;
}

add_filter('before_ajax_form', 'before_ajax_form_captcha', 1010, 2);
function before_ajax_form_captcha($logs, $name) {
	global $wpdb;
	
	if (is_captcha($name)) {
			
		$number = pn_strip_input(is_param_post('number'));	
		$data = captcha_reload(0);
			
		$error = 0;
		if (!isset($data->id) or $data->value != $number) {		
			$error = 1;				
		}
			
		$new_data = captcha_reload(1);
		$cd = create_captcha($new_data);
		$logs = array_merge($logs, $cd);						
			
		if ($error) {
			$logs['status'] = 'error';
			$logs['status_code'] = '30';
			$logs['status_text'] = __('Error! Incorrect verification number entered', 'pn');				
		} 	
	}
		
	return $logs;
}

add_filter('all_settings_option', 'captcha_all_settings_option');
function captcha_all_settings_option($options) {
	
	$plugin = get_plugin_class();
			
	$options[] = array(
		'view' => 'line',
	);	
	$options['site_captcha'] = array(
		'view' => 'select',
		'title' => __('Website captcha', 'pn'),
		'options' => array('0' => __('only numbers addition', 'pn'), '1' => __('all mathematical actions with numbers', 'pn')),
		'default' => $plugin->get_option('site_captcha'),
		'name' => 'site_captcha',
		'work' => 'input',
	);	
			
	return $options;
}

add_action('all_settings_option_post', 'captcha_all_settings_option_post');
function captcha_all_settings_option_post($data) {
	
	$plugin = get_plugin_class();
	$site_captcha = intval($data['site_captcha']);
	$plugin->update_option('site_captcha', '', $site_captcha);
	
}