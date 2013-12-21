<?php
/**
 * Setup Class
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
 * EDD_GF_Setup Class
 *
 * @package EDD_GF
 * @since 1.0
 * @version 1.0
 * @author Chris Christoff
 */
class EDD_GF_Setup {
	/**
	 * Constructor Function
	 *
	 * @since 1.0
	 * @access protected
	 */
	public function __construct() {
		add_action( 'admin_init',         array( $this, 'is_wp_36_and_edd_activated' ), 1 );
		add_action( 'plugins_loaded',     array( $this, 'load_textdomain'            )    );
		add_action( 'wp_head',            array( $this, 'gf_version'                 )    );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts'            )    );
	}

	/**
	 * Check if user is running at least WordPress 3.6 and at least
	 * Easy Digital Downloads 1.7
	 *
	 * @since 1.0
	 * @access public
	 * @global int $wp_version
	 * @return void
	 */
	public function is_wp_36_and_edd_activated() {
		global $wp_version;

		if ( version_compare( $wp_version, '3.7', '< ' ) ) {
			if ( is_plugin_active( edd_gf()->basename ) ) {
				deactivate_plugins( edd_gf()->basename );
				unset( $_GET[ 'activate' ] );
				add_action( 'admin_notices', array( $this, 'wp_notice' ) );
			}
		} else if ( ! class_exists( 'Easy_Digital_Downloads' ) || ( version_compare( EDD_VERSION, '1.7' ) < 0 ) ) {
			if ( is_plugin_active( edd_gf()->basename ) ) {
				deactivate_plugins( edd_gf()->basename );
				unset( $_GET[ 'activate' ] );
				add_action( 'admin_notices', array( $this, 'edd_notice' ) );
			}
		}
	}

	/**
	 * Display notice if Easy Digital Downloads isn't active
	 *
	 * @since 1.0
	 * @access public
	 * @return void
	 */
	public function edd_notice() {
		?>
		<div class="updated">
			<p><?php printf( __( '<strong>Notice:</strong> Easy Digital Downloads Gateway Fees requires Easy Digital Downloads 1.7 or higher in order to function properly.', 'edd-gf' ) ); ?></p>
		</div>
		<?php
	}

	/**
	 * Display notice if WordPress isn't active
	 * @return [type] [description]
	 */
	public function wp_notice() {
		?>
		<div class="updated">
			<p><?php printf( __( '<strong>Notice:</strong> Easy Digital Downloads Gateway Fees requires WordPress 3.7 or higher in order to function properly.', 'edd-gf' ) ); ?></p>
		</div>
		<?php
	}

	/**
	 * Load Textdomain
	 *
	 * @since 1.0
	 * @access public
	 * @uses load_plugin_textdomain()
	 */
	public function load_textdomain() {
		load_plugin_textdomain( 'edd-gf', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}

	/**
	 * Add Gateway Fees version number in the header
	 *
	 * @since 1.0
	 * @access public
	 * @return void
	 */
	public function gf_version() {
		// Newline on both sides to avoid being in a blob
		echo '<meta name="generator" content="EDD Gateway Fees v' . edd_gf_plugin_version . '" />' . "\n";
	}

	/**
	 * Load Scripts
	 *
	 * @since 1.0
	 * @access public
	 */
	public function enqueue_scripts() {
		if ( edd_is_checkout() ) {
			wp_enqueue_script( 'jquery' );
			wp_enqueue_script( 'gateway-fees', edd_gf_plugin_url . 'assets/js/gateway-fees.js', array( 'jquery' ), edd_gf_plugin_version );
		}
	}
}