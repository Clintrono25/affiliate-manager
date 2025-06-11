<?php
/**
 * Admin Dashboard Template
 */
if (!defined('ABSPATH')) exit;

global $wpdb;
$affiliates = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}affiliate_manager_affiliates");
?>

// DEBUG: Verify roles and database status
$affiliate_users = get_users([
    'role' => 'affiliate',
    'fields' => 'ID'
]);

$db_affiliates = $wpdb->get_results("
    SELECT a.*, u.user_email, u.display_name 
    FROM {$wpdb->prefix}affiliate_manager_affiliates a
    JOIN {$wpdb->users} u ON a.user_id = u.ID
");

error_log("Admin dashboard loaded");
error_log("Users with affiliate role: " . count($affiliate_users));
error_log("Affiliates in database: " . count($db_affiliates));
?>

<div class="wrap">
    <h1>Affiliate Manager</h1>
    
    <!-- DEBUG OUTPUT -->
    <div class="notice notice-info">
        <h3>Debug Information</h3>
        <p><strong>Users with 'affiliate' role:</strong> <?php echo count($affiliate_users); ?></p>
        <p><strong>Records in affiliate table:</strong> <?php echo count($db_affiliates); ?></p>
        <?php if ($wpdb->last_error) : ?>
            <p class="error"><strong>Last DB Error:</strong> <?php echo esc_html($wpdb->last_error); ?></p>
        <?php endif; ?>
    </div>

<div class="wrap affiliate-manager-dashboard">
    <h1 class="wp-heading-inline"><?php esc_html_e('Affiliate Manager', 'affiliate-manager'); ?></h1>
    
    <div class="card">
        <h2><?php esc_html_e('Affiliates Overview', 'affiliate-manager'); ?></h2>
        
        <?php if (empty($affiliates)) : ?>
            <div class="notice notice-info">
                <p><?php esc_html_e('No affiliates found.', 'affiliate-manager'); ?></p>
            </div>
        <?php else : ?>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php esc_html_e('ID', 'affiliate-manager'); ?></th>
                        <th><?php esc_html_e('User', 'affiliate-manager'); ?></th>
                        <th><?php esc_html_e('Status', 'affiliate-manager'); ?></th>
                        <th><?php esc_html_e('Registration Date', 'affiliate-manager'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($affiliates as $affiliate) : 
                        $user = get_user_by('ID', $affiliate->user_id);
                    ?>
                        <tr>
                            <td><?php echo esc_html($affiliate->id); ?></td>
                            <td>
                                <a href="<?php echo esc_url(get_edit_user_link($affiliate->user_id)); ?>">
                                    <?php echo esc_html($user->display_name); ?>
                                </a>
                            </td>
                            <td><?php echo esc_html(ucfirst($affiliate->status)); ?></td>
                            <td><?php echo esc_html(date_i18n(get_option('date_format'), strtotime($affiliate->registration_date))); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>