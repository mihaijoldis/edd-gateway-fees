<?php
/**
 * Frontend Class
 *
 * @package EDD_GF
 * @subpackage Classes
 * @copyright Copyright © 2013 Chris Christoff
 * @since 1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * EDD_GF_Frontend Class
 *
 * @package EDD_GF
 * @since 1.0
 * @version 1.0
 * @author Chris Christoff
 */
class EDD_GF_Frontend {
	/**
	 * Constructor Function
	 *
	 * @since 1.0
	 * @access protected
	 */
	public function __construct() {
		add_action( 'init',                                      array( $this, 'gateway_fee'              ) );
		add_action( 'wp_ajax_edd_calculate_gateway_fees',        array( $this, 'recalculate_gateway_fees' ) );
		add_action( 'wp_ajax_nopriv_edd_calculate_gateway_fees', array( $this, 'recalculate_gateway_fees' ) );
	}

	/**
	 * Add Gateway Fees based on the gateway that has been selected
	 *
	 * @since 1.0
	 * @access public
	 * @param boolean $gateway (default to false)
	 * @return void
	 */
	public function gateway_fee( $gateway = false ) {
		EDD()->fees->remove_fee('gateway_fee');

		// Which gateway is being used?
		if ( ! $gateway ) {
			$gateway = isset( $_REQUEST['payment-mode'] ) ? $_REQUEST['payment-mode'] : false;

			if ( ! $gateway ) {
				$gateway = edd_get_default_gateway();
			}
		}

		$gateways_array = edd_get_payment_gateways();

		$fee = $this->calculate_gateway_fee( $gateway );

		if ( '0' !== $fee && '0.0' !== $fee && '0.00' !== $fee ) {
			EDD()->fees->add_fee( $fee, edd_get_gateway_checkout_label( $gateway ) . ' ' .__( 'fee', 'edd-gf' ), 'gateway_fee' );
		}
	}

	/**
	 * Calculate the gateway fee
	 *
	 * @since 1.0
	 * @access public
	 * @global array $edd_options
	 * @param boolean $gateway (default to false)
	 * @return int $fee Gateway fee
	 */
	public  function calculate_gateway_fee( $gateway = false ) {
		global $edd_options;

		// Get the cart total
		$total = edd_get_cart_total();

		// Which gateway is being used?
		if ( ! $gateway ) {
			$gateway = isset( $_REQUEST['payment-mode'] ) ? $_REQUEST['payment-mode'] : false;

			if ( ! $gateway ) {
				$gateway = edd_get_default_gateway();
			}
		}

		// Apply percentage if set
		$percent =  edd_get_option( 'edd_gf_percent_' . $gateway, '' );

		// Sanitize the percentage
		$percent = preg_replace( '/[^\\d.]+/', '', $percent );

		$fee = 0;

		if ( ! empty( $percent ) ) {
			$percent_fee = ( $total * ( 1 + ( $percent / 100 ) ) ) - $total;
		}

		// Apply flat rate if set
		$flat = edd_get_option( 'edd_gf_flat_' . $gateway, '' );

		// Sanitize flat rate
		$flat = preg_replace( '/[^\\d.]+/', '', $flat );

		if ( ! empty( $percent_fee ) ) {
			$fee += $percent_fee;
		}

		if ( ! empty( $flat ) ) {
			$fee += $flat;
		}

		// Return total fee
		return $fee;
	}

	/**
	 * Recalculate gateway fees
	 *
	 * @since 1.0
	 * @access public
	 * @return void
	 */
	public function recalculate_gateway_fees() {
		if ( isset( $_POST['gateway'] ) ) {
			$this->gateway_fee( $_POST['gateway'] );

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