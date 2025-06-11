<?php
namespace AffiliateManager;

class Commission {
    private $db;
    
    public function __construct() {
        global $wpdb;
        $this->db = $wpdb;
        
        // Track conversions from WooCommerce (if active)
        if (class_exists('WooCommerce')) {
            add_action('woocommerce_order_status_completed', [$this, 'track_conversion']);
        }
    }
    
    public function track_conversion($order_id) {
        if (!isset($_COOKIE['affiliate_tracking'])) return;
        
        $tracking = json_decode(stripslashes($_COOKIE['affiliate_tracking']), true);
        
        if (!isset($tracking['affiliate_id']) || !isset($tracking['link_id'])) {
            return;
        }
        
        $affiliate_id = intval($tracking['affiliate_id']);
        $link_id = intval($tracking['link_id']);
        
        // Get order total
        $order = wc_get_order($order_id);
        $total = $order->get_total();
        
        // Get affiliate rate
        $rate = $this->get_affiliate_rate($affiliate_id);
        $commission = $total * ($rate / 100);
        
        // Record commission
        $this->db->insert(
            "{$this->db->prefix}affiliate_manager_commissions",
            [
                'affiliate_id' => $affiliate_id,
                'link_id' => $link_id,
                'order_id' => $order_id,
                'amount' => $commission,
                'created_at' => current_time('mysql')
            ],
            ['%d', '%d', '%s', '%f', '%s']
        );
        
        // Update link conversions
        $this->db->query($this->db->prepare(
            "UPDATE {$this->db->prefix}affiliate_manager_links 
             SET conversions = conversions + 1 
             WHERE id = %d",
            $link_id
        ));
    }
    
    private function get_affiliate_rate($affiliate_id) {
        $rate = $this->db->get_var($this->db->prepare(
            "SELECT rate FROM {$this->db->prefix}affiliate_manager_affiliates 
             WHERE id = %d",
            $affiliate_id
        ));
        
        return $rate ?: get_option('affiliate_manager_settings')['default_rate'] ?? 10.00;
    }
    
    public function get_affiliate_commissions($affiliate_id, $limit = 10) {
        return $this->db->get_results($this->db->prepare(
            "SELECT * FROM {$this->db->prefix}affiliate_manager_commissions 
             WHERE affiliate_id = %d 
             ORDER BY created_at DESC 
             LIMIT %d",
            $affiliate_id, $limit
        ));
    }
    
    public function get_affiliate_earnings($affiliate_id, $status = 'paid') {
        return $this->db->get_var($this->db->prepare(
            "SELECT SUM(amount) FROM {$this->db->prefix}affiliate_manager_commissions 
             WHERE affiliate_id = %d AND status = %s",
            $affiliate_id, $status
        ));
    }
}