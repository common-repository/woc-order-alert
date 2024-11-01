<?php
/**
 * Settings class
 *
 * @author Pluginbazar
 */

use WPDK\Utils;

defined( 'ABSPATH' ) || exit;

class OLISTENER_Settings {

	/**
	 * OLISTENER_Settings constructor.
	 */
	public function __construct() {

		global $wooorderalert_wpdk;

		// Generate settings page
		$settings_args = array(
			'framework_title'    => esc_html__( 'Order Notification for WooCommerce', 'woc-order-alert' ),
			'menu_title'         => esc_html__( 'Order Notifier', 'woc-order-alert' ),
			'menu_type'          => 'menu',
			'menu_slug'          => 'olistener',
			'menu_position'      => 30,
			'database'           => 'option',
			'theme'              => 'light',
			'menu_icon'          => 'dashicons-bell',
			'show_search'        => false,
			'show_reset_all'     => false,
			'show_reset_section' => false,
			'product_url'        => OLISTENER_PLUGIN_LINK,
			'product_version'    => $wooorderalert_wpdk->plugin_version,
			'quick_links'        => array(
				'supports' => array(
					'label' => esc_html__( 'Supports', 'woc-order-alert' ),
					'url'   => OLISTENER_TICKET_URL,
				),
				'docs'     => array(
					'label' => esc_html__( 'Documentations', 'woc-order-alert' ),
					'url'   => OLISTENER_DOCS_URL,
				),
			),
			'pro_url'            => OLISTENER_PLUGIN_LINK,
		);

		WPDK_Settings::createSettingsPage( $wooorderalert_wpdk->plugin_unique_id, $settings_args, $this->get_settings_pages() );
	}


