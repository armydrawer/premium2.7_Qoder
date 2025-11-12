# Installation and Setup

<cite>
**Referenced Files in This Document**   
- [premiumbox.php](file://wp-content/plugins/premiumbox/premiumbox.php)
- [config.php](file://wp-content/plugins/premiumbox/plugin/config.php)
- [db.php](file://wp-content/plugins/premiumbox/activation/db.php)
- [migrate.php](file://wp-content/plugins/premiumbox/activation/migrate.php)
- [class-plugin.php](file://wp-content/plugins/premiumbox/includes/class-plugin.php)
- [hashed_functions.php](file://wp-content/plugins/premiumbox/includes/hashed_functions.php)
- [hashed_bd_functions.php](file://wp-content/plugins/premiumbox/includes/hashed_bd_functions.php)
- [wp-config.php](file://wp-config.php)
</cite>

## Table of Contents
1. [Prerequisites](#prerequisites)
2. [Installation Methods](#installation-methods)
3. [Configuration Requirements](#configuration-requirements)
4. [Activation Process](#activation-process)
5. [Post-Installation Configuration](#post-installation-configuration)
6. [Troubleshooting Common Issues](#troubleshooting-common-issues)
7. [Performance and Security Considerations](#performance-and-security-considerations)

## Prerequisites

Before installing the Premium Exchanger plugin, ensure your WordPress environment meets the following requirements:

### WordPress Installation
The Premium Exchanger plugin requires a working WordPress installation. The plugin is designed to integrate with WordPress's core functionality and relies on WordPress's database structure, user management, and admin interface.

### PHP 8.3 Compatibility
The Premium Exchanger plugin version 2.7 is specifically designed for PHP 8.3 compatibility. This version of PHP provides improved performance, enhanced error handling, and new features that the plugin leverages for optimal operation. Running the plugin on earlier PHP versions may result in compatibility issues, performance degradation, or security vulnerabilities.

### ionCube Loader Requirements
The Premium Exchanger plugin uses ionCube PHP Encoder for code protection, which requires the ionCube Loader extension to be installed and enabled on your server. The ionCube Loader is a PHP extension that decodes and executes the encoded PHP files.

The plugin files `hashed_functions.php` and `hashed_bd_functions.php` contain ionCube loader checks that display specific error messages if the loader is not present:

```php
if(extension_loaded('ionCube Loader')){die('The file '.__FILE__." is corrupted.\n");}echo("\nScript error: the ".(($cli=(php_sapi_name()=='cli')) ?'ionCube':'<a href="https://www.ioncube.com">ionCube</a>')." Loader for PHP needs to be installed.\n\nThe ionCube Loader is the industry standard PHP extension for running protected PHP code,\nand can usually be added easily to a PHP installation.\n\nFor Loaders please visit".($cli?":\n\nhttps://get-loader.ioncube.com\n\nFor":' <a href="https://get-loader.ioncube.com">get-loader.ioncube.com</a> and for')." an instructional video please see".($cli?":\n\nhttp://ioncu.be/LV\n\n":' <a href="http://ioncu.be/LV">http://ioncu.be/LV</a> ')."\n\n");exit(199);
```

This code snippet demonstrates the plugin's dependency on ionCube Loader and provides users with direct links to obtain the loader and view instructional videos for installation.

**Section sources**
- [hashed_functions.php](file://wp-content/plugins/premiumbox/includes/hashed_functions.php#L1-L11)
- [hashed_bd_functions.php](file://wp-content/plugins/premiumbox/includes/hashed_bd_functions.php#L1-L11)

## Installation Methods

### WordPress Plugin Installer
The recommended method for installing the Premium Exchanger plugin is through the WordPress admin interface:

1. Log in to your WordPress admin dashboard
2. Navigate to Plugins → Add New
3. Click "Upload Plugin" and select the Premium Exchanger plugin zip file
4. Click "Install Now" and wait for the installation to complete
5. Click "Activate Plugin" to enable the plugin functionality

### Manual Upload
For users who prefer manual installation or encounter issues with the WordPress installer:

1. Extract the plugin files from the downloaded archive
2. Using FTP or your hosting control panel's file manager, upload the entire `premiumbox` folder to the `/wp-content/plugins/` directory
3. Log in to your WordPress admin dashboard
4. Navigate to Plugins → Installed Plugins
5. Find "Premium Exchanger" in the list and click "Activate"

The plugin's main file `premiumbox.php` contains the standard WordPress plugin header that enables recognition by the WordPress system:

```php
/*
Plugin Name: Premium Exchanger
Plugin URI: https://premiumexchanger.com
Description: Professional e-currency exchanger
Version: 2.7
Author: Premium
Author URI: https://premiumexchanger.com
*/
```

**Section sources**
- [premiumbox.php](file://wp-content/plugins/premiumbox/premiumbox.php#L1-L10)

## Configuration Requirements

### Database Setup
During activation, the Premium Exchanger plugin creates multiple database tables to store its data. The `db.php` file in the activation directory contains the SQL statements for creating these tables:

- `psys` - Payment systems
- `currency_codes` - Currency codes
- `currency` - Currency information
- `currency_custom_fields` - Custom fields for currencies
- `direction_custom_fields` - Custom fields for exchange directions
- `directions` - Exchange directions
- `currency_reserv` - Reserve transactions
- `exchange_bids` - Exchange bids/transactions
- Various meta tables for additional data storage

The plugin uses WordPress's `$wpdb` class to interact with the database and creates tables with the site's table prefix (defined in `wp-config.php` as `pr_` in the provided example).

```php
global $wpdb;
$prefix = $wpdb->prefix;
$charset = $wpdb->charset;

$table_name = $wpdb->prefix . "psys";
$sql = "CREATE TABLE IF NOT EXISTS $table_name(
    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    `create_date` datetime NOT NULL,
    `edit_date` datetime NOT NULL,
    `auto_status` int(1) NOT NULL default '1',
    `edit_user_id` bigint(20) NOT NULL default '0',		
    `psys_title` longtext NOT NULL,
    `psys_logo` longtext NOT NULL,
    `t2_1` bigint(20) NOT NULL default '0',
    `t2_2` bigint(20) NOT NULL default '0',		
    PRIMARY KEY (`id`),
    INDEX (`auto_status`),
    INDEX (`create_date`),
    INDEX (`edit_date`),
    INDEX (`edit_user_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=$charset AUTO_INCREMENT=1;";
$wpdb->query($sql);
```

**Section sources**
- [db.php](file://wp-content/plugins/premiumbox/activation/db.php#L1-L438)

### File Permissions
Proper file permissions are essential for the plugin to function correctly and securely:

- The plugin directory (`/wp-content/plugins/premiumbox/`) should have read and execute permissions (typically 755)
- Configuration files should not be writable by the web server to prevent unauthorized modifications
- Upload directories used by the plugin should have appropriate write permissions (typically 755 or 775 depending on server configuration)

### Server Requirements
The server environment should meet the following requirements:

- PHP 8.3 or higher with ionCube Loader extension
- MySQL 5.6 or higher (or MariaDB 10.0 or higher)
- Web server (Apache, Nginx, or IIS) with URL rewriting capabilities
- Sufficient memory allocation (at least 256MB PHP memory limit recommended)
- SSL/TLS support for secure transactions

## Activation Process

### Dependency Checking
The Premium Exchanger plugin performs environment validation during activation. The presence of ionCube Loader is checked immediately when the encoded files are loaded. If the loader is not installed, the user receives a clear error message with instructions for installation.

### Environment Validation
The plugin validates the server environment through several mechanisms:

1. **PHP Version Check**: The plugin is designed for PHP 8.3, and certain language features and performance optimizations depend on this version.
2. **WordPress Compatibility**: The plugin integrates with WordPress hooks and APIs that are version-specific.
3. **Extension Verification**: The ionCube Loader extension must be loaded and enabled.

The activation process is initiated when WordPress loads the main plugin file (`premiumbox.php`), which includes the core plugin class and registers the plugin's functionality.

```php
if (!defined('ABSPATH')) { exit(); }

require(dirname(__FILE__) . "/includes/class-plugin.php");
if (!class_exists('Exchanger')) {
	return;
}

$plugin = new Exchanger(__FILE__);
```

The `Exchanger` class extends the `Premium` base class and initializes the plugin by setting version information, prefixes, and other configuration parameters.

**Section sources**
- [premiumbox.php](file://wp-content/plugins/premiumbox/premiumbox.php#L1-L41)
- [class-plugin.php](file://wp-content/plugins/premiumbox/includes/class-plugin.php#L34-L181)

## Post-Installation Configuration

### Integration with WordPress Settings System
The Premium Exchanger plugin integrates with WordPress's settings system through custom admin pages and options. The plugin creates several admin menu items under the "Exchange office settings" section:

```php
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
}
```

### wp-config.php Modifications
The provided `wp-config.php` file shows standard WordPress configuration with a table prefix of `pr_`. No additional modifications to `wp-config.php` are required for the Premium Exchanger plugin to function, as it uses the standard WordPress database configuration.

### Configuration Options
The plugin provides extensive configuration options accessible through the admin interface. The `config.php` file defines various settings groups:

- **General settings**: Updating mode, admin password remember, data copying options
- **Exchange settings**: Table display options, form behavior, minimum/maximum amount handling
- **Exchange form settings**: Form display options, error handling, data saving preferences
- **Other settings**: Step-by-step exchange process, terms acceptance display

```php
$options['exchange_title'] = array(
    'view' => 'h3',
    'title' => __('Exchange settings', 'pn'),
    'submit' => __('Save', 'pn'),
);
$tablevids = array(
    '1' => sprintf(__('Table %1s', 'pn'), '2'),
    '2' => sprintf(__('Table %1s', 'pn'), '3'),
    '3' => sprintf(__('Table %1s', 'pn'), '4'),
    '4' => sprintf(__('Table %1s', 'pn'), '5'),
    '99' => __('Exchange form', 'pn'),
);
$options['tablevid'] = array(
    'view' => 'select',
    'title' => __('Exchange pairs table type', 'pn'),
    'options' => $tablevids,
    'default' => $premiumbox->get_option('exchange', 'tablevid'),
    'name' => 'tablevid',
    'work' => 'int',
);
```

**Section sources**
- [class-plugin.php](file://wp-content/plugins/premiumbox/includes/class-plugin.php#L64-L82)
- [config.php](file://wp-content/plugins/premiumbox/plugin/config.php#L1-L242)

## Troubleshooting Common Issues

### PHP Version Conflicts
If the server is running a PHP version earlier than 8.3, the Premium Exchanger plugin may not function correctly. Symptoms include:

- White screen of death (WSOD)
- PHP fatal errors related to unsupported syntax
- Missing functionality or incomplete page rendering

**Solution**: Upgrade the server's PHP version to 8.3 or higher through your hosting control panel or by contacting your hosting provider.

### ionCube Loader Errors
Common ionCube loader issues and their solutions:

**Error**: "The ionCube Loader for PHP needs to be installed"
- **Cause**: ionCube Loader extension is not installed or enabled
- **Solution**: Install ionCube Loader through your hosting control panel, or contact your hosting provider to enable it

**Error**: "The file is corrupted"
- **Cause**: Incompatible ionCube Loader version or corrupted plugin files
- **Solution**: Update to the latest ionCube Loader version and re-upload the plugin files

### File Permission Problems
Issues related to file permissions:

**Problem**: Unable to save settings or upload files
- **Cause**: Incorrect file or directory permissions
- **Solution**: Set appropriate permissions (755 for directories, 644 for files) using FTP or your hosting control panel

**Problem**: Security warnings about writable configuration files
- **Cause**: Configuration files with overly permissive write permissions
- **Solution**: Remove write permissions from configuration files while maintaining read permissions

### Database Migration Issues
The plugin includes a migration system to handle updates between versions:

```php
/* psys */	
$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "psys LIKE 't2_1'"); /* 1.6 */
if (0 == $query) {
    $wpdb->query("ALTER TABLE " . $wpdb->prefix . "psys ADD `t2_1` bigint(20) NOT NULL default '0'");
}
```

This code checks for the existence of specific columns and adds them if missing, ensuring compatibility when upgrading from older versions.

**Section sources**
- [migrate.php](file://wp-content/plugins/premiumbox/activation/migrate.php#L1-L136)

## Performance and Security Considerations

### Server Configuration
For optimal performance, configure your server with:

- PHP OPcache enabled to improve PHP execution speed
- Database query caching (Redis or Memcached)
- HTTP caching (Varnish or similar)
- Content Delivery Network (CDN) for static assets

### Security Implications
The installation process has several security considerations:

- **ionCube Protection**: While providing code protection, ensure you obtain the plugin from trusted sources to avoid maliciously encoded code
- **File Permissions**: Restrict write permissions to only necessary directories
- **Database Security**: Use strong database credentials and limit user privileges
- **Regular Updates**: Keep the plugin, WordPress core, and all dependencies up to date

The plugin's use of ionCube encoding provides intellectual property protection but also means that code audits are not possible without the decoding key, emphasizing the importance of trusting the plugin source.

**Section sources**
- [hashed_functions.php](file://wp-content/plugins/premiumbox/includes/hashed_functions.php)
- [hashed_bd_functions.php](file://wp-content/plugins/premiumbox/includes/hashed_bd_functions.php)
- [wp-config.php](file://wp-config.php#L22-L28)