<?php
if (!defined('ABSPATH')) { exit(); }

/*
title: [en_US:]Deleting application files[:en_US][ru_RU:]Удаление файлов заявок[:ru_RU]
description: [en_US:]Deleting application files, with complete deletion or archiving[:en_US][ru_RU:]Удаление файлов заявок, при полном удалении или архивации[:ru_RU]
version: 2.7.0
category: [en_US:]Orders[:en_US][ru_RU:]Заявки[:ru_RU]
cat: req
new: 1
*/

add_filter('change_bid_status', 'delfile_change_bidstatus', 500);    
function delfile_change_bidstatus($data) { 
	global $premiumbox;	

	$set_status = $data['set_status'];
	$stop_action = intval(is_isset($data, 'stop')); 
	if (!$stop_action) {
		if ('realdelete' == $set_status) {
			
			$id = $data['bid']->id;
			
			$bids_dir = $premiumbox->upload_dir . '/bids/'; 
			$my_dir = wp_upload_dir();
			$bids_dir_old = $my_dir['basedir'] . '/bids/';
			
			$old_file = $bids_dir_old . $id . '.txt';
			if (is_file($old_file)) {
				@unlink($old_file);
			}
			
			$file = $bids_dir . $id . '.php';
			if (is_file($file)) {
				@unlink($file);
			}		
			
		}
	}

	return $data;
}