<?php
/*
	Plugin Name: Order Notification for WooCommerce
	Plugin URI: https://pluginbazar.com/
	Description: Play sound as notification instantly on new order in your WooCommerce store.
	Version: 3.5.4
	Author: Pluginbazar
	Text Domain: woc-order-alert
	Author URI: https://pluginbazar.com/
	License: GPLv2 or later
	License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/

global $wpdb;

defined( 'ABSPATH' ) || exit;
defined( 'OLISTENER_PLUGIN_URL' ) || define( 'OLISTENER_PLUGIN_URL', WP_PLUGIN_URL . '/' . plugin_basename( dirname( __FILE__ ) ) . '/' );
defined( 'OLISTENER_PLUGIN_DIR' ) || define( 'OLISTENER_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
defined( 'OLISTENER_PLUGIN_FILE' ) || define( 'OLISTENER_PLUGIN_FILE', plugin_basename( __FILE__ ) );
defined( 'OLISTENER_PLUGIN_LINK' ) || define( 'OLISTENER_PLUGIN_LINK', 'https://pluginbazar.com/?add-to-cart=3805' );
defined( 'OLISTENER_TICKET_URL' ) || define( 'OLISTENER_TICKET_URL', 'https://pluginbazar.com/supports/order-notification-for-woocommerce/' );
defined( 'OLISTENER_DOCS_URL' ) || define( 'OLISTENER_DOCS_URL', 'https://docs.pluginbazar.com/plugin/order-notification-for-woocommerce/' );
defined( 'OLISTENER_CONTACT_URL' ) || define( 'OLISTENER_CONTACT_URL', 'https://pluginbazar.com/contact/' );
defined( 'OLISTENER_REVIEW_URL' ) || define( 'OLISTENER_REVIEW_URL', 'https://wordpress.org/support/plugin/woc-order-alert/reviews/?filter=5#new-post' );
defined( 'OLISTENER_DATA_TABLE' ) || define( 'OLISTENER_DATA_TABLE', $wpdb->prefix . 'woocommerce_order_listener' );
defined( 'OLISTENER_PLUGIN_VERSION' ) || define( 'OLISTENER_PLUGIN_VERSION', '3.5.4' );


if ( ! function_exists( 'olistener_is_plugin_active' ) ) {
	function olistener_is_plugin_active( $plugin ) {
		return ( function_exists( 'is_plugin_active' ) ? is_plugin_active( $plugin ) :
			(
				in_array( $plugin, apply_filters( 'active_plugins', ( array ) get_option( 'active_plugins', array() ) ) ) ||
				( is_multisite() && array_key_exists( $plugin, ( array ) get_site_option( 'active_sitewide_plugins', array() ) ) )
			)
		);
	}
}

if ( ! olistener_is_plugin_active( 'woocommerce/woocommerce.php' ) || ! olistener_is_plugin_active( 'woc-order-alert/woc-order-alert.php' ) ) {
	return;
}

if ( ! class_exists( 'Olistener_main' ) ) {
	/**
	 * Class Olistener_main
	 */
	class Olistener_main {

		protected static $_instance = null;

		protected static $_script_version = null;

		/**
		 * Olistener_main constructor.
		 */
		function __construct() {

			self::$_script_version = defined( 'WP_DEBUG' ) && WP_DEBUG ? current_time( 'U' ) : OLISTENER_PLUGIN_VERSION;

			$this->loading_scripts();
			$this->loading_functions_classes();

			add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
		}


		/**
		 * @return \Olistener_main
		 */
		public static function instance() {
			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self();
			}

			return self::$_instance;
		}


		/**
		 * Load Textdomain
		 */
		function load_textdomain() {
			load_plugin_textdomain( 'woc-order-alert', false, plugin_basename( dirname( __FILE__ ) ) . '/languages/' );
		}


		/**
		 * Loading Functions and Classes
		 */
		function loading_functions_classes() {

			require_once OLISTENER_PLUGIN_DIR . 'includes/class-hooks.php';
			require_once OLISTENER_PLUGIN_DIR . 'includes/class-functions.php';
			require_once OLISTENER_PLUGIN_DIR . 'includes/functions.php';
			require_once OLISTENER_PLUGIN_DIR . 'includes/class-plugin-settings.php';
		}


		/**
		 * Admin Scripts
		 */
		function admin_scripts() {

			wp_enqueue_script( 'olistener-admin', plugins_url( '/assets/admin/js/scripts.js', __FILE__ ), array( 'jquery', 'jquery-migrate' ), self::$_script_version );
			wp_localize_script( 'olistener-admin', 'olistener', array(
				'ajaxUrl'     => admin_url( 'admin-ajax.php' ),
				'confirmText' => esc_html__( 'Are you really want to reset the notifier?', 'woc-order-alert' ),
				'interval'    => olistener()->get_interval(),
			) );

			wp_enqueue_style( 'tool-tip', OLISTENER_PLUGIN_URL . 'assets/tool-tip.min.css' );
			wp_enqueue_style( 'olistener-admin', OLISTENER_PLUGIN_URL . 'assets/admin/css/style.css', self::$_script_version );
		}


		/**
		 * Loading Scripts
		 */
		function loading_scripts() {
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );
		}
	}
}

function wpdk_init_woc_order_alert() {

	if ( ! function_exists( 'get_plugins' ) ) {
		include_once ABSPATH . '/wp-admin/includes/plugin.php';
	}

	if ( ! class_exists( 'WPDK\Client' ) ) {
		require_once( plugin_dir_path( __FILE__ ) . 'includes/wpdk/classes/class-client.php' );
	}

	global $wooorderalert_wpdk;

	$wooorderalert_wpdk = new WPDK\Client( esc_html( 'Order Notification for WooCommerce' ), 'woc-order-alert', 36, __FILE__ );

	do_action( 'wpdk_init_woc_order_alert', $wooorderalert_wpdk );
}

/**
 * @global \WPDK\Client $wooorderalert_wpdk
 */

global $wooorderalert_wpdk;

wpdk_init_woc_order_alert();

Olistener_main::instance();

