<?php
if (!defined('ABSPATH')) { exit(); }

if (is_admin()) {

	add_filter('pn_adminpage_title_pn_bc_corrs', 'def_adminpage_title_pn_bc_corrs');
	function def_adminpage_title_pn_bc_corrs($title) {

		return __('Adjustments', 'pn');
	}

	add_action('pn_adminpage_content_pn_bc_corrs', 'def_adminpage_content_pn_bc_corrs');
	function def_adminpage_content_pn_bc_corrs() {

		premium_table_list();

	}

}

add_action('premium_action_pn_bc_corrs', 'def_premium_action_pn_bc_corrs');
function def_premium_action_pn_bc_corrs() {
	global $wpdb;

	_method('post');
	pn_only_caps(array('administrator', 'pn_bestchange'));

	$arrs = array(
		'paged' => intval(is_param_post('paged')),
	);
	$action = get_request_action();

	if (isset($_POST['save'])) {

		if (isset($_POST['ids']) and is_array($_POST['ids'])) {
			foreach ($_POST['ids'] as $id) {
				$id = intval($id);
				$item = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "bestchange_directions WHERE id = '$id'");
				if (isset($item->id)) {

					$arr = array();

					if (isset($_POST['v1'])) {
						$arr['v1'] = intval($_POST['v1'][$id]);
					}

					if (isset($_POST['v2'])) {
						$arr['v2'] = intval($_POST['v2'][$id]);
					}

					if (isset($_POST['city_id'])) {
						$arr['city_id'] = intval($_POST['city_id'][$id]);
					}

					if (isset($_POST['pars_position'])) {
						$arr['pars_position'] = pn_strip_input($_POST['pars_position'][$id]);
					}

					if (isset($_POST['step'])) {
						$arr['step'] = pn_parser_num($_POST['step'][$id]);
					}

					if (isset($_POST['min_res'])) {
						$arr['min_res'] = is_sum($_POST['min_res'][$id]);
					}

					if (isset($_POST['min_sum'])) {
						$arr['min_sum'] = is_sum($_POST['min_sum'][$id]);
					}

					if (isset($_POST['max_sum'])) {
						$arr['max_sum'] = is_sum($_POST['max_sum'][$id]);
					}

					if (isset($_POST['standart_course_give'])) {
						$arr['standart_course_give'] = is_sum($_POST['standart_course_give'][$id]);
					}

					if (isset($_POST['standart_course_get'])) {
						$arr['standart_course_get'] = is_sum($_POST['standart_course_get'][$id]);
					}

					if (isset($_POST['reset_course'])) {
						$arr['reset_course'] = intval($_POST['reset_course'][$id]);
					}

					if (count($arr) > 0) {

						$result = $wpdb->update($wpdb->prefix . "bestchange_directions", $arr, array('id' => $id));
						do_action('item_bccorrs_save', $item->id, $item, $result, $arr);

					}

				}
			}
		}

		do_action('pntable_bccorrs_save');
		$arrs['reply'] = 'true';

	} else {
		if (isset($_POST['id']) and is_array($_POST['id'])) {

			if ('active' == $action) {
				foreach ($_POST['id'] as $id) {
					$id = intval($id);
					$item = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "bestchange_directions WHERE id = '$id' AND status != '1'");
					if (isset($item->id)) {
						$res = apply_filters('item_bccorrs_active_before', pn_ind(), $id, $item);
						if ($res['ind']) {
							$result = $wpdb->query("UPDATE " . $wpdb->prefix . "bestchange_directions SET status = '1' WHERE id = '$id'");
							do_action('item_bccorrs_active', $id, $item, $result);
						}
					}
				}
			}

			if ('deactive' == $action) {
				foreach ($_POST['id'] as $id) {
					$id = intval($id);
					$item = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "bestchange_directions WHERE id = '$id' AND status != '0'");
					if (isset($item->id)) {
						$res = apply_filters('item_bccorrs_deactive_before', pn_ind(), $id, $item);
						if ($res['ind']) {
							$result = $wpdb->query("UPDATE " . $wpdb->prefix . "bestchange_directions SET status = '0' WHERE id = '$id'");
							do_action('item_bccorrs_deactive', $id, $item, $result);
						}
					}
				}
			}

			if ('delete' == $action) {
				foreach ($_POST['id'] as $id) {
					$id = intval($id);
					$item = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "bestchange_directions WHERE id = '$id'");
					if(isset($item->id)){
						$res = apply_filters('item_bccorrs_delete_before', pn_ind(), $id, $item);
						if ($res['ind']) {
							$result = $wpdb->query("DELETE FROM " . $wpdb->prefix . "bestchange_directions WHERE id = '$id'");
							do_action('item_bccorrs_delete', $id, $item, $result);
						}
					}
				}
			}

			do_action('pntable_bccorrs_action', $action, $_POST['id']);
			$arrs['reply'] = 'true';
		}
	}

	$url = pn_admin_filter_data('', 'reply, paged');
	$url = add_query_args($arrs, $url);
	wp_redirect($url);
	exit;
}

