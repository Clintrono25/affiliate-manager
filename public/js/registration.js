jQuery(document).ready(function($) {
    $('#affiliate-registration-form').on('submit', function(e) {
        e.preventDefault();
        var $form = $(this);
        var $button = $form.find('button[type="submit"]');
        var originalText = $button.text();

        $button.prop('disabled', true).text('Processing...');

        var formData = {
            action: 'register_as_affiliate',
            payment_email: $form.find('#payment-email').val(),
            terms_agreed: $form.find('#terms-agreed').is(':checked') ? 1 : 0,
            // FIX: use the correct selector for the nonce field
            security: $form.find('[name="affiliate_nonce"]').val()
        };
        
        $.ajax({
            url: affiliateRegistration.ajax_url,
            method: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $('#affiliate-registration-message').html('<div class="success">' + response.data.message + '</div>');
                    if (response.data.redirect) {
                        setTimeout(function() {
                            window.location.href = response.data.redirect;
                        }, 1200);
                    }
                } else {
                    $('#affiliate-registration-message').html('<div class="error">' + response.data.message + '</div>');
                    $button.prop('disabled', false).text(originalText);
                }
            },
            error: function(xhr) {
                $('#affiliate-registration-message').html('<div class="error">An error occurred. Please try again.</div>');
                $button.prop('disabled', false).text(originalText);
            }
        });
    });
});