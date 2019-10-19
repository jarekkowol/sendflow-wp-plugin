jQuery(document).ready(function () {
    jQuery(document).on('submit', '#sendflow-admin-form', function (e) {
        e.preventDefault();

        jQuery(this).append('<input type="hidden" name="action" value="store_admin_data" />');
        jQuery(this).append('<input type="hidden" name="security" value="' + sendflow_exchanger._nonce + '" />');

        jQuery.ajax({
            url: sendflow_exchanger.ajax_url,
            type: 'post',
            data: jQuery(this).serialize(),
            success: function (response) {
                alert(response);
            }
        });
    });
});