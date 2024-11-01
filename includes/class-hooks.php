<?php
/**
 * Class Hooks
 *
 * @author Pluginbazar
 */

use WPDK\Utils;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Olistener_hooks' ) ) {
	/**
	 * Class Olistener_hooks
	 */
	class Olistener_hooks {
		/**
		 * Olistener_hooks constructor.
		 */
		function __construct() {

			add_action( 'init', array( $this, 'register_everything' ) );
			add_action( 'admin_init', array( $this, 'add_capabilities_to_shop_manager' ) );
			add_action( 'WPDK_Settings/section/order_listener', array( $this, 'render_listener' ) );
			add_action( 'rest_api_init', array( $this, 'register_endpoints' ) );
			add_action( 'wp_ajax_olistener', array( $this, 'olistener_listening' ) );
			add_action( 'admin_bar_menu', array( $this, 'handle_admin_bar_menu' ), 9999, 1 );

			add_filter( 'woocommerce_webhook_deliver_async', '__return_false' );
			add_filter( 'woocommerce_rest_check_permissions', '__return_true' );
			add_filter( 'plugin_row_meta', array( $this, 'add_plugin_meta' ), 10, 2 );
			add_filter( 'plugin_action_links_' . OLISTENER_PLUGIN_FILE, array( $this, 'add_plugin_actions' ), 10, 2 );

			add_action( 'admin_footer', array( $this, 'add_notification_popup_markup' ) );
		}


		/**
		 * Add capabilities to shop manager for Order Notifier
		 */
		function add_capabilities_to_shop_manager() {

			if ( current_user_can( 'manage_woocommerce' ) ) {
				$shop_manager = get_role( 'shop_manager' );
				$shop_manager->add_cap( 'manage_options' );
			}

		}


		/**
		 * Add popup markup in order notification page
		 */
		function add_notification_popup_markup() {

			global $current_screen;

			if ( 'toplevel_page_olistener' == $current_screen->id ) {
				?>
                <div class="olistener-popup">
                    <div class="olistener-popup-box">
                        <div class="popup-icon">
                            <span class="dashicons dashicons-bell"></span>
                        </div>
                        <div class="popup-content"></div>
                        <div class="popup-actions">
                            <div class="popup-action popup-action-skip"><?php esc_html_e( 'Skip', 'woc-order-alert' ) ?></div>
                            <div class="popup-action popup-action-ack"><?php esc_html_e( 'Acknowledge', 'woc-order-alert' ) ?></div>
                        </div>
                    </div>
                </div>
				<?php
			}
		}


		/**
		 * Add custom links to Plugin actions
		 *
		 * @param $links
		 *
		 * @return array
		 */
		function add_plugin_actions( $links ) {

			$action_links = array_merge( array(
				'notifier' => sprintf( '<a href="%s">%s</a>', admin_url( 'admin.php?page=olistener' ), esc_html__( 'Get Notification', 'woc-order-alert' ) ),
				'settings' => sprintf( '<a href="%s">%s</a>', admin_url( 'admin.php?page=olistener#tab=settings' ), esc_html__( 'Settings', 'woc-order-alert' ) ),
			), $links );

//			global $wooorderalert_wpdk;
//
//			if ( ! $wooorderalert_wpdk->license()->is_activate_pro() ) {
//				$action_links['go-pro'] = sprintf( '<a target="_blank" class="plugin-meta-buy" href="%s">%s</a>', esc_url( OLISTENER_PLUGIN_LINK ), esc_html__( 'Go Pro', 'woc-order-alert' ) );
//			}

			return $action_links;
		}


		/**
		 * Add custom links to plugin meta
		 *
		 * @param $links
		 * @param $file
		 *
		 * @return array
		 */
		function add_plugin_meta( $links, $file ) {

			if ( OLISTENER_PLUGIN_FILE === $file ) {

				$row_meta = array(
					'documentation' => sprintf( '<a class="olistener-doc" target="_blank" href="%s">%s</a>', esc_url( OLISTENER_DOCS_URL ), esc_html__( 'Documentation', 'woc-order-alert' ) ),
					'support'       => sprintf( '<a class="olistener-support" target="_blank" href="%s">%s</a>', esc_url( OLISTENER_TICKET_URL ), esc_html__( 'Support ticket', 'woc-order-alert' ) ),
				);

				return array_merge( $links, $row_meta );
			}

			return (array) $links;
		}


		/**
		 * Add nodes to WP Admin Bar
		 *
		 * @param WP_Admin_Bar $wp_admin_bar
		 */
		function handle_admin_bar_menu( \WP_Admin_Bar $wp_admin_bar ) {
			if ( current_user_can( 'manage_woocommerce' ) ) {
				$wp_admin_bar->add_node(
					array(
						'id'     => 'olistener',
						'title'  => esc_html__( 'Order Notifier', 'woc-order-alert' ),
						'href'   => admin_url( 'admin.php?page=olistener' ),
						'parent' => false,
					)
				);

				?>
                <style>
                    li#wp-admin-bar-olistener > a,
                    li#wp-admin-bar-olistener > a:hover,
                    li#wp-admin-bar-olistener > a:focus,
                    li#wp-admin-bar-olistener > a:active {
                        color: #fff !important;
                        background: #e61f63 !important;
                        outline: none;
                        box-shadow: none;
                        border: none;
                    }
                </style>
				<?php
			}
		}

		/**
		 * Search unread orders and send to ajax handler
		 */
		function olistener_listening() {

			global $wpdb;

			$all_orders           = $wpdb->get_results(
				$wpdb->prepare( "SELECT * FROM {$wpdb->prefix}woocommerce_order_listener WHERE read_status = %s", 'unread' )
			);
			$all_orders           = ! is_array( $all_orders ) ? array() : $all_orders;
			$order_list_items_all = olistener()->get_order_list_items();
			$order_list_items     = array_keys( $order_list_items_all );
			$trashed_items        = 0;
			ob_start();
			foreach ( $all_orders as $order_item ) {

				$order = wc_get_order( $order_item->order_id );

				if ( ! $order instanceof WC_Order ) {
					$trashed_items ++;
					$wpdb->delete( OLISTENER_DATA_TABLE, array( 'order_id' => $order_item->order_id ) );
					continue;
				}

				$customer    = get_user_by( 'ID', $order->get_customer_id() );
				$item_data   = array();
				$item_data[] = sprintf( '<div class="olistener-row-item"><a href="%s" target="_blank">#%s</a></div>', admin_url( "post.php?post={$order_item->order_id}&action=edit" ), $order_item->order_id );

				if ( in_array( 'status', $order_list_items ) ) {
					$item_data[] = sprintf( '<div class="olistener-row-item"><span class="order-status %1$s">%1$s</span></div>', $order->get_status() );
				}

				if ( in_array( 'customer', $order_list_items ) ) {
					$item_data[] = sprintf( '<div class="olistener-row-item"><a href="%s" target="_blank">%s</a></div>', admin_url( "edit.php?post_type=shop_order&_customer_user={$customer->ID}" ), $customer->display_name );
				}

				if ( in_array( 'order_amount', $order_list_items ) ) {
					$item_data[] = sprintf( '<div class="olistener-row-item"><span>%s</span></div>', wc_price( $order_item->order_total ) );
				}

				if ( in_array( 'actions', $order_list_items ) ) {
					$item_data[] = sprintf( '<div class="olistener-row-item"><div class="order-action mark-read tt--top" aria-label="%s"><span class="dashicons dashicons-visibility"></span></div></div>', esc_html__( 'Mark as Read', 'woc-order-alert' ) );
				}

				printf( '<div class="olistener-row order-%s">%s</div>', $order->get_id(), implode( '', $item_data ) );

				$wpdb->update( OLISTENER_DATA_TABLE, array( 'read_status' => 'read' ), array( 'id' => $order_item->id ) );
			}

			wp_send_json_success(
				array(
					'count'     => count( $all_orders ) - $trashed_items,
					'html'      => ob_get_clean(),
					'htmlPopup' => olistener_popup_html( $all_orders ),
					'audio'     => olistener_get_audio(),
				)
			);
		}


		/**
		 * Handle payload for new order
		 *
		 * @param WP_REST_Request $data
		 */
		function handle_payload( WP_REST_Request $data ) {

			global $wpdb;

			$json_params = $data->get_json_params();

			$json_params   = is_array( $json_params ) ? $json_params : array();
			$billing       = olistener()->get_args_option( 'billing', array(), $json_params );
			$billing_name  = sprintf( '%s %s', olistener()->get_args_option( 'first_name', '', $billing ), olistener()->get_args_option( 'last_name', '', $billing ) );
			$order_id      = sanitize_text_field( olistener()->get_args_option( 'id', '', $json_params ) );
			$should_notify = true;
			if ( ! empty( $order_id ) && apply_filters( 'olistener_filters_should_notify', $should_notify, $order_id, $json_params ) ) {

				$order_total  = sanitize_text_field( olistener()->get_args_option( 'total', '', $json_params ) );
				$all_orders   = $wpdb->get_results(
					$wpdb->prepare( "SELECT * FROM {$wpdb->prefix}woocommerce_order_listener WHERE order_id = %d", $order_id )
				);
				$all_orders   = ! is_array( $all_orders ) ? array() : $all_orders;
				$latest_order = end( $all_orders );
				$order_args   = array(
					'order_id'     => $order_id,
					'billing_name' => sanitize_text_field( $billing_name ),
					'order_total'  => $order_total,
					'read_status'  => 'unread',
					'datetime'     => current_time( 'mysql' ),
				);

				if ( $latest_order ) {
					if ( current_time( 'U' ) - strtotime( $latest_order->datetime ) > 10 ) {
						$wpdb->insert( OLISTENER_DATA_TABLE, $order_args );
					}
				} else {
					$wpdb->insert( OLISTENER_DATA_TABLE, $order_args );
				}
			}
		}


		/**
		 * Register endpoints
		 */
		function register_endpoints() {
			register_rest_route( 'olistener', '/new', array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'handle_payload' ),
				'permission_callback' => '__return_true',
			) );
		}


		/**
		 * Render
		 */
		function render_listener() {
			include OLISTENER_PLUGIN_DIR . 'templates/listener.php';
		}


		/**
		 * Register Post Types and Settings
		 */
		function register_everything() {

			/**
			 * Create table if not exists
			 */
			olistener_create_table();

			if ( function_exists( 'WC' ) ) {
				/**
				 * Create webhook
				 */
				olistener_create_webhooks();
			}
		}
	}
}

new Olistener_hooks();
