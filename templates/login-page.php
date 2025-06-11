<?php
/**
 * Template Name: Affiliate Login
 */
get_header();

if (!is_user_logged_in()): ?>
<div class="affiliate-login-form">
    <h2>Affiliate Login</h2>
    <?php
    $args = [
        'redirect' => home_url('/affiliate-dashboard'),
        'label_username' => __('Email', 'affiliate-manager'),
        'label_password' => __('Password', 'affiliate-manager'),
        'label_remember' => __('Remember Me', 'affiliate-manager'),
        'label_log_in' => __('Login', 'affiliate-manager'),
        'remember' => true
    ];
    wp_login_form($args); ?>
    
    <p class="login-links">
        <a href="<?php echo wp_lostpassword_url(); ?>">Forgot Password?</a> | 
        <a href="<?php echo home_url('/affiliate-register'); ?>">Register</a>
    </p>
</div>
<?php else: 
    echo do_shortcode('[affiliate_dashboard]');
endif;

get_footer();