<?php 
if (!defined('ABSPATH')) { exit(); }

if (!function_exists('without_path')) {
	function without_path($path) {
		$path = rtrim(wp_normalize_path($path), '/');
		$path_arr = explode('/', $path);
		$path_folder = array_pop($path_arr);
		$file_path = implode('/', $path_arr);
		return $file_path . '/';
	}
}

$file_path = without_path(dirname(__FILE__));

if (is_file($file_path . "userdata.php")) {
	require_once($file_path . "userdata.php"); 
}

if (is_file($file_path . "premium/index.php")) {
	require_once($file_path . "premium/index.php");
}

if (!class_exists('Premium')) {
	return;
}

if (is_file($file_path . "includes/migrate-constants.php")) {
	require_once($file_path . "includes/migrate-constants.php");
}

unset($file_path);

if (!class_exists('Exchanger')) {
	class Exchanger extends Premium {
		
		function __construct($file_path)
		{
			$this->plugin_version = '2.7';
			$this->plugin_prefix = 'pn';
			$this->plugin_name = 'Premium Exchanger';
			$this->theme_name = 'exchanger';
			$this->blog_page = 'news';
			
			global $premiumbox;
			$premiumbox = $this;
			
			parent::__construct($file_path);
			
			add_filter('query_vars', array($this, 'query_vars'));
			add_filter('generate_rewrite_rules', array($this, 'generate_rewrite_rules'));
		}
	
		function set_plugin_title() {
			
			$title['ru_RU'] = array(
				'name' => 'Premium Exchanger',
				'description' => 'Профессиональный обменный пункт',	
			);
			
			return $title;
		}	

		function admin_menu() { 
			
			if (current_user_cans('administrator, pn_change_notify')) {
				add_menu_page(__('Messages', $this->plugin_prefix), __('Messages', $this->plugin_prefix), 'read', "all_mail_temps", array($this, 'admin_temp'), $this->get_icon_link('mails'), 100);
				add_submenu_page("all_mail_temps", __('E-mail templates', $this->plugin_prefix), __('E-mail templates', $this->plugin_prefix), 'read', "all_mail_temps", array($this, 'admin_temp'));	
			}			
			
			if (current_user_cans('administrator')) {
				add_menu_page(__('Exchange office settings', $this->plugin_prefix), __('Exchange office settings', $this->plugin_prefix), 'read', "pn_config", array($this, 'admin_temp'), $this->get_icon_link('settings'), 500);	
				add_submenu_page("pn_config", __('General settings', $this->plugin_prefix), __('General settings', $this->plugin_prefix), 'read', "pn_config", array($this, 'admin_temp'));
				add_submenu_page("pn_config", __('Migration', $this->plugin_prefix), __('Migration', $this->plugin_prefix), 'read', "pn_migrate", array($this, 'admin_temp'));
			}			
			
			if (current_user_cans('administrator, pn_merchants')) {
				add_menu_page(__('Merchants', $this->plugin_prefix), __('Merchants', $this->plugin_prefix), 'read', "pn_merchants", array($this, 'admin_temp'), $this->get_icon_link('merchants'), 600);	
			}			
			
			add_menu_page(__('Modules', $this->plugin_prefix), __('Modules', $this->plugin_prefix), 'read', "pn_moduls", array($this, 'admin_temp'), $this->get_icon_link('moduls'), 700);				
			
		}	

		function list_tech_pages($pages) {
			 
			$pages['home'] = array(
				'post_name'      => 'home',
				'post_title'     => '[en_US:]Home[:en_US][ru_RU:]Главная[:ru_RU]',
				'post_content'   => '',
				'post_template'   => 'pn-homepage.php',
			);
			$pages['news'] = array(
				'post_name'      => 'news',
				'post_title'     => '[en_US:]News[:en_US][ru_RU:]Новости[:ru_RU]',
				'post_content'   => '',
				'post_template'   => '',
			);			
			$pages['notice'] = array( 
				'post_name'      => 'notice',
				'post_title'     => '[en_US:]Warning messages[:en_US][ru_RU:]Предупреждение[:ru_RU]',
				'post_content'   => '',
				'post_template'   => '',
			);				
			$pages['login'] = array(
				'post_name'      => 'login',
				'post_title'     => '[en_US:]Authorization[:en_US][ru_RU:]Авторизация[:ru_RU]',
				'post_content'   => '[login_page]',
				'post_template'   => 'pn-pluginpage.php',
			);
			$pages['register'] = array(
				'post_name'      => 'register',
				'post_title'     => '[en_US:]Register[:en_US][ru_RU:]Регистрация[:ru_RU]',
				'post_content'   => '[register_page]',
				'post_template'   => 'pn-pluginpage.php',
			);
			$pages['lostpass'] = array(
				'post_name'      => 'lostpass',
				'post_title'     => '[en_US:]Password recovery[:en_US][ru_RU:]Восстановление пароля[:ru_RU]',
				'post_content'   => '[lostpass_page]',
				'post_template'   => 'pn-pluginpage.php',
			);
			$pages['account'] = array(
				'post_name'      => 'account',
				'post_title'     => '[en_US:]Personal account[:en_US][ru_RU:]Личный кабинет[:ru_RU]',
				'post_content'   => '[account_page]',
				'post_template'   => 'pn-pluginpage.php',
			);	
			$pages['security'] = array(
				'post_name'      => 'security',
				'post_title'     => '[en_US:]Security settings[:en_US][ru_RU:]Настройки безопасности[:ru_RU]',
				'post_content'   => '[security_page]',
				'post_template'   => 'pn-pluginpage.php',
			);								
			$pages['exchange'] = array(
				'post_name'      => 'exchange',
				'post_title'     => '[en_US:]Exchange[:en_US][ru_RU:]Обмен[:ru_RU]',
				'post_content'   => '[exchange]',
				'post_template'   => 'pn-pluginpage.php',
			);	
			$pages['hst'] = array(
				'post_name'      => 'hst',
				'post_title'     => '[en_US:]Exchange - steps[:en_US][ru_RU:]Обмен - шаги[:ru_RU]',
				'post_content'   => '[exchangestep]',
				'post_template'   => 'pn-pluginpage.php',
			);		
			
			return $pages;
		}	
		
		public function query_vars($query_vars) {
			
			$query_vars[] = 'pnhash';
			$query_vars[] = 'hashed';

			return $query_vars;
		}

		public function general_tech_pages() {
			
			$g_pages = array(
				'exchange' => 'exchange_',
				'hst' => 'hst_',
			);
			
			return apply_filters('general_tech_pages', $g_pages, 'premiumbox');			
		}		
		
		public function generate_rewrite_rules($wp_rewrite) {
			
			$g_pages = $this->general_tech_pages();
			$rewrite_rules = array(
				$g_pages['exchange'] . '([\-\_A-Za-z0-9]+)$' => 'index.php?pagename=exchange&pnhash=$matches[1]',
				$g_pages['hst'] . '([A-Za-z0-9]{35})$' => 'index.php?pagename=hst&hashed=$matches[1]',
			);
			$wp_rewrite->rules = array_merge($rewrite_rules, $wp_rewrite->rules);
		
		}
		
	}    
}