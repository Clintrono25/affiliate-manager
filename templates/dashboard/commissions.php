<?php
$commissions = (new AffiliateManager\Commission())->get_affiliate_commissions(get_current_user_id(), 20);
?>

<?php if (empty($commissions)) : ?>
    <p><?php esc_html_e('No commissions yet.', 'affiliate-manager'); ?></p>
<?php else : ?>
    <table class="affiliate-commissions-table">
        <thead>
            <tr>
                <th><?php esc_html_e('Date', 'affiliate-manager'); ?></th>
                <th><?php esc_html_e('Order', 'affiliate-manager'); ?></th>
                <th><?php esc_html_e('Amount', 'affiliate-manager'); ?></th>
                <th><?php esc_html_e('Status', 'affiliate-manager'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($commissions as $commission) : ?>
                <tr>
                    <td><?php echo esc_html(date_i18n(get_option('date_format'), strtotime($commission->created_date))); ?></td>
                    <td>#<?php echo esc_html($commission->order_id); ?></td>
                    <td><?php echo esc_html(wc_price($commission->amount)); ?></td>
                    <td><?php echo esc_html(ucfirst($commission->status)); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>