class pn_bc_corrs_Table_List extends PremiumTable {

	function __construct() {

		parent::__construct();

		$this->primary_column = 'title';
		$this->save_button = 1;

	}

	function column_default($item, $column_name) {
		global $wpdb;

		if ('status' == $column_name) {
			if (0 == $item->status) {
				return '<span class="bred">' . __('No', 'pn') . '</span>';
			} else {
				return '<span class="bgreen">' . __('Yes', 'pn') . '</span>';
			}
		} elseif ('title' == $column_name) {
			$direction_id = intval($item->direction_id);
			$direction = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "directions WHERE id = '$direction_id'");
			$direction_title = '';
			if (isset($direction->id)) {
				$direction_title = pn_strip_input($direction->tech_name);
			}
			return $direction_title . '<input type="hidden" name="ids[]" value="' . $item->id . '" />';
		} elseif ('course' == $column_name) {
			$direction_id = intval($item->direction_id);
			$direction = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "directions WHERE id = '$direction_id'");
			if (isset($direction->id)) {
				$dir_c = is_course_direction($direction, '', '', 'admin');
				return is_isset($dir_c, 'give') . '&rarr;' . is_isset($dir_c, 'get');
			}
		} elseif ('giveget' == $column_name) {
			$html = '';
			$cities = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "bestchange_cities ORDER BY city_title ASC");
			$html .= '
			<div style="padding: 0 0 3px 0;">' . __('city', 'pn') . '</div>
			<div style="padding: 0 0 10px 0;">
				<select name="city_id[' . $item->id . ']" autocomplete="off" style="max-width: 100%;">
					<option value="0">--' . __('No item', 'pn') . '</option>
				';
					foreach ($cities as $city) {
						$html .= '<option value="' . $city->city_id . '" ' . selected($city->city_id, $item->city_id, false) . '>' . pn_strip_input($city->city_title) . '</option>';
					}
				$html .= '
				</select>
			</div>';

			$alls = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "bestchange_currency_codes ORDER BY currency_code_title ASC");
			$html .= '
			<div style="padding: 0 0 3px 0;">' . __('Give', 'pn') . '</div>
			<div style="padding: 0 0 10px 0;">
				<select name="v1[' . $item->id . ']" autocomplete="off" style="max-width: 100%;">
					<option value="0">--' . __('No item', 'pn') . '</option>
					';
						foreach ($alls as $all) {
							$html .= '<option value="' . $all->currency_code_id . '" ' . selected($all->currency_code_id, $item->v1, false) . '>' . pn_strip_input($all->currency_code_title) . '</option>';
						}
					$html .= '
				</select>
			</div>
			<div style="padding: 0 0 3px 0;">'. __('Get', 'pn') .'</div>
			<div style="padding: 0 0 10px 0;">
				<select name="v2[' . $item->id . ']" autocomplete="off" style="max-width: 100%;">
					<option value="0">--' . __('No item', 'pn') . '</option>
				';
					foreach ($alls as $all) {
						$html .= '<option value="' . $all->currency_code_id . '" ' . selected($all->currency_code_id, $item->v2, false) . '>' . pn_strip_input($all->currency_code_title) . '</option>';
					}
			$html .= '
				</select>
			</div>';
			return $html;
		} elseif ('standart' == $column_name) {
			$html = '
			<div>
				<select name="reset_course[' . $item->id . ']" autocomplete="off" style="max-width: 100%;">
					<option value="0" ' . selected(0, $item->reset_course, false) . '>' . __('No', 'pn') . '</option>
					<option value="1" ' . selected(1, $item->reset_course, false) . '>' . __('Yes', 'pn') . '</option>
				</select>
			</div>
			<div>
				<input type="text" style="width: 50px;" name="standart_course_give[' . $item->id . ']" value="' . is_sum($item->standart_course_give) . '" /> &rarr; <input type="text" style="width: 50px;" name="standart_course_get[' . $item->id . ']" value="' . is_sum($item->standart_course_get) . '" />
			</div>
			';
			return $html;
		} elseif ('position' == $column_name) {
			$html = '<input type="text" name="pars_position[' . $item->id . ']" style="width: 70px;" value="' . pn_strip_input($item->pars_position) . '" />';
			return $html;
		} elseif ('minres' == $column_name) {
			$html = '<input type="text" name="min_res[' . $item->id . ']" style="width: 70px;" value="' . is_sum($item->min_res) . '" />';
			return $html;
		} elseif ('step' == $column_name) {
			$html = '<input type="text" name="step[' . $item->id . ']" style="width: 70px;" value="' . pn_parser_num($item->step) . '" />';
			return $html;
		} elseif ('minsum' == $column_name) {
			$html = '<input type="text" name="min_sum[' . $item->id . ']" style="width: 70px;" value="' . is_sum($item->min_sum) . '" />';
			return $html;
		} elseif ('maxsum' == $column_name) {
			$html = '<input type="text" name="max_sum[' . $item->id . ']" style="width: 70px;" value="' . is_sum($item->max_sum) . '" />';
			return $html;
		} elseif ('display' == $column_name) {
			$html = '<a href="' . get_request_link('displayrating_bestchange', 'html'). '?v1=' . $item->v1 . '&v2=' . $item->v2 . '&city=' . $item->city_id . '" class="button" target="_blank">' . __('Link', 'pn') . '</a>';
			return $html;
		}

		return '';
	}

	function column_cb($item) {

		return '<input type="checkbox" name="id[]" class="pntable-checkbox-single" autocomplete="off" value="' . $item->id . '" />';
	}

	function get_row_actions($item) {

		$actions = array(
			'edit'      => '<a href="' . admin_url('admin.php?page=pn_bc_add_corrs&item_id=' . $item->id) . '">' . __('Edit', 'pn') . '</a>',
			'view' 		=> '<a href="' . admin_url('admin.php?page=pn_add_directions&item_id=' . $item->direction_id) . '" target="_blank">' . __('View', 'pn') . '</a>',
		);

		return $actions;
	}

	function get_columns() {

		$columns = array(
			'cb'        => '',
			'title'     => __('Exchange direction', 'pn'),
			'course'     => __('Rate', 'pn'),
			'giveget'     => __('Send and Receive', 'pn'),
			'position'    => __('Position', 'pn'),
			'minres'    => __('Min reserve for position', 'pn'),
			'step'    => __('Step', 'pn'),
			'minsum'    => __('Min rate', 'pn'),
			'maxsum'    => __('Max rate', 'pn'),
			'standart'    => __('Standart rate', 'pn'),
			'status'    => __('Status', 'pn'),
			'display' => __('Positions', 'pn'),
		);

		return $columns;
	}

	function tr_class($tr_class, $item) {

		if (1 != $item->status) {
			$tr_class[] = 'tr_red';
		}

		return $tr_class;
	}

	function get_bulk_actions() {

		$actions = array(
			'active'    => __('Activate', 'pn'),
			'deactive'    => __('Deactivate', 'pn'),
			'delete'    => __('Delete', 'pn'),
		);

		return $actions;
	}

	function get_search() {
		global $wpdb;

		$search = array();
		$options = array(
			'0' => '--' . __('All', 'pn') . '--',
		);
		$directions = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "directions ORDER BY site_order1 ASC");
		foreach ($directions as $direction) {
			$options[$direction->id]= pn_strip_input($direction->tech_name) . pn_item_status($direction, 'direction_status', array('0' => __('inactive direction', 'pn'), '2' => __('hold direction', 'pn'))) . pn_item_basket($direction);
		}
		$search['direction_id'] = array(
			'view' => 'select',
			'title' => __('Exchange direction', 'pn'),
			'default' => pn_strip_input(is_param_get('direction_id')),
			'options' => $options,
			'name' => 'direction_id',
		);
		$search['line1'] = array(
			'view' => 'line',
		);

		$currency = list_currency(__('All currency', 'pn'));

		$search['curr_give'] = array(
			'view' => 'select',
			'title' => __('Currency give', 'pn'),
			'default' => pn_strip_input(is_param_get('curr_give')),
			'options' => $currency,
			'name' => 'curr_give',
		);
		$search['curr_get'] = array(
			'view' => 'select',
			'title' => __('Currency get', 'pn'),
			'default' => pn_strip_input(is_param_get('curr_get')),
			'options' => $currency,
			'name' => 'curr_get',
		);

			return $search;
	}

	function get_submenu() {

		$options = array();
		$options['filter'] = array(
			'options' => array(
				'1' => __('active parser', 'pn'),
				'2' => __('inactive parser', 'pn'),
			),
			'title' => '',
		);

		return $options;
	}

	function prepare_items() {
		global $wpdb;

		$per_page = $this->count_items();
		$current_page = $this->get_pagenum();
		$offset = $this->get_offset();

		$oinfo = $this->db_order('id', 'desc');
		$orderby = $oinfo['orderby'];
		$order = $oinfo['order'];

		$where = '';

		$filter = intval(is_param_get('filter'));
		if (1 == $filter) {
			$where .= " AND status='1'";
		} elseif (2 == $filter) {
			$where .= " AND status='0'";
		}

		$direction_id = intval(is_param_get('direction_id'));
		if ($direction_id > 0) {
			$where .= " AND direction_id='$direction_id'";
		}

		$curr_give = intval(is_param_get('curr_give'));
		if ($curr_give > 0) {
			$where .= " AND currency_id_give = '$curr_give'";
		}

		$curr_get = intval(is_param_get('curr_get'));
		if ($curr_get > 0) {
			$where .= " AND currency_id_get = '$curr_get'";
		}

		$where = $this->search_where($where);
		$select_sql = $this->select_sql('');
		if ($this->navi) {
			$this->total_items = $wpdb->get_var("SELECT COUNT(id) FROM " . $wpdb->prefix . "bestchange_directions WHERE id > 0 $where");
		}
		$this->items = $wpdb->get_results("SELECT * $select_sql FROM " . $wpdb->prefix . "bestchange_directions WHERE id > 0 $where ORDER BY $orderby $order LIMIT $offset , $per_page");

	}

	function extra_tablenav($which) {
		?>
		<a href="<?php echo admin_url('admin.php?page=pn_bc_add_corrs'); ?>"><?php _e('Add new', 'pn'); ?></a>
		<?php
	}
} 