<?php if( !defined( 'ABSPATH')){ exit(); }

$ui = wp_get_current_user();
$user_id = intval($ui->ID);

$plugin = get_plugin_class();
$h_change = get_theme_option('h_change', array('fixheader','linkhead','skype','email','telegram','telegram_link','timetable','hideloginbutton'));
$ho_change = get_theme_option('ho_change',
    array(
        'banners_block',
        'b1_title',
        'b1_text',
        'b1_link',
        'b1_img',

        'b2_title',
        'b2_text',
        'b2_link',
        'b2_img',

        'b3_title',
        'b3_text',
        'b3_link',
        'b3_img',

        'b4_title',
        'b4_text',
        'b4_link',
        'b4_img',

        'b5_title',
        'b5_text',
        'b5_link',
        'b5_img',
    )
);
$hideloginbutton = intval($h_change['hideloginbutton']);
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>

	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0" />
	<meta name="HandheldFriendly" content="True" />
	<meta name="MobileOptimized" content="360" />
	<meta name="format-detection" content="telephone=no" />
	<meta name="PalmComputingPlatform" content="true" />
	<meta name="apple-touch-fullscreen" content="yes"/>

	<link rel="profile" href="http://gmpg.org/xfn/11">

	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<title><?php wp_title(); ?></title>
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />

	<?php wp_head(); ?>

