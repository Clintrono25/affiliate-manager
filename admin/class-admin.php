<?php
namespace AffiliateManager;

class Admin {
    public function __construct() {
        add_action('admin_menu', [$this, 'add_admin_pages']);
        add_action('admin_init', [$this, 'register_settings']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);
         error_log('Admin class initialized'); 
    }

    public function add_admin_pages() {
        add_menu_page(
            __('Affiliate Manager', 'affiliate-manager'),
            __('Affiliates', 'affiliate-manager'),
            'manage_options',
            'affiliate-manager',
            [$this, 'admin_dashboard'],
            'dashicons-groups',
            30
        );

        add_submenu_page(
            'affiliate-manager',
            __('Settings', 'affiliate-manager'),
            __('Settings', 'affiliate-manager'),
            'manage_options',
            'affiliate-manager-settings',
            [$this, 'settings_page']
        );
    }

public function admin_dashboard() {
    global $wpdb;
    
    error_log("Admin dashboard loaded");
    
    // Debug table status
    $table_exists = $wpdb->get_var("SHOW TABLES LIKE '{$wpdb->prefix}affiliate_manager_affiliates'");
    error_log("Table exists: " . ($table_exists ? 'Yes' : 'No'));
    
    // Get affiliates with user data
    $affiliates = $wpdb->get_results("
        SELECT a.*, u.user_email, u.display_name 
        FROM {$wpdb->prefix}affiliate_manager_affiliates a
        JOIN {$wpdb->users} u ON a.user_id = u.ID
    ");
    
    error_log("Affiliates query: " . $wpdb->last_query);
    error_log("Affiliates found: " . count($affiliates));
    error_log("Last error: " . $wpdb->last_error);
    
    include(AFFILIATE_MANAGER_PLUGIN_DIR . 'admin/dashboard.php');
}

    public function settings_page() {
        include(AFFILIATE_MANAGER_PLUGIN_DIR . 'admin/settings-page.php');
    }

    public function register_settings() {
        register_setting('affiliate_manager_settings', 'affiliate_manager_settings', [
            'sanitize_callback' => [$this, 'validate_settings']
        ]);

        add_settings_section(
            'general',
            __('General Settings', 'affiliate-manager'),
            null,
            'affiliate-manager-settings'
        );

        add_settings_field(
            'default_rate',
            __('Default Commission Rate (%)', 'affiliate-manager'),
            [$this, 'field_default_rate'],
            'affiliate-manager-settings',
            'general'
        );

        add_settings_field(
            'cookie_days',
            __('Cookie Duration (days)', 'affiliate-manager'),
            [$this, 'field_cookie_days'],
            'affiliate-manager-settings',
            'general'
        );

        add_settings_field(
            'min_payout',
            __('Minimum Payout Amount', 'affiliate-manager'),
            [$this, 'field_min_payout'],
            'affiliate-manager-settings',
            'general'
        );
    }

    public function field_default_rate() {
        $settings = get_option('affiliate_manager_settings');
        ?>
        <input type="number" step="0.01" min="0" max="100"
               name="affiliate_manager_settings[default_rate]"
               value="<?php echo esc_attr($settings['default_rate'] ?? 10.00); ?>">
        <?php
    }

    public function field_cookie_days() {
        $settings = get_option('affiliate_manager_settings');
        ?>
        <input type="number" min="1" max="365"
               name="affiliate_manager_settings[cookie_days]"
               value="<?php echo esc_attr($settings['cookie_days'] ?? 30); ?>">
        <?php
    }

    public function field_min_payout() {
        $settings = get_option('affiliate_manager_settings');
        ?>
        <input type="number" step="0.01" min="0"
               name="affiliate_manager_settings[min_payout]"
               value="<?php echo esc_attr($settings['min_payout'] ?? 50.00); ?>">
        <?php
    }

    public function validate_settings($input) {
        $output = [];

        $output['default_rate'] = isset($input['default_rate'])
            ? max(0, min(100, floatval($input['default_rate'])))
            : 10.00;

        $output['cookie_days'] = isset($input['cookie_days'])
            ? max(1, min(365, intval($input['cookie_days'])))
            : 30;

        $output['min_payout'] = isset($input['min_payout'])
            ? max(0, floatval($input['min_payout']))
            : 50.00;

        return $output;
    }

    public function enqueue_assets($hook) {
        if (strpos($hook, 'affiliate-manager') === false) return;

        wp_enqueue_style(
            'affiliate-admin',
            AFFILIATE_MANAGER_PLUGIN_URL . 'admin/css/admin.css',
            [],
            filemtime(AFFILIATE_MANAGER_PLUGIN_DIR . 'admin/css/admin.css')
        );
    }
}