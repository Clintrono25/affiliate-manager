<?php
namespace AffiliateManager;

class Affiliate {
    private $db;
    
    public function __construct() {
        global $wpdb;
        $this->db = $wpdb;
        
        // Removed the add_role here as it's usually handled on activation
        // add_role('affiliate', __('Affiliate', 'affiliate-manager'), [
        //     'read' => true,
        //     'edit_posts' => false,
        //     'upload_files' => true
        // ]);
        
        // Register AJAX handlers (Updated callbacks)
        add_action('wp_ajax_register_as_affiliate', [$this, 'handle_affiliate_registration']);
        add_action('wp_ajax_nopriv_register_as_affiliate', [$this, 'handle_no_privileges']);
        
        // Add other initialization code...
    }
    
    /**
     * Handles the affiliate registration form submission
     */
public function handle_affiliate_registration() {
    // 1. Verify nonce
    if (!check_ajax_referer('affiliate-registration', 'security', false)) {
        error_log("Nonce verification failed");
        wp_send_json_error(['message' => 'Security check failed'], 403);
    }

    // 2. Check user is logged in
    if (!is_user_logged_in()) {
        error_log("User not logged in");
        wp_send_json_error(['message' => 'You must be logged in'], 401);
    }

    $user_id = get_current_user_id();
    error_log("Processing registration for user ID: $user_id");

    // 3. Validate input
    $email = sanitize_email($_POST['payment_email'] ?? '');
    if (!is_email($email)) {
        error_log("Invalid email: $email");
        wp_send_json_error(['message' => 'Invalid email'], 400);
    }

    if (!isset($_POST['terms_agreed'])) {
        error_log("Terms not agreed");
        wp_send_json_error(['message' => 'You must accept terms'], 400);
    }

    // 4. Check if already registered
    global $wpdb;
    $exists = $wpdb->get_var($wpdb->prepare(
        "SELECT id FROM {$wpdb->prefix}affiliate_manager_affiliates WHERE user_id = %d", 
        $user_id
    ));
    
    if ($exists) {
        error_log("User already an affiliate");
        wp_send_json_success([
            'message' => 'You are already registered',
            'redirect' => home_url('/affiliate-dashboard')
        ]);
        return;
    }

    // 5. Insert into database
    $result = $wpdb->insert(
        $wpdb->prefix.'affiliate_manager_affiliates',
        [
            'user_id' => $user_id,
            'payment_email' => $email,
            'status' => 'active',
            'registration_date' => current_time('mysql')
        ],
        ['%d', '%s', '%s', '%s']
    );

    error_log("Insert result: " . print_r($result, true));
    error_log("Last query: " . $wpdb->last_query);
    error_log("Last error: " . $wpdb->last_error);

    if (!$result) {
        error_log("Database insert failed");
        wp_send_json_error(['message' => 'Registration failed'], 500);
    }

    // 6. Add user role
    $user = new WP_User($user_id);
    $user->add_role('affiliate');
    error_log("Added affiliate role to user");

    // 7. Success response
    wp_send_json_success([
        'message' => 'Registration successful!',
        'redirect' => home_url('/affiliate-dashboard')
    ]);
}

    /**
     * Helper function to check if user is already an affiliate (New method)
     */
    private function is_user_affiliate($user_id) {
        $affiliate = $this->db->get_row($this->db->prepare(
            "SELECT id FROM {$this->db->prefix}affiliate_manager_affiliates 
            WHERE user_id = %d", 
            $user_id
        ));
        
        return !empty($affiliate);
    }

    /**
     * Registers a user as an affiliate in the database (New method)
     */
    private function register_affiliate_user($user_id, $email) {
        // Insert into affiliate table
        $result = $this->db->insert(
            "{$this->db->prefix}affiliate_manager_affiliates",
            [
                'user_id' => $user_id,
                'payment_email' => $email,
                'status' => 'active', // or 'pending' for admin approval
                'registration_date' => current_time('mysql')
            ],
            ['%d', '%s', '%s', '%s']
        );

        if (!$result) {
            return new WP_Error('db_error', __('Failed to register affiliate', 'affiliate-manager'));
        }

        // Add affiliate role
        $user = new \WP_User($user_id); // Use \WP_User for global class in namespace
        $user->add_role('affiliate');

        // Send notifications (New helper calls)
        $this->send_admin_notification($user_id);
        $this->send_welcome_email($user_id);

        return true;
    }

    /**
     * Handles unauthorized AJAX requests (New method)
     */
    public function handle_no_privileges() {
        wp_send_json_error([
            'message' => __('You must be logged in to perform this action', 'affiliate-manager')
        ], 403); // Added HTTP status code
    }