	/**
	 * Return settings pages
	 *
	 * @return mixed|void
	 */
	function get_settings_pages() {

		$field_sections['listener'] = array(
			'title'    => esc_html__( 'Notifier', 'woc-order-alert' ),
			'sections' => array(
				array(
					'id'       => 'order_listener',
					'title'    => esc_html__( 'Order Notification', 'woc-order-alert' ),
					'external' => true,
				),
			),
		);

		$field_sections['settings'] = array(
			'title'    => esc_html__( 'Settings', 'woc-order-alert' ),
			'sections' => array(
				array(
					'title'  => esc_html__( 'General Settings', 'woc-order-alert' ),
					'fields' => array(
						array(
							'id'       => 'olistener_audio',
							'title'    => esc_html__( 'Custom audio', 'woc-order-alert' ),
							'subtitle' => esc_html__( 'Update your sound preference.', 'woc-order-alert' ),
							'type'     => 'media',
							'url'      => false,
						),
						array(
							'id'          => 'olistener_req_per_minute',
							'title'       => esc_html__( 'Requests per Minute', 'woc-order-alert' ),
							'subtitle'    => esc_html__( 'Request sends to server per minute. ', 'woc-order-alert' ),
							'desc'        => esc_html__( 'We heard some servers do not allow too many requests per minute, to handle this case, just decrease the checks count.', 'woc-order-alert' ),
							'type'        => 'spinner',
							'max'         => 60,
							'min'         => 10,
							'step'        => 5,
							'default'     => '30',
							'placeholder' => '30',
						),
					),
				),
				array(
					'title'  => esc_html__( 'Searching Rules on Items', 'woc-order-alert' ),
					'fields' => array(
						array(
							'id'           => 'olistener_enable_rules',
							'title'        => esc_html__( 'Enable These Rules', 'woc-order-alert' ),
							'subtitle'     => esc_html__( 'Customize your notifier.', 'woc-order-alert' ),
							'desc'         => esc_html__( 'Turning on this will open all the other fields.', 'woc-order-alert' ),
							'type'         => 'switcher',
							'default'      => false,
							'availability' => olistener()->is_pro() ? '' : 'pro',
						),
						array(
							'id'           => 'olistener_products_included',
							'title'        => esc_html__( 'Products Included', 'woc-order-alert' ),
							'desc'         => esc_html__( 'When any of these products are ordered only then alarm will start.', 'woc-order-alert' ),
							'type'         => 'select',
							'chosen'       => true,
							'multiple'     => true,
							'settings'     => array(
								'width' => '50%',
							),
							'options'      => 'posts',
							'query_args'   => array(
								'post_type' => 'product',
							),
							'availability' => olistener()->is_pro() ? '' : 'pro',
							'dependency'   => array( 'olistener_enable_rules', '==', true ),
						),
						array(
							'id'           => 'olistener_categories_included',
							'title'        => esc_html__( 'Product Categories Included', 'woc-order-alert' ),
							'desc'         => esc_html__( 'When any product from these categories are ordered only then alarm will start.', 'woc-order-alert' ),
							'type'         => 'select',
							'chosen'       => true,
							'multiple'     => true,
							'settings'     => array(
								'width' => '50%',
							),
							'options'      => 'categories',
							'query_args'   => array(
								'taxonomy' => 'product_cat',
							),
							'availability' => olistener()->is_pro() ? '' : 'pro',
							'dependency'   => array( 'olistener_enable_rules', '==', true ),
						),
						array(
							'id'            => 'olistener_tags_included',
							'title'         => esc_html__( 'Product Tags Included', 'woc-order-alert' ),
							'desc'          => esc_html__( 'When any product from these tags are ordered only then alarm will start.', 'woc-order-alert' ),
							'type'          => 'select',
							'chosen'        => true,
							'multiple'      => true,
							'settings'      => array(
								'width' => '50%',
							),
							'options'       => 'tags',
							'query_args'    => array(
								'taxonomy' => 'product_tag',
							),
							'empty_message' => 'Oops no product tags available!',
							'availability'  => olistener()->is_pro() ? '' : 'pro',
							'dependency'    => array( 'olistener_enable_rules', '==', true ),
						),
						array(
							'id'           => 'olistener_min_order_amount',
							'title'        => esc_html__( 'Minimum order amount', 'woc-order-alert' ),
							'desc'         => esc_html__( 'When this selected amount or more will be ordered only then alarm will start.', 'woc-order-alert' ),
							'type'         => 'number',
							'placeholder'  => esc_html__( '100', 'woc-order-alert' ),
							'availability' => olistener()->is_pro() ? '' : 'pro',
							'dependency'   => array( 'olistener_enable_rules', '==', true ),
						),
						array(
							'id'           => 'olistener_users',
							'title'        => esc_html__( 'Order from Users', 'woc-order-alert' ),
							'desc'         => esc_html__( 'When selected users place an order, then the alarm will start.', 'woc-order-alert' ),
							'type'         => 'select',
							'options'      => 'users',
							'multiple'     => true,
							'chosen'       => true,
							'settings'     => array(
								'width' => '50%',
							),
							'placeholder'  => esc_html__( 'Select Users', 'woc-order-alert' ),
							'availability' => olistener()->is_pro() ? '' : 'pro',
							'dependency'   => array( 'olistener_enable_rules', '==', true ),
						),
						array(
							'id'           => 'olistener_user_roles',
							'title'        => esc_html__( 'Order from User Roles', 'woc-order-alert' ),
							'desc'         => esc_html__( 'When any user from these selected roles place an order, only then alarm will start.', 'woc-order-alert' ),
							'type'         => 'select',
							'options'      => 'roles',
							'multiple'     => true,
							'chosen'       => true,
							'settings'     => array(
								'width' => '50%',
							),
							'placeholder'  => esc_html__( 'Select User Roles', 'woc-order-alert' ),
							'availability' => olistener()->is_pro() ? '' : 'pro',
							'dependency'   => array( 'olistener_enable_rules', '==', true ),
						),
						array(
							'id'           => 'olistener_rules_relation',
							'title'        => esc_html__( 'Relation', 'woc-order-alert' ),
							'type'         => 'checkbox',
							'options'      => olistener()->get_rules_relations(),
							'desc'         => esc_html__( 'Please select the conditions you wish to check for new order checking.', 'woc-order-alert' ) . '<br>' .
							                  __( '<strong>Multi conditions selected</strong> - System will notify you only if all the checked conditions are matched.' ) . '<br>' .
							                  __( '<strong>Single condition selected</strong> - System will notify you only when the selected condition is matched.' ) . '<br>' .
							                  __( '<strong>No condition selected</strong> - System will notify you if any of the condition is matched.' ),
							'availability' => olistener()->is_pro() ? '' : 'pro',
							'dependency'   => array( 'olistener_enable_rules', '==', true ),
						),
					),
				),
			),
		);

		return apply_filters( 'olistener_filters_setting_pages', $field_sections );
	}

}

new OLISTENER_Settings();

