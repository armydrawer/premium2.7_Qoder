<?php
if (!defined('ABSPATH')) { exit(); }

/*
title: [en_US:]Captcha for website (sеlect image)[:en_US][ru_RU:]Капча для сайта (выбор картинки)[:ru_RU]
description: [en_US:]Captcha for website with a correct image selection[:en_US][ru_RU:]Капча для сайта с выбором верной картинки[:ru_RU]
version: 2.7.0
category: [en_US:]Security[:en_US][ru_RU:]Безопасность[:ru_RU]
cat: secur
*/

$path = get_extension_file(__FILE__);
$name = get_extension_name($path);

add_action('pn_plugin_activate', 'bd_all_moduls_active_sci');
add_action('all_moduls_active_' . $name, 'bd_all_moduls_active_sci');
function bd_all_moduls_active_sci() {
	global $wpdb;	
		
	$table_name = $wpdb->prefix . "sitecaptcha";
	$sql = "CREATE TABLE IF NOT EXISTS $table_name(
		`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT ,
		`createdate` varchar(25) NOT NULL,
		`sess_hash` varchar(150) NOT NULL,
		`symbols` longtext NOT NULL,
		`hsymbols` longtext NOT NULL,
		`answer` varchar(50) NOT NULL,
		`value` varchar(150) NOT NULL,
		PRIMARY KEY (`id`),
		INDEX (`createdate`),
		INDEX (`sess_hash`)
	) ENGINE=InnoDB  DEFAULT CHARSET={$wpdb->charset} AUTO_INCREMENT=1;";
	$wpdb->query($sql);

}

function sitecaptcha_generate($word, $title, $size = 50) {
	
	$plugin = get_plugin_class();

	$word = pn_strip_input($word);
	$title = pn_strip_input($title);
	$size = intval($size);
	if ($size < 1) { $size = 50; }

	$font = $plugin->plugin_dir . '/moduls/sitecaptcha_img/fonts/font.ttf';

	$url = $plugin->upload_url . 'captcha/';
	$dir = $plugin->upload_dir . '/captcha/';	
	if (!realpath($dir)) {
		@mkdir($dir, 0777);
	}
		
	$image_dir = $dir . $title . '.png';
	$image_url = $url . $title . '.png';
	if (is_file($image_dir)) {
		return $image_url;
	}	
		
	$bgs_dir = $plugin->plugin_dir . '/moduls/sitecaptcha_img/bg/';
	$bgs_arr = glob("{$bgs_dir}*.png");
	if (is_array($bgs_arr)) { 
		shuffle($bgs_arr);
	}
	$bg_to = trim(is_isset($bgs_arr, 0));
	$bg_to = apply_filters('captcha_bg', $bg_to, 'sitecaptcha');
		
	if ($im = imagecreatetruecolor($size, $size)) {
		$bg_color = imagecolorallocate($im, 255, 255, 255);
		$f_color = imagecolorallocate($im, 0, 0, 0);
		imagefill($im, 0, 0, $bg_color);
			
		if ($bg_to) {
			$bg_im = imagecreatefrompng($bg_to);
			imagecopy($im, $bg_im, 0, 0, 0, 0, $size, $size);
		} 

		imagettftext($im, 25, 0, mt_rand(0,30), mt_rand(30,40) , $f_color, $font, $word);

		imagepng($im, $image_dir);
		imagedestroy($im);
			
		return $image_url;
	}

	return get_premium_url() . 'images/gd_error.png';
}

function sitecaptcha_del_img($sess_hash = '') {	
	global $wpdb;

	$plugin = get_plugin_class();
	if (!$plugin->is_up_mode()) {
			
		$del_ims = array();
 		$time = current_time('timestamp') - (HOUR_IN_SECONDS * 4);
		$items = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "sitecaptcha WHERE createdate < $time OR sess_hash = '$sess_hash'");
		foreach ($items as $item) {
			$hsymbols = pn_json_decode($item->hsymbols);
			if (is_array($hsymbols)) {
				foreach ($hsymbols as $hsymbol) {
					$del_ims[] = $item->id . '_' . $hsymbol;
				}
			}
		}
			
		$del_ims = array_unique($del_ims);
			
		$wpdb->query("DELETE FROM " . $wpdb->prefix . "sitecaptcha WHERE createdate < $time OR sess_hash = '$sess_hash'");
			
		$dir = $plugin->upload_dir . '/captcha/';
			
		foreach ($del_ims as $im_title) {
			$file = $dir . $im_title . '.png';
			if (is_file($file)) {
				@unlink($file);
			}
		} 
			
	}
}

add_filter('list_cron_func', 'sitecaptcha_list_cron_func');
function sitecaptcha_list_cron_func($filters) {	

	$filters['sitecaptcha_del_img'] = array(
		'title' => __('Removing captcha sessions', 'pn'),
		'site' => '10min',
		'file' => 'none',
	);
	
	return $filters;
}

function sitecaptcha_reload($replace = 0) {
	global $wpdb;
		
	$data = '';
		
	$replace = intval($replace);
	$sess_hash = get_session_id();
		
	if ($replace) {
		sitecaptcha_del_img($sess_hash);
	} else {
		$data = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "sitecaptcha WHERE sess_hash = '$sess_hash'");
	}
	$count = apply_filters('count_image_from_sitecaptcha', 6);
	$max = $count - 1;
	if (!isset($data->id)) {
		$array = array();
		$array['createdate'] = current_time('timestamp');
		$array['sess_hash'] = $sess_hash;
		$symbols = array();
		$hsymbols = array();
		$r = 0;
		while ($r++ < $count) {
			$symbols[] = mt_rand(1, 5);
			$hsymbols[] = get_random_password(3, true, true);
		}
		$array['symbols'] = pn_json_encode($symbols);
		$array['hsymbols'] = pn_json_encode($hsymbols);
		$answer = $symbols[mt_rand(0, $max)];
		$array['answer'] = $answer;
		$value = '';
		foreach ($symbols as $symbol_k => $symbol) {
			if ($symbol == $answer) {
				$value .= $hsymbols[$symbol_k];
			}
		}
		$array['value'] = $value;
		$wpdb->insert($wpdb->prefix . "sitecaptcha", $array);
		$array['id'] = $wpdb->insert_id;
		return (object)$array;
	}	
		
	return $data;	
}

add_action('template_redirect', 'sitecaptcha_init', 20);
function sitecaptcha_init() {
	global $pn_sitecaptcha;
	
	$pn_sitecaptcha = sitecaptcha_reload(0); 			
}

add_action('go_exchange_calc_js_response', 'ajax_post_form_result_captcha_sci');
add_action('ajax_post_form_result', 'ajax_post_form_result_captcha_sci');
function ajax_post_form_result_captcha_sci($place = '') {
	
	$place = trim($place);
	if (!$place) { $place = 'site'; }
	if ('site' == $place) {
?>
		if (res['sk_answer']) {
			$('.captcha_sci_title').html(res['sk_answer']);
		}
		
		if (res['sk_images']) {
			$('.captcha_sci_img').remove();
			$.each(res['sk_images'], function(index, value) {	
				$('.captcha_sci_hidden').before(value);
			});
			$('.captcha_sci_hidden').val('0');
		}		
<?php	
	}
} 

add_action('premium_js', 'premium_js_captcha_sci');
function premium_js_captcha_sci() {
?>
jQuery(function($) { 

	$(document).on('click', '.captcha_sci_reload', function() {
		var thet = $(this);
		thet.addClass('act');
		var param ='have=reload';
		$.ajax({
			type: "POST",
			url: "<?php echo get_pn_action('sitecaptcha_reload'); ?>",
			dataType: 'json',
			data: param,
			error: function(res, res2, res3) {
				<?php do_action('pn_js_error_response', 'ajax'); ?>
			},		
			success: function(res)
			{
				if (res['sk_answer']) {
					$('.captcha_sci_title').html(res['sk_answer']);
				}
				
				if (res['sk_images']) {
					$('.captcha_sci_img').remove();
					$.each(res['sk_images'], function(index, value) {	
						$('.captcha_sci_hidden').before(value);
					});
					$('.captcha_sci_hidden').val('0');
				}	
				
				thet.removeClass('act');
			}
		});
			
		return false;
	});
		
	$(document).on('click', '.captcha_sci_img', function() {
		var thet = $(this);
		thet.toggleClass('active');
		
		var hashed = '';
		var par = thet.parents('.captcha_sci_body');
		par.find('.captcha_sci_img').each(function() {
			if ($(this).hasClass('active')) {
				hashed += $(this).find('img').attr('data-id')
			}
		});
			
		$('.captcha_sci_hidden').val(hashed);
		
		return false;
	});	
	
});	
<?php	
}

add_action('premium_siteaction_sitecaptcha_reload', 'def_premium_siteaction_sitecaptcha_reload');
function def_premium_siteaction_sitecaptcha_reload() {
	
	$plugin = get_plugin_class();

	_method('post');
	_json_head();
		
	$log = array();
	$log['status'] = 'success';
	$log['status_text'] = '';
	$log['status_code'] = 0;
		
	$plugin->up_mode('post');
		
	$data = sitecaptcha_reload(1);
	$cd = create_sitecaptcha($data);
	$log = array_merge($log, $cd);	

	echo pn_json_encode($log);
	exit;
}

function create_sitecaptcha($data) {
		
	$array = array();	
		
	if (isset($data->id)) {
		$images = array();
		$symbols = pn_json_decode($data->symbols);
		$hsymbols = pn_json_decode($data->hsymbols);
		foreach ($symbols as $sym_k => $sym_v) {
			$images[] = '<div class="captcha_sci_img"><img src="'. sitecaptcha_generate($sym_v, $data->id . '_' . $hsymbols[$sym_k]) .'" class="sci_img" data-id="' . $hsymbols[$sym_k] . '" alt="" /></div>';
		}
		$array['sk_answer'] = sprintf(__('Select all pictures with numbers <span>"%s"</span>', 'pn'), $data->answer)  . ' (<a href="#" class="captcha_sci_reload">' . __('replace task?', 'pn') . '</a>)';
		$array['sk_images'] = $images;			
	} 
				
	return $array;	
}

add_filter('get_form_filelds', 'get_form_filelds_captcha_sci', 1000, 2);
function get_form_filelds_captcha_sci($items, $name) {
	
	$plugin = get_plugin_class();	
	if (is_captcha($name)) {
		$items['captcha_sci'] = array(
			'type' => 'captcha_sci',
		);
	}
	
	return $items;
}

add_filter('form_field_line', 'form_field_line_captcha_sci', 10, 3);
function form_field_line_captcha_sci($line, $filter, $data) {
	global $pn_sitecaptcha;	
		
	$type = trim(is_isset($data, 'type'));
	if ('captcha_sci' == $type) {
		$cd = create_sitecaptcha($pn_sitecaptcha);
		$line = get_sitecaptcha_temp($cd);	
	}
		
	return $line;
}

add_action('comment_form', 'comment_form_captcha_sci', 990);
function comment_form_captcha_sci() {
	global $pn_sitecaptcha;
	
	$plugin = get_plugin_class();
	if (is_captcha('commentform')) {
		$cd = create_sitecaptcha($pn_sitecaptcha);
		$line = get_sitecaptcha_temp($cd);
		echo $line;
	}	
}

add_filter('exchange_step1', 'exchange_form_captcha_sci', 1000);
function exchange_form_captcha_sci($line) {
	global $pn_sitecaptcha;	

	if (is_captcha('exchangeform')) {
		
		if (!isset($pn_sitecaptcha->id)) {
			$pn_sitecaptcha = sitecaptcha_reload(0);
		}		
		
		$cd = create_sitecaptcha($pn_sitecaptcha);
		$line .= get_sitecaptcha_temp($cd);
	}
	
	return $line;	
}

function get_sitecaptcha_temp($cd) {

	$temp = '
	<div class="captcha_sci_div">
		<div class="captcha_sci_title">
			'. is_isset($cd, 'sk_answer') .'
		</div>
		<div class="captcha_sci_body">
			<input type="hidden" class="captcha_sci_hidden" name="captcha_sci" value="" />';
						
				$images = is_isset($cd, 'sk_images');	
				if (is_array($images)) {
					foreach ($images as $image) {
						$temp .= $image;
					}
				}
					
			$temp .= '	
				<div class="clear"></div>
		</div>
	</div>	
	';
	$temp = apply_filters('get_captcha_sci_temp', $temp, $cd);
	
	return $temp;
}

add_filter('before_ajax_form', 'before_ajax_form_captcha_sci', 1010, 2);
function before_ajax_form_captcha_sci($logs, $name) {
	global $wpdb;
	
	if (is_captcha($name)) {
			
		$captcha = pn_strip_input(is_param_post('captcha_sci'));	
		$data = sitecaptcha_reload(0);
			
		$error = 0;
		if (!isset($data->id) or $data->value != $captcha) {		
			$error = 1;				
		}
			
		$new_data = sitecaptcha_reload(1);
		$cd = create_sitecaptcha($new_data);
		$logs = array_merge($logs, $cd);						
			
		if ($error) {
			$logs['status'] = 'error';
			$logs['status_code'] = '30';
			$logs['status_text'] = __('Error! You have not selected all captcha images', 'pn');				
		} 	
	}
		
	return $logs;
}