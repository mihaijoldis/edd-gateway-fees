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
				$fee = ($total * (1+($percent/100))) - $total;
			}
		// apply flat if appl
			$flat = edd_get_option('edd_gf_flat_'.$gateway,'');
			// sanitize flat
			$flat = preg_replace('/[^\\d.]+/', '', $flat);
			if ( !empty($percent) ){
				$fee = $fee + $flat;
			}
		// return total
		return $fee;
		
	}
}
new EDD_GF_Frontend;