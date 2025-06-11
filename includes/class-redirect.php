<?php
namespace AffiliateManager;

class Redirect {
    private $db;
    
    public function __construct() {
        global $wpdb;
        $this->db = $wpdb;
        
        add_action('init', [$this, 'add_rewrite_rules']);
        add_filter('query_vars', [$this, 'add_query_vars']);
        add_action('template_redirect', [$this, 'handle_redirect']);
    }
    
    public function add_rewrite_rules() {
        add_rewrite_rule('^go/([^/]+)/?$', 'index.php?affiliate_redirect=$matches[1]', 'top');
    }
    
    public function add_query_vars($vars) {
        $vars[] = 'affiliate_redirect';
        return $vars;
    }
    
    public function handle_redirect() {
        if ($slug = get_query_var('affiliate_redirect')) {
            $this->process_redirect($slug);
            exit;
        }
    }
    
    private function process_redirect($slug) {
        $link = $this->db->get_row($this->db->prepare(
            "SELECT * FROM {$this->db->prefix}affiliate_manager_links 
             WHERE slug = %s AND is_active = 1",
            $slug
        ));
        
        if (!$link) {
            $this->redirect_to_default();
            return;
        }
        
        // Update click count
        $this->db->update(
            "{$this->db->prefix}affiliate_manager_links",
            ['clicks' => $link->clicks + 1],
            ['id' => $link->id],
            ['%d'],
            ['%d']
        );
        
        // Set tracking cookie (30 days by default)
        $cookie_days = get_option('affiliate_manager_settings')['cookie_days'] ?? 30;
        setcookie(
            'affiliate_tracking',
            json_encode([
                'affiliate_id' => $link->affiliate_id,
                'link_id' => $link->id,
                'timestamp' => time()
            ]),
            time() + (DAY_IN_SECONDS * $cookie_days),
            '/',
            COOKIE_DOMAIN,
            is_ssl(),
            true
        );
        
        // Redirect to destination
        wp_redirect(esc_url_raw($link->destination_url), 302);
        exit;
    }
    
    private function redirect_to_default() {
        wp_redirect(home_url(), 302);
        exit;
    }
}