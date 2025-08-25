<?php
if (!defined('ABSPATH')) exit();

function _order_by_parser() {
    global $premiumbox;

    $order_by = 'menu_order ASC';
    $sort = intval($premiumbox->get_option('newparser', 'parser_sort'));
    if ($sort) {
        $order_by = 'title_pair_give ASC, title_pair_get ASC';
    }

    return $order_by;
}

#[AllowDynamicProperties]
class ParserPair {
    public function __construct($list) {
        foreach ($list as $key => $value) {
            $this->$key = $value;
        }
    }

    private function compute_property($name) {
        switch ($name) {
            case 'rate_give':
                return get_parser_course($this->pair_give);
            case 'rate_get':
                return get_parser_course($this->pair_get);
            case 'rate':
                return is_rate($this->rate_give, $this->rate_get);
            case 'best_rate':
                return is_best_rate($this->rate_give, $this->rate_get);
            case 'title':
                return get_parser_title($this);
            case 'title_course':
                return !show_parser_courses() ? $this->title : "{$this->title} [{$this->rate_give} => {$this->rate_get}]";
            case 'title_rate':
                return !show_parser_courses() ? $this->title : "{$this->title} [{$this->rate}]";
            case 'title_best_rate':
                return !show_parser_courses() ? $this->title : "{$this->title} [{$this->best_rate}]";
        }

        return null;
    }

    public function __get($name) {
        return $this->$name = $this->compute_property($name);
    }
}

function get_parser_pairs_course() {
    global $pn_parser_pairs_cours, $wpdb;

    if (!is_array($pn_parser_pairs_cours)) {
        $pn_parser_pairs_cours = array();
        $order_by = _order_by_parser();
        $lists = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "parser_pairs ORDER BY $order_by");
        foreach ($lists as $list) {
            $pn_parser_pairs_cours[$list->id] = new ParserPair($list);
        }
    }

    return $pn_parser_pairs_cours;
}

function get_parser_list($output = '') {
    global $wpdb;

    $where = '';
    $in = create_data_for_db($output, 'int');
    if ($in) {
        $where .= "AND id IN($in)";
    }
    $order_by = _order_by_parser();
    $lists = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "parser_pairs WHERE id > 0 $where ORDER BY $order_by");

    return $lists;
}

function get_parser_title($item) {

    return get_parser_give($item) . '-' . get_parser_get($item) . ' (' . get_parser_source($item) . ')';
}

function get_parser_give($item) {

    return str_replace('[copy]', '', pn_strip_input($item->title_pair_give));
}

function get_parser_get($item) {

    return str_replace('[copy]', '', pn_strip_input($item->title_pair_get));
}

function get_parser_source($item) {

    return str_replace('[copy]', '', pn_strip_input(ctv_ml($item->title_birg)));
}

function get_parser_rate_give($item, $decimal = 12) {

    return is_out_sum(get_parser_course($item->pair_give), $decimal, 'course');
}

function get_parser_rate_get($item, $decimal = 12) {

    return is_out_sum(get_parser_course($item->pair_get), $decimal, 'course');
}

function show_parser_courses() {
    global $premiumbox;

    $hidecours = intval($premiumbox->get_option('newparser', 'hidecours'));
    if ($hidecours) {
        return 0;
    }

    return 1;
}

add_action('pn_adminpage_content_pn_new_parser', 'newparser_pn_adminpage_content_pn_cron', 9);
add_action('pn_adminpage_content_pn_parser_logs', 'newparser_pn_adminpage_content_pn_cron', 9);
add_action('pn_adminpage_content_pn_settings_new_parser', 'newparser_pn_adminpage_content_pn_cron', 9);
function newparser_pn_adminpage_content_pn_cron() {
    ?>
    <div class="premium_substrate">
        <?php _e('Cron URL for updating rates of CB and cryptocurrencies', 'pn'); ?><br/>
        <a href="<?php echo get_cron_link('new_parser_upload_data'); ?>" target="_blank"><?php echo get_cron_link('new_parser_upload_data'); ?></a>
    </div>
    <?php
}

/* currency codes */
add_filter('standart_course_cc', 'newparser_standart_course_cc', 10, 2);
function newparser_standart_course_cc($ind, $item) {

    if (is_isset($item, 'new_parser') > 0) {
        return 1;
    }

    return $ind;
}

add_filter('pn_currency_code_addform', 'newparser_pn_currency_code_addform', 10, 2);
function newparser_pn_currency_code_addform($options, $data) {
    global $wpdb;

    $options[] = array(
        'view' => 'line',
    );
    $options[] = array(
        'view' => 'h3',
        'title' => '',
        'submit' => __('Save', 'pn'),
    );

    $parsers = array();
    $parsers[0] = '-- ' . __('No item', 'pn') . ' --';

    $en_parsers = get_parser_pairs_course();
    foreach ($en_parsers as $item) {
        $parsers[$item->id] = $item->title_rate;
    }
    $options['new_parser'] = array(
        'view' => 'select_search',
        'title' => __('Automatic change of rate', 'pn'),
        'options' => $parsers,
        'default' => is_isset($data, 'new_parser'),
        'name' => 'new_parser',
        'work' => 'input',
    );
    $options['new_parser_actions'] = array(
        'view' => 'inputbig',
        'title' => __('Add to rate', 'pn'),
        'default' => is_isset($data, 'new_parser_actions'),
        'name' => 'new_parser_actions',
    );

    return $options;
}

add_filter('pn_currency_code_addform_post', 'newparser_pn_currency_code_addform_post');
function newparser_pn_currency_code_addform_post($array) {

    $array['new_parser'] = intval(is_param_post('new_parser'));
    $array['new_parser_actions'] = pn_parser_num(is_param_post('new_parser_actions'));

    return $array;
}

add_filter('pntable_columns_pn_currency_codes', 'newparser_pntable_columns_pn_currency_codes');
function newparser_pntable_columns_pn_currency_codes($columns) {

    $columns['new_parser'] = __('Rate automatic adjustment', 'pn');

    return $columns;
}

