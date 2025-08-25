<?php
if (!defined('ABSPATH')) { exit(); }

/*
title: [en_US:]Second currency logo[:en_US][ru_RU:]Второй логотип валюты[:ru_RU]
description: [en_US:]Second currency logo[:en_US][ru_RU:]Второй логотип валюты[:ru_RU]
version: 2.7.0
category: [en_US:]Currency[:en_US][ru_RU:]Валюты[:ru_RU]
cat: currency
dependent: -
*/

add_filter('pn_second_logo', 'twologo_pn_second_logo');
function twologo_pn_second_logo() {
	return 1;
}