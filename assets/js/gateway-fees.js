var edd_global_vars, edd_gf_gateway;
jQuery(document).ready(function($) {

    edd_gf_gateway = $('select#edd-gateway, input.edd-gateway').val();

    $('select#edd-gateway, input.edd-gateway').change( function (e) {

        edd_gf_gateway = $(this).val();

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
            }
        }).fail(function (data) {
            if ( window.console && window.console.log ) {
                console.log(data);
            }
        });

        return false;
    });

    $('body').on( 'edd_taxes_recalculated edd_discount_applied edd_discount_removed edd_quantity_updated', function (e) {

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
            }
        }).fail(function (data) {
            if ( window.console && window.console.log ) {
                console.log(data);
            }
        });

        return false;
    });
});