add_filter('pntable_column_pn_currency_codes', 'newparser_pntable_column_pn_currency_codes', 10, 3);
function newparser_pntable_column_pn_currency_codes($html, $column_name, $item) {

    if ('new_parser' == $column_name) {
        $parser_pairs = get_parser_pairs_course();

        $html = '
		<div style="width: 200px;">
		';
        $html .= '
			<select name="new_parser[' . $item->id . ']" autocomplete="off" id="currency_code_new_parser_' . $item->id . '" class="currency_code_new_parser" style="width: 200px; display: block; margin: 0 0 10px;"> 
			';
        $enable = 0;
        $html .= '<option value="0" ' . selected($item->new_parser, 0, false) . '>-- ' . __('No item', 'pn') . ' --</option>';
        if (is_array($parser_pairs)) {
            foreach ($parser_pairs as $parser) {
                $selected = '';
                if (!$enable and $item->new_parser and ($selected = selected($item->new_parser, $parser->id, false))) {
                    $enable = 1;
                }

                $html .= "<option value=\"{$parser->id}\"{$selected}>{$parser->title_rate}</option>";
            }
        }
        $style = 'style="display: none;"';
        if (1 == $enable) {
            $style = '';
        }
        $html .= '
			</select>
			<div id="the_currency_code_new_parser_' . $item->id . '" ' . $style . '>
				<input type="text" name="new_parser_actions[' . $item->id . ']" value="' . pn_parser_num($item->new_parser_actions) . '" />
			</div>		
			';
        $html .= '</div>';
    }

    return $html;
}

add_action('pntable_currency_codes_save', 'new_parser_pntable_currency_codes_save');
function new_parser_pntable_currency_codes_save() {
    global $wpdb;

    if (isset($_POST['new_parser'], $_POST['new_parser_actions']) and is_array($_POST['new_parser'])) {
        foreach ($_POST['new_parser'] as $id => $parser_id) {
            $id = intval($id);
            $item = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "currency_codes WHERE id = '$id'");
            if (isset($item->id)) {

                $new_parser = intval($parser_id);
                $new_parser_actions = pn_parser_num($_POST['new_parser_actions'][$id]);

                $array = array();
                if ($new_parser > 0) {
                    $array['new_parser'] = $new_parser;
                    $array['new_parser_actions'] = $new_parser_actions;
                } else {
                    $array['new_parser'] = 0;
                    $array['new_parser_actions'] = 0;
                }
                $result = $wpdb->update($wpdb->prefix . 'currency_codes', $array, array('id' => $id));

                do_action('item_currency_code_save', $item->id, $item, $result, $array);

            }
        }
    }
}

add_action('pn_adminpage_content_pn_currency_codes', 'new_parser_pn_adminpage_content_pn_currency_codes');
function new_parser_pn_adminpage_content_pn_currency_codes() {
    ?>
    <style>
        .not_adaptive th.pntable-column-new_parser {
            width: 200px;
        }
    </style>
    <script type="text/javascript">
        jQuery(function ($) {

            $('.currency_code_new_parser').on('change', function () {

                var id = $(this).attr('id').replace('currency_code_new_parser_', '');
                var vale = $(this).val();
                if (vale > 0) {
                    $('#the_currency_code_new_parser_' + id).show();
                } else {
                    $('#the_currency_code_new_parser_' + id).hide();
                }

            });

        });
    </script>
    <?php
}

add_filter('is_cc_rate', 'new_parser_is_cc_rate', 50, 2);
function new_parser_is_cc_rate($course, $item) {

    if ($item->new_parser > 0) {
        $pairs_course = get_parser_pairs_course();
        if (isset($pairs_course[$item->new_parser])) {
            $curs_data = $pairs_course[$item->new_parser];
            $curs = is_rate(get_parser_course($curs_data->pair_give), get_parser_course($curs_data->pair_get));
            $new_curs = rate_plus_interest($curs, $item->new_parser_actions);

            //return is_sum(rate_plus_interest($pairs_course[$item->new_parser]->rate, $item->new_parser_actions));
            return is_sum($new_curs);
        }

        return 0;
    }

    return $course;
}

/* end currency codes */

/* directions */
add_filter('standart_course_direction', 'new_parser_standart_course_direction', 10, 2);
function new_parser_standart_course_direction($ind, $item) {

    if ($item->new_parser > 0) {
        $ind = 1;
    }

    return $ind;
}

add_action('pn_adminpage_content_pn_directions', 'new_parser_pn_adminpage_content_pn_directions');
function new_parser_pn_adminpage_content_pn_directions() {
    ?>
    <style>
        .not_adaptive th.pntable-column-new_parser {
            width: 230px;
        }
    </style>
    <script type="text/javascript">
        jQuery(function ($) {

            $('.directions_new_parser').change(function () {

                var id = $(this).attr('id').replace('directions_new_parser_', '');
                var vale = $(this).val();
                if (vale > 0) {
                    $('#the_directions_new_parser_' + id).show();
                } else {
                    $('#the_directions_new_parser_' + id).hide();
                }

            });

        });
    </script>
    <?php
}

add_filter('pntable_columns_pn_directions', 'new_parser_pntable_columns_pn_directions');
function new_parser_pntable_columns_pn_directions($columns) {

    $new_columns = array();
    $new_columns['new_parser'] = __('Auto adjust rate', 'pn');
    $columns = pn_array_insert($columns, 'course_get', $new_columns);

    return $columns;
}

