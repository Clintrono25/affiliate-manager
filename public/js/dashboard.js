jQuery(document).ready(function($) {
    // Link generation
    $('#generate-link-btn').on('click', function() {
        var $btn = $(this);
        var url = $('#destination-url').val().trim();
        var name = $('#link-name').val().trim();
        
        if (!url) {
            alert('Please enter a destination URL');
            return;
        }
        
        $btn.prop('disabled', true).text('Generating...');
        
        $.ajax({
            url: affiliateDashboard.ajaxurl,
            type: 'POST',
            data: {
                action: 'generate_affiliate_link',
                nonce: affiliateDashboard.nonce,
                url: url,
                name: name
            },
            success: function(response) {
                if (response.success) {
                    var $container = $('.generated-link-container');
                    $('#generated-link').val(response.data.link);
                    $container.slideDown();
                } else {
                    alert(response.data);
                }
            },
            error: function() {
                alert('An error occurred. Please try again.');
            },
            complete: function() {
                $btn.prop('disabled', false).text('Generate Link');
            }
        });
    });
    
    // Copy to clipboard
    $('.copy-link-btn').on('click', function() {
        var $input = $(this).siblings('input');
        $input.select();
        document.execCommand('copy');
        
        var originalText = $(this).text();
        $(this).text('Copied!');
        
        setTimeout(function() {
            $btn.text(originalText);
        }, 2000);
    });
});