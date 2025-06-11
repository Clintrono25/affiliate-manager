<?php
$links = (new AffiliateManager\Affiliate())->get_affiliate_links(get_current_user_id());
?>

<?php if (empty($links)) : ?>
    <p><?php esc_html_e('You haven\'t created any affiliate links yet.', 'affiliate-manager'); ?></p>
<?php else : ?>
    <table class="affiliate-links-table">
        <thead>
            <tr>
                <th><?php esc_html_e('Link Name', 'affiliate-manager'); ?></th>
                <th><?php esc_html_e('URL', 'affiliate-manager'); ?></th>
                <th><?php esc_html_e('Clicks', 'affiliate-manager'); ?></th>
                <th><?php esc_html_e('Conversions', 'affiliate-manager'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($links as $link) : ?>
                <tr>
                    <td><?php echo esc_html($link->name ?: __('No name', 'affiliate-manager')); ?></td>
                    <td>
                        <input type="text" readonly value="<?php echo esc_url(home_url("/go/{$link->slug}")); ?>"
                               onclick="this.select()" style="width:100%">
                    </td>
                    <td><?php echo esc_html($link->clicks); ?></td>
                    <td><?php echo esc_html($link->conversions); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>