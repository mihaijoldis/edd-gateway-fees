var edd_global_vars;
jQuery(document).ready(function($) {
    $('select#edd-gateway, input.edd-gateway').change( function (e) {

        var $this = $(this), postData = {
            action: 'edd_calculate_gateway_fees',
            gateway: $this.val()
        };

        $.ajax({
            type: "POST",
            data: postData,
            dataType: "json",
            url: edd_global_vars.ajaxurl,
            success: function (discount_response) {
				$('#edd_checkout_cart').replaceWith(discount_response.html);
				$('.edd_cart_amount').html(discount_response.total);
            }
        }).fail(function (data) {
            console.log(data);
        });

        return false;
    });
});