jQuery(document).ready(function($) {
    $body.on('change', '#edd-gateway option', function (event) {

        var $this = $(this), postData = {
            action: 'edd_remove_discount',
            code: $this.data('code')
        };

        $.ajax({
            type: "POST",
            data: postData,
            dataType: "json",
            url: edd_global_vars.ajaxurl,
            success: function (discount_response) {
                recalculate_taxes();
            }
        }).fail(function (data) {
            console.log(data);
        });

        return false;
    });
});