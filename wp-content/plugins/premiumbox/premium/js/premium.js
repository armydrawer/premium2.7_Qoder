jQuery(function ($) {

    /* head mess */
    $(document).on('click', '.js_reply_close', function () {

        $(this).parents('.js_reply_wrap').fadeOut(500);

        return false;
    });
    /* end head mess */

    /* ml */
    $('.post_title_currentlang').on('change', function () {

        var vale = $(this).val();
        $('#title').val(vale);

    });

    $(document).on('click', '.tab_multi_title', function () {

        var id = $(this).attr('name');
        var par = $(this).parents('.multi_wrapper');
        par.find('.tab_multi_title').removeClass('active');
        $(this).addClass('active');
        par.find('.wrap_multi').removeClass('active');
        par.find('div[tablang=' + id + ']').addClass('active');

        return false;
    });

    $(document).on('click', '.clear_multi_title', function () {

        var par = $(this).parents('.multi_wrapper');
        par.find('input[type=text], textarea').val('');
        par.find('input[type=text], textarea').trigger('change');

        return false;
    });
    /* end ml */

    var userLang = navigator.language || navigator.userLanguage;
    var user_lang = userLang.substr(0, 2).toLowerCase();

    $.datetimepicker.setLocale(user_lang);

    $('.js_datepicker').datetimepicker({
        timepicker: false,
        format: 'd.m.Y'
    });

    $('.js_datetimepicker').datetimepicker({
        step: 15,
        format: 'd.m.Y H:i'
    });

    $('.js_timepicker').datetimepicker({
        datepicker: false,
        step: 15,
        format: 'H:i'
    });

    /* list table */
    $(document).AdaptiveTable({trigger: '.premium_table table', interval: 500});

    $(document).on('click', '.premium_tf_button', function () {

        $('.premium_tf').toggleClass('show_div');
        $('.premium_tf_ins').toggle();

        return false;
    });

    $(document).on('change', '.premium_tf_checkbox', function () {

        var parent_label = $(this).parents('label');
        if ($(this).prop('checked')) {
            parent_label.find('.premium_tf_checkbox_hidden').prop('checked', false);
        } else {
            parent_label.find('.premium_tf_checkbox_hidden').prop('checked', true);
        }

        return false;
    });

    function action_table_row(item) {

        var parent_tr = item.parents('.pntable_tr');
        if (item.prop('checked')) {
            parent_tr.addClass('tr_active');
        } else {
            parent_tr.removeClass('tr_active');
        }

    }

    $(document).on('change', '.pntable-checkbox', function (event) {

        var parent_table = $(this).parents('.premium_table_wrap');
        if ($(this).prop('checked')) {
            parent_table.find('.pntable-checkbox-single, .pntable-checkbox').each(function () {
                $(this).prop('checked', true);
                action_table_row($(this));
            });
        } else {
            parent_table.find('.pntable-checkbox-single, .pntable-checkbox').each(function () {
                $(this).prop('checked', false);
                action_table_row($(this));
            });
        }
    });

    function all_check_inputs() {

        $('.premium_table_wrap').each(function () {
            var parent_table = $(this);
            if (parent_table.find('.pntable-checkbox-single:not(:checked)').length < 1) {
                parent_table.find('.pntable-checkbox').prop('checked', true);
            } else {
                parent_table.find('.pntable-checkbox').prop('checked', false);
            }
        });

    }

    $(document).on('change', '.pntable-checkbox-single', function (e) {

        action_table_row($(this));
        all_check_inputs();

    });

    $(document).on('keydown', '.premium_table', function (e) {
        if (e.key !== 'Enter' || $(e.target).is('textarea')) return;

        e.preventDefault();
        $(this).closest('form').find('input[name="save"]').click();
    });

    /* end list table */

    /* hiden input */
    $(document).on('change', '.js_hide_input', function () {

        var id = $(this).val();
        var class_id = $(this).attr('to_class');
        $('.' + class_id).hide();
        $('.' + class_id + id).show();
        $('.premium_body').trigger('resize');

        return false;
    });

    $(document).on('change', '.js_adhide_input', function () {

        var id = $(this).val();
        var class_id = $(this).attr('to_class');
        if (id == '0') {
            $('.' + class_id).show();
        } else {
            $('.' + class_id).hide();
        }
        $('.premium_body').trigger('resize');

        return false;
    });
    /* end hiden input */

    $(document).on('keydown', '.standart_window', function (e) {

        if (e.shiftKey && e.which == 13) {
            $(this).find('form').submit();
            return false;
        }

    });

    /* single form */
    function str_rand(num_lenth) {

        var result = '';
        var words = '0123456789qwertyuiopasdfghjklzxcvbnmQWERTYUIOPASDFGHJKLZXCVBNM';
        var max_position = words.length - 1;
        for (i = 0; i < num_lenth; ++i) {
            var position = Math.floor(Math.random() * max_position);
            result = result + words.substring(position, position + 1);
        }

        return result;
    }

    $(document).on('click', '.js_password_generate', function () {

        $('input.js_input_password').val(str_rand(16));

    });

    $(document).on('click', '.premium_helptitle span', function () {

        $(this).parents('.premium_wrap_help').toggleClass('act');

        return false;
    });

    $(document).on('click', '.js_line_label', function () {

        var value_key = $.trim($(this).attr('data-for'));
        var par = $(this).parents('.premium_standart_line');
        if (value_key.length > 0) {
            par.find('div[data-forlabel=' + value_key + ']').find('input:visible, select:visible, textarea:visible').each(function () {

                $(this).focus();

                return false;
            });
        }
    });

    function set_aonce() {

        if ($('.premium_stline_left').length > 0) {
            var wid = window.innerWidth;
            if (wid >= 700) {
                $('.premium_stline_left:visible').each(function () {
                    if ($(this).find('.premium_stline_left_ins').length > 0) {
                        var line_hei = $(this).parents('.premium_standart_line').height();
                        $(this).find('.premium_stline_left_ins').css({'height': line_hei});
                    }
                });
            } else {
                $('.premium_stline_left_ins').css({'height': 'auto'});
            }
        }

    }

    $(window).on('resize', function () {
        set_aonce();
    });

    $('.premium_body').on('resize', function () {
        set_aonce();
    });

    set_aonce();
    /* end single form */

    $(document).on('change', '.checkbox_once', function (event) {

        var parent_div = $(this).parents('.checkbox_all_div');
        if (parent_div.find('.checkbox_once:not(:checked)').length < 1) {
            parent_div.find('.checkbox_all').prop('checked', true);
        } else {
            parent_div.find('.checkbox_all').prop('checked', false);
        }

    });

    $(document).on('change', '.checkbox_all', function (event) {

        var parent_div = $(this).parents('.checkbox_all_div');
        if ($(this).prop('checked')) {
            parent_div.find('.checkbox_once, .checkbox_all').each(function () {
                if (!$(this).prop('checked')) {
                    $(this).prop('checked', true).trigger('change');
                }
            });
        } else {
            parent_div.find('.checkbox_once, .checkbox_all').each(function () {
                if ($(this).prop('checked')) {
                    $(this).prop('checked', false).trigger('change');
                }
            });
        }

    });

    function search_check_text(thet) {

        var par = thet.parents('.checkbox_all_div');
        var txt = $.trim(thet.val()).toLowerCase();
        par.find('.checkbox_once_div').hide();
        if (txt.length > 0) {
            par.find('.checkbox_once_div .in_check').each(function () {
                var option_html = $(this).attr('data-s');
                if (option_html.toLowerCase().indexOf(txt) + 1) {
                    $(this).parents('.checkbox_once_div').show();
                }
            });
        } else {
            par.find('.checkbox_once_div').show();
        }
        $('.premium_body').trigger('resize');

    }

    $(document).on('keydown', '.checkbox_all_search', function (e) {

        if (e.which == '13') {
            return false;
        }

    });
    $(document).ChangeInput({
        trigger: '.checkbox_all_search',
        success: function (obj) {
            search_check_text(obj);
        }
    });

    function search_select_action(thet, ind) {
        var par = thet.parents('.js_select_search_wrap');
        var txt = $.trim(thet.val()).toLowerCase();
        var now_select = par.find('select');

        now_select.find('option:not(:selected)').hide();
        now_select.prop('disabled', true);

        if (txt.length > 0) {

            now_select.find('option').each(function () {
                var option_html = $(this).html();
                if (option_html.toLowerCase().indexOf(txt) + 1) {
                    $(this).show();
                }
            });

        } else {
            now_select.find('option').show();
        }

        now_select.prop('disabled', false);
    }

    $(document).ChangeInput({
        trigger: '.js_select_search',
        success: function (obj) {
            search_select_action(obj, 1);
        }
    });

    $(document).TextareaWordCount();
    $(document).EditorTags('init', {trigger: '.js_editor_tag:not(.js_form_upload)'});

    $(document).on('click', '.save_admin_ajax_form', function () {

        $('.admin_ajax_form').submit();

        return false;
    });

    function collect_cf(parent) {

        var new_value = '0';
        parent.find('.checkbox_once:checked').each(function () {
            new_value = new_value + ',' + $(this).attr('data-id');
        });
        parent.find('.ajax_checkbox_input').val(new_value);

    }

    $(document).on('change', '.ajax_checkbox .checkbox_once', function () {

        var parent = $(this).parents('.ajax_checkbox');
        collect_cf(parent);

    });

    if ($('.ajax_checkbox').length > 0) {
        $('.ajax_checkbox').each(function () {
            collect_cf($(this));
        });
    }

    /* wp update mess */
    if ($('.update-nag').length > 0 && $('.premium_tf').length > 0) {
        var nag_text = $('.update-nag').html();
        $('.update-nag').remove();
        $('.premium_tf').after('<div class="update-nag notice notice-warning inline">' + nag_text + '</div>');
    }
    /* end wp update mess */

    var clipboard = new ClipboardJS('.clpb_item');

});