add_action('pntable_directions_save', 'new_parser_pn_directions_save');
function new_parser_pn_directions_save() {
    global $wpdb;

    if (isset($_POST['new_parser'], $_POST['new_parser_actions_give'], $_POST['new_parser_actions_get']) and is_array($_POST['new_parser'])) {
        foreach ($_POST['new_parser'] as $id => $parser_id) {
            $id = intval($id);
            $item = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "directions WHERE id = '$id'");
            if (isset($item->id)) {

                $parser = intval($parser_id);
                $nums1 = pn_parser_num($_POST['new_parser_actions_give'][$id]);
                $nums2 = pn_parser_num($_POST['new_parser_actions_get'][$id]);
                $array = array();
                if ($parser > 0) {
                    $array['new_parser'] = $parser;
                    $array['new_parser_actions_give'] = $nums1;
                    $array['new_parser_actions_get'] = $nums2;
                } else {
                    $array['new_parser'] = 0;
                    $array['new_parser_actions_give'] = 0;
                    $array['new_parser_actions_get'] = 0;
                }

                $result = $wpdb->update($wpdb->prefix . 'directions', $array, array('id' => $id));

                do_action('item_direction_save', $item->id, $item, $result, $array);

            }
        }
    }
}

add_filter('pntable_column_pn_directions', 'new_parser_pntable_column_pn_directions', 10, 3);
function new_parser_pntable_column_pn_directions($show, $column_name, $item) {

    if ('new_parser' == $column_name) {

        $parser_pairs = get_parser_pairs_course();

        $html = '
		<div style="width: 230px;">
		';

        $html .= '
		<select name="new_parser[' . $item->id . ']" autocomplete="off" id="directions_new_parser_' . $item->id . '" class="directions_new_parser" style="width: 230px; display: block; margin: 0 0 10px;"> 
		';
        $enable = 0;

        $html .= '<option value="0" ' . selected($item->new_parser, 0, false) . '>-- ' . __('No item', 'pn') . ' --</option>';

        if (is_array($parser_pairs)) {
            foreach ($parser_pairs as $parser) {
                $selected = '';
                if (!$enable and $item->new_parser and ($selected = selected($item->new_parser, $parser->id, false))) {
                    $enable = 1;
                }

                $html .= "<option value=\"{$parser->id}\"{$selected}>{$parser->title_course}</option>";
            }
        }

        $style = 'style="display: none;"';
        if (1 == $enable) {
            $style = '';
        }

        $html .= '
		</select>
			
		<div id="the_directions_new_parser_' . $item->id . '" ' . $style . '>
			<input type="text" name="new_parser_actions_give[' . $item->id . ']" style="width: 95px; float: left; margin: 0px 0px 0 0;" value="' . pn_parser_num($item->new_parser_actions_give) . '" />
			<div style="float: left; margin: 3px 2px 0 2px;">=></div>
			<input type="text" name="new_parser_actions_get[' . $item->id . ']" style="width: 95px; float: left; margin: 0px 0px 0 0;" value="' . pn_parser_num($item->new_parser_actions_get) . '" />				
				<div class="premium_clear"></div>
		</div>		
		';

        $html .= '</div>';

        return $html;
    }

    return $show;
}

if (!function_exists('autoadjust_list_tabs_direction')) {
    add_filter('list_tabs_direction', 'autoadjust_list_tabs_direction', 10, 2);
    function autoadjust_list_tabs_direction($list_tabs, $item) {

        $new_list_tabs = array();
        $tab_title = '';
        $id = intval(is_isset($item, 'new_parser'));
        if ($id > 0) {
            $tab_title = ' <span class="bgreen">*</span>';
        }
        $new_list_tabs['autoadjust'] = __('Auto adjust rate', 'pn') . $tab_title;
        $list_tabs = pn_array_insert($list_tabs, 'tab2', $new_list_tabs);

        return $list_tabs;
    }
}

add_action('tab_direction_autoadjust', 'new_parser_tab_direction_autoadjust', 1, 2);
function new_parser_tab_direction_autoadjust($data, $data_id) {

    $parsers = array();
    $parsers[0] = '-- ' . __('No item', 'pn') . ' --';

    $en_parsers = get_parser_pairs_course();
    if (is_array($en_parsers)) {
        foreach ($en_parsers as $item) {
            $parsers[$item->id] = $item->title_course;
        }
    }
    ?>
    <div class="add_tabs_line">
        <div class="add_tabs_title"><?php _e('Parsers 2.0', 'pn'); ?></div>
        <div class="add_tabs_submit">
            <input type="submit" name="" class="button" value="<?php _e('Save', 'pn'); ?>"/>
        </div>
    </div>
    <div class="add_tabs_line">
        <div class="add_tabs_single long">
            <div class="add_tabs_sublabel"><span><?php _e('Auto adjust rate', 'pn'); ?></span></div>
            <div class="premium_wrap_standart">

                <?php
                $form = new PremiumForm();
                $atts = array();
                $atts['id'] = 'the_new_parser_select';
                $option_data = array();
                $opts = array();
                foreach ($parsers as $parser_key => $parser_title) {
                    $opts[$parser_key] = $parser_title;
                }
                $form->select_search('new_parser', $opts, is_isset($data, 'new_parser'), $atts, $option_data);
                ?>

            </div>
        </div>
    </div>
    <div class="add_tabs_line">
        <div class="add_tabs_single">
            <div class="add_tabs_sublabel"><span><?php _e('Add to rate', 'pn'); ?> (<?php _e('Send', 'pn'); ?>)</span>
            </div>
            <div class="premium_wrap_standart">
                <input type="text" name="new_parser_actions_give" style="width: 100%;" value="<?php echo pn_parser_num(is_isset($data, 'new_parser_actions_give')); ?>"/>
            </div>
        </div>
        <div class="add_tabs_single">
            <div class="add_tabs_sublabel">
                <span><?php _e('Add to rate', 'pn'); ?> (<?php _e('Receive', 'pn'); ?>)</span></div>
            <div class="premium_wrap_standart">
                <input type="text" name="new_parser_actions_get" style="width: 100%;" value="<?php echo pn_parser_num(is_isset($data, 'new_parser_actions_get')); ?>"/>
            </div>
        </div>
    </div>
    <?php
}

