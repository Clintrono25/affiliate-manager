<?php
namespace AffiliateManager;

class Database {
    public static function install() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // Affiliates table
        $affiliates_table = $wpdb->prefix . 'affiliate_manager_affiliates';
        $sql1 = "CREATE TABLE $affiliates_table (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            status varchar(20) NOT NULL DEFAULT 'pending',
            payment_email varchar(100),
            rate decimal(5,2) DEFAULT 10.00,
            registration_date datetime NOT NULL,
            PRIMARY KEY  (id),
            UNIQUE KEY user_id (user_id)
        ) $charset_collate;";
        
        // Links table
        $links_table = $wpdb->prefix . 'affiliate_manager_links';
        $sql2 = "CREATE TABLE $links_table (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            affiliate_id bigint(20) NOT NULL,
            destination_url varchar(255) NOT NULL,
            slug varchar(100) NOT NULL,
            name varchar(100),
            clicks bigint(20) DEFAULT 0,
            conversions bigint(20) DEFAULT 0,
            created_at datetime NOT NULL,
            is_active tinyint(1) DEFAULT 1,
            PRIMARY KEY  (id),
            UNIQUE KEY slug (slug),
            KEY affiliate_id (affiliate_id)
        ) $charset_collate;";
        
        // Commissions table
        $commissions_table = $wpdb->prefix . 'affiliate_manager_commissions';
        $sql3 = "CREATE TABLE $commissions_table (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            affiliate_id bigint(20) NOT NULL,
            link_id bigint(20),
            order_id varchar(100),
            amount decimal(10,2) NOT NULL,
            status varchar(20) NOT NULL DEFAULT 'pending',
            created_at datetime NOT NULL,
            paid_at datetime,
            PRIMARY KEY  (id),
            KEY affiliate_id (affiliate_id),
            KEY link_id (link_id),
            KEY order_id (order_id)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql1);
        dbDelta($sql2);
        dbDelta($sql3);
        
        // Add default options
        add_option('affiliate_manager_settings', [
            'default_rate' => 10.00,
            'cookie_days' => 30,
            'min_payout' => 50.00,
            'currency' => 'USD'
        ]);
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }
}