jQuery(document).ready(function ($) {
    $('#mpr-test-push-btn').on('click', function (e) {
        e.preventDefault();

        var $btn = $(this);
        var $status = $('#mpr-test-push-status');

        $btn.prop('disabled', true);
        $status.text(mpr_admin_notif_vars.sending_msg).css('color', '#666');

        $.ajax({
            url: mpr_admin_notif_vars.ajax_url,
            type: 'POST',
            data: {
                action: 'mpr_test_push',
                nonce: mpr_admin_notif_vars.nonce
            },
            success: function (response) {
                if (response.success) {
                    $status.text(mpr_admin_notif_vars.success_msg).css('color', 'green');
                } else {
                    $status.text(mpr_admin_notif_vars.error_msg + response.data.message).css('color', 'red');
                }
                $btn.prop('disabled', false);
            },
            error: function () {
                $status.text(mpr_admin_notif_vars.error_msg).css('color', 'red');
                $btn.prop('disabled', false);
            }
        });
    });
});
