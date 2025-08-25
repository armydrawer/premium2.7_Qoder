<?php
if (!defined('ABSPATH')) exit();


if (is_admin()) {

    add_filter('pn_adminpage_title_pn_sort_directions', 'pn_admin_title_pn_sort_directions');
    function pn_admin_title_pn_sort_directions() {

        return __('Sort exchange directions', 'pn');
    }

    add_action('pn_adminpage_content_pn_sort_directions', 'def_adminpage_content_pn_sort_directions');
    function def_adminpage_content_pn_sort_directions() {

        $selects = array();

        $form = new PremiumForm();

        $places = array(
            'admin' => __('For admin panel', 'pn'),
            'tbl1' => sprintf(__('For exchange table %s', 'pn'), '1,4,5'),
            'tbl2' => sprintf(__('For exchange table %s', 'pn'), '2'),
            'tbl3' => sprintf(__('For exchange table %s', 'pn'), '3'),
        );

        $sort_place = is_param_get('sort_place');

        $selects[] = array(
            'link' => admin_url("admin.php?page=pn_sort_directions"),
            'title' => '--' . __('Make a choice', 'pn') . '--',
            'default' => '',
        );

        foreach ($places as $place_key => $place_title) {
            $selects[] = array(
                'link' => admin_url("admin.php?page=pn_sort_directions&sort_place=" . $place_key),
                'title' => $place_title,
                'default' => $place_key,
            );
        }

        $form->select_box($sort_place, $selects, __('Make a choice', 'pn'));

        if (isset($places[$sort_place])) {

            do_action('sort_directions_' . $sort_place);

        }
    }

}

/***********/

add_action('sort_directions_admin', 'def_sort_directions_admin');
function def_sort_directions_admin() {
    global $wpdb;

    $form = new PremiumForm();

    $items = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "directions ORDER BY site_order1 ASC");
    $sort_list = array();
    foreach ($items as $item) {
        $sort_list[0][] = array(
            'title' => pn_strip_input($item->tech_name) . pn_item_status($item, 'direction_status', array('0' => __('inactive direction', 'pn'), '2' => __('hold direction', 'pn'))) . pn_item_basket($item),
            'id' => $item->id,
            'number' => $item->id,
        );
    }
    $form->sort_one_screen($sort_list, pn_link('pn_sortdir_admin', 'post'));

}

add_action('premium_action_pn_sortdir_admin', 'def_premium_action_pn_sortdir_admin');
function def_premium_action_pn_sortdir_admin() {
    global $wpdb;

    if (current_user_can('administrator') or current_user_can('pn_directions')) {
        $ids = is_param_post('number');
        $ids = is_array($ids) ? array_values(array_unique(array_filter(array_map('absint', array_map('trim', $ids))))) : [];
        if (!empty($ids)) {
            $cases = array_map(fn($id, $pos) => $wpdb->prepare('WHEN %d THEN %d', $id, $pos), $ids, array_keys($ids));

            $wpdb->query("UPDATE {$wpdb->prefix}directions SET site_order1 = CASE id " . implode(' ', $cases) . " END WHERE id IN (" . implode(',', $ids) . ")");
        }
    }
}

/***********/

add_action('sort_directions_tbl1', 'def_sort_directions_tbl1');
function def_sort_directions_tbl1() {
    global $wpdb;

    $form = new PremiumForm();

    $place = is_param_get('place');

    $selects = array();
    $selects[] = array(
        'link' => admin_url("admin.php?page=pn_sort_directions&sort_place=tbl1&place=left"),
        'title' => '--' . __('Left column', 'pn') . '--',
        'default' => '',
    );
    $selects[] = array(
        'link' => admin_url("admin.php?page=pn_sort_directions&sort_place=tbl1&place=right"),
        'title' => '--' . __('Right column', 'pn') . '--',
        'default' => 'right',
    );
    $form->select_box($place, $selects, __('Setting up', 'pn'));

    $sort_list = array();

    $has_give = $has_get = array();
    $items = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "directions");
    foreach ($items as $item) {
        $has_give[$item->currency_id_give] = 1;
        $has_get[$item->currency_id_get] = 1;
    }

    if ('right' == $place) {
        $datas = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "currency ORDER BY t1_2 ASC");
        foreach ($datas as $val) {
            if (isset($has_get[$val->id])) {
                $sort_list[0][] = array(
                    'title' => get_currency_title($val) . pn_item_status($val, 'currency_status') . pn_item_basket($val),
                    'id' => $val->id,
                    'number' => $val->id,
                );
            }
        }
        $sort_link = pn_link('sort_table1_right', 'post');
    } else {
        $datas = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "currency ORDER BY t1_1 ASC");
        foreach ($datas as $val) {
            if (isset($has_give[$val->id])) {
                $sort_list[0][] = array(
                    'title' => get_currency_title($val) . pn_item_status($val, 'currency_status') . pn_item_basket($val),
                    'id' => $val->id,
                    'number' => $val->id,
                );
            }
        }
        $sort_link = pn_link('sort_table1_left', 'post');
    }

    $form->sort_one_screen($sort_list, $sort_link);

}

add_action('premium_action_sort_table1_left', 'def_premium_action_sort_table1_left');
function def_premium_action_sort_table1_left() {
    global $wpdb;

    if (current_user_can('administrator') or current_user_can('pn_directions')) {
        $ids = is_param_post('number');
        $ids = is_array($ids) ? array_values(array_unique(array_filter(array_map('absint', array_map('trim', $ids))))) : [];
        if (!empty($ids)) {
            $cases = array_map(fn($id, $pos) => $wpdb->prepare('WHEN %d THEN %d', $id, $pos), $ids, array_keys($ids));

            $wpdb->query("UPDATE {$wpdb->prefix}currency SET t1_1 = CASE id " . implode(' ', $cases) . " END WHERE id IN (" . implode(',', $ids) . ")");
        }
    }
}

