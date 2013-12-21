jQuery(document).ready(function($) {
    var $body = $('body'),
        $edd_cart_amount = $('.edd_cart_amount');

    // Update state/province field on checkout page
    $body.on('change', '#edd-payment-mode-wrap', function() {
        var $this = $(this);
        recalculate_taxes();

        return false;
    });

    function recalculate_taxes( state ) {
        var $edd_cc_address = $('#edd_cc_address');

        if( ! state ) {
            state = $edd_cc_address.find('#card_state').val();
        }

        var postData = {
            action: 'edd_recalculate_taxes',
            nonce: edd_global_vars.checkout_nonce,
            country: $edd_cc_address.find('#billing_country').val(),
            state: state
        };

        $.ajax({
            type: "POST",
            data: postData,
            dataType: "json",
            url: edd_global_vars.ajaxurl,
            success: function (tax_response) {
                $('#edd_checkout_cart').replaceWith(tax_response.html);
                $('.edd_cart_amount').html(tax_response.total);
                var tax_data = new Object();
                tax_data.postdata = postData;
                tax_data.response = tax_response;
                $('body').trigger('edd_taxes_recalculated', [ tax_data ]);
            }
        }).fail(function (data) {
            console.log(data);
        });
    }
});