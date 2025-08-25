<?php 
if (!defined('ABSPATH')) { exit(); } 

$ui = wp_get_current_user();
$user_id = intval($ui->ID);

$plugin = get_plugin_class();
$h_change = get_theme_option('h_change', array('fixheader', 'linkhead', 'phone', 'icq', 'skype', 'email', 'telegram', 'viber', 'whatsapp', 'jabber', 'timetable', 'hideloginbutton'));
$hideloginbutton = intval($h_change['hideloginbutton']);
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

	<?php do_action('pn_header_theme'); ?>

	<div class="container">
		
		
		<div class="mobile_menu_wrap">
			<div class="mobile_menu_abs"></div>
			<div class="mobile_menu">
				<div class="mobile_menu_ins">
					<div class="mobile_menu_title"><?php _e('Menu', 'pntheme'); ?></div>
					<div class="mobile_menu_close"></div>
					<div class="mobile_menu_inner"></div>
					<?php if (1 != $hideloginbutton) { ?>
					<div class="mobile_menu_button">
						<?php if ($user_id) { ?>
							<a href="<?php echo get_pn_action('logout', 'get'); ?>" class="toplink toplink_exit"><span><?php _e('Exit', 'pntheme'); ?></span></a>
							<a href="<?php echo $plugin->get_page('account'); ?>" class="toplink toplink_userlogin"><span><?php _e('Account', 'pntheme'); ?></span></a>
						<?php } else { ?>
							<a href="<?php echo $plugin->get_page('register'); ?>" class="toplink toplink_signup js_window_join"><span><?php _e('Sign up', 'pntheme'); ?></span></a>
							<a href="<?php echo $plugin->get_page('login'); ?>" class="toplink toplink_signin js_window_login"><span><?php _e('Sign in', 'pntheme'); ?></span></a>
						<?php } ?>					
						<div class="clear"></div>
					</div>	
					<?php } ?>
				</div>
			</div>	
		</div>	
	
		<!-- top bar -->
		<div class="topbar_wrap <?php if (1 == $h_change['fixheader']) { ?>fix_div<?php } ?>" fix-top="0" fix-id="fix_topbar">
			<div class="topbar_ins" id="fix_topbar">
				<div class="topbar">
					<div class="topbar_contain">
					
						<?php the_lang_list('tolbar_lang'); ?>
						
						<div class="topbar_icon_wrap">
							<div class="topbar_icon_tab"><?php _e('Contacts', 'pntheme'); ?></div>
							<div class="topbar_icon_tabul">
								<?php if ($h_change['icq']) { ?>
									<div class="topbar_icon icq">
										<?php echo get_contact($h_change['icq'], 'icq'); ?>
									</div>		
								<?php } ?>
										
								<?php if($h_change['telegram']){ ?>
									<div class="topbar_icon telegram">
										<?php echo get_contact($h_change['telegram'], 'tm', __('to write', 'pntheme')); ?>
									</div>		
								<?php } ?>

								<?php if ($h_change['viber']) { ?>
									<div class="topbar_icon viber">
										<?php echo get_contact($h_change['viber'], 'viber'); ?>
									</div>		
								<?php } ?>

								<?php if ($h_change['whatsapp']) { ?>
									<div class="topbar_icon whatsapp">
										<?php echo get_contact($h_change['whatsapp'], 'whatsapp'); ?>
									</div>		
								<?php } ?>

								<?php if ($h_change['jabber']) { ?>
									<div class="topbar_icon jabber">
										<?php echo get_contact($h_change['jabber'], 'jabber'); ?>
									</div>		
								<?php } ?>				
										
								<?php if ($h_change['skype']) { ?>
									<div class="topbar_icon skype">
										<?php echo get_contact($h_change['skype'], 'skype'); ?>
									</div>		
								<?php } ?>
										
								<?php if ($h_change['email']) { ?>
									<div class="topbar_icon email">
										<?php echo get_contact($h_change['email'], 'email'); ?>
									</div>		
								<?php } ?>

								<?php if ($h_change['phone']) { ?>
									<div class="topbar_icon phone">
										<?php echo get_contact($h_change['phone'], 'phone'); ?>
									</div>		
								<?php } ?>						
							</div>
						</div>						
					
						<?php if (1 != $hideloginbutton) { ?>
							<?php if ($user_id) { ?>
								<a href="<?php echo get_pn_action('logout', 'get'); ?>" class="toplink toplink_exit"><span><?php _e('Exit', 'pntheme'); ?></span></a>
								<a href="<?php echo $plugin->get_page('account'); ?>" class="toplink toplink_userlogin"><span><?php _e('Account', 'pntheme'); ?></span></a>
							<?php } else { ?>
								<a href="<?php echo $plugin->get_page('register'); ?>" class="toplink toplink_signup js_window_join"><span><?php _e('Sign up', 'pntheme'); ?></span></a>
								<a href="<?php echo $plugin->get_page('login'); ?>" class="toplink toplink_signin js_window_login"><span><?php _e('Sign in', 'pntheme'); ?></span></a>
							<?php } ?>
						<?php } ?>
							<div class="clear"></div>
					</div>		
				</div>
			</div>
		</div>
		<!-- end top bar -->

		<!-- top menu -->
		<div class="tophead_wrap">
			<div class="tophead_ins">
				<div class="tophead">
				
					<div class="logoblock">
						<div class="logoblock_ins">
							<?php if (1 == $h_change['linkhead'] and !is_front_page() or 1 != $h_change['linkhead']) { ?>
								<a href="<?php echo get_site_url_ml(); ?>">
							<?php } ?>
								
								<?php
								$logo = get_logotype();
								$textlogo = get_textlogo();
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
								
							<?php if (1 == $h_change['linkhead'] and !is_front_page() or 1 != $h_change['linkhead']) { ?>	
								</a>
							<?php } ?>	
						</div>
					</div>
					
					<div class="topmenu js_menu">
						<?php
						if ($user_id) {
							$theme_location = 'the_top_menu_user';
						} else {
							$theme_location = 'the_top_menu';	
						}
						wp_nav_menu(array(
							'sort_column' => 'menu_order',
							'container' => 'div',
							'container_class' => 'menu',
							'menu_class' => 'hmenu',
							'menu_id' => '',
							'depth' => '3',
							'fallback_cb' => 'no_menu',
							'theme_location' => $theme_location
						));					
						?>				
						<div class="clear"></div>
					</div>
					
					<?php if ($h_change['timetable']) { ?>
					<div class="header_timetable">
						<div class="header_timetable_ins">
							<?php echo apply_filters('comment_text', $h_change['timetable']); ?>
						</div>
					</div>
					<?php } ?>	
					
					<div class="topmenu_ico"></div>
					
						<div class="clear"></div>
				</div>
			</div>
		</div>
		<!-- end top menu -->	

		<div class="wrapper">
		
			<?php if (!is_front_page()) { ?>
			<div class="breadcrumb_wrap">
				<div class="breadcrumb_div">
					<div class="breadcrumb_ins">
				
						<h1 class="breadcrumb_title" id="the_title_page">
							<?php the_breadcrumb_title(); ?>
						</h1>
				
						<div class="breadcrumb">
							<?php the_breadcrumb(__('Currency exchange', 'pntheme')); ?>
						</div>
				
					</div>
				</div>
			</div>	
			<?php } ?>

			<div class="content_wrap">
			
				<?php if (!is_front_page() and !is_page_template('pn-notsidebar.php')) { ?>	
				<div class="content">
				<?php } ?>		