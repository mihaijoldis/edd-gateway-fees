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
		$fee = EDD_GF_Frontend::calculate_gateway_fee();
		EDD()->fees->add_fee( $fee, 'Gateway Fee', 'gateway_fee' );
	}
	function calculate_gateway_fee(){
		global $edd_settings;
		// get total
		$total = edd_get_cart_total();
		// which gateway
		$gateway = edd_get_chosen_gateway();
		// apply % if appl
			$percent = $edd_settings['edd_gf_percent_'.$gateway];
			// sanitize percent
			$percent = preg_replace('/[^\\d.]+/', '', $percent);
			if ( !empty($percent) ){
				$total = $total + $total * $percent;
			}
		// apply flat if appl
			$flat = $edd_settings['edd_gf_flat_'.$gateway];
			// sanitize flat
			$flat = preg_replace('/[^\\d.]+/', '', $flat);
			if ( !empty($percent) ){
				$total = $total + $flat;
			}
		// return total
		return $total;
		
	}
}
new EDD_GF_Frontend;