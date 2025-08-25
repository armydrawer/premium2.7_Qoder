<?php

if (!defined('ABSPATH')) exit();

if (!class_exists('PremiumTable')) {
    #[AllowDynamicProperties]
    class PremiumTable {

        public $version = "1.0";
        public $page = '';
        public $navi = 1;
        public $count_items = 20;
        public $primary_column = 'title';
        public $save_button = 0;
        public $items = array();
        public $total_items = '-1';
        public $txtfile = 0;

        function __construct($page = '') {
            $page = pn_strip_input($page);
            if (!$page) {
                $page = pn_strip_input(is_param_get('page'));
            }
            $this->page = $page;

            $ui = wp_get_current_user();
            $user_id = intval($ui->ID);
            $mini_navi = intval(is_isset($ui, 'mini_navi'));
            if (1 == $mini_navi) {
                $this->navi = 0;
            }

            global $user_pntable_settings;
            if (!is_array($user_pntable_settings)) {
                $option_name = $this->page . '_Table_List_options';
                $user_pntable_settings = get_user_meta($user_id, $option_name, true);
                if (!is_array($user_pntable_settings)) $user_pntable_settings = array();
            }
        }

        function count_items() {
            global $user_pntable_settings;

            $count_items = intval(is_isset($user_pntable_settings, 'count_items'));
            if ($count_items < 1) {
                $count_items = $this->count_items;
            }
            if ($count_items < 1) {
                $count_items = 1;
            }
            if (isset($_GET['crazy'])) {
                $count_items = 1;
            }

            return $count_items;
        }

        function set_url($not) {
            if (!is_array($not)) {
                $not = array();
            }

            $now_url = is_isset($_SERVER, 'REQUEST_URI');
            $now_url = explode('/wp-admin/', $now_url);
            $now_url = explode('?', is_isset($now_url, 1));
            $now_url = $now_url[0];

            $hidden_items = '';
            if (isset($_GET) and is_array($_GET)) {
                foreach ($_GET as $key => $val) {
                    if (!in_array($key, $not)) {
                        $hidden_items .= '&' . pn_strip_symbols($key, '_') . '=' . esc_html($val);
                    }
                }
            }

            $url = admin_url($now_url . '?page=' . $this->page . $hidden_items);

            return $url;
        }

        function get_pagenum() {

            $paged = intval(is_param_get('paged'));
            if ($paged < 1) {
                $paged = 1;
            }

            return $paged;
        }

        function get_offset() {

            $current_page = $this->get_pagenum();
            $per_page = $this->count_items();
            $offset = ($current_page - 1) * $per_page;

            return $offset;
        }

        function show_columns() {
            global $user_pntable_settings;

            $hide_columns = is_isset($user_pntable_settings, 'hide_columns');
            if (!is_array($hide_columns)) {
                $hide_columns = array();
            }

            $show_columns = array();
            $columns = $this->get_columns_filter();
            if (is_array($columns)) {
                foreach ($columns as $column_key => $column_title) {
                    if (!isset($hide_columns[$column_key])) {
                        $show_columns[$column_key] = 1;
                    }
                }
            }

            return $show_columns;
        }

        function head_action() {
            global $user_pntable_settings, $p_form;

            if (!isset($p_form)) $p_form = new PremiumForm();

            $opts = [0 => __('Default', 'pn')];
            $default = pn_strip_input(is_isset($user_pntable_settings, 'default_sort'));
            $attrs = ['wrap_class' => 'nothing'];

            $columns = $this->get_columns_filter();
            $orders = (array)$this->get_sortable_columns_filter();

            foreach ($orders as $key => $data) {
                if (empty($data[0])) continue;

                $title = !empty($columns[$key]) ? $columns[$key] : mb_strtoupper($data[0]);

                $opts["{$key}:::ASC"] = "{$title} &#9650;";
                $opts["{$key}:::DESC"] = "{$title} &#9660;";
            }

            $default_sort_html = $p_form->get_select('default_sort', $opts, $default, $attrs);

            $count_items = $this->count_items();
            $show_columns = $this->show_columns();

            $html = '
			<div class="premium_tf">
				<div class="premium_tf_inner">
					<div class="premium_tf_button open_block"><span>' . __('Display settings', 'premium') . '</span></div>	
					<form action="' . pn_link('pntable_head_action') . '" method="post">
						' . wp_referer_field(false) . '
						' . $p_form->get_hidden_input('old_count_items', $count_items) . '
						' . $p_form->get_hidden_input('page', $this->page) . '
						
						<div class="premium_tf_ins">';

            $columns = $this->get_columns_filter();
            $columns = pn_array_unset($columns, array('cb', $this->primary_column));

            if (is_array($columns) and count($columns) > 0) {
                $html .= '
								<div class="premium_tf_line">
									<div class="premium_tf_label">' . __('Columns', 'premium') . '</div>
									<div class="premium_tf_items">';

                foreach ($columns as $column_key => $column_title) {

                    $ch1 = '';
                    $ch2 = '';
                    if (isset($show_columns[$column_key])) {
                        $ch1 = 'checked="checked"';
                    } else {
                        $ch2 = 'checked="checked"';
                    }

                    $html .= '
											<div class="premium_tf_item"><label>
												<input name="show_columns[]" type="checkbox" class="premium_tf_checkbox" autocomplete="off" value="' . $column_key . '" ' . $ch1 . '>' . $column_title . '
												<input name="hide_columns[]" type="checkbox" class="premium_tf_checkbox_hidden" style="display: none;" autocomplete="off" value="' . $column_key . '" ' . $ch2 . '>
											</label></div>
											';

                }

                $html .= '
											<div class="premium_clear"></div>
									</div>
								</div>
								';
            }

            $html .= '
							<div class="premium_tf_line">
								<div class="premium_tf_label">' . __('Page navigation', 'premium') . '</div>
								<div class="premium_tf_items">
									<label>' . __('Number of elements on the page', 'premium') . ': <input type="number" step="1" min="1" max="999" class="screen-per-page" name="count_items" maxlength="3" value="' . $count_items . '" /></label>
								</div>							
							</div>
							
							<div class="premium_tf_line">
								<div class="premium_tf_label">' . __('Sort', 'pn') . '</div>
								<div class="premium_tf_items">
									<label>' . $default_sort_html . '</label>
								</div>							
							</div>
							
							<div class="premium_tf_submit">
								<input type="submit" name="" class="premium_button" value="' . __('Apply', 'premium') . '" />
							</div>
								
						</div>	
					</form>	
				</div>	
			</div>';

            echo $html;

        }

        function searchbox() {

            $search = $this->get_search();
            $search = apply_filters('pntable_searchbox_' . $this->page, $search);

            $works = pn_admin_prepare_lost('reply, paged');

            if (is_array($search) and count($search) > 0) {
                $has_filter = 0;

                $now_url = is_isset($_SERVER, 'REQUEST_URI');
                $now_url = explode('/wp-admin/', $now_url);
                $now_url = explode('?', is_isset($now_url, 1));
                $now_url = $now_url[0];

                foreach ($search as $item) {
                    $name = trim(is_isset($item, 'name'));
                    if ($name) {
                        $works[] = $name;
                    }
                }
                ?>
                <div class="premium_search">
                    <form action="" method="get">

                        <?php
                        $hidden_items = '';
                        if (isset($_GET) and is_array($_GET)) {
                            foreach ($_GET as $key => $val) {
                                $key = pn_strip_symbols($key, '_');
                                $val = pn_strip_input($val);
                                if (!in_array($key, $works)) {
                                    if ('page' != $key) {
                                        $hidden_items .= '&' . $key . '=' . $val;
                                    }
                                    ?>
                                    <input type="hidden" name="<?php echo $key; ?>" value="<?php echo $val; ?>"/>
                                    <?php
                                }
                            }
                        }
                        ?>

                        <?php
                        foreach ($search as $option) {

                            $view = trim(is_isset($option, 'view'));
                            $title = trim(is_isset($option, 'title'));
                            $name = trim(is_isset($option, 'name'));
                            $default = trim(is_isset($option, 'default'));

                            if (strlen($default) > 0) {
                                $has_filter = 1;
                            }

                            if ('input' == $view) {
                                ?>
                                <div class="premium_search_div">
                                    <div class="premium_search_label"><?php echo $title; ?></div>
                                    <input type="search" name="<?php echo $name; ?>" autocomplete="off" value="<?php echo $default; ?>"/>
                                </div>
                                <?php
                            } elseif ('date' == $view) {
                                ?>
                                <div class="premium_search_div">
                                    <div class="premium_search_label"><?php echo $title; ?></div>
                                    <input type="search" name="<?php echo $name; ?>" class="js_datepicker" autocomplete="off" value="<?php echo $default; ?>"/>
                                </div>
                                <?php
                            } elseif ('datetime' == $view) {
                                ?>
                                <div class="premium_search_div">
                                    <div class="premium_search_label"><?php echo $title; ?></div>
                                    <input type="search" name="<?php echo $name; ?>" class="js_datetimepicker" autocomplete="off" value="<?php echo $default; ?>"/>
                                </div>
                                <?php
                            } elseif ('select' == $view) {
                                $options = is_isset($option, 'options');
                                ?>
                                <div class="premium_search_div">
                                    <div class="premium_search_label"><?php echo $title; ?></div>
                                    <select name="<?php echo $name; ?>" class="select_shift_search" style="position: relative; top: -1px;" autocomplete="off">
                                        <?php foreach ($options as $key => $title) { ?>
                                            <option value="<?php echo $key; ?>" <?php selected($key, $default); ?>><?php echo $title; ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                                <?php
                            } elseif ('line' == $view) {
                                ?>
                                <div class="premium_clear"></div>
                                <div class="premium_search_line"></div>
                                <?php
                            }
                        }
                        ?>

                        <div class="premium_search_div">
                            <div class="premium_search_label"></div>
                            <input type="submit" style="float: left; margin: -1px 5px 0 0;" name="" class="premium_button" value="<?php _e('Filter', 'premium'); ?>"/>
                            <?php if ($has_filter) { ?>
                                <a href="<?php echo admin_url($now_url . '?page=' . $this->page . $hidden_items); ?>" style="background: #fef4f4; margin: -1px 0 0 0;" class="premium_button"><?php _e('Cancel', 'premium'); ?></a>
                            <?php } ?>
                        </div>

                        <div class="premium_clear"></div>
                    </form>
                </div>
                <div class="premium_clear"></div>
                <?php
            }
        }

        function submenu() {

            $options = $this->get_submenu();
            $options = apply_filters('pntable_submenu_' . $this->page, $options);
            if (is_array($options)) {
                foreach ($options as $option_name => $option) {
                    $title = pn_strip_input(is_isset($option, 'title'));
                    $lists = is_isset($option, 'options');
                    $ignore = is_isset($option, 'ignore');
                    if (!is_array($ignore)) {
                        $ignore = array();
                    }
                    $filter = pn_strip_input(is_param_get($option_name));

                    $not = array('reply', 'paged', 'page', $option_name);
                    $not = array_merge($not, $ignore);
                    $link = $this->set_url($not);

                    $temp = '
					<div class="premium_submenu">';

                    if (strlen($title) > 0) {
                        $temp .= '
							<div class="premium_submenu_title">
								' . $title . ':
							</div>';
                    }

                    $cl = '';
                    if (!$filter) {
                        $cl = 'class="current"';
                    }

                    $temp .= '
						<ul>
							<li ' . $cl . '><a href="' . $link . '">' . __('All', 'premium') . '</a></li>';

                    if (is_array($lists)) {
                        foreach ($lists as $key => $val) {
                            $cl = '';
                            if ($filter == $key) {
                                $cl = 'class="current"';
                            }

                            $temp .= '<li ' . $cl . '>| <a href="' . $link . '&' . $option_name . '=' . $key . '">' . $val . '</a></li>';
                        }
                    }

                    $temp .= '	
							<div class="premium_clear"></div>
						</ul>';

                    $temp .= '	
					</div>
						<div class="premium_clear"></div>';

                    echo $temp;
                }
            }
        }

        function actions($which) {
            if ('top' != $which) {
                $which = 'bottom';
            }

            $actions = $this->get_bulk_actions();
            $actions = apply_filters('pntable_bulkactions_' . $this->page, $actions);

            $select_name = 'action';
            if ('bottom' == $which) {
                $select_name = 'action2';
            }

            $per_page = $this->count_items();
            $current_page = $this->get_pagenum();
            $items = $this->items;
            $count_items = count($items);
            $total_items = $this->total_items;

            $total_pages = 0;
            if ($total_items > 0) {
                $total_pages = ceil($total_items / $per_page);
            }
            $prev = $current_page - 1;
            if ($prev < 1) {
                $prev = 1;
            }

            $next = $current_page + 1;
            if ($next > $total_pages and '-1' != $total_items) {
                $next = $total_pages;
            }
            $url = $this->set_url(array('reply', 'paged', 'page'));
            $txtfile_url = $this->set_url(array('reply', 'page'));
            ?>
            <div class="premium_table_pagenavi">
                <?php if ('-1' != $total_items) { ?>
                    <div class="premium_table_pagenavi_text">
                    <strong><?php _e('items', 'premium'); ?>:</strong> <?php echo $total_items; ?> -</div><?php } ?>
                <?php if (1 != $current_page) { ?><a href="<?php echo $url; ?>">&laquo;</a><?php } ?>
                <?php if ($prev != $current_page) { ?>
                <a href="<?php echo $url; ?>&paged=<?php echo $prev; ?>">&larr;</a><?php } ?>
                <?php if ('top' == $which) { ?>
                    <input type="text" name="paged" value="<?php echo $current_page; ?>" autocomplete="off"/>
                <?php } else { ?>
                    <div class="premium_table_pagenavi_text"><?php echo $current_page; ?></div>
                <?php } ?>
                <?php if ($total_pages) { ?>
                    <div class="premium_table_pagenavi_text"><?php _e('out of', 'premium'); ?></div><?php } ?>
                <?php if ($total_pages) { ?>
                    <div class="premium_table_pagenavi_text"><?php echo $total_pages; ?></div><?php } ?>
                <?php if ($next and $next != $current_page) { ?>
                <a href="<?php echo $url; ?>&paged=<?php echo $next; ?>">&rarr;</a><?php } ?>
                <?php if ($total_pages and $total_pages != $current_page) { ?>
                <a href="<?php echo $url; ?>&paged=<?php echo $total_pages; ?>">&raquo;</a><?php } ?>
                <div class="premium_clear"></div>
            </div>

            <div class="premium_table_actions">

                <?php if (is_array($actions) and count($actions) > 0 and $count_items > 0) { ?>

                    <select name="<?php echo $select_name; ?>" class="pntable-bulk-select pntable-bulk-select-<?php echo $which; ?>" autocomplete="off">
                        <option value="-1">-- <?php _e('Actions', 'premium'); ?> --</option>
                        <?php
                        foreach ($actions as $action_key => $action_val) {
                            $bg = '';
                            if (in_array($action_key, ['delete', 'delete_all'])) {
                                $bg = 'background: #ff0000; color: #fff;';
                            }
                            ?>
                            <option value="<?php echo $action_key; ?>" style="<?php echo $bg; ?>"><?php echo $action_val; ?></option>
                        <?php } ?>
                    </select>

                    <?php
                    $this->prev_tablenav($which);
                    ?>

                    <input type="submit" class="pntable-bulk-action pntable-bulk-action-<?php echo $which; ?>" data-key="<?php echo $which; ?>" name="" value="<?php _e('Apply', 'premium'); ?>"/>

                <?php } else { ?>
                    <?php if ('top' == $which) { ?>
                        <input type="submit" style="display: none;" name="" value="<?php _e('Apply', 'premium'); ?>"/>
                    <?php } ?>
                <?php } ?>

                <?php
                $txtfilebutton = apply_filters('pntable_txtfilebutton_' . $this->page, $this->txtfile);
                if ($txtfilebutton > 0 and $count_items > 0) {
                    if (1 == $txtfilebutton) {
                        $txtfile_class = 'txtfile_print';
                        $txtfile_title = __('Print table', 'premium');
                    } else {
                        $txtfile_class = 'txtfile_download';
                        $txtfile_title = __('Download table', 'premium');
                    }
                    ?>
                    <a href="<?php the_pn_link('pntable_print_action'); ?>&url=<?php echo urlencode($txtfile_url); ?>" target="_blank" class="<?php echo $txtfile_class; ?>" title="<?php echo $txtfile_title; ?>"></a>
                    <?php
                }

                $save_button = apply_filters('pntable_savebutton_' . $this->page, $this->save_button);
                if (1 == $save_button and $count_items > 0) {
                    ?>
                    <input type="submit" name="save" style="background: #eaf4eb;" value="<?php _e('Save', 'premium'); ?>"/>
                    <?php
                }

                $this->extra_tablenav($which);

                echo apply_filters('pntable_actions_' . $this->page, '');
                ?>

                <div class="premium_clear"></div>
            </div>
            <div class="premium_clear"></div>
            <?php
        }

        function display_print() {

            $this->prepare_items();

            $columns = $this->get_columns_filter();
            if (isset($columns['cb'])) {
                unset($columns['cb']);
            }

            $items = $this->items;

            $show_columns = $this->show_columns();
            if ($this->primary_column) {
                $show_columns[$this->primary_column] = 1;
            }

            $content = '';
            $content_table = '<div style="width: 800px; padding: 20px 0; margin: 0 auto;"><table style="width: 100%; border-collapse: separate; border-spacing: 0;">';
            $content_table .= '<tr>';
            foreach ($columns as $column_key => $column_title) {
                if (isset($show_columns[$column_key])) {
                    $now = str_replace(array(';', '"'), '', $column_title);
                    $content .= $now . ';';
                    $content_table .= '<th style="border: 1px solid #000; border-collapse: separate; padding: 3px 5px;">' . $now . '</th>';
                }
            }
            $content .= "\n";
            $content_table .= '</tr>';

            if (is_array($items) and count($items) > 0) {
                foreach ($items as $item) {
                    $line = '';
                    $content_table .= '<tr>';
                    foreach ($columns as $column_key => $column_title) {
                        if (isset($show_columns[$column_key])) {
                            $column_name = 'column_' . $column_key;
                            $column_line = '';
                            if (method_exists($this, $column_name)) {
                                $column_line = call_user_func(array($this, $column_name), $item, $column_key, $column_title);
                            } else {
                                $column_line = $this->column_default($item, $column_key);
                            }
                            $column_line = apply_filters('pntable_column_' . $this->page, $column_line, $column_key, $item);
                            $column_line = trim(str_replace(array(';', '"'), '', strip_tags($column_line)));
                            $content_table .= '<td style="border: 1px solid #000; border-collapse: separate; padding: 3px 5px;">' . $column_line . '</td>';
                            $line .= $column_line . ';';
                        }
                    }
                    $content .= $line . "\n";
                    $content_table .= '</tr>';
                }
            }

            $content_table .= '</table></div>';

            if (1 == $this->txtfile) {
                echo $content_table;
                exit;
            } else {

                $my_dir = wp_upload_dir();
                $path = $my_dir['basedir'] . '/';

                $file = $path . 'table-' . time() . '-' . mt_rand(0, 10000) . '.txt';
                $fs = @fopen($file, 'w');
                @fwrite($fs, $content);
                @fclose($fs);

                pn_download_file($file, basename($file), 1);

                die(__('Error! Unable to create file!', 'premium'));

            }

        }

        function display() {

            $this->prepare_items();

            $this->searchbox();

            $this->submenu();

            $columns = $this->get_columns_filter();

            $items = $this->items;

            $show_columns = $this->show_columns();
            $show_columns['cb'] = 1;
            if ($this->primary_column) {
                $show_columns[$this->primary_column] = 1;
            }

            $sortable_columns = $this->get_sortable_columns_filter();

            $oinfo = $this->db_order('', '');
            $orderby = $oinfo['orderbykey'];
            $order = strtolower($oinfo['order']);

            $url = $this->set_url(array('reply', 'paged', 'page', 'orderby', 'order'));

            $confirm_actions = $this->get_confirm_buttons();
            ?>
            <script type="text/javascript">
                jQuery(function ($) {
                    <?php
                    $ui = wp_get_current_user();
                    $confirm_deletion = intval(is_isset($ui, 'confirm_deletion'));
                    if (1 != $confirm_deletion) {
                    ?>
                    $(document).on('click', '.pntable-bulk-action', function () {
                        var select_key = $(this).attr('data-key');
                        var select_action = $('.pntable-bulk-select-' + select_key).val();
                        <?php foreach ($confirm_actions as $c_action => $c_text) { ?>
                        if (select_action == '<?php echo $c_action; ?>') {
                            if (!confirm("<?php echo $c_text; ?>")) {
                                return false;
                            }
                        }
                        <?php } ?>
                    });
                    <?php
                    }
                    ?>

                    $(document).on('change', '.pntable-bulk-select', function () {
                        var now_value = $(this).val();
                        $('.pntable-bulk-select').val(now_value);
                    });
                });
            </script>

            <style>
                .not_adaptive th.pntable-column-cb {
                    width: 10px;
                }

                .not_adaptive th.pntable-column-id {
                    width: 30px;
                }

                <?php
                $th_widths = $this->get_thwidth_filter();
                if (!isset($th_widths[$this->primary_column])) {
                    $th_widths[$this->primary_column] = '200px';
                }
                foreach ($th_widths as $th_wkey => $th_width) {
                ?>
                .not_adaptive th.pntable-column-<?php echo $th_wkey; ?> {
                    min-width: <?php echo $th_width; ?>;
                }

                <?php
                }
                ?>
            </style>

            <form method="post" action="<?php the_pn_link(); ?>">
                <?php wp_referer_field(); ?>

                <?php
                $this->actions('top');
                $count_columns = count($columns);

                $adaptive = '';
                if ($count_columns > 0 and isset($columns['cb'])) {
                    $adaptive = '<div class="premium_table_checkbox has_adaptive_content"><label><input type="checkbox" class="pntable_checkbox pntable-checkbox" name="" autocomplete="off" value="1" /> <strong>' . __('Check all/Uncheck all', 'premium') . '</strong></label></div>';
                }
                ?>

                <div class="premium_table_wrap">

                    <?php echo $adaptive; ?>

                    <div class="premium_wrap_table">
                        <div class="premium_table">
                            <table>

                                <?php
                                $thead = '
								<thead>
									<tr>';

                                foreach ($columns as $column_key => $column_title) {
                                    if (isset($show_columns[$column_key])) {
                                        $class = $orderby == $column_key ? "th_{$order}" : '';
                                        $n_order = 'asc' == $order ? 'desc' : 'asc';

                                        $thead .= '
												<th class="pntable-column pntable-column-' . $column_key . ' ' . $class . '">';
                                        if (isset($sortable_columns[$column_key])) {
                                            $thead .= '<a href="' . $url . '&orderby=' . $column_key . '&order=' . $n_order . '">';
                                        }
                                        $thead .= '<span>';
                                        if ('cb' == $column_key) {
                                            $thead .= '<input type="checkbox" class="pntable-checkbox" name="" autocomplete="off" value="1" />';
                                        } else {
                                            $thead .= $column_title;
                                        }
                                        $thead .= '</span>';
                                        if (isset($sortable_columns[$column_key])) {
                                            $thead .= '</a>';
                                        }
                                        $thead .= '
												</th>
												';
                                    }
                                }

                                $thead .= '
									</tr>
								</thead>								
								';

                                echo $thead;
                                ?>

                                <tbody>
                                <?php
                                if (is_array($items) and count($items) > 0) {
                                    $r = 0;
                                    foreach ($items as $item) {
                                        $r++;
                                        $tr_class = array();
                                        $tr_class[] = 'pntable_tr';
                                        if (0 == $r % 2) {
                                            $tr_class[] = 'tr_odd';
                                        } else {
                                            $tr_class[] = 'tr_even';
                                        }
                                        $tr_class = $this->tr_class($tr_class, $item);
                                        $tr_class = apply_filters('pntable_trclass_' . $this->page, $tr_class, $item);
                                        ?>
                                        <tr class="<?php echo join(' ', $tr_class); ?>">
                                            <?php
                                            foreach ($columns as $column_key => $column_title) {
                                                if (isset($show_columns[$column_key])) {
                                                    $td_atts = apply_filters('pntable_column_atts_' . $this->page, '', $column_key, $item);
                                                    ?>
                                                    <td class="pntable-column pntable-column-<?php echo $column_key; ?>" <?php echo $td_atts; ?>>

                                                        <?php
                                                        $td_html = '';

                                                        $column_name = 'column_' . $column_key;
                                                        if (method_exists($this, $column_name)) {
                                                            $td_html = call_user_func(array($this, $column_name), $item, $column_key, $column_title);
                                                        } else {
                                                            $td_html = $this->column_default($item, $column_key);
                                                        }

                                                        $td_html = apply_filters('pntable_column_' . $this->page, $td_html, $column_key, $item);
                                                        echo $td_html;

                                                        if ($this->primary_column == $column_key) {
                                                            $this->show_row_filters($item);
                                                            $this->show_row_actions($item);
                                                        }
                                                        ?>

                                                        <div class="premium_clear"></div>
                                                    </td>
                                                    <?php
                                                }
                                            }
                                            ?>
                                        </tr>
                                        <?php
                                    }
                                } else {
                                    ?>
                                    <tr class="noitem">
                                        <td colspan="<?php echo $count_columns; ?>"><?php _e('No items', 'premium'); ?></td>
                                    </tr>
                                    <?php
                                }
                                ?>
                                </tbody>

                                <?php
                                echo $thead;
                                ?>
                            </table>
                        </div>
                    </div>

                    <?php echo $adaptive; ?>

                </div>

                <?php
                $this->actions('bottom');
                ?>
            </form>
            <?php
        }

        function show_row_filters($item) {

            $actions = $this->get_row_filters_filter($item);
            if (is_array($actions) and count($actions) > 0) {
                echo implode(' ', $actions);
            }

        }

        function get_row_filters_filter($item) {

            $actions = $this->get_row_filters($item);
            $actions = apply_filters('pntable_rowfilters_' . $this->page, $actions, $item);

            return $actions;
        }

        function get_row_filters($item) {

            return array();
        }

        function show_row_actions($item) {
            $actions = $this->get_row_actions_filter($item);
            if (is_array($actions) and count($actions) > 0) {
                ?>
                <div class="pntable_actions_wrap">
                    <div class="pntable_actions">
                        <?php echo implode(' | ', $actions); ?>
                    </div>
                </div>
                <?php
            }
        }

        function get_row_actions_filter($item) {

            $actions = $this->get_row_actions($item);
            $actions = apply_filters('pntable_rowactions_' . $this->page, $actions, $item);

            return $actions;
        }

        function get_row_actions($item) {
            return array();
        }

        function get_confirm_buttons() {

            $array = [
                'delete' => __('Are you sure you want to delete these items?', 'premium'),
                'delete_all' => __('Confirm Deletion'),
            ];
            return apply_filters('pntable_cbuttons_' . $this->page, $array);
        }

        function search_where($where) {

            $where = apply_filters('pntable_searchwhere_' . $this->page, $where);

            return $where;
        }

        function select_sql($select_sql) {

            $select_sql = apply_filters('pntable_select_sql_' . $this->page, $select_sql);

            return $select_sql;
        }

        function get_sortable_columns_filter() {

            $columns = $this->get_sortable_columns();
            $columns = apply_filters('pntable_sortable_columns_' . $this->page, $columns);

            return $columns;
        }

        function get_sortable_columns() {
            return array();
        }

        function db_order($def_orderby, $def_order) {
            global $user_pntable_settings;

            $orders = (array)$this->get_sortable_columns_filter();

            $get_orderby = trim(is_param_get('orderby'));
            if ($get_orderby && !empty($orders[$get_orderby][0])) {
                return $this->_prepare_order($get_orderby, $orders[$get_orderby][0], trim(is_param_get('order')));
            }

            $ds_exp = explode(':::', pn_strip_input(is_isset($user_pntable_settings, 'default_sort')), 2);
            if (count($ds_exp) === 2) {
                foreach ($orders as $key => $data) {
                    if (empty($data[0]) || strcasecmp($key, $ds_exp[0])) continue;

                    return $this->_prepare_order($key, $data[0], $ds_exp[1]);
                }
            }

            foreach ($orders as $key => $data) {
                if (empty($data[0]) || empty($data[1])) continue;

                return $this->_prepare_order($key, $data[0], $data[1]);
            }

            foreach ($orders as $key => $data) {
                if (empty($data[0])) continue;

                return $this->_prepare_order($key, $data[0], !empty($data[1]) ? $data[1] : $def_order);
            }

            return $this->_prepare_order('', $def_orderby, $def_order);
        }

        private function _prepare_order($orderbykey, $orderby, $order) {
            return [
                'orderbykey' => $orderbykey,
                'orderby' => $orderby,
                'order' => strtoupper($order) == 'ASC' ? 'ASC' : 'DESC'
            ];
        }

        function get_thwidth_filter() {

            $columns = $this->get_thwidth();
            $columns = apply_filters('pntable_thwidth_' . $this->page, $columns);

            return $columns;
        }

        function get_thwidth() {
            return array();
        }

        function get_columns_filter() {

            $columns = $this->get_columns();
            $columns = apply_filters('pntable_columns_' . $this->page, $columns);

            return $columns;
        }

        function get_columns() {
            return array();
        }

        function get_search() {
            return array();
        }

        function get_submenu() {
            return array();
        }

        function get_bulk_actions() {
            return array();
        }

        function prev_tablenav($which) {

        }

        function extra_tablenav($which) {

        }

        function prepare_items() {

        }

        function column_default($item, $column_key) {
            return '';
        }

        function tr_class($tr_class, $item) {
            return $tr_class;
        }

        function txtfile_access() {

            $access = apply_filters('txtfile_access_' . $this->page, array('read'));

            return $access;
        }
    }
}

if (!function_exists('premium_pntable_print_action')) {
    add_action('premium_action_pntable_print_action', 'premium_pntable_print_action');
    function premium_pntable_print_action() {

        $rtn_url = pn_strip_input(urldecode(is_param_get('url')));
        $scheme_url = parse_url($rtn_url);
        if (isset($scheme_url['query'])) {
            parse_str($scheme_url['query'], $pars_data);
            $page = pn_strip_input(is_isset($pars_data, 'page'));
            if ($page) {
                $not = array('meth', 'yid', 'lang', 'url');
                foreach ($pars_data as $pars_data_key => $pars_data_val) {
                    if (!in_array($pars_data_key, $not)) {
                        $_GET[$pars_data_key] = $pars_data_val;
                    }
                }
                $class_name = $page . '_Table_List';
                if (class_exists($class_name)) {
                    $table = new $class_name($page);
                    if ($table->txtfile > 0) {
                        pn_only_caps($table->txtfile_access(), 'get');
                        $table->display_print();
                        exit;
                    }
                }
            }
        }

        _e('Error!', 'premium');
    }
}

if (!function_exists('premium_pntable_head_action')) {
    add_action('premium_action_pntable_head_action', 'premium_pntable_head_action');
    function premium_pntable_head_action() {

        pn_only_caps(array('read'));

        $ui = wp_get_current_user();
        $user_id = intval($ui->ID);

        $lost = array('reply');

        if ($user_id) {

            $old_count_items = intval(is_param_post('old_count_items'));
            $page = pn_strip_input(is_param_post('page'));
            $count_items = intval(is_param_post('count_items'));
            $default_sort = pn_strip_input(is_param_post('default_sort'));
            $hide_columns = is_param_post('hide_columns');
            if (!is_array($hide_columns)) $hide_columns = array();

            if ($old_count_items != $count_items) {
                $lost[] = 'paged';
            }

            $hides = array();
            foreach ($hide_columns as $hide_column) {
                $hide_column = pn_strip_symbols($hide_column, '_');
                if ($hide_column) {
                    $hides[$hide_column] = 1;
                }
            }

            if ($page && class_exists("{$page}_Table_List")) {
                $options = [
                    'count_items' => $count_items,
                    'hide_columns' => $hides,
                    'default_sort' => $default_sort,
                ];
                update_user_meta($user_id, "{$page}_Table_List_options", $options);
            }

        }

        wp_redirect(get_safe_url(pn_admin_filter_data('', $lost)));
    }
}