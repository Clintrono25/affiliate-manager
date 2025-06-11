jQuery(document).ready(function($) {
    $('#affiliate-registration-form').on('submit', function(e) {
        e.preventDefault();
        
        var $form = $(this);
        var $button = $form.find('button[type="submit"]');
        var originalText = $button.text();
        // The $message variable from original is removed as per updated file logic
        
        // Show loading state
        $button.prop('disabled', true).text('Processing...');
        // The $message.html('').removeClass('error success'); from original is removed

        // Prepare form data
        var formData = {
            action: 'register_as_affiliate',
            payment_email: $form.find('#payment-email').val(),
            terms_agreed: $form.find('#terms-agreed').is(':checked') ? 1 : 0, // Changed selector from [name="terms_agreed"] to #terms-agreed
            security: $form.find('#affiliate_nonce').val() // Changed from affiliateRegistration.nonce to #affiliate_nonce
        };
        
        // Make AJAX request
        $.ajax({
            url: ajaxurl, // Changed from affiliateRegistration.ajaxurl to ajaxurl
            type: 'POST',
            data: formData, // Uses the new formData variable
            dataType: 'json', // Added dataType
            success: function(response) {
                if (response.success) {
                    // Show success message
                    $('#affiliate-registration-message').html( // Direct targeting, no $message variable
                        '<div class="notice notice-success">' + response.data.message + '</div>' // Expected response.data.message
                    );
                    
                    // Redirect if needed
                    if (response.data.redirect) { // Changed redirect logic
                        window.location.href = response.data.redirect;
                    }
                    // The slideUp() from original is removed
                } else {
                    // Show error message
                    $('#affiliate-registration-message').html( // Direct targeting, no $message variable
                        '<div class="notice notice-error">' + response.data.message + '</div>' // Expected response.data.message
                    );
                    $button.text(originalText).prop('disabled', false);
                }
            },
            error: function(xhr) {
                var errorMsg = xhr.responseJSON && xhr.responseJSON.data ? 
                    xhr.responseJSON.data.message : 'An error occurred. Please try again.'; // Expected response.data.message
                
                $('#affiliate-registration-message').html( // Direct targeting, no $message variable
                    '<div class="notice notice-error">' + errorMsg + '</div>'
                );
                $button.text(originalText).prop('disabled', false);
            }
        });
    });
});