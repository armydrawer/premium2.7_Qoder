<?php
if (!defined('ABSPATH')) exit();

/*
title: [en_US:]Up arrow[:en_US][ru_RU:]Стрелочка наверх[:ru_RU]
description: [en_US:]Up arrow[:en_US][ru_RU:]Стрелочка наверх[:ru_RU]
version: 2.7.1
category: [en_US:]Other[:en_US][ru_RU:]Остальное[:ru_RU]
cat: other
*/


add_filter('pn_theme_footer_option', 'def_pn_theme_footer_topbutton_option', 100);
function def_pn_theme_footer_topbutton_option($options) {
    global $premiumbox;

    $noptions = [];

    $noptions['show_button'] = [
        'view' => 'select',
        'title' => __('Up arrow', 'pn'),
        'options' => [
            0 => __('Right', 'pn'),
            1 => __('Left', 'pn'),
            2 => __('Hide button', 'pn')
        ],
        'default' => $premiumbox->get_option('topbutton', 'show_button'),
        'name' => 'show_button',
        'work' => 'int',
    ];

    return pn_array_insert($options, 'timetable', $noptions);
}


add_action('premium_action_pn_theme_footer', 'def_premium_action_pn_theme_footer_topbutton');
function def_premium_action_pn_theme_footer_topbutton() {
    global $premiumbox;

    _method('post');

    $form = new PremiumForm();
    $form->send_header();

    pn_only_caps(['administrator']);

    $options_data = $form->strip_options('pn_theme_footer_option');
    $keys = ['show_button'];

    foreach ($options_data as $key => $val) {
        if (!in_array($key, $keys)) continue;

        $premiumbox->update_option('topbutton', $key, $val);
    }
}


add_action('wp_footer', 'wp_footer_topbutton');
function wp_footer_topbutton() {
    global $premiumbox;

    $show = intval($premiumbox->get_option('topbutton', 'show_button'));
    if (2 == $show) return;

    $style = $show ? 'toleft' : 'toright';
    ?>
    <div id="topped" class="js_to_top <?= $style ?>"><span></span></div>

    <script type="text/javascript">
        jQuery(function ($) {
            const $js_to_top = $('.js_to_top');

            $(window).on('scroll', function () {
                if ($(window).scrollTop() > 200) {
                    $js_to_top.show();
                } else {
                    $js_to_top.hide();
                }
            });

            $js_to_top.on('click', function () {
                $('body, html').animate({scrollTop: 0}, 500);
            });
        });
    </script>
    <?php
}
