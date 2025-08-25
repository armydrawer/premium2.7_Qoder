<?php
if (!defined('ABSPATH')) { exit(); }

if (!function_exists('is_ml')) {
	function is_ml() {
		
		$langs = get_langs_ml();
		if (count($langs) > 1) {
			return 1;
		}		
		
		return 0;
	}
}

if (!function_exists('get_browser_lang')) {
	function get_browser_lang() {
		
		$your_lang_k = mb_substr(is_isset($_SERVER,'HTTP_ACCEPT_LANGUAGE'), 0, 2);
		$k_langs = array();
		$langs = _get_site_langs();
		foreach ($langs as $lk => $lv) {
			$k_langs[mb_substr($lk,0,2)] = $lk;
		}
		
		if (isset($k_langs[$your_lang_k])) {
			return $k_langs[$your_lang_k];
		} else {
			return array_key_first($langs);
		}		
		
	}
}

if (!function_exists('_get_site_langs')) {
	function _get_site_langs() {
		
		$langs = array(
			'ru_RU' => 'Русский',
			'en_US' => 'English',
		);
		$langs = apply_filters('pn_site_langs', $langs);
		
		return $langs;
	}
}

if (!function_exists('get_site_langs')) {
	function get_site_langs() {
		
		$langs = _get_site_langs();
		$get_langs = array();
		$browser_lang = get_browser_lang();
		if ($browser_lang and isset($langs[$browser_lang])) {
			$get_langs[$browser_lang] = $langs[$browser_lang];
			foreach ($langs as $lan_k => $lan_data) {
				$get_langs[$lan_k] = $lan_data;
			}			
		} else {
			$get_langs = $langs;
		}
		
		return $get_langs;
	}
}

if (!function_exists('get_title_forkey')) {
	function get_title_forkey($key) {
		
		$key = pn_string($key);
		$langs = get_site_langs();
		
		return is_isset($langs,$key);
	}
}

if (!function_exists('get_lang_icon')) {
	function get_lang_icon($key) {
		
		$key = pn_string($key); 
		$url = plugin_basename(__FILE__);
		$parts = explode('/', $url);
		$plugin_folder = apply_filters('ml_flag_url', $parts[0]);
		$new_url = WP_PLUGIN_URL . '/' . $plugin_folder . '/flags/'. $key .'.png';
		
		return $new_url;
	}
}

if (!function_exists('get_site_lang')) {
	function get_site_lang() {
		global $pn_lang;
		
		$lang = get_locale();
		if (isset($pn_lang['site_lang']) and is_lang_attr($pn_lang['site_lang'])) {
			$lang = is_lang_attr($pn_lang['site_lang']);
		} 		
		
		return $lang;
	}
}

if (!function_exists('get_admin_lang')) {
	function get_admin_lang() {
		global $pn_lang;
		
		$lang = get_locale();
		if (isset($pn_lang['admin_lang']) and is_lang_attr($pn_lang['admin_lang'])) {
			$lang = is_lang_attr($pn_lang['admin_lang']);
		} 	
		
		return $lang;
	}
}

if (!function_exists('get_lang_key')) {
	function get_lang_key($arg) {
		
		$arg = pn_string($arg);
		$keyname = explode('_', $arg);
		$keyname = $keyname[0];
		
		return $keyname;
	}
}

if (!function_exists('get_site_url_or')) {
	function get_site_url_or() {
		
		return PN_SITE_URL;
	}
}

if (!function_exists('get_site_url_ml')) {
	function get_site_url_ml() {

		$now_lang = get_locale();
		$def_lang = get_site_lang();
		
		$url = PN_SITE_URL;
		if ($now_lang != $def_lang) {
			$l_key = get_lang_key($now_lang);
			return $url . $l_key . '/';
		}
		
		return $url;
	}
}