    /**
     * Sends a notification to the admin about new affiliate registration (New method - placeholder)
     */
    private function send_admin_notification($user_id) {
        // TODO: Implement actual email sending logic here
        $user = get_userdata($user_id);
        $admin_email = get_option('admin_email');
        
        $subject = __('New Affiliate Application', 'affiliate-manager');
        $message = sprintf(
            __('A new user has applied to become an affiliate: %s (%s)', 'affiliate-manager'),
            $user->display_name,
            $user->user_email
        );
        
        wp_mail($admin_email, $subject, $message);
    }

    /**
     * Sends a welcome email to the newly registered affiliate (New method - placeholder)
     */
    private function send_welcome_email($user_id) {
        // TODO: Implement actual welcome email logic here
        $user = get_userdata($user_id);
        
        $subject = __('Welcome to the Affiliate Program!', 'affiliate-manager');
        $message = sprintf(
            __('Hello %s, thank you for registering as an affiliate with us.', 'affiliate-manager'),
            $user->display_name
        );
        
        wp_mail($user->user_email, $subject, $message);
    }
    
    // Original methods retained below
    public function generate_link($affiliate_id, $destination, $name = '') {
        $slug = $this->generate_unique_slug();
        
        $this->db->insert(
            "{$this->db->prefix}affiliate_manager_links",
            [
                'affiliate_id' => $affiliate_id,
                'destination_url' => esc_url_raw($destination),
                'slug' => $slug,
                'name' => sanitize_text_field($name),
                'created_at' => current_time('mysql')
            ],
            ['%d', '%s', '%s', '%s', '%s']
        );
        
        return [
            'id' => $this->db->insert_id,
            'url' => home_url("/go/$slug")
        ];
    }
    
    private function generate_unique_slug() {
        $slug = substr(md5(uniqid(rand(), true)), 0, 8);
        
        // Check if slug exists
        $exists = $this->db->get_var($this->db->prepare(
            "SELECT id FROM {$this->db->prefix}affiliate_manager_links WHERE slug = %s",
            $slug
        ));
        
        if ($exists) {
            return $this->generate_unique_slug();
        }
        
        return $slug;
    }
    
    public function get_affiliate_links($affiliate_id) {
        return $this->db->get_results($this->db->prepare(
            "SELECT * FROM {$this->db->prefix}affiliate_manager_links 
             WHERE affiliate_id = %d ORDER BY created_at DESC",
            $affiliate_id
        ));
    }

    public function create_test_affiliate() {
    // Security check - only admins can run this
    if (!current_user_can('manage_options')) {
        wp_die('You do not have permission to perform this action.');
    }

    global $wpdb;
    
    // 1. Create test user if doesn't exist
    $username = 'testaffiliate_' . time(); // Unique username
    $email = 'testaffiliate_' . time() . '@example.com'; // Unique email
    
    $user_id = username_exists($username);
    
    if (!$user_id) {
        error_log("Creating test user: $username");
        $user_id = wp_create_user($username, 'testpassword', $email);
        
        if (is_wp_error($user_id)) {
            error_log("User creation failed: " . $user_id->get_error_message());
            wp_die('Failed to create test user: ' . $user_id->get_error_message());
        }
    }

    // 2. Add to affiliate table
    $result = $wpdb->insert(
        $wpdb->prefix . 'affiliate_manager_affiliates',
        [
            'user_id' => $user_id,
            'payment_email' => $email,
            'status' => 'active',
            'registration_date' => current_time('mysql')
        ],
        ['%d', '%s', '%s', '%s']
    );

    error_log("Insert result: " . print_r($result, true));
    error_log("Last query: " . $wpdb->last_query);
    error_log("Last error: " . $wpdb->last_error);

    if (!$result) {
        wp_die('Failed to insert affiliate record. Error: ' . $wpdb->last_error);
    }

    // 3. Assign affiliate role
    $user = new WP_User($user_id);
    $user->add_role('affiliate');
    
    // 4. Verify the creation
    $affiliate = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}affiliate_manager_affiliates WHERE user_id = %d",
        $user_id
    ));

    // Output results
    echo '<div class="notice notice-success">';
    echo '<h3>Test Affiliate Created Successfully</h3>';
    echo '<p><strong>User ID:</strong> ' . $user_id . '</p>';
    echo '<p><strong>Username:</strong> ' . $username . '</p>';
    echo '<p><strong>Email:</strong> ' . $email . '</p>';
    echo '<p><strong>Affiliate Record:</strong> <pre>' . print_r($affiliate, true) . '</pre></p>';
    
    // Show user roles
    echo '<p><strong>User Roles:</strong> ' . implode(', ', $user->roles) . '</p>';
    
    // Link to admin dashboard
    echo '<p><a href="' . admin_url('admin.php?page=affiliate-manager') . '">View in Admin Dashboard</a></p>';
    echo '</div>';
    
    exit;
}
}