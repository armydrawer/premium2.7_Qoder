<?php
if (!defined('ABSPATH')) exit();

if (is_admin()) {

    add_filter('pn_adminpage_title_pn_sort_parser_pairs', 'pn_admin_title_pn_sort_parser_pairs');
    function pn_admin_title_pn_sort_parser_pairs($title) {

        return __('Sorting rates', 'pn');
    }

    add_action('pn_adminpage_content_pn_sort_parser_pairs', 'def_adminpage_content_pn_sort_parser_pairs');
    function def_adminpage_content_pn_sort_parser_pairs() {

        $form = new PremiumForm();

        $datas = get_parser_list();
        $sort_list = array();
        foreach ($datas as $item) {
            $sort_list[0][] = array(
                'title' => get_parser_title($item),
                'id' => $item->id,
                'number' => $item->id,
            );
        }

        $form->sort_one_screen($sort_list);
        $form->sort_js('.thesort ul', pn_link('', 'post'));
    }

}

add_action('premium_action_pn_sort_parser_pairs', 'def_premium_action_pn_sort_parser_pairs');
function def_premium_action_pn_sort_parser_pairs() {
    global $wpdb;

    _method('post');

    if (current_user_can('administrator') or current_user_can('pn_directions') or current_user_can('pn_parser')) {
        $ids = is_param_post('number');
        $ids = is_array($ids) ? array_values(array_unique(array_filter(array_map('absint', array_map('trim', $ids))))) : [];
        if (!empty($ids)) {
            $cases = array_map(fn($id, $pos) => $wpdb->prepare('WHEN %d THEN %d', $id, $pos), $ids, array_keys($ids));

            $wpdb->query("UPDATE {$wpdb->prefix}parser_pairs SET menu_order = CASE id " . implode(' ', $cases) . " END WHERE id IN (" . implode(',', $ids) . ")");
        }
    }
}