if (!function_exists('get_langs_ml')) {
	function get_langs_ml($default_lang = '') {
		global $pn_lang;
		
		$default_lang = trim($default_lang);
		if (!$default_lang) { $default_lang = get_locale(); }
		
		$ml_array = array();
		$ml_array[$default_lang] = is_lang_attr($default_lang);
		
		if (isset($pn_lang['multisite_lang'])) {
			$array = $pn_lang['multisite_lang'];
			if (is_array($array)) {
				foreach ($array as $key) {
					$key = is_lang_attr($key);
					if ($key) {
						$ml_array[$key] = $key;
					}
				}
			}		
		}
		
		return $ml_array;
	}
}

if (!function_exists('is_lang_prefix')) {
	function is_lang_prefix($arg) {
		
		$arg = pn_string($arg);
		if (preg_match("/^[A-Za-z]{2}$/", $arg, $matches)) {
			return strtolower($arg);
		}
		
		return '';
	}
}

if (!function_exists('set_locale')) {
	function set_locale($set_locale) {
		global $locale;
		
		$set_locale = is_lang_attr($set_locale);
		if ($set_locale) {
			$locale = $set_locale;
		}
	}
}

if (!function_exists('is_lang_attr')) {
	function is_lang_attr($arg) {
		
		$arg = pn_string($arg);
		if (preg_match("/^[A-Za-z]{2}[_]{1}[A-Za-z]{2}$|[A-Za-z]{2}$/", $arg, $matches)) {
			return $arg;
		} 
		
		return '';
	}
}

if (!function_exists('lang_self_link')) {
	function lang_self_link($lang = '', $url = '') {
		
		$lang = is_lang_attr($lang);
		if (!$lang) {
			$lang = get_locale();
		}
		$def_lang = get_site_lang();

		$url = trim($url);
		if (!$url) {
			$scheme = 'http://'; if (is_ssl()) { $scheme = 'https://'; }
			$url = $scheme . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];			
		}
		
		$new_url = $url;
		
		$url_data = parse_url($url);
		$now_data = parse_url(PN_SITE_URL);
		$url_host = trim(is_isset($url_data, 'host'));
		$now_host = trim(is_isset($now_data, 'host'));		
		
		if ($url_host and $url_host == $now_host) {	
			$url_now = ltrim(str_replace(PN_SITE_URL, '', $url), '/');
			if ($def_lang == $lang) {
				$new_url = trailingslashit(PN_SITE_URL) . $url_now;
			} else {
				$key = get_lang_key($lang);
				$new_url = trailingslashit(PN_SITE_URL) . $key . '/' . $url_now;
			}			
		}	
		
		return $new_url;
	}
}

if (!function_exists('convert_to_ml')) {
	function convert_to_ml($string) {
		
		$site_lang = get_site_lang();
		
		if (is_string($string)) {
			$string = trim($string);
			if ($string) {
				if (false === strpos($string, '[' . $site_lang . ':]')) {
					$string = '[' . $site_lang . ':]' . $string . '[:' . $site_lang . ']';
				}
			}
		}
		
		return $string;
	}
}

if (!function_exists('get_value_ml')) {
	function get_value_ml($string) { 
	
		$array = array();
		if (is_string($string)) {
			$now_lang = get_locale();
			if (false === strpos($string, '[:')) {
				$array[$now_lang] = $string;
				return $array;
			}		
			if (preg_match_all('/\[(.*?):\](.*?)\[:(.*?)\]/s',$string, $match, PREG_PATTERN_ORDER)) {
				foreach ($match[1] as $key => $lang) {
					$array[$lang] = $match[2][$key];
				}
			} else {
				$array[$now_lang] = $string;
			}
		}	
		
		return $array;
	}
}

