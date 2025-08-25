<?php
if (!defined('ABSPATH')) { exit(); }

/*
title: [en_US:]Warning messages in header[:en_US][ru_RU:]Уведомление в шапке[:ru_RU]
description: [en_US:]Warning messages column marked in red located in header[:en_US][ru_RU:]Блок уведомления на красном фоне в шапке сайта[:ru_RU]
version: 2.7.0
category: [en_US:]Security[:en_US][ru_RU:]Безопасность[:ru_RU]
cat: secur
dependent: -
*/

$path = get_extension_file(__FILE__);
$name = get_extension_name($path);

if (!function_exists('e_active_noticeheader')) {
	add_action('all_moduls_active_' . $name, 'e_active_noticeheader');
	add_action('pn_plugin_activate', 'e_active_noticeheader');
	function e_active_noticeheader() {
		global $wpdb;
		
		$table_name = $wpdb->prefix . "notice_head";
		$sql = "CREATE TABLE IF NOT EXISTS $table_name(
			`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			`create_date` datetime NOT NULL,
			`edit_date` datetime NOT NULL,
			`auto_status` int(1) NOT NULL default '1',
			`edit_user_id` bigint(20) NOT NULL default '0',
			`notice_type` int(1) NOT NULL default '0',
			`notice_display` int(1) NOT NULL default '0',
			`datestart` datetime NOT NULL,
			`dateend` datetime NOT NULL,
			`op_status` int(5) NOT NULL default '-1',
			`h1` varchar(5) NOT NULL default '0',
			`m1` varchar(5) NOT NULL default '0',
			`h2` varchar(5) NOT NULL default '0',
			`m2` varchar(5) NOT NULL default '0',		
			`d1` int(1) NOT NULL default '0',
			`d2` int(1) NOT NULL default '0',
			`d3` int(1) NOT NULL default '0',
			`d4` int(1) NOT NULL default '0',
			`d5` int(1) NOT NULL default '0',
			`d6` int(1) NOT NULL default '0',
			`d7` int(1) NOT NULL default '0',
			`url` longtext NOT NULL,
			`text` longtext NOT NULL,
			`button_text` varchar(250) NOT NULL,
			`save_days` int(3) NOT NULL default '0',
			`status` int(1) NOT NULL default '0',
			`theclass` varchar(250) NOT NULL,
			`site_order` bigint(20) NOT NULL default '0',
			PRIMARY KEY (`id`),
			INDEX (`create_date`),
			INDEX (`edit_date`),
			INDEX (`auto_status`),
			INDEX (`edit_user_id`),
			INDEX (`notice_type`),
			INDEX (`notice_display`),
			INDEX (`datestart`),
			INDEX (`dateend`),
			INDEX (`op_status`),
			INDEX (`status`),
			INDEX (`site_order`)
		) ENGINE=InnoDB  DEFAULT CHARSET={$wpdb->charset} AUTO_INCREMENT=1;"; 
		$wpdb->query($sql);	

		$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "notice_head LIKE 'notice_display'"); /* 2.0 */
		if (0 == $query){
			$wpdb->query("ALTER TABLE " . $wpdb->prefix . "notice_head ADD `notice_display` int(1) NOT NULL default '0'");
		}
		
		$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "notice_head LIKE 'button_text'"); /* 2.2 */
		if (0 == $query){
			$wpdb->query("ALTER TABLE " . $wpdb->prefix . "notice_head ADD `button_text` varchar(250) NOT NULL");
		}
		
		$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "notice_head LIKE 'save_days'"); /* 2.2 */
		if (0 == $query){
			$wpdb->query("ALTER TABLE " . $wpdb->prefix . "notice_head ADD `save_days` int(3) NOT NULL default '0'");
		}		
	}
}

if (!function_exists('noticeheader_pn_caps')) {
	add_filter('pn_caps', 'noticeheader_pn_caps');
	function noticeheader_pn_caps($pn_caps) {
		
		$pn_caps['pn_noticeheader'] = __('Warning messages in header', 'pn');
		
		return $pn_caps;
	}
}

if (!function_exists('noticeheader_admin_menu')) {
	add_action('admin_menu', 'noticeheader_admin_menu');
	function noticeheader_admin_menu() {
		
		$plugin = get_plugin_class();
		if (current_user_cans('administrator, pn_noticeheader')) {
			add_menu_page(__('Warning messages', 'pn'), __('Warning messages', 'pn'), 'read', "all_noticeheader", array($plugin, 'admin_temp'), $plugin->get_icon_link('icon'), 10000);
			add_submenu_page("all_noticeheader", __('Add', 'pn'), __('Add', 'pn'), 'read', "all_add_noticeheader", array($plugin, 'admin_temp'));
			add_submenu_page("all_noticeheader", __('Sort', 'pn'), __('Sort', 'pn'), 'read', "all_sort_noticeheader", array($plugin, 'admin_temp'));
		}		 	
		
	}
}

if (!function_exists('pn_header_theme_noticehead')) {
	add_action('pn_header_theme', 'pn_header_theme_noticehead');
	function pn_header_theme_noticehead() {
		global $wpdb, $pn_list_notice;

		$notice_lists = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "notice_head WHERE status='1' AND auto_status = '1' ORDER BY site_order ASC");
		foreach ($notice_lists as $notice) {
			$notice_display = intval($notice->notice_display);
			$text = ctv_ml($notice->text);
			$url = pn_strip_input(ctv_ml($notice->url));
			$status = get_noticehead_status($notice);
			$save_days = intval(is_isset($notice, 'save_days'));
			if ($save_days < 1) { $save_days = 1; }
			if (1 == $status) {
				if ($notice_display > 0) {
					$pn_list_notice[] = $notice;
				} else {
					$cl = array('wclosearea');
					$theclass = pn_strip_input($notice->theclass, '_- ');
					if ($theclass) {
						$cl[] = $theclass;
					}				
		?>	
		<div class="<?php echo implode(' ', $cl); ?> js_hnotice" id="hnotice_<?php echo $notice->id; ?>">
			<div class="wclosearea_ins">
				<div class="wclosearea_hide js_hnotice_close" data-exp="<?php echo $save_days;?>"><div class="wclosearea_hide_ins"></div></div>
				<div class="wclosearea_text">
					<div class="wclosearea_text_ins">
						<?php if ($url) { ?><a href="<?php echo $url; ?>"><?php } ?>
							<?php echo apply_filters('comment_text', $text); ?>
						<?php if ($url) { ?></a><?php } ?>
					</div>	
				</div>
			</div>
		</div>
		<?php 
				}
			}
		} 
	} 
}

if (!function_exists('pn_footer_theme_noticeheader')) {
	add_action('wp_footer', 'pn_footer_theme_noticeheader');
	function pn_footer_theme_noticeheader() {
		global $wpdb, $pn_list_notice;

		$list_windows = array();
		$list_notifies = array();
		
		if (is_array($pn_list_notice)) {
			foreach ($pn_list_notice as $notice) {
				$notice_display = intval($notice->notice_display);
				if (1 == $notice_display) {
					$list_windows[0] = $notice;
				} else {	
					$list_notifies[] = $notice;
				}
			}
			unset($pn_list_notice);
		}
		
		foreach ($list_windows as $notice) {
			$text = ctv_ml($notice->text);
			$url = pn_strip_input(ctv_ml($notice->url));
			$button_text = pn_strip_input(ctv_ml($notice->button_text));
			if (!$button_text) { $button_text = __('I have read', 'pn'); }
			$save_days = intval(is_isset($notice, 'save_days'));
			if ($save_days < 1) { $save_days = 1; }
			?>
			<div class="js_wc_<?php echo $notice->id; ?>" style="display: none;">
				<?php if ($url) { ?><a href="<?php echo $url; ?>"><?php } ?>
					<?php echo apply_filters('comment_text', $text); ?>
				<?php if ($url) { ?></a><?php } ?>
			</div>
			<script type="text/javascript">
			jQuery(function($) {
				
				$(document).JsWindow('show', {
					id: 'wc_<?php echo $notice->id; ?>',
					window_class: 'wc_window',
					close_class: 'js_hnotice_window_close',
					title: '<?php _e("Attention!", "pn"); ?>!',
					content: $('.js_wc_<?php echo $notice->id; ?>').html(),
					shadow: 1,
					enable_button: 1,
					button_title: '<?php echo $button_text; ?>',
					button_class: 'js_window_close js_hnotice_window_close',
					close_after: function(thet) {
						var id = thet.parents('.wc_window').attr('id').replace('techwindow_wc_', '');
						$(document).PHPCookie('set', {key: "hm" + id, value: 1, domain: '<?php echo PN_SITE_URL; ?>', days: '<?php echo $save_days; ?>'});
					}
				});	
				
			});
			</script>			
			<?php
		}		

 		foreach ($list_notifies as $notice) {
			$text = ctv_ml($notice->text);
			$url = pn_strip_input(ctv_ml($notice->url));
			$button_text = pn_strip_input(ctv_ml($notice->button_text));
			if (!$button_text) { $button_text = __('I have read', 'pn'); }
			$save_days = intval(is_isset($notice, 'save_days'));
			if ($save_days < 1) { $save_days = 1; }
			?>
			<div class="wn_wrap">
				<div class="wn_div">
					<div class="wn_div_text">
						<?php if ($url) { ?><a href="<?php echo $url; ?>"><?php } ?>
							<?php echo apply_filters('comment_text', $text); ?>
						<?php if ($url) { ?></a><?php } ?>
					</div>
					<div class="wn_div_button">
						<input type="submit" name="" data-id="<?php echo $notice->id; ?>" data-exp="<?php echo $save_days;?>" class="wn_div_submit" value="<?php echo $button_text; ?>" />
					</div>
				</div>
			</div>
			<?php
		} 
		
	}
}

if (!function_exists('premium_js_noticehead')) {
	add_action('premium_js', 'premium_js_noticehead');
	function premium_js_noticehead() {	
	?>	 
	jQuery(function($) { 
	
		$(document).on('click', '.js_hnotice_close', function() {
			var id = $(this).parents('.js_hnotice').attr('id').replace('hnotice_', '');
			var exp_day = parseInt($(this).attr('data-exp'));
			
			$(document).PHPCookie('set', {key: "hm" + id, value: 1, domain: '<?php echo PN_SITE_URL; ?>', days: exp_day});
			
			$('#hnotice_' + id).hide();
		});
		
		$(document).on('click', '.wn_div_submit', function() {
			var id = $(this).attr('data-id');
			var exp_day = parseInt($(this).attr('data-exp'));
			
			$(document).PHPCookie('set', {key: "hm" + id, value: 1, domain: '<?php echo PN_SITE_URL; ?>', days: exp_day});
			
			$(this).parents('.wn_wrap').hide();
		});	
		
	});	
	<?php	
	}
}

$plugin = get_plugin_class();
$plugin->include_path(__FILE__, 'add_noticeheader');
$plugin->include_path(__FILE__, 'list_noticeheader');
$plugin->include_path(__FILE__, 'sort_noticeheader');