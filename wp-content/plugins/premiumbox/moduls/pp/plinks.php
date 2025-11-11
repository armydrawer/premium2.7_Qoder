<?php
if (!defined('ABSPATH')) exit();

if (is_admin()) {

    add_filter('pn_adminpage_title_pn_plinks', 'def_adminpage_title_pn_plinks');
    function def_adminpage_title_pn_plinks() {

        return __('Transitions', 'pn');
    }

    add_action('pn_adminpage_content_pn_plinks', 'def_adminpage_content_pn_plinks');
    function def_adminpage_content_pn_plinks() {

        premium_table_list();

    }

}

add_action('premium_action_pn_plinks', 'def_premium_action_pn_plinks');
function def_premium_action_pn_plinks() {
    global $wpdb;

    _method('post');
    pn_only_caps(array('administrator', 'pn_pp'));

    $arrs = array(
        'paged' => intval(is_param_post('paged')),
    );
    $action = get_request_action();

    if (isset($_POST['save'])) {

        do_action('pntable_plinks_save');
        $arrs['reply'] = 'true';

    } else {
        if (isset($_POST['id']) and is_array($_POST['id'])) {

            if ('delete' == $action) {

                foreach ($_POST['id'] as $id) {
                    $id = intval($id);
                    $item = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "plinks WHERE id = '$id'");
                    if (isset($item->id)) {
                        $res = apply_filters('item_plinks_delete_before', pn_ind(), $id, $item);
                        if ($res['ind']) {
                            $result = $wpdb->query("DELETE FROM " . $wpdb->prefix . "plinks WHERE id = '$id'");
                            do_action('item_plinks_delete', $id, $item, $result);
                        }
                    }
                }

                do_action('pntable_plinks_action', $action, $_POST['id']);
                $arrs['reply'] = 'true';
            }
        }
    }

    $url = pn_admin_filter_data('', 'reply, paged');
    $url = add_query_args($arrs, $url);
    wp_redirect($url);
    exit;
}

class pn_plinks_Table_List extends PremiumTable {

    function __construct() {

        parent::__construct();

        $this->primary_column = 'date';
        $this->save_button = 0;

    }

    function column_default($item, $column_name) {

        if ('user' == $column_name) {
            return '<a href="' . pn_edit_user_link($item->user_id) . '">' . is_user($item->user_login) . '</a>';
        } elseif ('date' == $column_name) {
            return pn_strip_input($item->pdate);
        } elseif ('browser' == $column_name) {
            return get_browser_name($item->pbrowser);
        } elseif ('qstring' == $column_name) {
            return pn_strip_input($item->query_string);
        } elseif ('ip' == $column_name) {
            return pn_strip_input($item->pip);
        } elseif ('ref' == $column_name) {
            return pn_strip_input($item->prefer);
        }

        return '';
    }

    function column_cb($item) {

        return '<input type="checkbox" name="id[]" class="pntable-checkbox-single" autocomplete="off" value="' . $item->id . '" />';
    }

    function get_columns() {

        $columns = array(
            'cb' => '',
            'date' => __('Date', 'pn'),
            'user' => __('User', 'pn'),
            'browser' => __('Browser', 'pn'),
            'ip' => __('IP', 'pn'),
            'ref' => __('Referral website', 'pn'),
            'qstring' => __('Query string', 'pn'),
        );

        return $columns;
    }

    function get_bulk_actions() {

        $actions = array(
            'delete' => __('Delete', 'pn'),
        );

        return $actions;
    }

    function get_search() {

        $search = array();
        $search['user_login'] = array(
            'view' => 'input',
            'title' => __('User', 'pn'),
            'default' => pn_strip_input(is_param_get('user_login')),
            'name' => 'user_login',
        );

        $search['date1'] = array(
            'view' => 'date',
            'title' => __('Start date', 'pn'),
            'default' => is_pn_date(is_param_get('date1')),
            'name' => 'date1',
        );

        $search['date2'] = array(
            'view' => 'date',
            'title' => __('End date', 'pn'),
            'default' => is_pn_date(is_param_get('date2')),
            'name' => 'date2',
        );

        return $search;
    }

    function prepare_items() {
        global $wpdb;

        $per_page = $this->count_items();
        $current_page = $this->get_pagenum();
        $offset = $this->get_offset();

        $oinfo = $this->db_order('id', 'DESC');
        $orderby = $oinfo['orderby'];
        $order = $oinfo['order'];

        $where = '';
        $user_login = pn_sfilter(pn_strip_input(is_param_get('user_login')));
        if ($user_login) {
            $where .= " AND user_login LIKE '%$user_login%'";
        }

        $date1 = is_pn_date(is_param_get('date1'));
        if ($date1) {
            $date = get_pn_date($date1, 'Y-m-d');
            $where .= " AND pdate >= '$date'";
        }

        $date2 = is_pn_date(is_param_get('date2'));
        if ($date2) {
            $date = get_pn_date($date2, 'Y-m-d');
            $where .= " AND pdate < '$date'";
        }

        $where = $this->search_where($where);
        $select_sql = $this->select_sql('');
        if ($this->navi) {
            $this->total_items = $wpdb->get_var("SELECT COUNT(id) FROM " . $wpdb->prefix . "plinks WHERE id > 0 $where");
        }
        $this->items = $wpdb->get_results("SELECT * $select_sql FROM " . $wpdb->prefix . "plinks WHERE id > 0 $where ORDER BY $orderby $order LIMIT $offset , $per_page");

    }
}
