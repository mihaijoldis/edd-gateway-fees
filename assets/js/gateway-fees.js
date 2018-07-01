var edd_global_vars, edd_gf_gateway;
jQuery(document).ready(function($) {

    $('body').on( 'edd_taxes_recalculated edd_gateway_loaded edd_discount_applied edd_discount_removed edd_quantity_updated', function (e) {

        edd_gf_gateway = $( "input[name='edd-gateway']" ).val();

        var $this = $(this), postData = {
            action: 'edd_calculate_gateway_fees',
            gateway: edd_gf_gateway
        };

        $.ajax({
            type: "POST",
            data: postData,
            dataType: "json",
            url: edd_global_vars.ajaxurl,
            success: function (discount_response) {
				$('#edd_checkout_cart').replaceWith(discount_response.html);
				$('.edd_cart_amount').html(discount_response.total);
                console.log(discount_response);
            }
        }).fail(function (data) {
            if ( window.console && window.console.log ) {
            }
        });

        return false;
    });
});