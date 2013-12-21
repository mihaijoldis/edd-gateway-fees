<?php
/**
 * Settings Class
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
 * EDD_GF_Settings Class
 *
 * @package EDD_GF
 * @since 1.0
 * @version 1.0
 * @author Chris Christoff
 */
class EDD_GF_Settings {
	/**
	 * Constructor Function
	 *
	 * @since 1.0
	 * @access protected
	 */
	public function __construct() {
		add_filter('edd_settings_gateways', array( $this, 'edd_gf_add_settings') );
	}

	/**
	 * Add all the settings
	 *
	 * @since 1.0
	 * @access public
	 * @param array $settings Pre-existing settings
	 * @return array Merged array with the new settings
	 */
	public function edd_gf_add_settings( $settings ) {
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

		foreach ( $gateways_array as $key => $val ) {
			$gateways[] = array(
				'id'   => 'edd_gf_percent_' . $key,
				'name' => __( 'Percent fee for ', 'edd-gf' ) . $val['admin_label'],
				'desc' => __( 'Leave blank for no % fee.', 'edd-gf' ),
				'type' => 'text',
				'std'  => ''
			);
			$gateways[] = array(
				'id'   => 'edd_gf_flat_'.$key,
				'name' => __( 'Flat fee for ', 'edd-gf' ) . $val['admin_label'],
				'desc' => __( 'Leave blank for no flat fee.', 'edd-gf' ),
				'type' => 'text',
				'std'  => ''
			);
		}

		$edd_gf_settings = array_merge( $edd_gf_settings, $gateways );

		return array_merge( $settings, $edd_gf_settings );
	}
}

new EDD_GF_Settings;