</head>
<body <?php body_class(); ?>>
    <?php do_action('pn_header_theme'); ?>

	<div class="user-bar <?php if($h_change['fixheader'] == 1) echo 'sticky '?> <?php if(is_admin_bar_showing()) echo 'with-adminbar '?>">
        <div class="container user-bar__container">
            <div class="user-bar__left">
                <div class="lang_auth_wrapper">
                <?php the_lang_list('tolbar_lang'); ?>
                </div>
                <div class="header__dark-mode light">
                </div>
            </div>
            <div class="header__contacts">
                <?php if($h_change['telegram']){ ?>
                    <a href="tg://resolve?domain=<?php echo pn_strip_input(str_replace('@','', $h_change['telegram_link'])); ?>" class="header__contacts-link contacts-tg"  target="_blank">
                       <span class="header__contacts-icon"><?php echo premiumexchanger_get_svg(array('icon' => 'tg')); ?></span>
                       <span><?php echo $h_change['telegram'] ?></span>
                    </a>
                <?php } ?>
                <?php if($h_change['email']){ ?>
                   <a href="mailto:<?php echo $h_change['email'] ?>" class="header__contacts-link contacts-mail" target="_blank">
                      <span class="header__contacts-icon"><?php echo premiumexchanger_get_svg(array('icon' => 'email')); ?></span>
                      <span><?php echo $h_change['email'] ?></span>
                   </a>
                <?php } ?>
                <?php if($h_change['skype']){ ?>
                    <a href="skype:<?php echo $h_change['skype'] ?>" class="contacts_link contacts-skype" target="_blank">
                        <span class="header__contacts-icon"><?php echo premiumexchanger_get_svg(array('icon' => 'skype')); ?></span>
                        <span><?php echo $h_change['skype'] ?></span>
                    </a>
                <?php } ?>
			</div>
			<?php if ($hideloginbutton != 1) { ?>
                <div class="lang_auth_wrapper">
                    <div class="header__auth">
                        <div class="auth-wrapper">
                            <?php if($user_id){ ?>
                                <a href="<?php echo get_pn_action('logout', 'get'); ?>" class="user-bar__link"><?php _e('Exit','pntheme'); ?></a>
                                <a href="<?php echo $plugin->get_page('account'); ?>" class="user-bar__link"><?php _e('Account','pntheme'); ?></a>

                            <?php } else { ?>
                                <a href="<?php echo $plugin->get_page('login'); ?>" class="user-bar__link js_window_login"><?php _e('Sign in','pntheme'); ?></a>
                                <a href="<?php echo $plugin->get_page('register'); ?>" class="user-bar__link js_window_join"><?php _e('Sign up','pntheme'); ?></a>

                            <?php }?>
                        </div>
                    </div>
                </div>
            <?php }?>
        </div>
    </div>

	<div class="navigation <?php if($h_change['fixheader'] == 2) echo 'sticky '?> <?php if(is_admin_bar_showing()) echo 'with-adminbar '?>">
		<div class="container navigation__container">
			<div class="logo_operator_wrapper">

                <?php if($h_change['linkhead'] == 1 and !is_front_page() or $h_change['linkhead'] != 1){ ?>
                    <a class="header__logo" href="<?php echo get_site_url_ml(); ?>">
                <?php } ?>

                <?php
                    $logo = get_logotype();
                    $textlogo = get_textlogo();
                    if($logo){
                ?>
                    <img src="<?php echo $logo; ?>" alt="" />
                <?php } elseif($textlogo){ ?>
                    <?php echo $textlogo; ?>
                <?php } else {
                    $textlogo = str_replace(array('http://','https://','www.'),'', PN_SITE_URL);
                    $textlogo = rtrim($textlogo,'/');
                ?>
                    <img src="<?php echo PN_TEMPLATEURL; ?>/images/logowhite.svg" alt="logo">
                <?php } ?>
                <?php if($h_change['linkhead'] == 1 and !is_front_page() or $h_change['linkhead'] != 1){ ?>
                    </a>
                <?php } ?>
				<?php
                	// status operator
                	global $premiumbox;
                	$show_button = $premiumbox->get_option('statuswork', 'show_button');
                	if ($show_button) {theme_operator();}
                ?>
				<div class ="header__dark-mode header__dark-mode--mobile"></div>
			</div>

			<div class="info_lang_menu_wrapper">
				<div class="topmenu_wrapper">
					<button class="main-menu-btn md-visible"><span></span><span></span><span></span></button>
					<nav class="main-menu">
					    <div class="main-menu__top">
						    <?php the_flags_list('flags_wrapper'); ?>
						    <button class="main-menu-btn-close md-visible"><span></span><span></span></button>
						</div>
						<div class="menu-items-wrapper">
							<?php
								if($user_id){
									$theme_location = 'the_top_menu_user';
								} else {
									$theme_location = 'the_top_menu';
								}
								wp_nav_menu(array(
									'sort_column' => 'menu_order',
									'container' => 'div',
									'container_class' => 'menu',
									'menu_class' => 'main-menu__list',
									'menu_id' => '',
									'depth' => '3',
									'fallback_cb' => 'no_menu',
									'theme_location' => $theme_location
								));
							?>
						</div>
						<?php if($h_change['timetable']){ ?>
                            <div class="hidden_timetable">
                                <div class ="timetable_mobile">
                                    <?php echo apply_filters('comment_text',$h_change['timetable']); ?>
                                </div>
                            </div>
						<?php }?>
						<?php if ($hideloginbutton != 1) { ?>
                            <div class="hidden_auth">
                                <div class="auth-wrapper">
                                    <?php if($user_id){ ?>
                                        <a href="<?php echo $plugin->get_page('account'); ?>" class="user-bar__link active link-account">
                                            <span class="header__account-icon header__account-icon--mobile"><?php echo premiumexchanger_get_svg(array('icon' => 'account')); ?></span>
                                            <span><?php _e('Account','pntheme'); ?></span>
                                        </a>
                                        <a href="<?php echo get_pn_action('logout', 'get'); ?>" class="user-bar__link active link-logout">
                                            <span><?php _e('Exit','pntheme'); ?></span>
                                            <span class="header__account-icon header__account-icon--mobile"><?php echo premiumexchanger_get_svg(array('icon' => 'logout')); ?></span>
                                        </a>
                                    <?php } else { ?>
                                        <a href="<?php echo $plugin->get_page('register'); ?>" class="user-bar__link active js_window_join active link-register">
                                            <span class="header__account-icon header__account-icon--mobile"><?php echo premiumexchanger_get_svg(array('icon' => 'register')); ?></span>
                                            <span><?php _e('Sign up','pntheme'); ?></span>
                                        </a>
                                        <a href="<?php echo $plugin->get_page('login'); ?>" class="user-bar__link js_window_login active link-login">
                                            <span class="header__account-icon header__account-icon--mobile"><?php echo premiumexchanger_get_svg(array('icon' => 'logout')); ?></span>
                                            <span><?php _e('Sign in','pntheme'); ?></span>
                                        </a>
                                    <?php }?>
                                </div>
                            </div>
                        <?php }?>
						<div class="hidden_contacts">
						<?php if($h_change['telegram']){ ?>
                            <div class="link_wrapper">
                                <a href="tg://resolve?domain=<?php echo pn_strip_input(str_replace('@','', $h_change['telegram_link'])); ?>" class="contacts_link contacts-tg"  target="_blank">
                                    <span class="header__contacts-icon header__contacts-icon--mobile"><?php echo premiumexchanger_get_svg(array('icon' => 'tg')); ?></span>
                                    <span> <?php _e('Telegram','pntheme'); ?></span>
                                </a>
                            </div>
                        <?php } ?>
                        <?php if($h_change['email']){ ?>
                            <div class="link_wrapper">
                                <a href="mailto:<?php echo $h_change['email'] ?>" class="contacts_link contacts-mail" target="_blank">
                                    <span class="header__contacts-icon header__contacts-icon--mobile"><?php echo premiumexchanger_get_svg(array('icon' => 'email')); ?></span>
                                    <span><?php echo $h_change['email'] ?></span>
                                </a>
                            </div>
                        <?php } ?>
                        <?php if($h_change['skype']){ ?>
                            <div class="link_wrapper">
                                <a href="skype:<?php echo $h_change['skype'] ?>" class="contacts_link contacts-skype" target="_blank">
                                    <span class="header__contacts-icon header__contacts-icon--mobile"><?php echo premiumexchanger_get_svg(array('icon' => 'skype')); ?></span>
                                    <span><?php echo $h_change['skype'] ?></span>
                                </a>
                            </div>
                        <?php } ?>
						</div>
					</nav>
				</div>
			</div>
				<?php if($h_change['timetable']){ ?>
                    <div class="header_timetable">
                        <div class="header_timetable_ins">
                            <?php echo apply_filters('comment_text',$h_change['timetable']); ?>
                        </div>
                    </div>
                <?php } ?>
		</div>
	</div>

	<main class="main no-margin">
        <?php if ($ho_change['banners_block']) { ?>
            <div class="swiper mySwiper banner-swiper container">
                <div class="swiper-wrapper">
                <?php for ($i=1; $i<=2; $i++) { ?>
                    <?php if ($ho_change['b'.$i.'_title'] || $ho_change['b'.$i.'_text']) { ?>
                        <a href="<?php echo $ho_change['b'.$i.'_link'] ?>" class="swiper-slide banner-slide">
                            <img class="swiper-img" src="<?php echo $ho_change['b'.$i.'_img'] ?>" alt="logo">
                            <div class="banner_textblock">
                                <span class="banner_title"><?php echo $ho_change['b'.$i.'_title'] ?></span>
                                <span class="banner_text"><?php echo $ho_change['b'.$i.'_text'] ?></span>
                            </div>
                            <span class="banner_link"></span>
                        </a>
                    <?php } ?>
                <?php } ?>
                </div>
                <div class="swiper-pagination"></div>
            </div>
        <?php } ?>
		<?php if(is_front_page()){ ?>
		    <div class="page-block no-padding">

		<?php } else { ?>
		    <div class="page-block container">
		<?php } ?>

        <?php //inner pages ?>
        <?php if(!is_front_page()){ ?>

            <?php //breadcrumbs ?>
            <ol class="breadcrumb mb-m-40">
                <?php the_breadcrumb(__('Currency exchange','pntheme')); ?>
                <li class="breadcrumb-item active"><?php the_breadcrumb_title(); ?></li>
            </ol>

            <?php //h1 ?>
            <h1 class="main-title"><?php the_breadcrumb_title(); ?></h1>

            <?php //main content and aside ?>
            <div class="inner grid premium-3-1">
                <?php //main content ?>

                <?php if (is_page_template('pn-notsidebar.php')) { ?>
                    <div class="inner-content span--lg-4 span--md-2">
                <?php } else { ?>
                    <div class="inner-content span--lg-3 span--md-2">
                <?php } ?>
        <?php } ?>
