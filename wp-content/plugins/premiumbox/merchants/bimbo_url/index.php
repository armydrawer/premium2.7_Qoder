<?php
if (!defined('ABSPATH')) exit();

/*
title: [en_US:]BimBo Link[:en_US][ru_RU:]BimBo ссылка[:ru_RU]
description: [en_US:]BimBo merchant (link)[:en_US][ru_RU:]мерчант BimBo (ссылка)[:ru_RU]
version: 2.7.1
*/

if (!class_exists('Ext_Merchant_Premiumbox')) return;

if (!class_exists('merchant_bimbo')) {
    class merchant_bimbo extends Ext_Merchant_Premiumbox {

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

            $options['link'] = array(
                'view' => 'inputbig',
                'title' => __('Link', 'pn'),
                'default' => is_isset($data, 'link'),
                'name' => 'link',
                'work' => 'input',
                'ml' => 1,
            );

            return $options;
        }

        function merch_type($m_id) {

            return 'link';
        }

        function init($m_id, $pay_sum, $direction, $m_defin, $m_data) {
            global $bids_data;

            $pay_link = $this->get_pay_link($bids_data->id);
            if (!$pay_link) {
                $pay_link = trim(ctv_ml(is_isset($m_data, 'link')));
                $this->update_pay_link($bids_data->id, $pay_link);

                $update_data = [
                    'pay_sum' => $pay_sum,
                ];
                $bids_data = update_bid_tb_array($bids_data->id, $update_data, $bids_data);
            }

            if ($pay_link) {
                return 1;
            }

            return 0;
        }

    }
}

new merchant_bimbo(__FILE__, 'BimBo Link');
