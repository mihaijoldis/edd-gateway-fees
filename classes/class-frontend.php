<?php
if ( !defined( 'ABSPATH' ) ) {
	exit;
}

class EDD_GF_Frontend {
	public function __construct() {
		add_action( 'init', array($this,'gateway_fee' ));
	}
	function gateway_fee() {
		EDD()->fees->remove_fee('gateway_fee');
		$gateways_array = edd_get_payment_gateways();
		$gateway = edd_get_chosen_gateway();
		$fee = EDD_GF_Frontend::calculate_gateway_fee();
		EDD()->fees->add_fee( $fee, $gateways_array[$gateway]['checkout_label'].__( 'fee', 'edd_opg'), 'gateway_fee' );
	}
	function calculate_gateway_fee(){
		global $edd_options;
		// get total
		$total = edd_get_cart_total();
		
		// which gateway
		$gateway = edd_get_chosen_gateway();
		
		// apply % if appl
		$percent =  edd_get_option('edd_gf_percent_'.$gateway,'');
		
		// sanitize percent
		$percent = preg_replace('/[^\\d.]+/', '', $percent);
		
		$fee = 0;
		
		if ( !empty($percent) ){
			$percent_fee = ($total * (1+($percent/100))) - $total;
		}
		
		// apply flat if appl
		$flat = edd_get_option('edd_gf_flat_'.$gateway,'');
		
		// sanitize flat
		$flat = preg_replace('/[^\\d.]+/', '', $flat);
		
		if ( !empty( $percent_fee ) ) {
			$fee += $percent_fee;
		}

		if ( ! empty( $flat ) ) {
			$fee += $flat;
		}
		
		// return total
		return $fee;
		
	}
}
new EDD_GF_Frontend;