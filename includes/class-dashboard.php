<?php
namespace AffiliateManager;

class Dashboard {
    private $affiliate;
    
    public function __construct() {
        // Ensure Affiliate class is available and instantiated correctly within the namespace
        $this->affiliate = new Affiliate(); 
        
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
        add_action('wp_ajax_generate_affiliate_link', [$this, 'ajax_generate_link']);
        // You might need to add wp_ajax_nopriv_generate_affiliate_link if non-logged in users can generate links
    }
    
    public function enqueue_assets() {
        // --- NEW UPDATE: Load on all pages (Base CSS) ---
        wp_enqueue_style(
            'affiliate-base',
            AFFILIATE_MANAGER_PLUGIN_URL . 'public/css/base.css',
            [],
            filemtime(AFFILIATE_MANAGER_PLUGIN_DIR . 'public/css/base.css')
        );
        // --- END NEW UPDATE ---
        
        // --- NEW UPDATE: Load authentication assets conditionally ---
        if (is_page('affiliate-register') || is_page('affiliate-login')) {
            wp_enqueue_style(
                'affiliate-auth',
                AFFILIATE_MANAGER_PLUGIN_URL . 'public/css/auth.css',
                ['affiliate-base'], // Dependency added
                filemtime(AFFILIATE_MANAGER_PLUGIN_DIR . 'public/css/auth.css')
            );
            
            wp_enqueue_script(
                'affiliate-auth',
                AFFILIATE_MANAGER_PLUGIN_URL . 'public/js/auth.js',
                ['jquery'],
                filemtime(AFFILIATE_MANAGER_PLUGIN_DIR . 'public/js/auth.js'),
                true
            );
            // Removed wp_localize_script('affiliate-registration', 'affiliateRegistration', ...)
            // You will need to add this back if 'auth.js' or 'registration.js' still need it.
            // For example:
            // wp_localize_script('affiliate-auth', 'affiliateAuth', [
            //    'ajaxurl' => admin_url('admin-ajax.php'),
            //    'nonce' => wp_create_nonce('affiliate-registration') // Or a new nonce for auth actions
            // ]);
        }
        // --- END NEW UPDATE ---

        // Only load dashboard assets for affiliates (conditional check remains)
        // Note: The original 'if (!is_user_logged_in() || !$this->is_affiliate()) return;'
        // is now replaced by wrapping the dashboard assets in the conditional.
        if (is_user_logged_in() && $this->is_affiliate()) { 
            wp_enqueue_style(
                'affiliate-dashboard',
                AFFILIATE_MANAGER_PLUGIN_URL . 'public/css/dashboard.css',
                ['affiliate-base'], // Dependency changed from affiliate-registration to affiliate-base
                filemtime(AFFILIATE_MANAGER_PLUGIN_DIR . 'public/css/dashboard.css')
            );
            
            wp_enqueue_script(
                'affiliate-dashboard',
                AFFILIATE_MANAGER_PLUGIN_URL . 'public/js/dashboard.js',
                ['jquery', 'affiliate-auth'], // Dependency changed from affiliate-registration to affiliate-auth
                filemtime(AFFILIATE_MANAGER_PLUGIN_DIR . 'public/js/dashboard.js'),
                true
            );
            
            // Removed wp_localize_script('affiliate-dashboard', 'affiliateDashboard', ...)
            // You will need to add this back if 'dashboard.js' still needs it.
            // For example:
            // wp_localize_script('affiliate-dashboard', 'affiliateDashboard', [
            //     'ajaxurl' => admin_url('admin-ajax.php'),
            //     'nonce' => wp_create_nonce('affiliate-actions'),
            //     'is_affiliate' => true
            // ]);
        }
    }
    
    public function ajax_generate_link() {
        check_ajax_referer('affiliate-actions', 'nonce');
        
        if (!is_user_logged_in() || !$this->is_affiliate()) {
            // Updated error response to match new format from Affiliate class
            wp_send_json_error([
                'message' => __('Permission denied', 'affiliate-manager')
            ], 403); 
        }
        
        $url = esc_url_raw($_POST['url']);
        $name = sanitize_text_field($_POST['name'] ?? '');
        
        if (empty($url)) {
            // Updated error response to match new format from Affiliate class
            wp_send_json_error([
                'message' => __('Please enter a valid URL', 'affiliate-manager')
            ], 400); 
        }
        
        $affiliate_id = $this->get_user_affiliate_id();
        $result = $this->affiliate->generate_link($affiliate_id, $url, $name);
        
        wp_send_json_success([
            'message' => __('Link generated successfully', 'affiliate-manager'),
            'link' => $result['url'],
            'id' => $result['id']
        ]);
    }
    
    private function is_affiliate() {
        $user = wp_get_current_user();
        return in_array('affiliate', (array) $user->roles);
    }
    
    private function get_user_affiliate_id() {
        global $wpdb;
        $user_id = get_current_user_id();
        
        return $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM {$wpdb->prefix}affiliate_manager_affiliates 
             WHERE user_id = %d",
            $user_id
        ));
    }
}