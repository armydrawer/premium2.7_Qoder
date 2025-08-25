<?php
if (!defined('ABSPATH')) { exit(); }

/*
title: [en_US:]The card number with spaces from the admin panel[:en_US][ru_RU:]Номер карты с пробелами в админке[:ru_RU]
description: [en_US:]The card number with spaces from the admin panel[:en_US][ru_RU:]Номер карты с пробелами в админке[:ru_RU]
version: 2.7.0
category: [en_US:]Exchange directions[:en_US][ru_RU:]Направления обменов[:ru_RU]
cat: directions
new: 1
*/

add_filter('onebid_col2', 'onebid_col2_beautycard', 20, 3);
function onebid_col2_beautycard($actions, $item, $v) {
	
	$currency_id = $item->currency_id_give;
	$vd = is_isset($v, $currency_id);
	if (isset($vd->cat_id) and 4 == $vd->cat_id and isset($actions['account_give'])) {
		$actions['account_give']['label'] = $actions['account_give']['copy'] = get_beauty_card(pn_strip_input($item->account_give));
	}
	
	return $actions;
}

add_filter('onebid_col3', 'onebid_col3_beautycard', 20, 3);
function onebid_col3_beautycard($actions, $item, $v) {
	
	$currency_id = $item->currency_id_get;
	$vd = is_isset($v, $currency_id);
	if (isset($vd->cat_id) and 4 == $vd->cat_id and isset($actions['account_get'])) {
		$actions['account_get']['label'] = $actions['account_get']['copy'] = get_beauty_card(pn_strip_input($item->account_get));
	}
	
	return $actions;
}

function get_beauty_card($item) {
	
	$item_arr = str_split($item, 4);
	
	return implode('&nbsp;', $item_arr);
}