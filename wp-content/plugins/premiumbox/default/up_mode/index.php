<?php
if (!defined('ABSPATH')) { exit(); }

if (is_admin()) {
	if (!function_exists('after_pn_adminpage_title_up_mode')) {
		add_action('after_pn_adminpage_title', 'after_pn_adminpage_title_up_mode', 10, 2);
		function after_pn_adminpage_title_up_mode() { 
		
			$plugin = get_plugin_class();
			$up_mode = intval($plugin->get_option('up_mode'));
			if (1 == $up_mode) {
		?>
			<div class="up_mode_div">
				<div class="up_mode_form">
					<?php if (current_user_can('administrator')) { ?>
						<form method="post" action="<?php the_pn_link('change_up_mode_close', 'get'); ?>">
							<?php wp_referer_field(); ?>
							<input type="submit" class="button" style="background: #fcf6ee;" name="" value="<?php _e('Disable update mode', 'pn'); ?>" />
						</form>
					<?php } else { ?>
						<div class="up_mode_text"><?php _e('Update mode enabled', 'pn'); ?></div>
					<?php } ?>
				</div>
			</div>
				<div class="premium_clear"></div>
		<?php	
			}
			
		}
	}
}

if (!function_exists('def_premium_action_change_up_mode_close')) {
	add_action('premium_action_change_up_mode_close', 'def_premium_action_change_up_mode_close');
	function def_premium_action_change_up_mode_close() {	

		_method('post');
			
		pn_only_caps(array('administrator'));
			
		$plugin = get_plugin_class();
				
		$plugin->update_option('up_mode', '', 0);	
				
		$back_url = rtrim(PN_SITE_URL, '/') . urldecode(is_param_post('_wp_http_referer'));		
		wp_redirect(get_safe_url($back_url));
		exit;					
		
	} 
} 
