<?php
if ( !defined( 'ABSPATH' ) ) {
	exit;
}

class EDD_GF_Frontend {
	public function __construct() {
		add_action( 'init', array($this,'gateway_fee' ));
		add_action( 'wp_ajax_edd_calculate_gateway_fees', array( $this, 'recalculate_gateway_fees' ) );
		add_action( 'wp_ajax_nopriv_edd_calculate_gateway_fees', array( $this, 'recalculate_gateway_fees' ) );
	}

	function gateway_fee( $gateway = false ) {

		EDD()->fees->remove_fee('gateway_fee');

		if ( edd_get_cart_total(); == 0 ) {
			return;
		}
		
		// which gateway
		if( ! $gateway ) {
			$gateway = isset( $_REQUEST['payment-mode'] ) ? $_REQUEST['payment-mode'] : false;
			
			if( ! $gateway ) {
				$gateway = edd_get_default_gateway();
			}
		}

		$gateways_array = edd_get_payment_gateways();

		$fee = $this->calculate_gateway_fee( $gateway );
		$fee = apply_filters( 'edd_gf_fee_total_before_add_fee', $fee );

		$label = edd_get_option( 'edd_gf_label_' . $gateway, edd_get_gateway_checkout_label( $gateway ) . ' ' .__( 'fee', 'edd_gf') );
		
		if ($fee !== '0' && $fee !== '0.0' && $fee !== '0.00'){
			EDD()->fees->add_fee( $fee, $label, 'gateway_fee' );
		}
	}


	
	function calculate_gateway_fee( $gateway = false ){

		global $edd_options;
		
		// get total
		$total = edd_get_cart_total();
		
		// which gateway
		if( ! $gateway ) {
			$gateway = isset( $_REQUEST['payment-mode'] ) ? $_REQUEST['payment-mode'] : false;
			
			if( ! $gateway ) {
				$gateway = edd_get_default_gateway();
			}
		}

		// apply % if appl
		$percent =  edd_get_option('edd_gf_percent_'.$gateway,'');
		
		// sanitize percent
		$percent = preg_replace('/[^\\d.]+/', '', $percent);
		
		$fee = 0;
		
		// apply flat if appl
		$flat = edd_get_option('edd_gf_flat_'.$gateway,'');
		
		// sanitize flat
		$flat = preg_replace('/[^\\d.]+/', '', $flat);
		
		if ( ! empty( $flat ) && ! empty($percent) ){
			// paypal style
			$percent = $percent/100;
			$fee     = ($total + $flat) / (1 - $percent);
			$fee     = round($fee, 2);
			$fee     = $fee - $total;
		}
		else if ( ! empty( $flat ) ){
			// simple add flat fee
			$fee     = $flat;
		}
		else if ( ! empty($percent) ){
			// simple add percentage fee
			$percent = $percent/100;
			$fee     = ($total) / (1 - $percent);
			$fee     = round($fee, 2);
			$fee     = $fee - $total;
		}
		else{
			// no fee to apply
			
		}
		
		// return total
		return $fee;
		
	}
	
	function recalculate_gateway_fees() {
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
