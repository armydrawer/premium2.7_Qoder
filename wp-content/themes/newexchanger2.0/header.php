<?php if (!defined('ABSPATH')) {
    exit();
}

$ui = wp_get_current_user();
$user_id = intval($ui->ID);

$plugin = get_plugin_class();
$h_change = get_theme_option('h_change', array(
    'fixheader',
    'linkhead',
    'phone',
    'icq',
    'skype',
    'email',
    'telegram_link',
    'telegram',
    'telegram_link2',
    'telegram2',
    'telegram_link3',
    'telegram3',
    'viber',
    'inst',
    'whatsapp',
    'jabber',
    'timetable',
    'hideloginbutton',

    'logo-mobile',
    'matrix',
    'email_text',
));

$ho_change = get_theme_option('ho_change', array(
    'banners_block',
    'b1_title',
    'b1_text',
    'b1_link',
    'b1_img',
    'b1_mobileimg',

    'b2_title',
    'b2_text',
    'b2_link',
    'b2_img',
    'b2_mobileimg',
));

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
    <meta name="apple-touch-fullscreen" content="yes" />

    <link rel="profile" href="http://gmpg.org/xfn/11">

    <meta charset="<?php bloginfo('charset'); ?>">
    <title><?php wp_title(); ?></title>

    <?php
    $change = get_option('h_change');
    $mode   = isset($change['switcher']) ? (int)$change['switcher'] : 0; // 0=light,1=dark,2=switcher
    ?>
    <script>
    (function () {
      try {
        var mode  = <?php echo (int)$mode; ?>;
        var saved = localStorage.getItem('theme'); // 'light' | 'dark' | null
        var theme;
        if (mode === 0) theme = 'light';
        else if (mode === 1) theme = 'dark';
        else {
          theme = (saved === 'light' || saved === 'dark')
            ? saved
            : (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
        }
        var el = document.documentElement;
        el.classList.remove('light','dark');
        el.classList.add(theme);
      } catch(e) {}
    })();
    </script>

    <style>
      html { background:#F3F7FF; }
      html.dark { background:#232323; color-scheme: dark; }
      html.light { background:#F3F7FF; color-scheme: light; }
    </style>

    <?php wp_head(); ?>

    <?php
    $change = get_option('h_change');
    $mode   = isset($change['switcher']) ? (int)$change['switcher'] : 0; // 0=light,1=dark,2=switcher
    ?>

</head>

<body <?php body_class(); ?> data-theme-mode="<?php echo esc_attr($mode); ?>">
    <?php do_action('pn_header_theme'); ?>
    <div class="user-bar <?php if ($h_change['fixheader'] == 1) echo 'sticky ' ?> <?php if (is_admin_bar_showing()) echo 'with-adminbar ' ?>">
        <div class="container user-bar__container">
            <div class="user-bar__left">
                <?php the_lang_list('tolbar_lang'); ?>
                <button class="header__dark-mode"></button>
            </div>
            <div class="contacts_wrapper">
                <div class="header__contacts">
                    <?php if ($h_change['telegram']) { ?>
                        <a href="tg://resolve?domain=<?php echo pn_strip_input(str_replace('@', '', $h_change['telegram_link'])); ?>" class="header__contacts-link" target="_blank">
                            <span class="header__contacts-icon contacts-tg"></span>
                            <span><?php echo $h_change['telegram'] ?></span>
                        </a>
                    <?php } ?>
                    <!--                         <?php if ($h_change['telegram2']) { ?> -->
                    <!--                             <a href="tg://resolve?domain=<?php echo pn_strip_input(str_replace('@', '', $h_change['telegram_link2'])); ?>" class="header__contacts-link" target="_blank"> -->
                    <!--                                 <span class="header__contacts-icon contacts-tg"></span> -->
                    <!--                                 <span><?php echo $h_change['telegram2'] ?></span> -->
                    <!--                             </a> -->
                    <!--                         <?php } ?> -->
                    <?php if ($h_change['email']) { ?>
                        <a href="mailto:<?php echo $h_change['email'] ?>" class="header__contacts-link" target="_blank">
                            <span class="header__contacts-icon contacts-email"></span>
                            <span><?php echo $h_change['email'] ?></span>
                        </a>
                    <?php } ?>
                    <?php if ($h_change['skype']) { ?>
                        <a href="skype:<?php echo $h_change['skype'] ?>" class="header__contacts-link" target="_blank">
                            <span class="header__contacts-icon contacts-skype"></span>
                            <span><?php echo $h_change['skype'] ?></span>
                        </a>
                    <?php } ?>
                </div>
            </div>
            <div class="lang_auth_wrapper">
                <?php if ($hideloginbutton != 1) { ?>
                    <div class="header__auth">
                        <div class="auth-wrapper">
                            <?php if ($user_id) { ?>
                                <a href="<?php echo $plugin->get_page('account'); ?>" class="user-bar__link active link-account">
                                    <!--                                             <span class="auth-wrapper__icon link-account__icon"></span> -->
                                    <span><?php _e('Account', 'pntheme'); ?></span>
                                </a>
                                <a href="<?php echo get_pn_action('logout', 'get'); ?>" class="user-bar__link link-logout">
                                    <!--                                             <span class="auth-wrapper__icon link-logout__icon"></span> -->
                                    <span><?php _e('Exit', 'pntheme'); ?></span>
                                </a>
                            <?php } else { ?>
                                <a href="<?php echo $plugin->get_page('register'); ?>" class="user-bar__link active js_window_join link-register">
                                    <!--                                             <span class="auth-wrapper__icon link-register__icon"></span> -->
                                    <span><?php _e('Sign up', 'pntheme'); ?></span>
                                </a>
                                <a href="<?php echo $plugin->get_page('login'); ?>" class="user-bar__link js_window_login link-login">
                                    <!--                                             <span class="auth-wrapper__icon link-login__icon"></span> -->
                                    <span><?php _e('Sign in', 'pntheme'); ?></span>
                                </a>
                            <?php } ?>
                        </div>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>
        <!--     <div class="nav-wrapper "> -->

        <div class="navigation <?php if ($h_change['fixheader'] == 1) echo 'sticky ' ?> <?php if (is_admin_bar_showing()) echo 'with-adminbar ' ?>">
            <div class="container navigation__container">
                <div class="navigation__wrapper">
                    <div class="header__info">
                        <?php if ($h_change['linkhead'] == 1 and !is_front_page() or $h_change['linkhead'] != 1) { ?>
                            <a class="header__logo header__logo-desktop" href="<?php echo get_site_url_ml(); ?>">
                            <?php } ?>

                            <?php
                            $logo = get_logotype();
                            $textlogo = get_textlogo();
                            if ($logo) {
                            ?>
                                <img class="header__logo-desktop" src="<?php echo $logo; ?>" alt="" />
                            <?php } elseif ($textlogo) { ?>
                                <?php echo $textlogo; ?>
                            <?php } else {
                                $textlogo = str_replace(array('http://', 'https://', 'www.'), '', PN_SITE_URL);
                                $textlogo = rtrim($textlogo, '/');
                            ?>
                                <img src="<?php echo PN_TEMPLATEURL; ?>/images/dist/logo.svg" alt="logo">
                            <?php } ?>

                            <?php if ($h_change['linkhead'] == 1 and !is_front_page() or $h_change['linkhead'] != 1) { ?>
                            </a>
                        <?php } ?>

                        <?php if ($h_change['logo-mobile']) { ?>
                            <a class="header__logo header__logo-mobile" href="<?php echo get_site_url_ml(); ?>">
                                <img src="<?php echo $h_change['logo-mobile'] ?>" alt="logo">
                            </a>
                        <?php } ?>
                    </div>

                    <div class="info_lang_menu_wrapper info_lang_menu_wrapper--mobile">
                        <div class="topmenu_wrapper">
                            <div class="logo_operator_wrapper">
                                <?php the_lang_list('tolbar_lang'); ?>
                                <?php
                                    // status operator
                                    global $premiumbox;
                                    $show_button = $premiumbox->get_option('statuswork', 'show_button');
                                    if ($show_button) {
                                        theme_operator();
                                    }
                                ?>
                                <div class="header__dark-mode header__dark-mode--mobile "></div>
                                <button class="main-menu-btn md-visible"><span></span><span></span><span></span></button>
                            </div>

                            <nav class="main-menu">
                                <div class="main-menu__wrapper">
                                    <div class="menu-btns-wrapper">
                                        <?php the_flags_list('flags_wrapper'); ?>
                                        <button class="menu-close-btn"></button>
                                    </div>
                                    <div class="menu-items-wrapper">
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
                                            'menu_class' => 'main-menu__list',
                                            'menu_id' => '',
                                            'depth' => '3',
                                            'fallback_cb' => 'no_menu',
                                            'theme_location' => $theme_location
                                        ));
                                        ?>
                                    </div>
                                    <?php if ($h_change['timetable']) { ?>
                                        <div class="header_timetable header_timetable--mobile">
                                            <div class="header_timetable_ins">
                                                <?php echo apply_filters('comment_text', $h_change['timetable']); ?>
                                            </div>
                                        </div>
                                    <?php } ?>
                                    <div class="hidden_auth">
                                        <div class="auth-wrapper">
                                            <?php if ($user_id) { ?>
                                                <a href="<?php echo $plugin->get_page('account'); ?>" class="user-bar__link active link-account">
                                                    <span class="auth-wrapper__icon link-account__icon"></span>
                                                    <span><?php _e('Account', 'pntheme'); ?></span>
                                                </a>
                                                <a href="<?php echo get_pn_action('logout', 'get'); ?>" class="user-bar__link link-logout">
                                                    <span class="auth-wrapper__icon link-logout__icon"></span>
                                                    <span><?php _e('Exit', 'pntheme'); ?></span>
                                                </a>
                                            <?php } else { ?>
                                                <a href="<?php echo $plugin->get_page('register'); ?>" class="user-bar__link js_window_join active link-register">
                                                    <span class="auth-wrapper__icon link-register__icon"></span>
                                                    <span><?php _e('Sign up', 'pntheme'); ?></span>
                                                </a>
                                                <a href="<?php echo $plugin->get_page('login'); ?>" class="user-bar__link js_window_login link-login">
                                                    <span class="auth-wrapper__icon link-login__icon"></span>
                                                    <span><?php _e('Sign in', 'pntheme'); ?></span>
                                                </a>
                                            <?php } ?>
                                        </div>
                                    </div>
                                    <div class="hidden_contacts">
                                        <?php if ($h_change['telegram']) { ?>
                                            <div class="link_wrapper">
                                                <a href="tg://resolve?domain=<?php echo pn_strip_input(str_replace('@', '', $h_change['telegram_link'])); ?>" class="contacts_link contacts-tg" target="_blank">
                                                    <span class="contacts_mobile_icon contacts_mobile_icon-tg"></span>
                                                    <span><?php echo $h_change['telegram'] ?></span>
                                                </a>
                                            </div>
                                        <?php } ?>
                                        <!--                                         <?php if ($h_change['telegram2']) { ?> -->
                                        <!--                                             <div class="link_wrapper"> -->
                                        <!--                                                 <a href="tg://resolve?domain=<?php echo pn_strip_input(str_replace('@', '', $h_change['telegram_link2'])); ?>" class="contacts_link contacts-tg" target="_blank"> -->
                                        <!--                                                     <span class="contacts_mobile_icon contacts_mobile_icon-tg"></span> -->
                                        <!--                                                     <span><?php echo $h_change['telegram2'] ?></span> -->
                                        <!--                                                 </a> -->
                                        <!--                                             </div> -->
                                        <!--                                         <?php } ?> -->
                                        <?php if ($h_change['email']) { ?>
                                            <div class="link_wrapper">
                                                <a href="mailto:<?php echo $h_change['email'] ?>" class="contacts_link contacts-email" target="_blank">
                                                    <span class="contacts_mobile_icon contacts_mobile_icon-email"></span>
                                                    <span><?php echo $h_change['email'] ?></span>
                                                </a>
                                            </div>
                                        <?php } ?>
                                        <?php if ($h_change['skype']) { ?>
                                            <div class="link_wrapper">
                                                <a href="skype:<?php echo $h_change['skype'] ?>" class="contacts_link contacts-skype" target="_blank">
                                                    <span class="contacts_mobile_icon contacts_mobile_icon-skype"></span>
                                                    <span><?php echo $h_change['skype'] ?></span>
                                                </a>
                                            </div>
                                        <?php } ?>
                                    </div>
                                </div>
                            </nav>
                        </div>
                    </div>

                    <div class="info_lang_menu_wrapper info_lang_menu_wrapper--desktop">
                        <div class="topmenu_wrapper">
                            <button class="main-menu-btn md-visible"><span></span><span></span><span></span></button>

                            <nav class="main-menu">
                                <?php the_flags_list('flags_wrapper'); ?>
                                <div class="menu-items-wrapper">
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
                                        'menu_class' => 'main-menu__list',
                                        'menu_id' => '',
                                        'depth' => '3',
                                        'fallback_cb' => 'no_menu',
                                        'theme_location' => $theme_location
                                    ));
                                    ?>
                                </div>
                            </nav>
                        </div>
                    </div>
                    <?php if ($h_change['timetable']) { ?>
                        <div class="header_timetable header_timetable--desktop">
                            <div class="header_timetable_ins">
                                <?php echo apply_filters('comment_text', $h_change['timetable']); ?>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>
        <!--     </div> -->

    <main class="main no-margin">

        <?php if ($ho_change['banners_block']) { ?>
            <div class="swiper mySwiper banner-swiper container">
                <div class="swiper-wrapper">
                    <?php for ($i = 1; $i <= 2; $i++) { ?>
                        <?php if ($ho_change['b' . $i . '_title'] || $ho_change['b' . $i . '_text']) { ?>
                            <a href="<?php echo $ho_change['b' . $i . '_link'] ?>" class="swiper-slide banner-slide">
                                <div class="banner_textblock">
                                    <?php if ($ho_change['b' . $i . '_title']) { ?>
                                        <span class="banner_title"><?php echo $ho_change['b' . $i . '_title'] ?></span>
                                    <?php } ?>
                                    <?php if ($ho_change['b' . $i . '_text']) { ?>
                                        <span class="banner_text"><?php echo $ho_change['b' . $i . '_text'] ?></span>
                                    <?php } ?>
                                    <?php if ($ho_change['b' . $i . '_img']) { ?>
                                        <img class="banner_img" src="<?php echo $ho_change['b' . $i . '_img'] ?>" alt="" />
                                    <?php } ?>
                                    <?php if ($ho_change['b' . $i . '_mobileimg']) { ?>
                                        <img class="banner_img--mobile" src="<?php echo $ho_change['b' . $i . '_mobileimg'] ?>" alt="" />
                                    <?php } ?>
                                </div>
                            </a>
                        <?php } ?>
                    <?php } ?>
                </div>
                <div class="swiper-pagination"></div>
            </div>
        <?php } ?>

        <?php if (is_front_page()) { ?>
            <div class="page-block no-padding">

            <?php } else { ?>
                <div class="page-block container">
                <?php } ?>

                <?php //inner pages
                ?>
                <?php if (!is_front_page()) { ?>
                    <?php //breadcrumbs
                    ?>
                    <ol class="breadcrumb mb-m-40">
                        <?php the_breadcrumb(__('Currency exchange', 'pntheme')); ?>
                        <li class="breadcrumb-item active"><?php the_breadcrumb_title(); ?></li>
                    </ol>
                    <div class="main-wrapper">
                        <?php //h1
                        ?>
                        <h1 class="main-title"><?php the_breadcrumb_title(); ?></h1>

                        <?php //main content and aside
                        ?>
                        <div class="inner grid premium-3-1">
                            <?php //main content
                            ?>

                            <?php if (is_page_template('pn-notsidebar.php')) { ?>
                                <div class="inner-content span--lg-4 span--md-2">
                                <?php } else { ?>
                                    <div class="inner-content span--lg-3 span--md-2">
                                    <?php } ?>

                                <?php } ?>
