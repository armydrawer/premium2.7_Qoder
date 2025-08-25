<?php
if (!defined('ABSPATH')) { exit(); }

add_action('pn_caps', 'pn_caps_update');
function pn_caps_update($pn_caps) {
	
	$pn_caps['pn_dev_news'] = sprintf(__('Show developer news for %s', 'pn'), 'Premium Exchanger');
	
	return $pn_caps;	
}

function get_premiumbox_info() {
	global $premiumbox_info_data;
	
	if (!is_array($premiumbox_info_data)) {
		$premiumbox_info_data = get_option('pn_version');
		if (!is_array($premiumbox_info_data)) { $premiumbox_info_data = array(); }
	}
	
	return $premiumbox_info_data;
}

function premiumbox_update_news($has_cookie = 0) {
	
	$has_cookie = intval($has_cookie);
	$news = array();
	$data = get_premiumbox_info();
	$dev_news = is_isset($data, 'news');
	if (is_array($dev_news) and count($dev_news) > 0) {
		foreach ($dev_news as $news_id => $news_value) { 
			$read_news = intval(get_pn_cookie('devnews' . $news_id));
			if (1 != $has_cookie or 1 == $has_cookie and 1 != $read_news) {
				$news[] = array(
					'id' => $news_id,
					'title' => pn_strip_input(ctv_ml(is_isset($news_value, 'title'))),
					'text' => apply_filters('comment_text', ctv_ml(is_isset($news_value, 'text'))),
					'read' => $read_news,
				);
			}
		}	
	}	
	
	return $news;
}
 
add_action('admin_footer', 'premiumbox_update_admin_footer');
function premiumbox_update_admin_footer() {
	if (current_user_can('administrator') or current_user_can('pn_dev_news')) {
?>
	<script type="text/javascript">
	jQuery(function($) {
		
		jQuery('.developer_news_title').on('click', function() {
			$(this).parents('.developer_news').toggleClass('show');
		});		
		
		jQuery('.devnews_read').on('change', function() {
			var thet = $(this);
			var id = $(this).attr('data-id');
			if (thet.prop('checked')) {
				var reader = 1;
			} else {
				var reader = 0;
			}
			$(document).PHPCookie('set', {key: "devnews" + id, value: reader, domain: '<?php echo PN_SITE_URL; ?>', days: '14'});
		});	
		
	});
	</script>		
<?php	
	}
}		

add_action('wp_dashboard_setup', 'wp_dashboard_setup_premiumbox' );
function wp_dashboard_setup_premiumbox() {
	if (current_user_can('administrator') or current_user_can('pn_dev_news')) {
		wp_add_dashboard_widget('standart_update_dashboard_widget_premiumbox', __('News from developer', 'pn'), 'dashboard_update_in_admin_panel_premiumbox');
	}
}

function dashboard_update_in_admin_panel_premiumbox() {
	$news = premiumbox_update_news();
	$r = 0;
	if (is_array($news) and count($news) > 0) {
		foreach ($news as $news_value) { $r++;
		?>
			<div class="one_developer_news">
				<div class="one_developer_news_title"><?php echo is_isset($news_value, 'title'); ?></div>
				<div class="one_developer_news_content"><?php echo is_isset($news_value, 'text'); ?></div>
			</div>
		<?php
			if (3 == $r) { break; }
		}
	}
}

add_action('admin_menu', 'premiumbox_devnews_admin_menu');
function premiumbox_devnews_admin_menu() {
	$plugin = get_plugin_class();	
	if (current_user_cans('administrator,pn_dev_news')) {
		add_submenu_page("pn_none_menu", __('News from developer', 'pn'), __('News from developer', 'pn'), 'administrator', "pn_dev_news", array($plugin, 'admin_temp'));
	}
}

add_filter('pn_adminpage_title_pn_dev_news', 'def_adminpage_title_pn_dev_news');
function def_adminpage_title_pn_dev_news() {
	
	return __('News from developer', 'pn');
}

add_action('pn_adminpage_content_pn_dev_news', 'def_adminpage_content_pn_dev_news');
function def_adminpage_content_pn_dev_news() {
	
	$news = premiumbox_update_news();
	if (is_array($news) and count($news) > 0) {
		foreach ($news as $news_value) {
		?>
			<div class="developer_news">
				<div class="developer_news_title"><?php echo is_isset($news_value, 'title'); ?></div>
				<div class="developer_news_content"><?php echo is_isset($news_value, 'text'); ?></div>
				<div class="developer_news_agree"><label><input type="checkbox" class="devnews_read" data-id="<?php echo is_isset($news_value, 'id'); ?>" <?php checked(is_isset($news_value, 'read'), 1); ?> name="" autocomplete="off" value="" /> <?php _e('I read news', 'pn'); ?></label></div>
			</div>
		<?php
		}
	}	
}

add_action('wp_before_admin_bar_render', 'premiumbox_update_icon_admin_bar_render', 3);
function premiumbox_update_icon_admin_bar_render() {
	global $wp_admin_bar, $wpdb;
	 
	$premium_url = get_premium_url();	
	if (current_user_can('administrator') or current_user_can('pn_dev_news')) {	
		$news = premiumbox_update_news(1);
		if (count($news) > 0) {
			
			$wp_admin_bar->add_menu( array(
				'id'     => 'new_pn_devnews',
				'href' => admin_url('admin.php?page=pn_dev_news'),
				'title'  => '<div style="height: 32px; width: 32px; background: url(' . $premium_url . 'images/dev_news_bar.png) no-repeat center center"></div>',
				'meta' => array('title' => sprintf(__('News from developer (%s)', 'pn'), count($news)), 'class' => 'premium_ab_icon')		
			));		
			
		}
	}
}  

add_filter('list_cron_func', 'premiumbox_update_list_cron_func');
function premiumbox_update_list_cron_func($filters) {	

	$filters['premiumbox_chkv'] = array(
		'title' => __('Check updates', 'pn'),
		'site' => '3hour',
		'allways' => 1,
	);
	
	return $filters;
} 