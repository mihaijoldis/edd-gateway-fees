<?php
if ( !defined( 'ABSPATH' ) ) {
	exit;
}

class EDD_GF_Frontend {
	public function __construct() {
		add_action( 'init', array( $this, 'default_gateway_fee' ) );
		add_action( 'wp_ajax_edd_calculate_gateway_fees', array( $this, 'recalculate_gateway_fees' ) );
		add_action( 'wp_ajax_nopriv_edd_calculate_gateway_fees', array( $this, 'recalculate_gateway_fees' ) );
		add_filter( 'edd_chosen_gateway', array( $this, 'fix_edd_chosen_gateway' ), 10, 1 );
		remove_action( 'edd_purchase_form_after_cc_form', 'edd_checkout_submit', 9999 );
		add_action( 'edd_purchase_form_after_cc_form', array( $this, 'edd_checkout_submit' ), 9999 );
	}

	function edd_checkout_submit() {
?>
		<fieldset id="edd_purchase_submit">
			<?php do_action( 'edd_purchase_form_before_submit' ); ?>

			<?php edd_checkout_hidden_fields(); ?>

			<?php echo edd_checkout_button_purchase(); ?>

			<?php do_action( 'edd_purchase_form_after_submit' ); ?>

			<?php if ( edd_is_ajax_disabled() ) { ?>
				<p class="edd-cancel"><a href="<?php echo edd_get_checkout_uri(); ?>"><?php _e( 'Go back', 'easy-digital-downloads' ); ?></a></p>
			<?php } ?>
		</fieldset>
	<?php
	}

	function fix_edd_chosen_gateway( $enabled_gateway ) {
		if ( ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] === 'edd_recalculate_taxes' )  ) {
			if ( $_SERVER['HTTP_REFERER'] ){
				parse_str(parse_url($_SERVER['HTTP_REFERER'], PHP_URL_QUERY), $gateway);
				if ( ! empty( $gateway['payment-mode'] ) ) {
					$enabled_gateway = $gateway['payment-mode'];
					$enabled_gateway = urldecode( $enabled_gateway );
					return $enabled_gateway;
				} else {
					return $enabled_gateway;
				}
			}
			return $enabled_gateway;
		}
				
		$gateways = edd_get_enabled_payment_gateways();
		$chosen   = isset( $_REQUEST['payment-mode'] ) ? $_REQUEST['payment-mode'] : false;
		if ( false !== $chosen ) {
			$chosen = preg_replace( '/[^a-zA-Z0-9-_]+/', '', $chosen );
		}
		if ( ! empty ( $chosen ) ) {
			$enabled_gateway = urldecode( $chosen );
		} else if ( edd_get_cart_subtotal() <= 0 ) {
				$enabled_gateway = 'manual';
		} else {
			$enabled_gateway = edd_get_default_gateway();
		}
		return $enabled_gateway;
	}

	function default_gateway_fee() {

		EDD()->fees->remove_fee( 'gateway_fee' );

		if ( edd_get_cart_total() == 0 ) {
			return;
		}

		$gateway = edd_get_chosen_gateway();

		$fee = $this->calculate_gateway_fee( $gateway );

		$label = edd_get_option( 'edd_gf_label_' . $gateway, edd_get_gateway_checkout_label( $gateway ) . ' ' .__( 'fee', 'edd_gf' ) );

		$fee = apply_filters( 'edd_gf_fee_total_before_add_fee', $fee );

		if ( $fee !== '0' && $fee !== '0.0' && $fee !== '0.00' ) {
			EDD()->fees->add_fee( $fee, $label, 'gateway_fee' );
		}
	}

	function gateway_fee( $gateway = false ) {
		EDD()->fees->remove_fee( 'gateway_fee' );

		if ( edd_get_cart_total() == 0 ) {
			return;
		}

		$fee = $this->calculate_gateway_fee( $gateway );

		$label = edd_get_option( 'edd_gf_label_' . $gateway, edd_get_gateway_checkout_label( $gateway ) . ' ' .__( 'fee', 'edd_gf' ) );

		$fee = apply_filters( 'edd_gf_fee_total_before_add_fee', $fee );

		if ( $fee !== '0' && $fee !== '0.0' && $fee !== '0.00' ) {
			EDD()->fees->add_fee( $fee, $label, 'gateway_fee' );
		}
	}

	function calculate_gateway_fee( $gateway ) {

		// get total
		$total = edd_get_cart_total();

		// apply % if appl
		$percent =  edd_get_option( 'edd_gf_percent_'.$gateway, '' );

		// sanitize percent
		$percent = preg_replace( '/[^\\d.]+/', '', $percent );

		$fee = 0;

		// apply flat if appl
		$flat = edd_get_option( 'edd_gf_flat_'.$gateway, '' );

		// sanitize flat
		$flat = preg_replace( '/[^\\d.]+/', '', $flat );

		if ( ! empty( $flat ) && ! empty( $percent ) ) {
			// paypal style
			$percent = $percent/100;
			$fee     = ( $total + $flat ) / ( 1 - $percent );
			$fee     = round( $fee, 2 );
			$fee     = $fee - $total;
		}
		else if ( ! empty( $flat ) ) {
				// simple add flat fee
				$fee     = $flat;
			}
		else if ( ! empty( $percent ) ) {
				// simple add percentage fee
				$percent = $percent / 100;
				$fee     = ( $total ) / ( 1 - $percent );
				$fee     = round( $fee, 2 );
				$fee     = $fee - $total;
			}

		// return total
		return $fee;

	}

	function recalculate_gateway_fees() {
		if ( ! empty ( $_REQUEST['action'] ) && $_REQUEST['action'] === 'edd_calculate_gateway_fees' ) {
			$this->gateway_fee( $_REQUEST['gateway'] );
			ob_start();
			edd_checkout_cart();
			$cart = ob_get_contents();
			ob_end_clean();
			$response = array(
				'html'  => $cart,
				'total' => html_entity_decode( edd_cart_total( false ), ENT_COMPAT, 'UTF-8' ),
			);

			echo json_encode( $response );

		}
		edd_die();
	}
}
new EDD_GF_Frontend;
