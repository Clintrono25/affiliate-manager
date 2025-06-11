<?php
/**
 * Affiliate Registration Form Template
 */
if (!defined('ABSPATH')) exit; // Exit if accessed directly
?>

<div class="affiliate-registration-form">
    <?php if (is_user_logged_in()) : ?>
        <?php 
        // This check assumes $this->is_affiliate() is available in this template's context.
        // It's usually the case if this template is included within a method of the Shortcodes class.
        if ($this->is_affiliate()) : // --- NEW UPDATE: Check if already an affiliate ---
        ?>
            <div class="notice notice-info">
                <p><?php _e('You are already registered as an affiliate.', 'affiliate-manager'); ?></p>
                <p><a href="<?php echo esc_url(home_url('/affiliate-dashboard')); ?>">
                    <?php _e('Go to Dashboard', 'affiliate-manager'); ?>
                </a></p>
            </div>
        <?php else : // --- If logged in but NOT an affiliate, show the registration form --- ?>
            <h2><?php _e('Become an Affiliate', 'affiliate-manager'); ?></h2>
            
            <form id="affiliate-registration-form" method="post">
                <div class="form-group">
                    <label for="payment-email"><?php _e('Payment Email', 'affiliate-manager'); ?></label>
                    <input type="email" id="payment-email" name="payment_email" required 
                            value="<?php echo esc_attr(wp_get_current_user()->user_email); ?>">
                </div>
                
                <div class="form-group terms-group">
                    <input type="checkbox" id="terms-agreed" name="terms_agreed" required>
                    <label for="terms-agreed">
                        <?php printf(
                            __('I agree to the <a href="%s" target="_blank">Terms and Conditions</a>', 'affiliate-manager'),
                            esc_url(get_permalink(get_option('affiliate_terms_page_id')))
                        ); ?>
                    </label>
                </div>
                
                <?php wp_nonce_field('affiliate-registration', 'affiliate_nonce'); ?>
                <input type="hidden" name="action" value="register_as_affiliate">
                
                <button type="submit" class="button button-primary">
                    <?php _e('Apply to Become an Affiliate', 'affiliate-manager'); ?>
                </button>
            </form>
            
            <div id="affiliate-registration-message"></div>
        <?php endif; ?>
    <?php else : // --- NEW UPDATE: If user is NOT logged in --- ?>
        <div class="registration-options">
            <h3><?php _e('Join Our Affiliate Program', 'affiliate-manager'); ?></h3>
            <div class="option-buttons">
                <a href="<?php echo esc_url(home_url('/affiliate-login')); ?>" class="button">
                    <?php _e('Existing User? Login', 'affiliate-manager'); ?>
                </a>
                <a href="<?php echo esc_url(home_url('/affiliate-register')); ?>?action=register" class="button button-primary">
                    <?php _e('New User? Register', 'affiliate-manager'); ?>
                </a>
            </div>
        </div>
    <?php endif; ?>
</div>