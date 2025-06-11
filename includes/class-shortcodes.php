<?php
namespace AffiliateManager;

class Shortcodes {
    public function __construct() {
        add_shortcode('affiliate_register', [$this, 'register_form']);
        add_shortcode('affiliate_link_generator', [$this, 'link_generator']);
        add_shortcode('affiliate_dashboard', [$this, 'dashboard']);
        
        // --- NEW UPDATE: Register the new user registration shortcode ---
        add_shortcode('affiliate_user_register', [$this, 'user_register_form']); 
        // You can change 'affiliate_user_register' to any shortcode tag you prefer, e.g., 'am_user_register'
        // --- END NEW UPDATE ---
    }
    
    public function register_form() {
        if (is_user_logged_in() && $this->is_affiliate()) {
            return '<div class="affiliate-notice">' . 
                   __('You are already registered as an affiliate', 'affiliate-manager') . 
                   '</div>';
        }
        
        ob_start();
        include(AFFILIATE_MANAGER_PLUGIN_DIR . 'templates/register-form.php');
        return ob_get_clean();
    }
    
    public function link_generator($atts) {
        if (!is_user_logged_in() || !$this->is_affiliate()) {
            return '<div class="affiliate-notice">' . 
                   __('Please login as an affiliate to access this feature', 'affiliate-manager') . 
                   '</div>';
        }
        
        $atts = shortcode_atts([
            'default_url' => ''
        ], $atts);
        
        ob_start();
        include(AFFILIATE_MANAGER_PLUGIN_DIR . 'templates/link-generator.php');
        return ob_get_clean();
    }
    
    public function dashboard() {
        if (!is_user_logged_in() || !$this->is_affiliate()) {
            return '<div class="affiliate-notice">' . 
                   __('Please login as an affiliate to access this feature', 'affiliate-manager') . 
                   '</div>';
        }
        
        ob_start();
        include(AFFILIATE_MANAGER_PLUGIN_DIR . 'templates/dashboard/main.php');
        return ob_get_clean();
    }
    
    private function is_affiliate() {
        $user = wp_get_current_user();
        return in_array('affiliate', (array) $user->roles);
    }

    // --- NEW UPDATE: The user_register_form() method is added here ---
    public function user_register_form() {
        if (is_user_logged_in()) {
            return '<div class="notice notice-info">' . __('You are already logged in.', 'affiliate-manager') . '</div>';
        }
        
        ob_start();
        ?>
        <form id="affiliate-user-register" method="post">
            <div class="form-group">
                <label for="reg-email"><?php _e('Email', 'affiliate-manager'); ?></label>
                <input type="email" id="reg-email" name="email" required>
            </div>
            
            <div class="form-group">
                <label for="reg-password"><?php _e('Password', 'affiliate-manager'); ?></label>
                <input type="password" id="reg-password" name="password" required>
            </div>
            
            <div class="form-group">
                <label for="reg-firstname"><?php _e('First Name', 'affiliate-manager'); ?></label>
                <input type="text" id="reg-firstname" name="first_name">
            </div>
            
            <div class="form-group">
                <label for="reg-lastname"><?php _e('Last Name', 'affiliate-manager'); ?></label>
                <input type="text" id="reg-lastname" name="last_name">
            </div>
            
            <?php wp_nonce_field('affiliate_user_register', 'affiliate_register_nonce'); ?>
            <input type="hidden" name="action" value="affiliate_user_register">
            
            <button type="submit"><?php _e('Register', 'affiliate-manager'); ?></button>
        </form>
        <?php
        return ob_get_clean();
    }
    // --- END NEW UPDATE ---
}