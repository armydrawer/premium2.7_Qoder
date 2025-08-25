<?php
if (!defined('ABSPATH')) exit();

/*
title: [en_US:]BimBo Account[:en_US][ru_RU:]BimBo счет[:ru_RU]
description: [en_US:]BimBo merchant (Account)[:en_US][ru_RU:]мерчант BimBo (счет)[:ru_RU]
version: 2.7.1
*/

if (!class_exists('Ext_Merchant_Premiumbox')) return;

if (!class_exists('merchant_bimbo_account')) {
    class merchant_bimbo_account extends Ext_Merchant_Premiumbox {

        function __construct($file, $title) {
            parent::__construct($file, $title);
        }

        function options($options, $data, $id, $place) {

            $options = pn_array_unset($options, 'pagenote');
            $options = pn_array_unset($options, 'note');
            $options = pn_array_unset($options, 'check_api');
            $options = pn_array_unset($options, 'stp');
            $options = pn_array_unset($options, 'sfp');
            $options = pn_array_unset($options, 'corr');
            $options = pn_array_unset($options, 'center_title');
            $options = pn_array_unset($options, 'check');
            $options = pn_array_unset($options, 'invalid_ctype');
            $options = pn_array_unset($options, 'invalid_minsum');
            $options = pn_array_unset($options, 'invalid_maxsum');
            $options = pn_array_unset($options, 'enableip');
            $options = pn_array_unset($options, 'resulturl');
            $options = pn_array_unset($options, 'help_resulturl');
            $options = pn_array_unset($options, 'cronhash');
            $options = pn_array_unset($options, 'show_error');

            $options['private_line'] = array(
                'view' => 'line',
            );

            $options['accnum'] = array(
                'view' => 'inputbig',
                'title' => __('Account number', 'pn'),
                'default' => is_isset($data, 'accnum'),
                'name' => 'accnum',
                'work' => 'input',
            );

            return $options;
        }

        function merch_type($m_id) {

            return 'address';
        }

        function init($m_id, $pay_sum, $direction, $m_defin, $m_data) {
            global $bids_data;

            $update_data = [
                'to_account' => pn_strip_input(is_isset($m_data, 'accnum')),
                'pay_sum' => $pay_sum,
            ];
            $bids_data = update_bid_tb_array($bids_data->id, $update_data, $bids_data);

            return 1;
        }
    }
}

new merchant_bimbo_account(__FILE__, 'BimBo Account');
