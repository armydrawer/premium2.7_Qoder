<?php
if (!defined('ABSPATH')) {
    exit();
}

function theme_promo_block($place = 0)
{
    global $wpdb, $direction_data, $bids_data;

    $place = intval($place);
    $change = get_theme_option('promo_change', array(
        'showpromo',
        'inbids',
        'promo_title',
        'promo_descr',
        'promo_banner_img',
        'button',
        'button_link',
        'timer_title',
        'days',
        'promo_reviews_title',
        'promo_reviews_count',
        'promo_bank_title',
        'promo_bank_value',
        'promo_rules_title',
        'promo_rules_step1_title',
        'button_step1_link',
        'button_step1_text',
        'promo_rules_step2_title',
        'promo_rules_step3_title',
        'promo_sites-review_title',
        'review_list_subtitle',
        'review1_title',
        'btn_review1_link',
        'btn_review1_text',
        'review2_title',
        'btn_review2_link',
        'btn_review2_text',
        'review3_title',
        'btn_review3_link',
        'btn_review3_text',
        'forum_list_subtitle',
        'forum1_title',
        'btn_forum1_link',
        'btn_forum1_text',
        'forum2_title',
        'btn_forum2_link',
        'btn_forum2_text',
        'forum3_title',
        'btn_forum3_link',
        'btn_forum3_text',
        'forum4_title',
        'btn_forum4_link',
        'btn_forum4_text',
        'monitoring_list_subtitle',
        'monitoring1_title',
        'btn_monitoring1_link',
        'btn_monitoring1_text',
        'monitoring2_title',
        'btn_monitoring2_link',
        'btn_monitoring2_text',
        'promo_btn-text',
        'promo_btn-link',
        'promo_rules_textarea',
        'promo_sites-review_textarea',

    ));

    $html = '';
    $enable = 0;
    $cl = ' notwidget';
    if ($place == 0) {
        if ($change['showpromo'] == 1) {
            $enable = 1;
        }
    } elseif ($place == 1) {
        if (is_pn_page('hst')) {
            if (is_object($bids_data) and isset($bids_data->status) and is_array($change['inbids']) and in_array($bids_data->status, $change['inbids'])) {
                $enable = 1;
            }
        }
    } else {
        $enable = 1;
        $cl = '';
    }

    if ($enable) {
        $html .= '
		<div class="promo_wrap">
			<div class="promo' . $cl . '">
                <div class="promo_main">
                    <div class="promo_top">
                        <div class="promo_banner">
                            <div class="promo_banner_text">
                                <h2 class="promo_banner-title">' . $change['promo_title'] . '</h2>
                                <div class="promo_banner-descr">' . $change['promo_descr'] . '</div>
                                <a class="promo_btn" href="' . $change['promo_btn-link'] . '" target="_blank">' . $change['promo_btn-text'] . '</a>
                            </div>
                            <img class="promo_banner_img" src="' . $change['promo_banner_img'] . '"/>
                        </div>
                        <div class="promo_bottom">
                            <div class="promo_timer">';
        $date_end = trim($change['days']);
        if ($date_end and function_exists('shortcode_js_timer')) {
            $html .= '
                                <div class="promo_timer_title">' . $change['timer_title'] . '</div>
                                <div class="timer_clock">' . shortcode_js_timer('', $date_end) . '</div>
                                ';
        }
        $html .= '
                            </div>
                            <div class="promo_reviews">
                                <div class="promo_reviews_title">' . $change['promo_reviews_title'] . '</div>
                                <div class="promo_reviews_count">' . $change['promo_reviews_count'] . '</div>
                            </div>
                            <div class="promo_bank">
                                <div class="promo_bank_title">' . $change['promo_bank_title'] . '</div>
                                <div class="promo_bank_value">' . $change['promo_bank_value'] . '</div>
                            </div>
                        </div>
                    </div>
			    </div>';

                if($change['promo_rules_textarea']) { 
                    $html .= '<div class="promo_rules">
                '. $change['promo_rules_textarea'] .'
                </div>';
                 };

                if($change['promo_sites-review_textarea']) { 
                    $html .= '<div class="promo_sites-review">
                '. $change['promo_sites-review_textarea'] .'
                </div>';
                 };

			$html .='</div>
		</div>
		';
    }

    return $html;
}

add_action('widgets_init', 'themepromo_register_widgets');
function themepromo_register_widgets()
{
    class pn_themepromo_Widget extends WP_Widget
    {

        public function __construct($id_base = false, $widget_options = array(), $control_options = array())
        {
            parent::__construct('get_themepromo_reviews', __('Promo', 'pntheme'), $widget_options = array(), $control_options = array());
        }

        public function widget($args, $instance)
        {
            extract($args);

            echo theme_promo_block(2);
        }
    }

    register_widget('pn_themepromo_Widget');
}
