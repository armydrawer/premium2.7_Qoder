<?php
if (!defined('ABSPATH')) {
    exit();
}

add_action('admin_menu', 'admin_menu_theme_promo');
function admin_menu_theme_promo()
{
    $plugin = get_plugin_class();

    add_submenu_page("themes.php", __('Promo', 'pntheme'), __('Promo', 'pntheme'), 'administrator', "pn_theme_promo", array($plugin, 'admin_temp'));
}

add_action('pn_adminpage_title_pn_theme_promo', 'def_adminpage_title_pn_theme_promo');
function def_adminpage_title_pn_theme_promo($page)
{
    _e('Promo', 'pntheme');
}

add_filter('pn_theme_promo_option', 'def_pn_theme_promo_option', 1);
function def_pn_theme_promo_option($options)
{
    global $wpdb;

    $change = get_option('promo_change');

    $options['top_title'] = array(
        'view' => 'h3',
        'title' => __('Promo block', 'pntheme'),
        'submit' => __('Save', 'pntheme'),
        'colspan' => 2,
    );
    $options['showpromo'] = array(
        'view' => 'select',
        'title' => __('Promo display', 'pntheme'),
        'options' => array('0' => __('hide', 'pntheme'), '1' => __('show', 'pntheme')),
        'default' => is_isset($change, 'showpromo'),
        'name' => 'showpromo',
        'work' => 'int',
    );
    $options['line1'] = array(
        'view' => 'line',
    );
    $options['promo'] = array(
        'view' => 'h3',
        'title' => __('Promo', 'pntheme'),
        'submit' => __('Save', 'pntheme'),
    );
    $options['promo_title'] = array(
        'view' => 'inputbig',
        'title' => __('Promo title', 'pntheme'),
        'default' => is_isset($change, 'promo_title'),
        'name' => 'promo_title',
        'work' => 'input',
        'ml' => 1,
    );
    $options['promo_descr'] = array(
        'view' => 'inputbig',
        'title' => __('Promo description', 'pntheme'),
        'default' => is_isset($change, 'promo_descr'),
        'name' => 'promo_descr',
        'work' => 'input',
        'ml' => 1,
    );
    $options['promo_btn-link'] = array(
        'view' => 'inputbig',
        'title' => __('Button link', 'pntheme'),
        'default' => is_isset($change, 'promo_btn-link'),
        'name' => 'promo_btn-link',
        'work' => 'input',
        'ml' => 1,
    );
    $options['promo_btn-text'] = array(
        'view' => 'inputbig',
        'title' => __('Button text', 'pntheme'),
        'default' => is_isset($change, 'promo_btn-text'),
        'name' => 'promo_btn-text',
        'work' => 'input',
        'ml' => 1,
    );
    $options['promo_banner_img'] = array(
        'view' => 'uploader',
        'title' => __('Image', 'pn'),
        'default' => is_isset($change, 'promo_banner_img'),
        'name' => 'promo_banner_img',
        'work' => 'input',
    );
    $options['line2'] = array(
        'view' => 'line',
    );
    $options['timer'] = array(
        'view' => 'h3',
        'title' => __('Timer', 'pntheme'),
        'submit' => __('Save', 'pntheme'),
    );
    $options['timer_title'] = array(
        'view' => 'h3',
        'title' => __('Timer', 'pntheme'),
        'submit' => __('Save', 'pntheme'),
    );
    $options['timer_title'] = array(
        'view' => 'inputbig',
        'title' => __('Timer title', 'pntheme'),
        'default' => is_isset($change, 'timer_title'),
        'name' => 'timer_title',
        'work' => 'input',
        'ml' => 1,
    );
    $options['days'] = array(
        'view' => 'date',
        'title' => __('Date end', 'pntheme'),
        'default' => is_isset($change, 'days'),
        'name' => 'days',
        'work' => 'input',
    );
    $options['line3'] = array(
        'view' => 'line',
    );
    $options['reviews'] = array(
        'view' => 'h3',
        'title' => __('Reviews', 'pntheme'),
        'submit' => __('Save', 'pntheme'),
    );
    $options['promo_reviews_title'] = array(
        'view' => 'inputbig',
        'title' => __('Promo reviews title', 'pntheme'),
        'default' => is_isset($change, 'promo_reviews_title'),
        'name' => 'promo_reviews_title',
        'work' => 'input',
        'ml' => 1,
    );
    $options['promo_reviews_count'] = array(
        'view' => 'inputbig',
        'title' => __('Promo reviews count', 'pntheme'),
        'default' => is_isset($change, 'promo_reviews_count'),
        'name' => 'promo_reviews_count',
        'work' => 'input',
        'ml' => 1,
    );
    $options['line4'] = array(
        'view' => 'line',
    );
    $options['bank'] = array(
        'view' => 'h3',
        'title' => __('Promo bank', 'pntheme'),
        'submit' => __('Save', 'pntheme'),
    );
    $options['promo_bank_title'] = array(
        'view' => 'inputbig',
        'title' => __('Promo bank title', 'pntheme'),
        'default' => is_isset($change, 'promo_bank_title'),
        'name' => 'promo_bank_title',
        'work' => 'input',
        'ml' => 1,
    );
    $options['promo_bank_value'] = array(
        'view' => 'inputbig',
        'title' => __('Promo bank value', 'pntheme'),
        'default' => is_isset($change, 'promo_bank_value'),
        'name' => 'promo_bank_value',
        'work' => 'input',
        'ml' => 1,
    );

    $options['line45'] = array(
        'view' => 'line',
    );
    $options['forum_list'] = array(
        'view' => 'h3',
        'title' => __('Promo rules', 'pntheme'),
        'submit' => __('Save', 'pntheme'),
    );
    $options['promo_rules_textarea'] = array(
        'view' => 'editor',
        'title' => __('HTML', 'pntheme'),
        'default' => is_isset($change, 'promo_rules_textarea'),
        'name' => 'promo_rules_textarea',
        'work' => 'text',
        'rows' => '10',
        'media' => 1,
        'formatting_tags' => 1,
        'ml' => 1,
    );
    $options['line44'] = array(
        'view' => 'line',
    );
    $options['rules'] = array(
        'view' => 'h3',
        'title' => __('Forum list', 'pntheme'),
        'submit' => __('Save', 'pntheme'),
    );
    $options['promo_sites-review_textarea'] = array(
        'view' => 'editor',
        'title' => __('HTML', 'pntheme'),
        'default' => is_isset($change, 'promo_sites-review_textarea'),
        'name' => 'promo_sites-review_textarea',
        'work' => 'text',
        'rows' => '10',
        'media' => 1,
        'formatting_tags' => 1,
        'ml' => 1,
    );

    return $options;
}