if (!function_exists('replace_value_ml')) {
	function replace_value_ml($string, $newtext = '', $lang = '') {
		
		if (!$lang) { $lang = get_locale(); }
		if (is_string($string)) {
			if (false === strpos($string, '[:')) {
				return $string;	
			}
			if (preg_match_all('/\[(.*?):\](.*?)\[:(.*?)\]/s', $string, $match, PREG_PATTERN_ORDER)) {
				$key = array_search($lang, $match[1]);
				if (is_numeric($key)) {
					$string = preg_replace('/\[' . $lang . ':\](.*?)\[:' . $lang . '\]/s', '[' . $lang . ':]' . $newtext . '[:' . $lang . ']', $string);
				} else {
					$string .= '[' . $lang . ':]' . $newtext . '[:' . $lang . ']';
				}		
			}	
		}
		
		return $string;
	}
}

if (!function_exists('ctv_ml')) {
	function ctv_ml($string, $now_lang = '') {
		
		$now_lang = is_lang_attr($now_lang);
		if (!$now_lang) {
			$now_lang = get_locale();
		}
		
		if (is_string($string)) {
			
			if (false === strpos($string, '[:')) {
				return $string;	
			}
			
			if (preg_match_all('/\[(.*?):\](.*?)\[:(.*?)\]/s', $string, $match, PREG_PATTERN_ORDER)) {
				$key = array_search($now_lang, $match[1]);
				if (is_numeric($key)) {
					$newtext = trim($match[2][$key]);
				} else {
					$newtext = trim($match[2][0]);
					$newtext = apply_filters('ctv_ml_default', $newtext, $match);
				}
				
				return $newtext;			
			}	
			
		} elseif (is_object($string)) {
			
			$new_object = array();
			foreach ($string as $key => $val) {
				$new_object[$key] = ctv_ml($val, $now_lang);
			}
			
			return (object)$new_object;
			
		} elseif (is_array($string)) { 
		
			$new_array = array();
			foreach ($string as $key => $val) {
				$new_array[$key] = ctv_ml($val, $now_lang);
			}
			
			return $new_array;	
		}
			
		return $string;
	}
}

if (!function_exists('is_param_post_ml')) {
	function is_param_post_ml($name) {
		
		if (isset($_POST[$name])) {
			return $_POST[$name];
		} else {
			$arg = '';
			$langs = get_langs_ml();
			foreach ($langs as $lang => $l_val) {
				$val = is_param_post($lang . '_' . $name);
				if (strlen($val) > 0) {
					$arg .= '[' . $lang . ':]' . $val . '[:' . $lang . ']';
				}	
			}
			
			return $arg;
		}
		
	} 
}

if (!function_exists('is_param_get_ml')) {
	function is_param_get_ml($name) {
		
		if (isset($_GET[$name])) {
			return $_GET[$name];
		} else {
			$arg = '';
			$langs = get_langs_ml();
			foreach ($langs as $lang => $l_val) {
				$val = is_param_get($lang . '_' . $name);
				if (strlen($val) > 0) {
					$arg .= '[' . $lang . ':]'. $val . '[:' . $lang . ']';
				}	
			}
			
			return $arg;
		}
		
	} 
}

