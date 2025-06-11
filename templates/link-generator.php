<div class="affiliate-link-generator">
    <h2><?php _e('Generate Affiliate Link', 'affiliate-manager'); ?></h2>
    
    <div class="generator-form">
        <div class="form-group">
            <label for="destination-url"><?php _e('Destination URL', 'affiliate-manager'); ?></label>
            <input type="url" id="destination-url" class="form-control" 
                   placeholder="https://example.com/product" required>
        </div>
        
        <div class="form-group">
            <label for="link-name"><?php _e('Link Name (optional)', 'affiliate-manager'); ?></label>
            <input type="text" id="link-name" class="form-control" 
                   placeholder="<?php esc_attr_e('My Product Link', 'affiliate-manager'); ?>">
        </div>
        
        <button id="generate-link-btn" class="btn btn-primary">
            <?php _e('Generate Link', 'affiliate-manager'); ?>
        </button>
    </div>
    
    <div class="generated-link-container" style="display:none;">
        <h3><?php _e('Your Affiliate Link', 'affiliate-manager'); ?></h3>
        <div class="input-group">
            <input type="text" id="generated-link" class="form-control" readonly>
            <button class="btn btn-secondary copy-link-btn">
                <?php _e('Copy', 'affiliate-manager'); ?>
            </button>
        </div>
    </div>
</div>