add_action('pn_adminpage_content_pn_theme_promo', 'def_pn_adminpage_content_pn_theme_promo');
function def_pn_adminpage_content_pn_theme_promo()
{

    $form = new PremiumForm();
    $params_form = array(
        'filter' => 'pn_theme_promo_option',
        'method' => 'ajax',
    );
    $form->init_form($params_form);
}

function pn_theme_promo_inbids($change)
{
    $bid_status_list = apply_filters('bid_status_list', array());
    $inbids = is_isset($change, 'inbids');
    if (!is_array($inbids)) {
        $inbids = array();
    }
?>
    <div class="premium_standart_line">
        <div class="premium_stline_left">
            <div class="premium_stline_left_ins"><?php _e('Block in bids if status', 'pntheme'); ?></div>
        </div>
        <div class="premium_stline_right">
            <div class="premium_stline_right_ins">
                <div class="premium_wrap_standart">
                    <?php
                    $scroll_lists = array();
                    if (is_array($bid_status_list)) {
                        foreach ($bid_status_list as $key => $val) {
                            $checked = 0;
                            if (in_array($key, $inbids)) {
                                $checked = 1;
                            }
                            $scroll_lists[] = array(
                                'title' => $val,
                                'checked' => $checked,
                                'value' => $key,
                            );
                        }
                    }
                    echo get_check_list($scroll_lists, 'inbids[]', '', '', 1);
                    ?>
                    <div class="premium_clear"></div>
                </div>
            </div>
        </div>
        <div class="premium_clear"></div>
    </div>
<?php
}

add_action('premium_action_pn_theme_promo', 'def_premium_action_pn_theme_promo');
function def_premium_action_pn_theme_promo()
{
    global $wpdb;

    only_post();
    pn_only_caps(array('administrator'));

    $form = new PremiumForm();
    $data = $form->strip_options('pn_theme_promo_option', 'post');

    $change = get_option('promo_change');
    if (!is_array($change)) {
        $change = array();
    }

    $change['showexch'] = $data['showexch'];
    $change['showpromo'] = $data['showpromo'];

    $change['promo_title'] = $data['promo_title'];
    $change['promo_descr'] = $data['promo_descr'];
    $change['promo_btn-link'] = $data['promo_btn-link'];
    $change['promo_btn-text'] = $data['promo_btn-text'];
    $change['promo_banner_img'] = pn_strip_input(is_param_post('promo_banner_img'));

    $change['timer_title'] = $data['timer_title'];
    $change['days'] = $data['days'];
    $change['promo_reviews_title'] = $data['promo_reviews_title'];
    $change['promo_reviews_count'] = $data['promo_reviews_count'];
    $change['promo_bank_title'] = $data['promo_bank_title'];
    $change['promo_bank_value'] = $data['promo_bank_value'];

    $change['promo_rules_textarea'] = $data['promo_rules_textarea'];
    $change['promo_sites-review_textarea'] = $data['promo_sites-review_textarea'];



    $change['inbids'] = $data['inbids'];

    update_option('promo_change', $change);

    $back_url = is_param_post('_wp_http_referer');
    $back_url .= '&reply=true';

    $form->answer_form($back_url);
}