add_filter('pn_direction_addform_post', 'new_parser_pn_direction_addform_post');
function new_parser_pn_direction_addform_post($array) {

    $array['new_parser'] = $parser = intval(is_param_post('new_parser'));
    if ($parser > 0) {
        $array['new_parser_actions_give'] = pn_parser_num(is_param_post('new_parser_actions_give'));
        $array['new_parser_actions_get'] = pn_parser_num(is_param_post('new_parser_actions_get'));
    } else {
        $array['new_parser_actions_give'] = 0;
        $array['new_parser_actions_get'] = 0;
    }

    return $array;
}

add_filter('get_calc_data', 'get_calc_data_newparser', 50, 2);
function get_calc_data_newparser($cdata, $calc_data) {

    $direction = $calc_data['direction'];
    $set_course = intval(is_isset($calc_data, 'set_course'));
    if ($direction->new_parser > 0 and 1 != $set_course) {
        $pairs_course = get_parser_pairs_course();
        $vd1 = $calc_data['vd1'];
        $vd2 = $calc_data['vd2'];
        if (isset($pairs_course[$direction->new_parser])) {
            $curs_data = $pairs_course[$direction->new_parser];
            $curs1 = get_parser_course($curs_data->pair_give);
            $curs2 = get_parser_course($curs_data->pair_get);
            $ncurs1 = rate_plus_interest($curs1, $direction->new_parser_actions_give);
            $ncurs2 = rate_plus_interest($curs2, $direction->new_parser_actions_get);
            //$ncurs1 = rate_plus_interest($curs_data->rate_give, $direction->new_parser_actions_give);
            //$ncurs2 = rate_plus_interest($curs_data->rate_get, $direction->new_parser_actions_get);
            $cdata['course_give'] = is_sum($ncurs1, $vd1->currency_decimal);
            $cdata['course_get'] = is_sum($ncurs2, $vd2->currency_decimal);

            return $cdata;
        }
        $cdata['course_give'] = 0;
        $cdata['course_get'] = 0;
    }

    return $cdata;
}

add_filter('is_course_direction', 'newparser_is_course_direction', 50, 5);
function newparser_is_course_direction($arr, $direction, $vd1, $vd2, $place) {

    if ($direction->new_parser > 0) {
        $pairs_course = get_parser_pairs_course();
        if (isset($pairs_course[$direction->new_parser])) {
            $curs_data = $pairs_course[$direction->new_parser];
            $curs1 = get_parser_course($curs_data->pair_give);
            $ncurs1 = rate_plus_interest($curs1, $direction->new_parser_actions_give);
            //$ncurs1 = rate_plus_interest($curs_data->rate_give, $direction->new_parser_actions_give);
            if (isset($vd1->currency_decimal)) {
                $arr['give'] = is_sum($ncurs1, $vd1->currency_decimal);
            } else {
                $arr['give'] = is_sum($ncurs1);
            }
            $curs2 = get_parser_course($curs_data->pair_get);
            $ncurs2 = rate_plus_interest($curs2, $direction->new_parser_actions_get);
            //$ncurs2 = rate_plus_interest($curs_data->rate_get, $direction->new_parser_actions_get);
            if (isset($vd2->currency_decimal)) {
                $arr['get'] = is_sum($ncurs2, $vd2->currency_decimal);
            } else {
                $arr['get'] = is_sum($ncurs2);
            }
            return $arr;
        }
        $arr['give'] = 0;
        $arr['get'] = 0;
    }

    return $arr;
}

/* end directions */

/* best */
add_action('pn_adminpage_content_pn_bcorrs', 'new_parser_pn_admin_content_pn_bc_adjs');
add_action('pn_adminpage_content_pn_bc_corrs', 'new_parser_pn_admin_content_pn_bc_adjs');
function new_parser_pn_admin_content_pn_bc_adjs() {
    ?>
    <style>
        .not_adaptive th.pntable-column-new_parser {
            width: 230px;
        }
    </style>
    <script type="text/javascript">
        jQuery(function ($) {
            $('.bccorrs_parser').change(function () {
                var id = $(this).attr('id').replace('bccorrs_parser_', '');
                var vale = $(this).val();
                if (vale > 0) {
                    $('#the_bccorrs_parser_' + id).show();
                } else {
                    $('#the_bccorrs_parser_' + id).hide();
                }
            });
        });
    </script>
    <?php
}

add_filter('pntable_columns_pn_bcorrs', 'new_parser_pntable_columns_pn_bc_adjs');
add_filter('pntable_columns_pn_bc_corrs', 'new_parser_pntable_columns_pn_bc_adjs');
function new_parser_pntable_columns_pn_bc_adjs($columns) {

    $new_columns = array();
    $new_columns['new_parser'] = __('Auto adjust rate', 'pn');
    $columns = pn_array_insert($columns, 'standart', $new_columns);

    return $columns;
}

add_action('pntable_bccorrs_save', 'new_parser_pn_bccorrs_save');
function new_parser_pn_bccorrs_save() {
    global $wpdb;

    if (isset($_POST['standart_new_parser']) and is_array($_POST['standart_new_parser'])) {
        foreach ($_POST['standart_new_parser'] as $id => $parser_id) {
            $id = intval($id);
            $item = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "bestchange_directions WHERE id = '$id'");
            if (isset($item->id)) {

                $parser = intval($parser_id);
                $standart_parser_actions_give = pn_parser_num($_POST['standart_new_parser_actions_give'][$id]);
                $standart_parser_actions_get = pn_parser_num($_POST['standart_new_parser_actions_get'][$id]);
                $array = array();
                if ($parser > 0) {
                    $array['standart_new_parser'] = $parser;
                    $array['standart_new_parser_actions_give'] = $standart_parser_actions_give;
                    $array['standart_new_parser_actions_get'] = $standart_parser_actions_get;
                } else {
                    $array['standart_new_parser'] = 0;
                    $array['standart_new_parser_actions_give'] = 0;
                    $array['standart_new_parser_actions_get'] = 0;
                }
                $result = $wpdb->update($wpdb->prefix . 'bestchange_directions', $array, array('id' => $id));

                do_action('item_bccorrs_save', $item->id, $item, $result, $array);

            }
        }
    }
}