add_action('premium_action_sort_table1_right', 'def_premium_action_sort_table1_right');
function def_premium_action_sort_table1_right() {
    global $wpdb;

    if (current_user_can('administrator') or current_user_can('pn_directions')) {
        $ids = is_param_post('number');
        $ids = is_array($ids) ? array_values(array_unique(array_filter(array_map('absint', array_map('trim', $ids))))) : [];
        if (!empty($ids)) {
            $cases = array_map(fn($id, $pos) => $wpdb->prepare('WHEN %d THEN %d', $id, $pos), $ids, array_keys($ids));

            $wpdb->query("UPDATE {$wpdb->prefix}currency SET t1_2 = CASE id " . implode(' ', $cases) . " END WHERE id IN (" . implode(',', $ids) . ")");
        }
    }
}

/****************/

add_action('sort_directions_tbl2', 'def_sort_directions_tbl2');
function def_sort_directions_tbl2() {
    global $wpdb;

    $form = new PremiumForm();

    $place = is_param_get('place');

    $selects = array();
    $selects[] = array(
        'link' => admin_url("admin.php?page=pn_sort_directions&sort_place=tbl2&place=left"),
        'title' => '--' . __('Left column', 'pn') . '--',
        'default' => '',
    );
    $selects[] = array(
        'link' => admin_url("admin.php?page=pn_sort_directions&sort_place=tbl2&place=right"),
        'title' => '--' . __('Right column', 'pn') . '--',
        'default' => 'right',
    );
    $form->select_box($place, $selects, __('Setting up', 'pn'));

    $sort_list = array();

    if ('right' == $place) {
        $datas = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "psys ORDER BY t2_2 ASC");
        foreach ($datas as $val) {
            $sort_list[0][] = array(
                'title' => get_pstitle($val->id) . pn_item_basket($val),
                'id' => $val->id,
                'number' => $val->id,
            );
        }
        $sort_link = pn_link('sort_table2_right', 'post');
    } else {
        $datas = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "psys ORDER BY t2_1 ASC");
        foreach ($datas as $val) {
            $sort_list[0][] = array(
                'title' => get_pstitle($val->id) . pn_item_basket($val),
                'id' => $val->id,
                'number' => $val->id,
            );
        }
        $sort_link = pn_link('sort_table2_left', 'post');
    }

    $form->sort_one_screen($sort_list, $sort_link);

}

add_action('premium_action_sort_table2_left', 'def_premium_action_sort_table2_left');
function def_premium_action_sort_table2_left() {
    global $wpdb;

    if (current_user_can('administrator') or current_user_can('pn_directions')) {
        $ids = is_param_post('number');
        $ids = is_array($ids) ? array_values(array_unique(array_filter(array_map('absint', array_map('trim', $ids))))) : [];
        if (!empty($ids)) {
            $cases = array_map(fn($id, $pos) => $wpdb->prepare('WHEN %d THEN %d', $id, $pos), $ids, array_keys($ids));

            $wpdb->query("UPDATE {$wpdb->prefix}psys SET t2_1 = CASE id " . implode(' ', $cases) . " END WHERE id IN (" . implode(',', $ids) . ")");
        }
    }
}

add_action('premium_action_sort_table2_right', 'def_premium_action_sort_table2_right');
function def_premium_action_sort_table2_right() {
    global $wpdb;

    if (current_user_can('administrator') or current_user_can('pn_directions')) {
        $ids = is_param_post('number');
        $ids = is_array($ids) ? array_values(array_unique(array_filter(array_map('absint', array_map('trim', $ids))))) : [];
        if (!empty($ids)) {
            $cases = array_map(fn($id, $pos) => $wpdb->prepare('WHEN %d THEN %d', $id, $pos), $ids, array_keys($ids));

            $wpdb->query("UPDATE {$wpdb->prefix}psys SET t2_2 = CASE id " . implode(' ', $cases) . " END WHERE id IN (" . implode(',', $ids) . ")");
        }
    }
}

/****************/

add_action('sort_directions_tbl3', 'def_sort_directions_tbl3');
function def_sort_directions_tbl3() {
    global $wpdb;

    $form = new PremiumForm();

    $items = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "directions ORDER BY to3_1 ASC");
    $sort_list = array();
    foreach ($items as $item) {
        $sort_list[0][] = array(
            'title' => pn_strip_input($item->tech_name) . pn_item_status($item, 'direction_status', array('0' => __('inactive direction', 'pn'), '2' => __('hold direction', 'pn'))) . pn_item_basket($item),
            'id' => $item->id,
            'number' => $item->id,
        );
    }
    $form->sort_one_screen($sort_list, pn_link('pn_sortdir_tbl3', 'post'));

}

add_action('premium_action_pn_sortdir_tbl3', 'def_premium_action_pn_sortdir_tbl3');
function def_premium_action_pn_sortdir_tbl3() {
    global $wpdb;

    if (current_user_can('administrator') or current_user_can('pn_directions')) {
        $ids = is_param_post('number');
        $ids = is_array($ids) ? array_values(array_unique(array_filter(array_map('absint', array_map('trim', $ids))))) : [];
        if (!empty($ids)) {
            $cases = array_map(fn($id, $pos) => $wpdb->prepare('WHEN %d THEN %d', $id, $pos), $ids, array_keys($ids));

            $wpdb->query("UPDATE {$wpdb->prefix}directions SET to3_1 = CASE id " . implode(' ', $cases) . " END WHERE id IN (" . implode(',', $ids) . ")");
        }
    }
}
