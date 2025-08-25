<?php
if (!defined('ABSPATH')) { exit(); }

/*
title: [en_US:]Additional filters for Requests section[:en_US][ru_RU:]Дополнительные фильтры для раздела Заявки[:ru_RU]
description: [en_US:]Additional filters for Requests section[:en_US][ru_RU:]Дополнительные фильтры для раздела Заявки[:ru_RU]
version: 2.7.0
category: [en_US:]Orders[:en_US][ru_RU:]Заявки[:ru_RU]
cat: req
*/

add_filter('onebid_icons','onebid_icons_dop_bidfilter', 99, 3);
function onebid_icons_dop_bidfilter($onebid_icon, $item, $v) {
	
	$user_id = $item->user_id;
	if ($user_id) {
		
		$onebid_icon['user_id'] = array(
			'type' => 'text',
			'title' => __('User','pn') . ': ' . $user_id,
			'label' => __('User','pn') . ': ' . $user_id,
			'link' => admin_url('admin.php?page=pn_bids&iduser=' . $user_id),
			'link_target' => '_blank',
		);	
		
	}
	
	return $onebid_icon;
}

add_filter('onebid_actions', 'onebid_actions_dop_bidfilter', 99, 3);
function onebid_actions_dop_bidfilter($onebid_actions, $item, $v) {
	
	$onebid_actions['similar_account_give'] = array(
		'type' => 'link',
		'title' => __('Similar by account Send', 'pn'),
		'label' => __('By account Send', 'pn'),
		'link' => admin_url('admin.php?page=pn_bids&ac1=' . pn_strip_input($item->account_give)),
		'link_target' => '_blank',
		'link_class' => '',
	);	
	$onebid_actions['similar_account_get'] = array(
		'type' => 'link',
		'title' => __('Similar by account Receive', 'pn'),
		'label' => __('By account Receive', 'pn'),
		'link' => admin_url('admin.php?page=pn_bids&ac2=' . pn_strip_input($item->account_get)),
		'link_target' => '_blank',
		'link_class' => '',
	);	
	$onebid_actions['similar_user_email'] = array(
		'type' => 'link',
		'title' => __('Similar by e-mail', 'pn'),
		'label' => __('By e-mail', 'pn'),
		'link' => admin_url('admin.php?page=pn_bids&user_email=' . pn_strip_input($item->user_email)),
		'link_target' => '_blank',
		'link_class' => '',
	);
	$onebid_actions['similar_user_ip'] = array(
		'type' => 'link',
		'title' => __('Similar by IP', 'pn'),
		'label' => __('By IP', 'pn'),
		'link' => admin_url('admin.php?page=pn_bids&user_ip=' . pn_strip_input($item->user_ip)),
		'link_target' => '_blank',
		'link_class' => '',
	);	

	return $onebid_actions;
}