add_action('pntable_bcorrs_save', 'new_parser_pn_bcorrs_save');
function new_parser_pn_bcorrs_save() {
    global $wpdb;

    if (isset($_POST['standart_new_parser']) and is_array($_POST['standart_new_parser'])) {
        foreach ($_POST['standart_new_parser'] as $id => $parser_id) {
            $id = intval($id);
            $item = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "bestchangeapi_directions WHERE id = '$id'");
            if (isset($item->id)) {

                $parser = intval($parser_id);
                $standart_parser_actions_give = pn_parser_num($_POST['standart_new_parser_actions_give'][$id]);
                $standart_parser_actions_get = pn_parser_num($_POST['standart_new_parser_actions_get'][$id]);
                $array = array();
                if ($parser > 0) {
                    $array['standart_new_parser'] = $parser;
                    $array['standart_new_parser_actions_give'] = $standart_parser_actions_give;
                    $array['standart_new_parser_actions_get'] = $standart_parser_actions_get;
                } else {
                    $array['standart_new_parser'] = 0;
                    $array['standart_new_parser_actions_give'] = 0;
                    $array['standart_new_parser_actions_get'] = 0;
                }
                $result = $wpdb->update($wpdb->prefix . 'bestchangeapi_directions', $array, array('id' => $id));

                do_action('item_bcorrs_save', $item->id, $item, $result, $array);

            }
        }
    }
}

add_filter('pntable_column_pn_bcorrs', 'new_parser_pntable_column_pn_bc_adjs', 10, 3);
add_filter('pntable_column_pn_bc_corrs', 'new_parser_pntable_column_pn_bc_adjs', 10, 3);
function new_parser_pntable_column_pn_bc_adjs($show, $column_name, $item) {
    if ('new_parser' == $column_name) {

        $parsers = get_parser_pairs_course();

        $html = '
		<div style="width: 230px;">
		';

        $html .= '
		<select name="standart_new_parser[' . $item->id . ']" autocomplete="off" id="bccorrs_parser_' . $item->id . '" class="bccorrs_parser" style="width: 230px; display: block; margin: 0 0 10px;"> 
		';
        $enable = 0;
        $html .= '<option value="0" ' . selected($item->standart_new_parser, 0, false) . '>-- ' . __('No item', 'pn') . ' --</option>';
        if (is_array($parsers)) {
            foreach ($parsers as $parser) {
                $selected = '';
                if (!$enable and $item->standart_new_parser and ($selected = selected($item->standart_new_parser, $parser->id, false))) {
                    $enable = 1;
                }

                $html .= "<option value=\"{$parser->id}\"{$selected}>{$parser->title_course}</option>";
            }
        }

        $style = 'style="display: none;"';
        if (1 == $enable) {
            $style = '';
        }

        $html .= '
		</select>
			
		<div id="the_bccorrs_parser_' . $item->id . '" ' . $style . '>
			<input type="text" name="standart_new_parser_actions_give[' . $item->id . ']" style="width: 95px; float: left; margin: 0px 0px 0 0;" value="' . pn_parser_num($item->standart_new_parser_actions_give) . '" />
			<div style="float: left; margin: 3px 2px 0 2px;">=></div>
			<input type="text" name="standart_new_parser_actions_get[' . $item->id . ']" style="width: 95px; float: left; margin: 0px 0px 0 0;" value="' . pn_parser_num($item->standart_new_parser_actions_get) . '" />				
				<div class="premium_clear"></div>
		</div>		
		';

        $html .= '</div>';

        return $html;
    }

    return $show;
}

add_filter('pn_bcorrs_addform', 'new_parser_pn_bcadjs_addform', 10, 2);
add_filter('pn_bccorrs_addform', 'new_parser_pn_bcadjs_addform', 10, 2);
function new_parser_pn_bcadjs_addform($options, $data) {

    $en_parsers = get_parser_pairs_course();

    $parsers = $ind_parsers = array();
    $parsers[0] = $ind_parsers[0] = '-- ' . __('No item', 'pn') . ' --';
    if (is_array($en_parsers)) {
        foreach ($en_parsers as $item) {
            $parsers[$item->id] = $item->title_course;
            $ind_parsers[$item->id] = $item->title_best_rate;
        }
    }

    $new_options = array();
    $new_options['minsum_new_parser'] = array(
        'view' => 'select',
        'title' => __('Auto adjust for min rate', 'pn'),
        'options' => $ind_parsers,
        'default' => is_isset($data, 'minsum_new_parser'),
        'name' => 'minsum_new_parser',
        'work' => 'input',
    );
    $new_options['minsum_new_parser_actions'] = array(
        'view' => 'inputbig',
        'title' => __('Add to min rate', 'pn'),
        'default' => is_isset($data, 'minsum_new_parser_actions'),
        'name' => 'minsum_new_parser_actions',
    );
    $options = pn_array_insert($options, 'min_sum', $new_options);

    $new_options = array();
    $new_options['maxsum_new_parser'] = array(
        'view' => 'select',
        'title' => __('Auto adjust for max rate', 'pn'),
        'options' => $ind_parsers,
        'default' => is_isset($data, 'maxsum_new_parser'),
        'name' => 'maxsum_new_parser',
        'work' => 'input',
    );
    $new_options['maxsum_new_parser_actions'] = array(
        'view' => 'inputbig',
        'title' => __('Add to max rate', 'pn'),
        'default' => is_isset($data, 'maxsum_new_parser_actions'),
        'name' => 'maxsum_new_parser_actions',
    );
    $options = pn_array_insert($options, 'max_sum', $new_options);

    $new_options = array();
    $new_options['standart_new_parser'] = array(
        'view' => 'select',
        'title' => __('Automatic change of rate', 'pn'),
        'options' => $parsers,
        'default' => is_isset($data, 'standart_new_parser'),
        'name' => 'standart_new_parser',
        'work' => 'input',
    );
    $new_options['standart_new_parser_actions_give'] = array(
        'view' => 'inputbig',
        'title' => __('Add to Send rate', 'pn'),
        'default' => is_isset($data, 'standart_new_parser_actions_give'),
        'name' => 'standart_new_parser_actions_give',
    );
    $new_options['standart_new_parser_actions_get'] = array(
        'view' => 'inputbig',
        'title' => __('Add to Receive rate', 'pn'),
        'default' => is_isset($data, 'standart_new_parser_actions_get'),
        'name' => 'standart_new_parser_actions_get',
    );
    $options = pn_array_insert($options, 'standart_course_get', $new_options);

    return $options;
}

