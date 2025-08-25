<?php
if (!defined('ABSPATH')) { exit(); }

if (!function_exists('pn_wp_nav_menu_args')) {
	
	add_filter('wp_nav_menu_args', 'pn_wp_nav_menu_args');
	function pn_wp_nav_menu_args($args = '') {
		
		$args['container'] = false;
		
		return $args;
	} 

	add_filter('wp_nav_menu_objects', 'pn_wp_nav_menu_objects');
	function pn_wp_nav_menu_objects($items) {
		
		$parents = array();
		$firstul = array();
		foreach ($items as $item) {
			$parents[] = $item->menu_item_parent;
			if (0 == $item->menu_item_parent) {
				$firstul[] = $item->ID;
			}
		}

		$first_class = $last_class = '';
		$r = 0;
		$count_first = count($firstul);
		foreach ($firstul as $fi) { $r++;
			if (1 == $r) {
				$first_class = $fi;
			}
			if ($r == $count_first) {
				$last_class = $fi;
			}
		}
		
		foreach ($items as $item) {
			
			$classed = '';
			if (in_array($item->ID, $parents)) {
				$classed .= 'has_sub_menu'; 
			}
			
			if ($item->ID == $first_class) {
				$classed .= ' first_menu_li';
			}
			
			if ($item->ID == $last_class) {
				$classed .= ' last_menu_li';
			}		
				
			$item->classes[] = $classed;
		}
		
		return $items;    
	}

	add_filter('nav_menu_item_title', 'pn_nav_menu_item_title');
	function pn_nav_menu_item_title($title) {
		
		return '<span>' . $title . '</span>';
	}	
} 