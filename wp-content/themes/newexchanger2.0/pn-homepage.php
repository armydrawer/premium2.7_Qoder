<?php
if (!defined('ABSPATH')) {
    exit();
}

/*

Template Name: Home page template

*/

get_header();

$ho_change = get_theme_option('ho_change', array(
    'wtitle',
    'wtext',
    'ititle',
    'itext',
    'blocknews',
    'catnews',
    'partners',

    'advantages',
    'advtitle',
    'advsubtitle',

    'blocreviews',
    'reserve',
    'hidecurr',
    'lastobmen',
    'showparsers',

    'info_title',
    'info_text',
    'iimg',

    'table_title',
));

$plugin = get_plugin_class();
?>
<div class="container">
    <div class="homepage_wrap">

        <?php //notification block ?>
        <?php if (strlen($ho_change['wtext']) > 0) { ?>
            <div class="homepage-disclaimer container">
                <div class="disclaimer">
                    <div class="disclaimer__wrapper">
                        <?php if($ho_change['wtitle']) { ?>
                            <h2 class="h2-title"><?php echo pn_strip_input($ho_change['wtitle']); ?></h2>
                        <?php } ?>
                        <?php echo apply_filters('the_content', $ho_change['wtext']); ?>
                    </div>
                </div>
            </div>
        <?php } ?>

        <?php //main exchange plugin?>
        <div class="main-form container">
            <div class="xchange_table_wrap">
                <div class="homechange">
                    <?php if (function_exists('the_exchange_home')) {
                        the_exchange_home();
                    }  ?>
                </div>
            </div>
            <?php if (function_exists('the_exchange_widget')) {
                the_exchange_widget();
            } ?>
        </div>

        <?php //home page text block ?>
        <?php if (strlen($ho_change['itext']) > 0) { ?>
            <section class="section hometext">
                <div class="container">
                    <div class="hometext-wrapper_ins">
                        <div class="hometext-wrapper">
                            <div class="hometext-wrapper__text">
                                <?php if($ho_change['ititle']) { ?>
                                    <h2 class="hometext-wrapper__title mb-20"><?php echo pn_strip_input($ho_change['ititle']); ?></h2>
                                <?php } ?>
                                <?php echo apply_filters('the_content', $ho_change['itext']); ?>
                            </div>
                            <?php if($ho_change['iimg']){ ?>
                                <div class="hometext-wrapper__img" style="background: url(<?php echo $ho_change['iimg']; ?>); background-repeat: no-repeat; background-size: contain; background-position: bottom center;"></div>
                            <?php }?>
                        </div>
                    </div>
                </div>
            </section>
        <?php } ?>

        <?php //reviews block ?>
        <?php
        if ($ho_change['blocreviews'] == 1 and function_exists('list_reviews')) {
            $review_url = $plugin->get_page('reviews');
            $data_posts = list_reviews(12);
        ?>
            <section class="section reviews">
                <div class="section_ins container">
                    <div class="section_content_wrapper">

                        <div class="section__title mb-20">
                            <h2 class="h2-title"><?php _e('Reviews', 'pntheme'); ?></h2>
                            <a href="<?php echo $review_url; ?>" class="btn btn-secondary btn-reviews"><span><?php _e('All reviews', 'pntheme'); ?></span><span class="btn-secondary__arrow"></span></a>
                        </div>
                        <div class="swiper reviewsSwiper">
                            <div class="swiper-wrapper">
                                <!--                 <div class="grid col--lg-3"> -->
                                <?php
                                $reviews_date_format = apply_filters('reviews_date_format', get_option('date_format') . ', ' . get_option('time_format'));
                                $r = 0;
                                foreach ($data_posts as $item) {
                                    $r++;
                                    $cl = '';
                                    if ($r % 4 == 0) {
                                        $cl = 'last_item';
                                    }
                                    $site = esc_url($item->user_site);
                                    $site1 = $site2 = '';
                                    if ($site) {
                                        $site1 = '<a href="' . $site . '" rel="nofollow noreferrer noopener" target="_blank">';
                                        $site2 = '</a>';
                                    }
                                ?>
                                    <div class="swiper-slide feedback feedback-slide">
                                        <div class="feedback__title">
                                            <span class="feedback__name"><?php echo $site1 . pn_strip_input($item->user_name) . $site2; ?></span>
                                            <span class="time"><?php echo get_pn_time($item->review_date, $reviews_date_format); ?></span>
                                        </div>
                                        <div class="feedback__text"><?php echo wp_trim_words(pn_strip_input($item->review_text), 15); ?></div>
                                    </div>

                                <?php } ?>
                            </div>
                            <div class="section-buttons">
                                <div class="reviews-button-navigation">
                                    <div class="swiper-button-next reviews-button-nav reviews-button-prev"></div>
                                    <div class="swiper-button-prev reviews-button-nav reviews-button-next"></div>
                                </div>
                            </div>
                        </div>
                        <!--                 </div> -->
                    </div>
                </div>
            </section>
        <?php } ?>

        <?php //last exchanges block ?>
        <?php if ($ho_change['lastobmen'] == 1 and function_exists('get_last_bids')) { ?>
            <section class="section last-exchanges">
                <div class="section_ins container">
                    <div class="section_content_wrapper">

                        <div class="section__title">
                            <h2 class="h2-title"><?php _e('Last exchanges', 'pntheme'); ?></h2>
                        </div>

                        <div class="swiper lastExchanges">
                            <div class="swiper-wrapper">
                                <!-- <div class="grid col--lg-3"> -->
                                <?php
                                $r = 0;
                                $last_bids = get_last_bids('success', 4);
                                foreach ($last_bids as $last_bid) {
                                    $r++;
                                    $cl = '';
                                    if ($r == 3) {
                                        $cl = 'last_item';
                                    }
                                ?>
                                    <div class="crypto swiper-slide">
                                        <div class="crypto__direction">
                                            <div class="direction">
                                                <span class="direction__title"><?php echo $last_bid['vtype_give']; ?></span>
                                                <span class="direction__arrow">→</span>
                                                <span class="direction__title"><?php echo $last_bid['vtype_get']; ?></span>
                                            </div>
                                            <span class="time"><?php echo $last_bid['createdate']; ?></span>
                                        </div>
                                        <div class="crypto__history">
                                            <div class="coin">
                                                <div class="coin__logo"><img src="<?php echo $last_bid['logo_give']; ?>" alt=""></div>
                                                <div class="coin__info">
                                                    <span><?php echo is_out_sum($last_bid['sum_give'], $last_bid['decimal_give'], 'all'); ?></span>
                                                    <span class="coin__name"><?php echo $last_bid['vtype_give']; ?></span>
                                                </div>
                                            </div>
                                            <div class="coin_arrow"></div>
                                            <div class="coin">
                                                <div class="coin__logo"><img src="<?php echo $last_bid['logo_get']; ?>" alt=""></div>
                                                <div class="coin__info">
                                                    <span><?php echo is_out_sum($last_bid['sum_get'], $last_bid['decimal_get'], 'all'); ?></span>
                                                    <span class="coin__name"><?php echo $last_bid['vtype_get']; ?></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php } ?>
                            </div>
                            <div class="section-buttons">
                                <div class="reviews-button-navigation">
                                    <div class="swiper-button-next reviews-button-nav reviews-button-prev"></div>
                                    <div class="swiper-button-prev reviews-button-nav reviews-button-next"></div>
                                </div>
                            </div>
                        </div>

                    </div>
                    <!-- </div> -->
                </div>
            </section>
        <?php } ?>

        <?php //news block ?>
        <?php
        if ($ho_change['blocknews'] == 1) {
            $blog_url = get_blog_url();

            $catnews = intval($ho_change['catnews']);
            $args = array(
                'post_type' => 'post',
                'posts_per_page' => 10
            );
            if ($catnews) {
                $args['cat'] = $catnews;
            }

            $data_posts = get_posts($args);
        ?>
            <section class="section news-section">
                <div class="section_ins container">
                    <div class="section_content_wrapper">

                        <div class="section__title">
                            <h2 class="h2-title"><?php _e('News', 'pntheme'); ?></h2>
                            <a href="<?php echo $blog_url ?>" class="btn btn-secondary btn-news"><span><?php _e('All news', 'pntheme'); ?></span><span class="btn-secondary__arrow"></span></a>
                        </div>
                        <div class="swiper newsSwiper">
                            <div class="swiper-wrapper">
                                <!-- <div class="grid col--lg-3"> -->
                                <?php
                                $r = 0;
                                // $date_format = get_option('date_format');
                                $date_format = ('d.m.Y h:m');
                                foreach ($data_posts as $item) {
                                    $r++;
                                    $cl = '';
                                    if ($r % 3 == 0) {
                                        $cl = 'last_item';
                                    }
                                    $image_arr = wp_get_attachment_image_src(get_post_thumbnail_id($item->ID), 'full');
                                    $image = trim(is_isset($image_arr, 0));
                                    $link = get_permalink($item->ID);
                                    $title = pn_strip_input(ctv_ml($item->post_title));
                                ?>

                                    <div class="news swiper-slide  news-slide">
                                        <?php if ($image) { ?>
                                            <a href="<?php echo $link; ?>" title="<?php echo $title; ?>" class="news__img"><img src="<?php echo $image; ?>" alt="<?php echo $title; ?>" /></a>
                                        <?php } ?>
                                        <div class="news__content <?php if (!$image) { echo 'border-top'; } ?>">
                                            <div>
                                                <span class="time"><?php echo get_the_time($date_format, $item->ID); ?></span>
                                                <a href="<?php echo $link; ?>" class="h4-title">
                                                    <span class="news__title"><?php echo pn_strip_input(ctv_ml($item->post_title)); ?></span>
                                                    <span></span>
                                                </a>
                                                <p><?php echo get_pn_excerpt($item, 10); ?></p>
                                            </div>
                                            <a href="<?php echo $link; ?>" class="more"><?php _e('Read more', 'pntheme'); ?></a>
                                        </div>
                                    </div>


                                <?php } ?>
                            </div>
                            <div class="section-buttons">
                                <div class="reviews-button-navigation">
                                    <div class="swiper-button-next reviews-button-nav reviews-button-prev"></div>
                                    <div class="swiper-button-prev reviews-button-nav reviews-button-next"></div>
                                </div>
                            </div>
                            <!-- </div> -->
                        </div>

                    </div>
                </div>
            </section>
        <?php } ?>

        <?php //advantages block ?>
        <?php
            if (function_exists('get_advantages') and $ho_change['advantages'] == 1) {
                $advantages = get_advantages();
                if (is_array($advantages) and count($advantages) > 0) {
            ?>

            <section class="section mb-60 mb-m-40 advantages">
                <div class="section_ins container">
                    <div class="section_content_wrapper">
                            <!--                     <div class="advantages__titles"> -->
                            <!--                         <h2 class="h2-title advantages__title"><?php echo pn_strip_input($ho_change['advtitle']); ?></h2> -->
                            <!--                         <h2 class="h2-title advantages__subtitle"><?php echo pn_strip_input($ho_change['advsubtitle']); ?></h2> -->
                            <!--                     </div> -->
                        <div class="swiper advantagesSwiper">
                            <div class="swiper-wrapper">
                                <!--                     <div class="grid grid-advantages col--lg-3"> -->

                                <?php
                                foreach ($advantages as $item) {
                                    $link = get_advantage_url($item);
                                    $img = get_advantage_image($item);
                                    $title = get_advantage_title($item);
                                    $content = get_advantage_content($item);
                                ?>
                                    <div class="icon-card swiper-slide advantages-slide">
                                        <?php if ($img) { ?>
                                            <div class="icon-card__icon"><img src="<?php echo $img; ?>" alt=""></div>
                                        <?php } ?>
                                        <div class="icon-card__text">
                                            <?php if ($link) { ?>
                                                <h3 class="icon-card__title h4-title"><a href="<?php echo $link ?>"><?php echo $title; ?></a></h3>
                                            <?php } else { ?>
                                                <h3 class="icon-card__title h4-title"><?php echo $title; ?></h3>
                                            <?php } ?>
                                            <p><?php echo $content; ?></p>
                                        </div>
                                    </div>
                                <?php } ?>
                            </div>
                            <div class="section-buttons">
                                <div class="reviews-button-navigation">
                                    <div class="swiper-button-next reviews-button-nav reviews-button-prev"></div>
                                    <div class="swiper-button-prev reviews-button-nav reviews-button-next"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        <?php }
        } ?>

        <?php //reserves block ?>
        <?php
        if (function_exists('list_view_currencies') and $ho_change['reserve'] == 1) {
            $hidecurr = explode(',', is_isset($ho_change, 'hidecurr'));
            $currencies = list_view_currencies('', $hidecurr);
            if (count($currencies) > 0) {
        ?>

                <section class="section home-reserve">
                    <div class="section_ins container">
                        <div class="section_content_wrapper">

                            <div class="section__title">
                                <h2 class="h2-title"><?php _e('Currency reserve', 'pntheme'); ?></h2>
                                <a href="#" class="btn btn-secondary btn-reserve" data-show-text="<?php _e('Show all', 'pntheme'); ?>" data-hide-text="<?php _e('Hide', 'pntheme'); ?>"><span><?php _e('Show all', 'pntheme'); ?></span><span class="btn-secondary__arrow"></span></a>
                            </div>

                            <div class="grid col--lg-4">
                                <?php
                                $r = 0;
                                foreach ($currencies as $currency) {
                                    $r++;
                                ?>
                                    <div class="reserve-item <?php echo $r > 4 ? 'to-hide hidden' : '' ?>">
                                        <div class="coin">
                                            <div class="coin__logo"><img src="<?php echo $currency['logo']; ?>" alt=""></div>
                                            <div class="coin__info">
                                                <span class="coin__amount"><?php echo is_out_sum($currency['reserv'], $currency['decimal'], 'reserv'); ?></span>
                                                <span class="coin__name"><?php echo $currency['title']; ?></span>
                                            </div>
                                        </div>
                                    </div>

                                <?php } ?>
                            </div>
                            <!--                             <div class="section-buttons"> -->
                            <!--                                  -->
                            <!--                             </div> -->
                        </div>
                    </div>
                </section>

        <?php
            }
        }
        ?>

        <?php //rates block
        ?>
        <?php
        // if (function_exists('get_parser_list')) {
        if (false) {
            $items = get_parser_list(explode(',', $ho_change['showparsers']));
            if (is_array($items) and count($items) > 0) {
        ?>
                <section class="home_prate_wrap filled mb-m-40 mb-12">
                    <div class="home_prate_ins">

                        <div class="home_prate_block">
                            <div class="home_prate_blocktitle h2-title mb-20"><?php _e('Rates', 'pntheme'); ?></div>

                            <div class="home_prate_div_wrap">
                                <div class="home_prate_div">
                                    <?php
                                    foreach ($items as $item) {
                                    ?>
                                        <div class="home_prate_one">
                                            <div class="home_prate_source"><?php echo get_parser_source($item); ?></div>
                                            <div class="home_prate_rates">
                                                <span class="rate_value"><?php echo get_parser_rate_give($item, 8); ?></span>
                                                <span class="rate_currency"><?php echo get_parser_give($item); ?></span>
                                                <span clas="equals">=</span>
                                                <span class="rate_value"><?php echo get_parser_rate_get($item, 8); ?></span>
                                                <span class="rate_currency"><?php echo get_parser_get($item); ?></span>
                                            </div>
                                        </div>
                                    <?php
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>

                    </div>
                </section>
        <?php
            }
        }
        ?>

        <?php if (false) { ?>
            <?php //statistic block
            ?>
            <?php

            /* кол-во пользователей на сайте */
            $count_user = get_user_for_site();

            /* кол-во пользователей на сайте начиная с даты */
            // $count_user = get_user_for_site('2015-10-22 14:25:04');

            /* кол-во обменов со статусом success */
            $count_exchange = get_count_exchanges();

            /* кол-во обменов со статусом success, начиная с даты */
            // $count_exchange = get_count_exchanges('2015-10-22 14:25:04');

            /* кол-во отзывов */
            function get_count_reviews()
            {
                global $wpdb;
                $query = $wpdb->query("CHECK TABLE " . $wpdb->prefix . "reviews");
                if ($query == 1) {
                    return $wpdb->get_var("SELECT COUNT(id) FROM " . $wpdb->prefix . "reviews WHERE auto_status = '1' AND review_status = 'publish'");
                }
                return '0';
            }

            $count_reviews = get_count_reviews();

            /* кол-во направлений обмена, не настоящая цифра */
            function get_count_directions()
            {
                global $wpdb;
                return $wpdb->get_var("SELECT COUNT(id) FROM " . $wpdb->prefix . "directions WHERE direction_status IN('1','2')");
            }

            $count_directions = get_count_directions();
            ?>
            <section class="home_stat section mb-60 mb-m-40">
                <div class="home_stat_ins container">
                    <div class="section_content_wrapper">
                        <div class="section__title">
                            <h2 class="h2-title mb-20"><?php _e('Statistic', 'pntheme'); ?></h2>
                        </div>
                        <div class="items_wrapper">
                            <div class="stat_item stat_users">
                                <div class="value"><?php echo $count_user ?></div>
                                <div class="title"><?php _e('Count users', 'pntheme'); ?></div>
                            </div>
                            <div class="stat_item stat_exchanges">
                                <div class="value"><?php echo $count_exchange ?></div>
                                <div class="title"><?php _e('Count exchanges', 'pntheme'); ?></div>
                            </div>
                            <div class="stat_item stat_reviews">
                                <div class="value"><?php echo $count_reviews ?></div>
                                <div class="title"><?php _e('Count reviews', 'pntheme'); ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        <?php } ?>


        <?php //rates block v.2
        ?>
        <?php
        if (function_exists('get_parser_list')) {
            $items = get_parser_list(explode(',', $ho_change['showparsers']));
            if (is_array($items) and count($items) > 0) {
        ?>
                <section class="section widget_cbr_div homepage_version courses-section">
                    <div class="widget_cbr_div_ins container">
                        <div class="section_content_wrapper">

                            <div class="widget_cbr_div_title">
                                <div class="widget_cbr_div_title_ins">
                                    <?php _e('Rates', 'pntheme'); ?>
                                </div>
                            </div>

                            <div class="widget_cbr_lines_wrapper">
                                <?php
                                foreach ($items as $item) {
                                ?>
                                    <div class="widget_cbr_line">
                                        <div class="widget_cbr_left">
                                            <div class="widget_cbr_title"><?php echo get_parser_give($item); ?>/<?php echo get_parser_get($item); ?></div>
                                            <div class="widget_cbr_birg"><?php echo get_parser_source($item); ?></div>
                                        </div>
                                        <div class="widget_cbr_curs">
                                            <div class="widget_cbr_onecurs"><?php echo substr(get_parser_rate_give($item, 8), 0, 12); ?> <?php echo get_parser_give($item); ?></div>
                                            <div class="widget_cbr_onecurs"><?php echo substr(get_parser_rate_get($item, 8), 0, 12); ?> <?php echo get_parser_get($item); ?></div>
                                        </div>
                                    </div>
                                <?php
                                }
                                ?>
                            </div>

                        </div>

                    </div>
                </section>
        <?php
            }
        }
        ?>

        <?php //partners block ?>
        <?php
        if (function_exists('get_partners') and $ho_change['partners'] == 1) {
            $partners = get_partners();
            if (is_array($partners) and count($partners) > 0) {
        ?>
            <section class="section partners-section">
                <div class="section_ins container">
                    <div class="section_content_wrapper">
                        <h2 class="h2-title partners-title"><?php _e('Partners', 'pntheme'); ?></h2>
                        <div class="partners">
                            <?php
                            foreach ($partners as $item) {
                                $link = get_partner_url($item);
                            ?>
                                <div class="partner_item">
                                    <?php if ($link) { ?><a href="<?php echo $link; ?>" rel="nofollow noreferrer noopener" target="_blank"><?php } ?>
                                        <img src="<?php echo get_partner_logo($item); ?>" alt="" />
                                        <?php if ($link) { ?></a><?php } ?>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            </section>
        <?php
            }
        }
        ?>

    </div>
</div>

<?php

get_footer();
