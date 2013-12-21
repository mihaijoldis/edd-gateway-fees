<?php
if ( !defined( 'ABSPATH' ) ) {
	exit;
}

class EDD_GF_Settings {
	public function __construct() {
		add_filter('edd_settings_extensions', array( $this, 'edd_gf_add_settings') );
	}
	
	function edd_gf_add_settings($settings) {
	$edd_gf_settings = array(
		array(
			'id' => 'edd_gf_settings',
			'name' => __('Gateway Fees', 'edd_gf'),
			'desc' => __('Configure your fees for gateways', 'edd_gf'),
			'type' => 'header'
		),
	);
	$gateways_array = edd_get_payment_gateways();
	$gateways = array();
	foreach ($gateways_array as $key => $val){
		$gateways [] = array(
			'id' => 'edd_gf_percent_'.$key,
			'name' => __( 'Percent fee for ', 'edd_gf' ).$val['admin_label'],
			'desc' => __( 'Leave blank for no % fee.', 'edd_gf' ),
			'type' => 'text',
			'std'  => ''
		);
		$gateways [] = array(
			'id' => 'edd_gf_flat_'.$key,
			'name' => __( 'Flat fee for ', 'edd_gf' ).$val['admin_label'],
			'desc' => __( 'Leave blank for no flat fee.', 'edd_gf' ),
			'type' => 'text',
			'std'  => ''
		);
	}

	$edd_gf_settings = array_merge( $edd_gf_settings, $gateways );
	
	return array_merge($settings, $edd_gf_settings);
}
}
new EDD_GF_Settings;