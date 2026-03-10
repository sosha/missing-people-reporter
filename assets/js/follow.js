jQuery(document).ready(function($) {
    'use strict';

    $('body').on('click', '.mpr-follow-btn, .mpr-unfollow-btn', function(e) {
        e.preventDefault();
        
        var button = $(this);
        var post_id = button.data('post-id');

        // Prevent multiple clicks
        button.prop('disabled', true);

        $.ajax({
            url: mpr_ajax_object.ajax_url,
            type: 'POST',
            data: {
                action: 'mpr_toggle_follow', // The wp_ajax_ hook
                nonce: mpr_ajax_object.nonce,
                post_id: post_id
            },
            success: function(response) {
                if (response.success) {
                    if (response.data.action === 'followed') {
                        button.text('Unfollow Case');
                        button.removeClass('mpr-follow-btn').addClass('mpr-unfollow-btn');
                    } else {
                        button.text('Follow Case');
                        button.removeClass('mpr-unfollow-btn').addClass('mpr-follow-btn');
                    }
                } else {
                    alert(response.data.message || 'Something went wrong.');
                }
            },
            error: function() {
                alert('An error occurred. Please try again.');
            },
            complete: function() {
                // Re-enable the button
                button.prop('disabled', false);
            }
        });
    });
});