add_filter('pn_bcorrs_addform_post', 'new_parser_pn_bcadjs_addform_post');
add_filter('pn_bccorrs_addform_post', 'new_parser_pn_bcadjs_addform_post');
function new_parser_pn_bcadjs_addform_post($array) {

    $array['standart_new_parser'] = intval(is_param_post('standart_new_parser'));
    $array['standart_new_parser_actions_give'] = pn_parser_num(is_param_post('standart_new_parser_actions_give'));
    $array['standart_new_parser_actions_get'] = pn_parser_num(is_param_post('standart_new_parser_actions_get'));
    $array['minsum_new_parser'] = intval(is_param_post('minsum_new_parser'));
    $array['minsum_new_parser_actions'] = pn_parser_num(is_param_post('minsum_new_parser_actions'));
    $array['maxsum_new_parser'] = intval(is_param_post('maxsum_new_parser'));
    $array['maxsum_new_parser_actions'] = pn_parser_num(is_param_post('maxsum_new_parser_actions'));

    return $array;
}

add_action('tab_bestchangeapi_min_sum', 'new_parser_tab_bestchangeapi_min_sum', 10, 2);
function new_parser_tab_bestchangeapi_min_sum($data, $broker) {

    $parsers = array();
    $parsers[0] = '-- ' . __('No item', 'pn') . ' --';

    $en_parsers = get_parser_pairs_course();
    if (is_array($en_parsers)) {
        foreach ($en_parsers as $item) {
            $parsers[$item->id] = $item->title_best_rate;
        }
    }
    ?>
    <div class="add_tabs_line">
        <div class="add_tabs_single">
            <div class="add_tabs_sublabel"><span><?php _e('Auto adjust for min rate', 'pn'); ?></span></div>
            <div class="premium_wrap_standart">

                <?php
                $form = new PremiumForm();
                $atts = array();
                $option_data = array();
                $opts = array();
                foreach ($parsers as $parser_key => $parser_title) {
                    $opts[$parser_key] = $parser_title;
                }
                $form->select_search('bcorrs_minsum_new_parser', $opts, is_isset($broker, 'minsum_new_parser'), $atts, $option_data);
                ?>

            </div>
        </div>
        <div class="add_tabs_single">
            <div class="add_tabs_sublabel"><span><?php _e('Add to rate', 'pn'); ?></span></div>
            <div class="premium_wrap_standart">
                <input type="text" name="bcorrs_minsum_new_parser_actions" style="width: 100%;" value="<?php echo pn_parser_num(is_isset($broker, 'minsum_new_parser_actions')); ?>"/>
            </div>
        </div>
    </div>
    <?php
}

add_action('tab_bestchange_min_sum', 'new_parser_tab_bestchange_min_sum', 10, 2);
function new_parser_tab_bestchange_min_sum($data, $broker) {

    $parsers = array();
    $parsers[0] = '-- ' . __('No item', 'pn') . ' --';

    $en_parsers = get_parser_pairs_course();
    if (is_array($en_parsers)) {
        foreach ($en_parsers as $item) {
            $parsers[$item->id] = $item->title_best_rate;
        }
    }
    ?>
    <div class="add_tabs_line">
        <div class="add_tabs_single">
            <div class="add_tabs_sublabel"><span><?php _e('Auto adjust for min rate', 'pn'); ?></span></div>
            <div class="premium_wrap_standart">

                <?php
                $form = new PremiumForm();
                $atts = array();
                $option_data = array();
                $opts = array();
                foreach ($parsers as $parser_key => $parser_title) {
                    $opts[$parser_key] = $parser_title;
                }
                $form->select_search('bccorrs_minsum_new_parser', $opts, is_isset($broker, 'minsum_new_parser'), $atts, $option_data);
                ?>

            </div>
        </div>
        <div class="add_tabs_single">
            <div class="add_tabs_sublabel"><span><?php _e('Add to rate', 'pn'); ?></span></div>
            <div class="premium_wrap_standart">
                <input type="text" name="bccorrs_minsum_new_parser_actions" style="width: 100%;" value="<?php echo pn_parser_num(is_isset($broker, 'minsum_new_parser_actions')); ?>"/>
            </div>
        </div>
    </div>
    <?php
}

add_action('tab_bestchangeapi_max_sum', 'new_parser_tab_bestchangeapi_max_sum', 10, 2);
function new_parser_tab_bestchangeapi_max_sum($data, $broker) {

    $parsers = array();
    $parsers[0] = '-- ' . __('No item', 'pn') . ' --';
    $en_parsers = get_parser_pairs_course();
    if (is_array($en_parsers)) {
        foreach ($en_parsers as $item) {
            $parsers[$item->id] = $item->title_best_rate;
        }
    }
    ?>
    <div class="add_tabs_line">
        <div class="add_tabs_single">
            <div class="add_tabs_sublabel"><span><?php _e('Auto adjust for max rate', 'pn'); ?></span></div>
            <div class="premium_wrap_standart">

                <?php
                $form = new PremiumForm();
                $atts = array();
                $option_data = array();
                $opts = array();
                foreach ($parsers as $parser_key => $parser_title) {
                    $opts[$parser_key] = $parser_title;
                }
                $form->select_search('bcorrs_maxsum_new_parser', $opts, is_isset($broker, 'maxsum_new_parser'), $atts, $option_data);
                ?>

            </div>
        </div>
        <div class="add_tabs_single">
            <div class="add_tabs_sublabel"><span><?php _e('Add to rate', 'pn'); ?></span></div>
            <div class="premium_wrap_standart">
                <input type="text" name="bcorrs_maxsum_new_parser_actions" style="width: 100%;" value="<?php echo pn_parser_num(is_isset($broker, 'maxsum_new_parser_actions')); ?>"/>
            </div>
        </div>
    </div>
    <?php
}

