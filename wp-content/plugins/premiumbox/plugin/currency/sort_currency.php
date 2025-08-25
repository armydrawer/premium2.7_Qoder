<?php
if (!defined('ABSPATH')) exit();

if (is_admin()) {

    add_filter('pn_adminpage_title_pn_sort_currency', 'pn_adminpage_title_pn_sort_currency');
    function pn_adminpage_title_pn_sort_currency() {

        return __('Sort currency', 'pn');
    }

    add_action('pn_adminpage_content_pn_sort_currency', 'def_pn_admin_content_pn_sort_currency');
    function def_pn_admin_content_pn_sort_currency() {
        global $wpdb;

        $form = new PremiumForm();

        $datas = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "currency WHERE auto_status = '1' ORDER BY reserv_order ASC");
        $sort_list = array();
        foreach ($datas as $item) {
            $sort_list[0][] = array(
                'title' => get_currency_title($item) . pn_item_status($item, 'currency_status'),
                'id' => $item->id,
                'number' => $item->id,
            );
        }
        $form->sort_one_screen($sort_list, pn_link('pn_sort_currency', 'post'));

    }

}

add_action('premium_action_pn_sort_currency', 'def_premium_action_pn_sort_currency');
function def_premium_action_pn_sort_currency() {
    global $wpdb;

    _method('post');

    if (current_user_can('administrator') or current_user_can('pn_currency')) {
        $ids = is_param_post('number');
        $ids = is_array($ids) ? array_values(array_unique(array_filter(array_map('absint', array_map('trim', $ids))))) : [];
        if (!empty($ids)) {
            $cases = array_map(fn($id, $pos) => $wpdb->prepare('WHEN %d THEN %d', $id, $pos), $ids, array_keys($ids));

            $wpdb->query("UPDATE {$wpdb->prefix}currency SET reserv_order = CASE id " . implode(' ', $cases) . " END WHERE id IN (" . implode(',', $ids) . ")");
        }
    }
}
