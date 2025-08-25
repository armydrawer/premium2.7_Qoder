<?php
if (!defined('ABSPATH')) exit();

/*
title: [en_US:]Maintenance mode[:en_US][ru_RU:]Режим тех. обслуживания[:ru_RU]
description: [en_US:]Maintenance mode[:en_US][ru_RU:]Режим тех. обслуживания[:ru_RU]
version: 2.7.1
category: [en_US:]Settings[:en_US][ru_RU:]Настройки[:ru_RU]
cat: sett
dependent: operator
*/

$path = get_extension_file(__FILE__);
$name = get_extension_name($path);

add_action('all_moduls_active_' . $name, 'bd_all_moduls_active_maintrance');
add_action('pn_plugin_activate', 'bd_all_moduls_active_maintrance');
function bd_all_moduls_active_maintrance() {
    global $wpdb;

    $table_name = $wpdb->prefix . "maintrance";
    $sql = "CREATE TABLE IF NOT EXISTS $table_name(
		`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
		`the_title` tinytext NOT NULL,
		`pages_law` longtext NOT NULL,
		`operator_status` varchar(150) NOT NULL default '-1',
		`show_text` longtext NOT NULL,
		`for_whom` int(1) NOT NULL default '0',
		PRIMARY KEY (`id`),
		INDEX (`operator_status`)
	) ENGINE=InnoDB  DEFAULT CHARSET={$wpdb->charset} AUTO_INCREMENT=1;";
    $wpdb->query($sql);

    $query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "maintrance LIKE 'for_whom'"); /* 1.6 */
    if (0 == $query) {
        $wpdb->query("ALTER TABLE " . $wpdb->prefix . "maintrance ADD `for_whom` int(1) NOT NULL default '0'");
    }

    $query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "maintrance LIKE 'pages_law'"); /* 1.6 */
    if (0 == $query) {
        $wpdb->query("ALTER TABLE " . $wpdb->prefix . "maintrance ADD `pages_law` longtext NOT NULL");
    }
}

add_filter('pn_caps', 'maintrance_pn_caps');
function maintrance_pn_caps($pn_caps) {

    $pn_caps['pn_maintrance'] = __('Use maintenance', 'pn');

    return $pn_caps;
}

add_action('admin_menu', 'admin_menu_maintrance');
function admin_menu_maintrance() {
    global $premiumbox;

    if (current_user_can('administrator') or current_user_can('pn_maintrance')) {
        add_menu_page(__('Maintenance mode', 'pn'), __('Maintenance mode', 'pn'), 'read', "pn_maintrance", array($premiumbox, 'admin_temp'), $premiumbox->get_icon_link('operator'));
        add_submenu_page("pn_maintrance", __('Settings', 'pn'), __('Settings', 'pn'), 'read', "pn_maintrance", array($premiumbox, 'admin_temp'));
        add_submenu_page("pn_maintrance", __('Maintenance mode', 'pn'), __('Maintenance mode', 'pn'), 'read', "pn_maintrance_list", array($premiumbox, 'admin_temp'));
        add_submenu_page("pn_maintrance", __('Add mode', 'pn'), __('Add mode', 'pn'), 'read', "pn_maintrance_add", array($premiumbox, 'admin_temp'));
    }

}

add_action('admin_footer', 'maintrance_adminpage_js_dashboard');
function maintrance_adminpage_js_dashboard() {
    global $premiumbox, $maintenance_widget;

    $maintenance_widget = intval($maintenance_widget);
    if ($maintenance_widget and 0 == intval($premiumbox->get_option('tech', 'maintrance'))) {
        ?>
        <script type="text/javascript">
            jQuery(function ($) {

                $(document).on('change', '#maintrance', function () {
                    var id = $(this).val();
                    var param = 'id=' + id;
                    $('#maintrance').prop('disabled', true);
                    $.ajax({
                        type: "POST",
                        url: "<?php the_pn_link('maintrance_change', 'post'); ?>",
                        data: param,
                        error: function (res, res2, res3) {
                            <?php do_action('pn_js_error_response', 'ajax'); ?>
                        },
                        success: function (res) {
                            $('#maintrance').prop('disabled', false);
                        }
                    });
                    return false;
                });

            });
        </script>
        <?php
    }
}

add_action('premium_action_maintrance_change', 'def_premium_action_maintrance_change');
function def_premium_action_maintrance_change() {
    global $premiumbox;

    _method('post');

    if (current_user_can('administrator') or current_user_can('pn_maintrance')) {
        $id = intval(is_param_post('id'));
        $premiumbox->update_option('tech', 'manualy', $id);
    }

}

add_action('wp_dashboard_setup', 'maintrance_wp_dashboard_setup');
function maintrance_wp_dashboard_setup() {
    global $premiumbox;

    if (current_user_can('administrator') or current_user_can('pn_maintrance')) {
        if (0 == intval($premiumbox->get_option('tech', 'maintrance'))) {
            wp_add_dashboard_widget('maintrance_dashboard_widget', __('Maintenance mode', 'pn'), 'maintrance_dashboard_widget_function');
        }
    }

}

function maintrance_dashboard_widget_function() {
    global $wpdb, $premiumbox, $maintenance_widget;

    $maintenance_widget = 1;
    $status = intval($premiumbox->get_option('tech', 'manualy'));
    $items = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "maintrance ORDER BY id DESC");
    ?>
    <select id="maintrance" name="maintrance" autocomplete="off">
        <option value="0" <?php selected($status, 0); ?>>--<?php _e('No', 'pn'); ?>--</option>
        <?php foreach ($items as $item) { ?>
            <option value="<?php echo $item->id; ?>" <?php selected($status, $item->id); ?>><?php echo pn_strip_input(ctv_ml($item->the_title)); ?></option>
        <?php } ?>
    </select>
    <?php
}

add_action('init', 'get_maintrance', 2);
function get_maintrance() {
    global $wpdb, $pn_maintrance, $premiumbox;

    $pn_maintrance = '';
    $data = '';
    if (!is_admin()) {
        if (0 == intval($premiumbox->get_option('tech', 'maintrance'))) {
            $id_mode = intval($premiumbox->get_option('tech', 'manualy'));
            if ($id_mode > 0) {
                $data = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "maintrance WHERE id = '$id_mode'");
            }
        } else {
            $operator = intval(get_operator_status());
            $data = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "maintrance WHERE operator_status = '$operator'");
        }
        if (isset($data->id)) {
            $pn_maintrance = $data;
        }
    }

}

add_filter('pn_exchanges_output', 'maintrance_exchanges_output', 0, 2);
function maintrance_exchanges_output($show_data, $place) {
    global $pn_maintrance;

    if (isset($pn_maintrance->id)) {

        $now = 0;

        if (0 == $pn_maintrance->for_whom or !current_user_can('read')) {
            $pages_law = @unserialize($pn_maintrance->pages_law);
            $now = intval(is_isset($pages_law, $place));
            $show_data['work'] = 0;
        }

        if ($now > 0) {
            $show_data['text'] = do_shortcode(pn_strip_text(ctv_ml($pn_maintrance->show_text)));
        }

        if (2 == $now) {
            $show_data['show'] = 0;
        }
    }

    return $show_data;
}

global $premiumbox;
$premiumbox->include_path(__FILE__, 'settings');
$premiumbox->include_path(__FILE__, 'list');
$premiumbox->include_path(__FILE__, 'add'); 