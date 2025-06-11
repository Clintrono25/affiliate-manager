<?php
/*
Plugin Name: Affiliate Manager
Description: Complete affiliate management system
Version: 1.001
Author: Your Name
*/

defined('ABSPATH') or die('Direct access not allowed');

// 1. Define ALL constants first
define('AFFILIATE_MANAGER_VERSION', '1.001');
define('AFFILIATE_MANAGER_PLUGIN_FILE', __FILE__);
define('AFFILIATE_MANAGER_PLUGIN_DIR', trailingslashit(plugin_dir_path(__FILE__)));
define('AFFILIATE_MANAGER_PLUGIN_URL', trailingslashit(plugin_dir_url(__FILE__)));

require_once AFFILIATE_MANAGER_PLUGIN_DIR . 'includes/class-database.php'; // Adjust filename if it's class-database.php


// 2. Debug file existence
error_log('Plugin directory: ' . AFFILIATE_MANAGER_PLUGIN_DIR);
error_log('Checking admin file: ' . AFFILIATE_MANAGER_PLUGIN_DIR . 'admin/class-admin.php');

// 3. Robust autoloader
spl_autoload_register(function ($class_name) {
    $prefix = 'AffiliateManager\\';
    
    if (strpos($class_name, $prefix) !== 0) {
        return;
    }

    $relative_class = substr($class_name, strlen($prefix));
    $class_path = str_replace('\\', DIRECTORY_SEPARATOR, $relative_class);
    
    // Add 'class-' prefix and convert to lowercase filename
    $class_file = 'class-' . strtolower($class_path) . '.php';
    
    $locations = [
        AFFILIATE_MANAGER_PLUGIN_DIR . 'includes' . DIRECTORY_SEPARATOR . $class_file,
        AFFILIATE_MANAGER_PLUGIN_DIR . 'admin' . DIRECTORY_SEPARATOR . $class_file
    ];
    
    foreach ($locations as $file) {
        if (file_exists($file)) {
            require $file;
            return;
        }
    }
    
    error_log("Class {$class_name} not found. Searched locations:");
    foreach ($locations as $location) {
        error_log("- " . $location);
    }
});

// 4. Manually require admin class if still having issues (temporary)
if (!class_exists('AffiliateManager\Admin') && file_exists(AFFILIATE_MANAGER_PLUGIN_DIR . 'admin/class-admin.php')) {
    require_once AFFILIATE_MANAGER_PLUGIN_DIR . 'admin/class-admin.php';
    error_log('Manually loaded Admin class');
}

// 5. Initialize the plugin
register_activation_hook(__FILE__, function() {
    // Verify PHP version first
    if (version_compare(PHP_VERSION, '7.4', '<')) {
        wp_die(__('This plugin requires PHP 7.4 or higher.', 'affiliate-manager'));
    }
    
    // Verify database tables
    require_once plugin_dir_path(__FILE__) . 'includes/class-database.php';
    AffiliateManager\Database::install();
    
    // Flush rewrite rules after table creation
    flush_rewrite_rules();
    
    // Create default options if needed
    if (!get_option('affiliate_manager_settings')) {
        update_option('affiliate_manager_settings', [
            'default_rate' => 15.00,
            'cookie_days' => 30,
            'min_payout' => 50.00
        ]);
    }
});

add_action('init', function() {
    // Load translations from /languages/ directory
    load_plugin_textdomain(
        'affiliate-manager',      // Text domain
        false,                    // Deprecated argument
        dirname(plugin_basename(__FILE__)) . '/languages/'  // Relative path
    );
    
    error_log('Translations loaded for affiliate-manager');
});

    // Debug loaded classes
    error_log('Initializing plugin components');
    
    // Initialize components with existence checks
    if (is_admin()) {
        if (class_exists('AffiliateManager\Admin')) {
            new AffiliateManager\Admin();
            error_log('Admin class initialized');
        } else {
            error_log('ERROR: Admin class not found');
        }
    }
    
    $components = [
        'Affiliate' => 'Core affiliate functions',
        'Shortcodes' => 'Shortcodes handler',
        'Redirect' => 'Link redirection',
        'Dashboard' => 'Frontend dashboard',
        'Commission' => 'Commission handling'
    ];
    
    foreach ($components as $class => $description) {
        $full_class = "AffiliateManager\\$class";
        if (class_exists($full_class)) {
            new $full_class();
            error_log("Initialized $full_class ($description)");
        } else {
            error_log("ERROR: $full_class not found ($description)");
        }
    }