if (!function_exists('pn_set_lang')) {
	add_action('plugins_loaded', 'pn_set_lang', 9);
	function pn_set_lang() {
		global $locale, $pn_lang;
		
		$current_lang = get_locale();
		
		$pn_lang = get_option('pn_lang');
		if (!is_array($pn_lang)) { $pn_lang = array(); }

		if (_is('is_admin') or _is('is_adminaction') or _is('is_loginpage')) {
			if (isset($pn_lang['admin_lang']) and is_lang_attr($pn_lang['admin_lang'])) {
				$current_lang = is_lang_attr($pn_lang['admin_lang']);
			} 
		} else {	
			if (isset($pn_lang['site_lang']) and is_lang_attr($pn_lang['site_lang'])) {
				$current_lang = is_lang_attr($pn_lang['site_lang']);
			} 
			
			if (is_ml()) {	
			
				$langs = get_langs_ml($current_lang);

				$url = 'http://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
				$site_url = str_replace('https://', 'http://', PN_SITE_URL);
				$request = str_replace($site_url, '', $url);
				$parse_url = parse_url($request);
				$req_arr = explode('/', is_isset($parse_url, 'path'));
				
				$site_parse_url = parse_url(PN_SITE_URL);
				$site_path = rtrim(is_isset($site_parse_url, 'path'), '/');
				
				$lprefix = '';	
				$get_lang = is_lang_prefix(is_param_get('lang'));
				if ($get_lang) {
					$unset_prefix = 0;
					$lprefix = $get_lang;
				} else {
					$unset_prefix = 1;
					$lprefix = is_lang_prefix($req_arr[0]);			
				}
				if ($lprefix) {	
					foreach ($langs as $lang_attr => $lang_flag) {
						$key_lang = get_lang_key($lang_attr);
						if ($key_lang and $key_lang == $lprefix) {	
							$current_lang = $lang_attr;
							if ($unset_prefix) {
								unset($req_arr[0]);
							}
							$_SERVER['REQUEST_URI'] = '/' . ltrim($site_path . '/' . implode('/', $req_arr), '/');
							break;
						}
					}
				} 	
				
			}
		}

		if (_is('is_merch')) {
			$site_locale = is_lang_attr(get_pn_cookie('site_locale'));
			if ($site_locale) {
				$current_lang = $site_locale;
			}
		}		
		
		$locale = $current_lang;
	}
}

if (!function_exists('pn_lang_redirect')) {
	add_action('template_redirect', 'pn_lang_redirect', 3);
	function pn_lang_redirect() {
		global $pn_lang;
		
		if (is_ml()) {
			$lang_redir = intval(is_isset($pn_lang, 'lang_redir'));
			$getlang = is_lang_prefix(is_param_get('lang'));
			if ($lang_redir and !$getlang) {
				$language_selected = intval(get_pn_cookie('language_selected'));
				if (1 != $language_selected) {
					$browser_lang = get_browser_lang();
					$now_locale = get_locale();
					if ($browser_lang and $browser_lang != $now_locale) {
						$langs = get_langs_ml();	
						foreach ($langs as $lang => $l_data) {
							if ($lang == $browser_lang) {
								$location = lang_self_link($browser_lang);
								header("X-Redirect-By: Auto-language");
								header("Location: $location", true, 302);
								exit;	
							}	
						}			
					}
				}
			}
		}
	}
}

if (!function_exists('set_premium_site_locale')) {
	add_action('template_redirect', 'set_premium_site_locale', 4);
	function set_premium_site_locale() {
		add_pn_cookie('site_locale', get_locale());
	}
}

if (!function_exists('def_set_site_lang')) {
	add_action('premium_siteaction_set_site_lang', 'def_set_site_lang');
	function def_set_site_lang() {
		
		$return_url = trim(urldecode(is_param_get('return_url')));
		add_pn_cookie('language_selected', '1');
		wp_redirect(get_safe_url($return_url));
		exit;
		
	}
}

if (!function_exists('get_lang_vers')) {
	function get_lang_vers($lang) {
		global $pn_lang;
		
		$lang_redir = intval(is_isset($pn_lang, 'lang_redir'));
		if ($lang_redir) {
			return get_pn_action('set_site_lang', 'get') . '&return_url=' . urlencode(lang_self_link($lang));
		} else {
			return lang_self_link($lang);
		}
	}
}

if (!function_exists('hreflang_wp_head')) {
	add_action('wp_head', 'hreflang_wp_head');
	function hreflang_wp_head() {
		global $pn_lang;
		
		$html = '';
		$lang_redir = intval(is_isset($pn_lang, 'lang_redir'));
		if (!$lang_redir) {
			$lang = get_site_lang();
			$langs = get_langs_ml();
			foreach ($langs as $lan) {
				if ($lan == $lang) {
					$html .= '<link rel="alternate" hreflang="x-default" href="' . lang_self_link($lan) . '" />' . "\n";
				} else {
					$html .= '<link rel="alternate" hreflang="' . str_replace('_', '-', $lan) . '" href="' . lang_self_link($lan) . '" />' . "\n";
				}
			}
		}
		
		echo $html;
	}
}