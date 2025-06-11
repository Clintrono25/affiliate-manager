<?php
/**
 * Simple User Registration Form for Affiliate Manager
 * Displayed if user is not logged in on [affiliate_register] page
 */
if (!defined('ABSPATH')) exit;

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['affiliate_user_register_nonce']) && wp_verify_nonce($_POST['affiliate_user_register_nonce'], 'affiliate_user_register')) {
    $username = sanitize_user($_POST['username'] ?? '');
    $email = sanitize_email($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($email) || empty($password)) {
        $error = 'All fields are required.';
    } elseif (username_exists($username) || email_exists($email)) {
        $error = 'Username or email already exists.';
    } else {
        $user_id = wp_create_user($username, $password, $email);
        if (is_wp_error($user_id)) {
            $error = esc_html($user_id->get_error_message());
        } else {
            // Optional: auto-login
            wp_set_current_user($user_id);
            wp_set_auth_cookie($user_id);
            $success = 'Registration successful! You are now logged in. Please continue to become an affiliate.';
            echo '<script>window.location.reload();</script>';
            exit;
        }
    }
}
?>

<div class="affiliate-user-register-form">
    <h2><?php _e('Create Your Account', 'affiliate-manager'); ?></h2>
    <?php if ($error): ?>
        <div class="error"><?php echo esc_html($error); ?></div>
    <?php elseif ($success): ?>
        <div class="success"><?php echo esc_html($success); ?></div>
    <?php endif; ?>
    <form method="post" action="">
        <div class="form-group">
            <label for="affiliate-username"><?php _e('Username', 'affiliate-manager'); ?></label>
            <input type="text" name="username" id="affiliate-username" required>
        </div>
        <div class="form-group">
            <label for="affiliate-email"><?php _e('Email', 'affiliate-manager'); ?></label>
            <input type="email" name="email" id="affiliate-email" required>
        </div>
        <div class="form-group">
            <label for="affiliate-password"><?php _e('Password', 'affiliate-manager'); ?></label>
            <input type="password" name="password" id="affiliate-password" required>
        </div>
        <?php wp_nonce_field('affiliate_user_register', 'affiliate_user_register_nonce'); ?>
        <button type="submit" class="button button-primary"><?php _e('Register & Continue', 'affiliate-manager'); ?></button>
    </form>
    <p>
        <?php _e('Already have an account?', 'affiliate-manager'); ?>
        <a href="<?php echo esc_url(home_url('/affiliate-login')); ?>"><?php _e('Log in', 'affiliate-manager'); ?></a>
    </p>
</div>