add_action('tab_bestchange_max_sum', 'new_parser_tab_bestchange_max_sum', 10, 2);
function new_parser_tab_bestchange_max_sum($data, $broker) {

    $parsers = array();
    $parsers[0] = '-- ' . __('No item', 'pn') . ' --';
    $en_parsers = get_parser_pairs_course();
    if (is_array($en_parsers)) {
        foreach ($en_parsers as $item) {
            $parsers[$item->id] = $item->title_best_rate;
        }
    }
    ?>
    <div class="add_tabs_line">
        <div class="add_tabs_single">
            <div class="add_tabs_sublabel"><span><?php _e('Auto adjust for max rate', 'pn'); ?></span></div>
            <div class="premium_wrap_standart">

                <?php
                $form = new PremiumForm();
                $atts = array();
                $option_data = array();
                $opts = array();
                foreach ($parsers as $parser_key => $parser_title) {
                    $opts[$parser_key] = $parser_title;
                }
                $form->select_search('bccorrs_maxsum_new_parser', $opts, is_isset($broker, 'maxsum_new_parser'), $atts, $option_data);
                ?>

            </div>
        </div>
        <div class="add_tabs_single">
            <div class="add_tabs_sublabel"><span><?php _e('Add to rate', 'pn'); ?></span></div>
            <div class="premium_wrap_standart">
                <input type="text" name="bccorrs_maxsum_new_parser_actions" style="width: 100%;" value="<?php echo pn_parser_num(is_isset($broker, 'maxsum_new_parser_actions')); ?>"/>
            </div>
        </div>
    </div>
    <?php
}

add_action('tab_bestchangeapi_standart_course', 'new_parser_tab_bestchangeapi_standart_course', 10, 2);
function new_parser_tab_bestchangeapi_standart_course($data, $broker) {

    $parsers = array();
    $parsers[0] = '-- ' . __('No item', 'pn') . ' --';
    $en_parsers = get_parser_pairs_course();
    if (is_array($en_parsers)) {
        foreach ($en_parsers as $item) {
            $parsers[$item->id] = $item->title_course;
        }
    }
    ?>
    <div class="add_tabs_line">
        <div class="add_tabs_single">
            <div class="add_tabs_sublabel"><span><?php _e('Auto adjust rate', 'pn'); ?></span></div>
            <div class="premium_wrap_standart">

                <?php
                $form = new PremiumForm();
                $atts = array();
                $option_data = array();
                $opts = array();
                foreach ($parsers as $parser_key => $parser_title) {
                    $opts[$parser_key] = $parser_title;
                }
                $form->select_search('bcorrs_standart_new_parser', $opts, is_isset($broker, 'standart_new_parser'), $atts, $option_data);
                ?>

            </div>
        </div>
    </div>
    <div class="add_tabs_line">
        <div class="add_tabs_single">
            <div class="add_tabs_sublabel"><span><?php _e('Add to rate', 'pn'); ?> (<?php _e('Send', 'pn'); ?>)</span>
            </div>
            <div class="premium_wrap_standart">
                <input type="text" name="bcorrs_standart_new_parser_actions_give" style="width: 100%;" value="<?php echo pn_parser_num(is_isset($broker, 'standart_new_parser_actions_give')); ?>"/>
            </div>
        </div>
        <div class="add_tabs_single">
            <div class="add_tabs_sublabel">
                <span><?php _e('Add to rate', 'pn'); ?> (<?php _e('Receive', 'pn'); ?>)</span></div>
            <div class="premium_wrap_standart">
                <input type="text" name="bcorrs_standart_new_parser_actions_get" style="width: 100%;" value="<?php echo pn_parser_num(is_isset($broker, 'standart_new_parser_actions_get')); ?>"/>
            </div>
        </div>
    </div>
    <?php
}

add_action('tab_bestchange_standart_course', 'new_parser_tab_bestchange_standart_course', 10, 2);
function new_parser_tab_bestchange_standart_course($data, $broker) {

    $parsers = array();
    $parsers[0] = '-- ' . __('No item', 'pn') . ' --';
    $en_parsers = get_parser_pairs_course();
    if (is_array($en_parsers)) {
        foreach ($en_parsers as $item) {
            $parsers[$item->id] = $item->title_course;
        }
    }
    ?>
    <div class="add_tabs_line">
        <div class="add_tabs_single">
            <div class="add_tabs_sublabel"><span><?php _e('Auto adjust rate', 'pn'); ?></span></div>
            <div class="premium_wrap_standart">

                <?php
                $form = new PremiumForm();
                $atts = array();
                $option_data = array();
                $opts = array();
                foreach ($parsers as $parser_key => $parser_title) {
                    $opts[$parser_key] = $parser_title;
                }
                $form->select_search('bccorrs_standart_new_parser', $opts, is_isset($broker, 'standart_new_parser'), $atts, $option_data);
                ?>

            </div>
        </div>
    </div>
    <div class="add_tabs_line">
        <div class="add_tabs_single">
            <div class="add_tabs_sublabel"><span><?php _e('Add to rate', 'pn'); ?> (<?php _e('Send', 'pn'); ?>)</span>
            </div>
            <div class="premium_wrap_standart">
                <input type="text" name="bccorrs_standart_new_parser_actions_give" style="width: 100%;" value="<?php echo pn_parser_num(is_isset($broker, 'standart_new_parser_actions_give')); ?>"/>
            </div>
        </div>
        <div class="add_tabs_single">
            <div class="add_tabs_sublabel">
                <span><?php _e('Add to rate', 'pn'); ?> (<?php _e('Receive', 'pn'); ?>)</span></div>
            <div class="premium_wrap_standart">
                <input type="text" name="bccorrs_standart_new_parser_actions_get" style="width: 100%;" value="<?php echo pn_parser_num(is_isset($broker, 'standart_new_parser_actions_get')); ?>"/>
            </div>
        </div>
    </div>
    <?php
}

