<?php
/**
 * All Functions
 *
 * @author Pluginbazar
 * @copyright 2020 Pluginbazar
 */

use WPDK\Utils;


if ( ! function_exists( 'olistener' ) ) {
	function olistener() {
		global $olistener;

		if ( empty( $olistener ) ) {
			$olistener = new Olistener_functions();
		}

		return $olistener;
	}
}


if ( ! function_exists( 'olistener_create_table' ) ) {
	/**
	 * Create table if not exists
	 */
	function olistener_create_table() {

		$sql = "CREATE TABLE " . OLISTENER_DATA_TABLE . " (
			id int(100) NOT NULL AUTO_INCREMENT,
			order_id int(100) NOT NULL UNIQUE,
			billing_name VARCHAR(255) NOT NULL,
			order_total int(100) NOT NULL,
			read_status VARCHAR(50) NOT NULL,
			datetime DATETIME NOT NULL,
			UNIQUE KEY id (id)
		);";

		if ( ! function_exists( 'maybe_create_table' ) ) {
			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		}

		maybe_create_table( OLISTENER_DATA_TABLE, $sql );
	}
}


if ( ! function_exists( 'olistener_create_webhooks' ) ) {
	/**
	 * Create webhooks if not exists
	 */
	function olistener_create_webhooks() {

		// Webhook on Create
		try {
			$webhook_id  = olistener()->get_option( 'olistener_webhook_id' );
			$def_webhook = wc_get_webhook( $webhook_id );

			// Set status to active if the webhook is not activated by default
			if ( $def_webhook instanceof WC_Webhook && $def_webhook->get_status() != 'active' ) {
				$def_webhook->set_status( 'active' );
				$def_webhook->save();

				return;
			}

			if ( ! $def_webhook instanceof WC_Webhook ) {
				$webhook = new WC_Webhook();
				$webhook->set_name( 'Order Notification - On Create' );
				$webhook->set_user_id( get_current_user_id() );
				$webhook->set_topic( 'order.created' );
				$webhook->set_delivery_url( site_url( 'wp-json/olistener/new' ) );
				$webhook->set_status( 'active' );
				$new_webhook_id = $webhook->save();

				if ( $new_webhook_id ) {
					update_option( 'olistener_webhook_id', $new_webhook_id );
				}
			}
		} catch ( Exception $e ) {
		}
	}
}


if ( ! function_exists( 'olistener_get_audio' ) ) {
	/**
	 * Get audio URL
	 *
	 * @return string
	 */
	function olistener_get_audio() {
		$audio     = Utils::get_option( 'olistener_audio' );
		$audio_src = isset( $audio['url'] ) ? $audio['url'] : '';

		if ( empty( $audio_src ) ) {
			$audio_src = OLISTENER_PLUGIN_URL . 'assets/alarm.mp3';
		}

		return apply_filters( 'olistener_audio_url', $audio_src );
	}
}


if ( ! function_exists( 'olistener_popup_html' ) ) {
	/**
	 * @param $all_orders
	 *
	 * @return string
	 */
	function olistener_popup_html( $all_orders ) {

		$order_ids       = array();
		$order_customers = array();

		foreach ( $all_orders as $order_item ) {

			$order = wc_get_order( $order_item->order_id );

			if ( ! $order instanceof WC_Order ) {
				continue;
			}

			$order_id          = $order->get_id();
			$order_customer_id = $order->get_customer_id();
			$order_ids[]       = sprintf( '<a href="%s">#%s</a>', admin_url( 'post.php?post=' . $order_id . '&action=edit' ), $order_id );
			$order_customers[] = sprintf( '<a href="%s">#%s</a>', admin_url( 'edit.php?post_type=shop_order&_customer_user=' . $order_customer_id ), $order->get_billing_first_name() );
		}

		return sprintf( esc_html__( 'Congratulations! You have received order(%s) from %s' ), implode( ', ', $order_ids ), implode( ', ', $order_customers ) );
	}
}
