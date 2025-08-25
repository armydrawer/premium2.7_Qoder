<?php 
if (!defined('ABSPATH')) { exit(); }

$ui = wp_get_current_user();
$user_id = intval($ui->ID);

$plugin = get_plugin_class();

$mobile_change = get_theme_option('mobile_change', array('logo', 'phone', 'icq', 'skype', 'email', 'linkhead', 'telegram', 'viber', 'whatsapp', 'jabber', 'hideloginbutton'));
$hideloginbutton = intval($mobile_change['hideloginbutton']);
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>

	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0" />
	<meta name="HandheldFriendly" content="True" />
	<meta name="MobileOptimized" content="320" />
	<meta name="format-detection" content="telephone=no" />
	<meta name="PalmComputingPlatform" content="true" />
	<meta name="apple-touch-fullscreen" content="yes"/>
	
	<link rel="profile" href="http://gmpg.org/xfn/11">
	
	<meta charset="<?php bloginfo('charset'); ?>">
	<title><?php wp_title(); ?></title>
			
	<?php wp_head(); ?>	
	
</head>
<body <?php body_class(); ?>>
<div id="container">

	<?php do_action('pn_header_theme', 'mobile'); ?>
	
	<div class="container">
		
		<!-- header -->
		<div class="header_wrap">
			<div class="header">
				<a href="#slide_menu" class="js_slide_menu topbar_link menu" title="<?php _e('Menu', 'pntheme'); ?>"></a>
			
				<div class="logo">
					<div class="logo_ins">
						<?php if (1 == $mobile_change['linkhead'] and !is_front_page() or 1 != $mobile_change['linkhead']) { ?>
							<a href="<?php echo get_site_url_ml(); ?>">
						<?php } ?>
											
							<?php
							$logo = get_mobile_logotype();
							$textlogo = get_mobile_textlogo();
							if ($logo) {
							?>
								<img src="<?php echo $logo; ?>" alt="" />
							<?php } elseif ($textlogo) { ?>
								<?php echo $textlogo; ?>	
							<?php } else { 
								$textlogo = str_replace(array('http://', 'https://', 'www.'), '', PN_SITEURL); 
							?>
								<?php echo get_caps_name($textlogo); ?>
							<?php } ?>
											
						<?php if (1 == $mobile_change['linkhead'] and !is_front_page() or 1 != $mobile_change['linkhead']) { ?>	
							</a>
						<?php } ?>
					</div>
				</div>
				
				<a href="#slide_contacts" class="js_slide_menu topbar_link contacts" title="<?php _e('Contacts', 'pntheme'); ?>"></a>
				
				<?php if (is_ml()) { ?>
					<a href="#slide_lang" class="js_slide_menu topbar_link language" title="<?php _e('Language selection', 'pntheme'); ?>"></a>
				<?php } ?>		
					<div class="clear"></div>
			</div>	
		</div>
		<!-- end header -->	
		
		<div class="wrapper">
			
			<?php if (is_ml()) { ?>
				<div class="slide_window <?php if (is_pn_rtl()) { ?>toleft<?php } else { ?>toright<?php } ?>" id="slide_lang">
					<div class="slide_window_ins">
						<div class="slide_window_abs"></div>
						<div class="slide_window_title"><?php _e('Language selection', 'pntheme'); ?></div>
					
						<div class="langmenu">
							<ul>
								<?php
								$lang = get_locale();
								$langs = get_langs_ml();
								foreach ($langs as $lan) {
									$cl = '';
									if ($lan == $lang) { $cl = 'current-menu-item';}
								?>
									<li class="<?php echo $cl; ?>">
										<a href="<?php echo get_lang_vers($lan); ?>" rel="nofollow">
											<div class="langlist_liimg">
												<img src="<?php echo get_lang_icon($lan); ?>" alt="" />
											</div>
											<?php echo get_title_forkey($lan); ?>
										</a>
									</li>	
								<?php } ?>
							</ul>
						</div>				
					
					</div>	
				</div>
			<?php } ?>	

			<div class="slide_window <?php if (is_pn_rtl()) { ?>toleft<?php } else { ?>toright<?php } ?>" id="slide_contacts">
				<div class="slide_window_ins">
					<div class="slide_window_abs"></div>
					<div class="slide_window_title"><?php _e('Contacts', 'pntheme'); ?></div>
					<div class="cmenu">
						<ul>
							<?php if ($mobile_change['icq']) { ?>
								<li class="icq">
									<span><?php echo get_contact($mobile_change['icq'], 'icq'); ?></span>
								</li>
							<?php } ?>
							
							<?php if ($mobile_change['telegram']) { ?>
								<li class="telegram">
									<span><?php echo get_contact($mobile_change['telegram'], 'tm', __('to write', 'pntheme')); ?></span>
								</li>
							<?php } ?>
							
							<?php if ($mobile_change['viber']) { ?>
								<li class="viber">
									<span><?php echo get_contact($mobile_change['viber'], 'viber'); ?></span>
								</li>		
							<?php } ?>
							
							<?php if ($mobile_change['whatsapp']) { ?>
								<li class="whatsapp">
									<span><?php echo get_contact($mobile_change['whatsapp'], 'whatsapp'); ?></span>
								</li>		
							<?php } ?>

							<?php if ($mobile_change['jabber']) { ?>
								<li class="jabber">
									<span><?php echo get_contact($mobile_change['jabber'], 'jabber'); ?></span>
								</li>		
							<?php } ?>				
								
							<?php if ($mobile_change['skype']) { ?>
								<li class="skype">
									<span><?php echo get_contact($mobile_change['skype'], 'skype'); ?></span>
								</li>		
							<?php } ?>
								
							<?php if ($mobile_change['email']) { ?>
								<li class="email">
									<span><?php echo get_contact($mobile_change['email'], 'email'); ?></span>
								</li>		
							<?php } ?>

							<?php if ($mobile_change['phone']) { ?>
								<li class="phone">
									<span><?php echo get_contact($mobile_change['phone'], 'phone'); ?></span>
								</li>		
							<?php } ?>				
						</ul>
					</div>				
				</div>	
			</div>		
	
			<div class="slide_window <?php if (is_pn_rtl()) { ?>toright<?php } else { ?>toleft<?php } ?>" id="slide_menu">
				<div class="slide_window_ins">
					
					<?php if (1 != $hideloginbutton) { ?>
					<div class="logmenu">
						<div class="logmenu_ins">
							<?php 
							if ($user_id) { 
							?>
								<a href="<?php echo $plugin->get_page('account'); ?>" class="toplink toplink_userlogin"><span><?php _e('Account', 'pntheme'); ?></span></a>
								<a href="<?php echo get_pn_action('logout', 'get'); ?>" class="toplink toplink_exit"><span><?php _e('Exit', 'pntheme'); ?></span></a>
							<?php } else { ?>
								<a href="<?php echo $plugin->get_page('login'); ?>" class="toplink toplink_signin js_window_login"><span><?php _e('Sign in', 'pntheme'); ?></span></a>
								<a href="<?php echo $plugin->get_page('register'); ?>" class="toplink toplink_signup js_window_join"><span><?php _e('Sign up', 'pntheme'); ?></span></a>
							<?php 
							} 
							?>
								<div class="clear"></div>
						</div>	
					</div>
					<?php } ?>
				
					<div class="topmenu">
						<?php
						if ($user_id) {
							$theme_location = 'mobile_top_menu_user';
						} else {
							$theme_location = 'mobile_top_menu';	
						}
						wp_nav_menu(array(
							'sort_column' => 'menu_order',
							'container' => 'div',
							'container_class' => 'menu',
							'menu_class' => 'hmenu',
							'menu_id' => '',
							'depth' => '1',
							'fallback_cb' => 'no_menu',
							'theme_location' => $theme_location
						));					
						?>
							<div class="clear"></div>
					</div>	
				
				</div>	
			</div>	
			
			<div class="content_wrap" id="content_wrap">	