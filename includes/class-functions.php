<?php
/**
 * Class Functions
 *
 * @author Pluginbazar
 */

use WPDK\Utils;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Olistener_functions' ) ) {
	/**
	 * Class Olistener_functions
	 */
	class Olistener_functions {

		/**
		 * Return interval in milliseconds
		 *
		 * @return mixed|void
		 */
		function get_interval() {

			return apply_filters( 'olistener_filters_interval', (int) round( 60 / (int) Utils::get_option( 'olistener_req_per_minute', 30 ) * 1000 ) );
		}

		/**
		 * Check if this plugin is pro version or not
		 *
		 * @return bool
		 */
		function is_pro() {
			return apply_filters( 'olistener_filters_is_pro', class_exists( 'OlistenerPro' ) );
		}

		/**
		 * Return rules relations
		 *
		 * @return array
		 */
		function get_rules_relations() {
			return array(
				'products'   => __( 'Products', 'woc-order-alert' ),
				'categories' => __( 'Product Categories', 'woc-order-alert' ),
				'tags'       => __( 'Product Tags', 'woc-order-alert' ),
				'amount'     => __( 'Order Minimum Amount', 'woc-order-alert' ),
				'users'      => __( 'Users', 'woc-order-alert' ),
				'roles'      => __( 'User Roles	', 'woc-order-alert' ),
			);
		}

		/**
		 * Return order list header items
		 *
		 * @return mixed|void
		 */
		function get_order_list_items() {
			return apply_filters( 'olistener_filters_order_list_items', array(
				'order_id'     => esc_html__( 'Order ID', 'woc-order-alert' ),
				'status'       => esc_html__( 'Status', 'woc-order-alert' ),
				'customer'     => esc_html__( 'Customer', 'woc-order-alert' ),
				'order_amount' => esc_html__( 'Total', 'woc-order-alert' ),
				'actions'      => esc_html__( 'Actions', 'woc-order-alert' ),
			) );
		}


		/**
		 * Print notice to the admin bar
		 *
		 * @param string $message
		 * @param bool $is_success
		 * @param bool $is_dismissible
		 */
		function print_notice( $message = '', $is_success = true, $is_dismissible = true ) {

			if ( empty ( $message ) ) {
				return;
			}

			if ( is_bool( $is_success ) ) {
				$is_success = $is_success ? 'success' : 'error';
			}

			printf( '<div class="notice notice-%s %s"><p>%s</p></div>', $is_success, $is_dismissible ? 'is-dismissible' : '', $message );
		}

		/**
		 * Return option value
		 *
		 * @param string $option_key
		 * @param string $default_val
		 *
		 * @return mixed|string|void
		 */
		function get_option( $option_key = '', $default_val = '' ) {

			if ( empty( $option_key ) ) {
				return '';
			}

			$option_val = get_option( $option_key, $default_val );
			$option_val = empty( $option_val ) ? $default_val : $option_val;

			return apply_filters( 'woc_filters_option_' . $option_key, $option_val );
		}

		/**
		 * Return Post Meta Value
		 *
		 * @param bool $meta_key
		 * @param bool $post_id
		 * @param string $default
		 *
		 * @return mixed|string|void
		 */
		function get_meta( $meta_key = false, $post_id = false, $default = '' ) {

			if ( ! $meta_key ) {
				return '';
			}

			$post_id    = ! $post_id ? get_the_ID() : $post_id;
			$meta_value = get_post_meta( $post_id, $meta_key, true );
			$meta_value = empty( $meta_value ) ? $default : $meta_value;

			return apply_filters( 'woc_filters_get_meta', $meta_value, $meta_key, $post_id, $default );
		}

		/**
		 * Return Arguments Value
		 *
		 * @param string $key
		 * @param string $default
		 * @param array $args
		 *
		 * @return mixed|string
		 */
		function get_args_option( $key = '', $default = '', $args = array() ) {

			global $wooopenclose_args;

			$args    = empty( $args ) ? $wooopenclose_args : $args;
			$default = empty( $default ) ? '' : $default;
			$key     = empty( $key ) ? '' : $key;

			if ( isset( $args[ $key ] ) && ! empty( $args[ $key ] ) ) {
				return $args[ $key ];
			}

			return $default;
		}
	}
}

global $olistener;
$olistener = new Olistener_functions();