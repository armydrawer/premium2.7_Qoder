<?php
if (!defined('ABSPATH')) { exit(); }

if (!function_exists('pn_page_indicator')) {
	function pn_page_indicator() {
		return preg_quote(apply_filters('pn_page_indicator', 'num_page'));
	}
} 

if (!function_exists('get_pagenavi_calc')) {
	function get_pagenavi_calc($limit, $current, $count) {
		
		$current = intval($current); 
		$limit = intval($limit);
		$count = intval($count);

		if ($current > 0) {
			$offset = ($current - 1) * $limit;
		} else {
			$offset = 0; 
			$current = 1;
		}
		
		$next = $current + 1;
		$prev = $current - 1; 
		if ($prev < 1) { $prev = 1; }
		
		$countpage = '-1';
		
		if ($count > 0 and $limit > 0) {
			$countpage = ceil($count/$limit);
		} 
		
		if ($next > $countpage and $count >= 0) {
			$next = $current;
		}

		$pagenavi = array (
			'current' => $current,
			'limit' => intval($limit),
			'offset' => $offset,
			'prev' => $prev,
			'next' => $next,
			'countpage' => $countpage,
		);		

		return $pagenavi;
	}
}

if (!function_exists('get_pagenavi')) {
	function get_pagenavi($pagenavi = '', $type = 'rewrite', $url = '', $page_name = '') {

		$page_name = trim($page_name);
		if (!$page_name) { $page_name = pn_page_indicator(); }


		if ($url) {
			$uri = $url;
		} else {
			$scheme = 'http://'; if (is_ssl()) { $scheme = 'https://'; }
			$uri = $scheme . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
		}
		
		$uri = pn_strip_input($uri);
		$uri_arr = parse_url($uri);

		$type = trim($type);

		if (is_admin()) {
			$new_url = get_site_url_or();
		} else {
			$new_url = get_site_url_ml();
		}
		
		$new_url = untrailingslashit($new_url);
		
		$path = trim(is_isset($uri_arr, 'path'));
		if ('rewrite' == $type) {
			$path = preg_replace('/\/page\/[0-9]{0,8}/', '', $path);
		}

		$new_url .= $path;
		$new_url = trailingslashit($new_url);		

		$query = trim(is_isset($uri_arr, 'query'));
		if ($query) {
			$sep = '?';
		} else {
			$sep = '';
		}
		
		if (is_array($pagenavi)) {
			$countpage = $pagenavi['countpage'];
			$num_page = $pagenavi['current'];
		} else {
			global $wp_query;
			$countpage = $wp_query->max_num_pages;
			$num_page = intval(get_query_var('paged'));		
		}	
		
		if ($num_page < 1) { $num_page=1; }
		
		$html = '';	
		
		if ('-1' == $countpage or $countpage > 1) {
			
			$html .= apply_filters('get_pagenavi_start', '<div class="pagenavi"><div class="pagenavi_ins">');
		
			$array = array();
			$array['first'] = __('First page', 'premium');
			$array['prev'] = '&larr;';
			$array['next'] = '&rarr;';
			$array['last'] = __('Last page', 'premium');
			$array['num'] = 2;
			$array['numleft'] = 2;
			$array['numright'] = 2;

			if (is_admin()) {
				$array['num'] = 2;
				$array['numleft'] = 1;
				$array['numright'] = 1;
			}
				
			$array = apply_filters('get_pagenavi', $array);		
		
			if ($num_page > 1) {
				if (isset($array['first']) and $array['first']) {
					if ('rewrite' == $type) {
						$html .= '<a href="' . $new_url . $sep . $query . '" data-page="1" rel="prev" class="first_navi">' . $array['first'] . '</a>';
					} else {
						$html .= '<a href="' . remove_query_args($page_name, $new_url . $sep . $query) . '" data-page="1" rel="prev" class="first_navi">' . $array['first'] . '</a>';
					}
				}
				if (isset($array['prev']) and $array['prev']) {
					$dpage = $num_page - 1;
					if ($dpage > 1) {
						if ('rewrite' == $type) {
							$html .= '<a href="' . $new_url . 'page/' . $dpage . '/' . $sep . $query . '" rel="prev" data-page="' . $dpage . '" class="prev_navi">' . $array['prev'] . '</a>';	
						} else {
							$html .= '<a href="' . add_query_args(array($page_name=>$dpage), $new_url . $sep . $query) . '" rel="prev" data-page="' . $dpage . '" class="prev_navi">' . $array['prev'] . '</a>';
						}   
					} else {
						if ('rewrite' == $type) {
							$html .= '<a href="' . $new_url . $sep . $query . '" rel="prev" data-page="' . $dpage . '" class="prev_navi">' . $array['prev'] . '</a>';	
						} else {
							$html .= '<a href="' . remove_query_args($page_name, $new_url . $sep . $query) . '" rel="prev" data-page="' . $dpage . '" class="prev_navi">' . $array['prev'] . '</a>';
						}   
					}
				}
			}	

			if ($countpage > 1) {

				$numleft = intval(is_isset($array, 'numleft'));
				$numright = intval(is_isset($array, 'numright'));
				$num = intval(is_isset($array, 'num'));	
			  
				$pagearr = array();
				$r = 0;
				while ($r++ < $numleft) {
					$pagearr[] = $r;
				}
					
				$dc = ($num * 2) + 1;
				$r = $num_page - 1 - $num;
				$mr = $r + $dc;
				while ($r++ < $mr) {
					$pagearr[] = $r;
				}	
					
				$r = $countpage - $numright;
				while ($r++ < $countpage) {
					$pagearr[] = $r;
				}

				$pagearr = array_unique($pagearr);
					
				$lv = 0;
				$rel = 'prev';
				foreach ($pagearr as $v) {
					if ($v > 0 and $v <= $countpage) {
						if ($lv and $lv != $v) {
							$html .= '<span>...</span>';
						}
						$lv = $v+1;				
							
						if ($v == $num_page) {
							$html .= '<span class="current" data-page="' . $v . '">' . $v . '</span>';
							$rel = 'next';
						} elseif (1 == $v) {
							if ('rewrite' == $type) {
								$html .= '<a href="' . $new_url . $sep . $query . '" rel="' . $rel . '" data-page="' . $v . '">' . $v . '</a>';
							} else {
								$html .= '<a href="' . remove_query_args($page_name, $new_url . $sep . $query) . '" rel="' . $rel . '" data-page="' . $v . '">' . $v . '</a>';
							}					
						} else {
							if ('rewrite' == $type) {
								$html .= '<a href="' . $new_url . 'page/' . $v . '/' . $sep . $query . '" rel="' . $rel . '" data-page="' . $v . '">' . $v . '</a>';	
							} else {
								$html .= '<a href="' . add_query_args(array($page_name => $v), $new_url . $sep . $query) . '" rel="' . $rel . '" data-page="' . $v . '">' . $v . '</a>'; 
							}					
						}
					}
				}
					
			}		

			if (isset($pagenavi['next'])) {
				if ($num_page != $pagenavi['next']) {
					if (isset($array['next']) and $array['next']) {
						$dpage = $pagenavi['next'];
						if ('rewrite' == $type) {
							$html .= '<a href="' . $new_url . 'page/' . $dpage . '/' . $sep . $query . '" data-page="' . $dpage . '" rel="next" class="next_navi">' . $array['next'] . '</a>';	
						} else {
							$html .= '<a href="' . add_query_args(array($page_name => $dpage), $new_url . $sep . $query) . '" data-page="' . $dpage . '" rel="next" class="next_navi">' . $array['next'] . '</a>';
						} 				
					}
					if (isset($array['last']) and $array['last'] and $countpage > 0) {
						if ('rewrite' == $type) {
							$html .= '<a href="' . $new_url . 'page/' . $countpage . '/' . $sep . $query . '" data-page="' . $countpage . '" rel="next" class="last_navi">' . $array['last'] . '</a>';	
						} else {
							$html .= '<a href="' . add_query_args(array($page_name => $countpage), $new_url . $sep . $query) . '" data-page="' . $countpage . '" rel="next" class="last_navi">' . $array['last'] . '</a>';
						} 				
					}						
				}
 			} elseif ($countpage > 0) {
				if ($num_page < $countpage) {
					if (isset($array['next']) and $array['next']) {
						$dpage = $num_page + 1;
						if ('rewrite' == $type) {
							$html .= '<a href="' . $new_url . 'page/' . $dpage . '/' . $sep . $query . '" data-page="' . $dpage . '" rel="next" class="next_navi">' . $array['next'] . '</a>';	
						} else {
							$html .= '<a href="' . add_query_args(array($page_name => $dpage), $new_url . $sep . $query) . '" data-page="' . $dpage . '" rel="next" class="next_navi">' . $array['next'] . '</a>';
						} 				
					}
					if (isset($array['last']) and $array['last'] and $countpage > 0) {
						if ('rewrite' == $type) {
							$html .= '<a href="' . $new_url . 'page/' . $countpage . '/' . $sep . $query . '" data-page="' . $countpage . '" rel="next" class="last_navi">' . $array['last'] . '</a>';	
						} else {
							$html .= '<a href="' . add_query_args(array($page_name => $countpage), $new_url . $sep . $query) . '" data-page="' . $countpage . '" rel="next" class="last_navi">' . $array['last'] . '</a>';
						} 				
					}						
				}
			}	
	  
			$html .= apply_filters('get_pagenavi_end', '<div class="clear"></div></div></div>');
		}
		
		return $html;
	}
}

if (!function_exists('the_pagenavi')) {
	function the_pagenavi($pagenavi = '', $type = 'rewrite', $url = '', $page_name = '') {
		echo get_pagenavi($pagenavi, $type, $url, $page_name);
	}
}