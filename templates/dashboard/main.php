<?php
/**
 * Frontend Affiliate Dashboard
 */
if (!defined('ABSPATH')) exit;

$affiliate = new AffiliateManager\Affiliate();
$commission = new AffiliateManager\Commission();
$affiliate_id = get_current_user_id();
?>

<div class="affiliate-dashboard">
    <h1><?php esc_html_e('Your Affiliate Dashboard', 'affiliate-manager'); ?></h1>
    
    <div class="dashboard-stats">
        <div class="stat-card">
            <h3><?php esc_html_e('Total Clicks', 'affiliate-manager'); ?></h3>
            <p class="stat-value"><?php echo esc_html($affiliate->get_total_clicks($affiliate_id)); ?></p>
        </div>
        
        <div class="stat-card">
            <h3><?php esc_html_e('Total Conversions', 'affiliate-manager'); ?></h3>
            <p class="stat-value"><?php echo esc_html($affiliate->get_total_conversions($affiliate_id)); ?></p>
        </div>
        
        <div class="stat-card">
            <h3><?php esc_html_e('Total Earnings', 'affiliate-manager'); ?></h3>
            <p class="stat-value"><?php echo esc_html(wc_price($commission->get_affiliate_earnings($affiliate_id))); ?></p>
        </div>
    </div>
    
    <div class="dashboard-sections">
        <div class="section-links">
            <h2><?php esc_html_e('Your Affiliate Links', 'affiliate-manager'); ?></h2>
            <?php include(AFFILIATE_MANAGER_PLUGIN_DIR . 'templates/dashboard/links.php'); ?>
        </div>
        
        <div class="section-commissions">
            <h2><?php esc_html_e('Your Commissions', 'affiliate-manager'); ?></h2>
            <?php include(AFFILIATE_MANAGER_PLUGIN_DIR . 'templates/dashboard/commissions.php'); ?>
        </div>
    </div>
</div>