add_filter('pn_bcorrs_tab_addform_post', 'new_parser_pn_bcorrs_tab_addform_post');
function new_parser_pn_bcorrs_tab_addform_post($array) {

    $array['standart_new_parser'] = intval(is_param_post('bcorrs_standart_new_parser'));
    $array['standart_new_parser_actions_give'] = pn_parser_num(is_param_post('bcorrs_standart_new_parser_actions_give'));
    $array['standart_new_parser_actions_get'] = pn_parser_num(is_param_post('bcorrs_standart_new_parser_actions_get'));
    $array['minsum_new_parser'] = intval(is_param_post('bcorrs_minsum_new_parser'));
    $array['minsum_new_parser_actions'] = pn_parser_num(is_param_post('bcorrs_minsum_new_parser_actions'));
    $array['maxsum_new_parser'] = intval(is_param_post('bcorrs_maxsum_new_parser'));
    $array['maxsum_new_parser_actions'] = pn_parser_num(is_param_post('bcorrs_maxsum_new_parser_actions'));

    return $array;
}

add_filter('pn_bccorrs_tab_addform_post', 'new_parser_pn_bccorrs_tab_addform_post');
function new_parser_pn_bccorrs_tab_addform_post($array) {

    $array['standart_new_parser'] = intval(is_param_post('bccorrs_standart_new_parser'));
    $array['standart_new_parser_actions_give'] = pn_parser_num(is_param_post('bccorrs_standart_new_parser_actions_give'));
    $array['standart_new_parser_actions_get'] = pn_parser_num(is_param_post('bccorrs_standart_new_parser_actions_get'));
    $array['minsum_new_parser'] = intval(is_param_post('bccorrs_minsum_new_parser'));
    $array['minsum_new_parser_actions'] = pn_parser_num(is_param_post('bccorrs_minsum_new_parser_actions'));
    $array['maxsum_new_parser'] = intval(is_param_post('bccorrs_maxsum_new_parser'));
    $array['maxsum_new_parser_actions'] = pn_parser_num(is_param_post('bccorrs_maxsum_new_parser_actions'));

    return $array;
}

add_filter('bestchangeapi_def_course', 'new_parser_bestchange_def_course', 10, 6);
add_filter('bestchange_def_course', 'new_parser_bestchange_def_course', 10, 6);
function new_parser_bestchange_def_course($darr, $item, $disable_security, $direction, $vd1, $vd2) {
    global $wpdb;

    $pairs_course = get_parser_pairs_course();

    $minsum_parser = intval($item->minsum_new_parser);
    $minsum_parser_actions = pn_parser_num($item->minsum_new_parser_actions);
    if ($minsum_parser > 0 and isset($pairs_course[$minsum_parser])) {
        $curs_data = $pairs_course[$minsum_parser];
        $curs1 = get_parser_course($curs_data->pair_give);
        $curs2 = get_parser_course($curs_data->pair_get);
        $curs = is_best_rate($curs1, $curs2);
        $ncurs = rate_plus_interest($curs, $minsum_parser_actions);
        //$ncurs = rate_plus_interest($pairs_course[$minsum_parser]->best_rate, $minsum_parser_actions);
        if ($ncurs > 0) {
            $darr['min_sum'] = $ncurs;
        }
    }

    $maxsum_parser = intval($item->maxsum_new_parser);
    $maxsum_parser_actions = pn_parser_num($item->maxsum_new_parser_actions);
    if ($maxsum_parser > 0 and isset($pairs_course[$maxsum_parser])) {
        $curs_data = $pairs_course[$maxsum_parser];
        $curs1 = get_parser_course($curs_data->pair_give);
        $curs2 = get_parser_course($curs_data->pair_get);
        $curs = is_best_rate($curs1, $curs2);
        $ncurs = rate_plus_interest($curs, $maxsum_parser_actions);
        //$ncurs = rate_plus_interest($pairs_course[$maxsum_parser]->best_rate, $maxsum_parser_actions);
        if ($ncurs > 0) {
            $darr['max_sum'] = $ncurs;
        }
    }

    $standart_parser = intval($item->standart_new_parser);
    $standart_parser_actions_give = pn_parser_num($item->standart_new_parser_actions_give);
    $standart_parser_actions_get = pn_parser_num($item->standart_new_parser_actions_get);
    if ($standart_parser > 0 and isset($pairs_course[$standart_parser])) {
        $curs_data = $pairs_course[$standart_parser];
        $curs1 = get_parser_course($curs_data->pair_give);
        $curs2 = get_parser_course($curs_data->pair_get);
        $n_course_give = is_sum(rate_plus_interest($curs1, $standart_parser_actions_give), is_isset($vd1, 'currency_decimal'));
        $n_course_get = is_sum(rate_plus_interest($curs2, $standart_parser_actions_get), is_isset($vd2, 'currency_decimal'));
        //$n_course_give = is_sum(rate_plus_interest($curs_data->rate_give, $standart_parser_actions_give), is_isset($vd1, 'currency_decimal'));
        //$n_course_get = is_sum(rate_plus_interest($curs_data->rate_get, $standart_parser_actions_get), is_isset($vd2, 'currency_decimal'));
        if ($n_course_give > 0 and $n_course_get > 0) {
            $darr['standart_course_give'] = $n_course_give;
            $darr['standart_course_get'] = $n_course_get;
        }
    }

    return $darr;
}

/* end best */

add_filter('get_formula_code', 'parser_formula_code', 1000, 4);
function parser_formula_code($n, $code, $id, $update) {

    $p = get_parser_pairs();
    $code = str_replace(array('[', ']'), '', $code);
    if (isset($p[$code], $p[$code]['course'])) {
        return $p[$code]['course'];
    }

    return $n;
}