<?php
if ( !defined( 'ABSPATH' ) ) {
	exit;
}
class EDD_GF_Setup {
	public function __construct() {
		add_action( 'admin_init', array(
			 $this,
			'is_wp_36_and_edd_activated' 
		), 1 );
		add_action( 'plugins_loaded', array(
			 $this,
			'load_textdomain' 
		) );
		add_action( 'wp_head', array(
			 $this,
			'gf_version' 
		) );
		add_action( 'wp_enqueue_scripts', array(
			 $this,
			'enqueue_scripts' 
		) );
	}
	public function is_wp_36_and_edd_activated() {
		global $wp_version;
		if ( version_compare( $wp_version, '3.7', '< ' ) ) {
			if ( is_plugin_active( edd_opg()->basename ) ) {
				deactivate_plugins( edd_opg()->basename );
				unset( $_GET[ 'activate' ] );
				add_action( 'admin_notices', array(
					 $this,
					'wp_notice' 
				) );
			}
		} else if ( !class_exists( 'Easy_Digital_Downloads' ) || ( version_compare( EDD_VERSION, '1.7' ) < 0 ) ) {
			if ( is_plugin_active( edd_opg()->basename ) ) {
				deactivate_plugins( edd_opg()->basename );
				unset( $_GET[ 'activate' ] );
				add_action( 'admin_notices', array(
					 $this,
					'edd_notice' 
				) );
			}
		}
	}
	public function edd_notice() {
?>
	<div class="updated">
		<p><?php
		printf( __( '<strong>Notice:</strong> Easy Digital Downloads Gateway Fees requires Easy Digital Downloads 1.7 or higher in order to function properly.', 'edd_fes' ) );
?>
		</p>
	</div>
	<?php
	}
	public function wp_notice() {
?>
	<div class="updated">
		<p><?php
		printf( __( '<strong>Notice:</strong> Easy Digital Downloads Gateway Fees requires WordPress 3.7 or higher in order to function properly.', 'edd_fes' ) );
?>
		</p>
	</div>
	<?php
	}
	public function load_textdomain() {
		load_plugin_textdomain( 'edd-gf', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}
	public function gf_version() {
		// Newline on both sides to avoid being in a blob
		echo '<meta name="generator" content="EDD GF v' . edd_gf_plugin_version . '" />' . "\n";
	}
	public function enqueue_scripts() {
		if ( edd_is_checkout() ) {
			wp_enqueue_script( 'jquery' );
			wp_enqueue_script( 'gateway-fees', edd_gf_plugin_url . 'assets/js/gateway-fees.js', array(
				 'jquery' 
			) );
		}
	}
}