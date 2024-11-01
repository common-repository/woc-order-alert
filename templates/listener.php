<?php
/*
* @Author 		Pluginbazar
* Copyright: 	2015 Pluginbazar
*/

use Pluginbazar\Utils;

defined( 'ABSPATH' ) || exit;

$order_list_items_all = olistener()->get_order_list_items();
$order_list_items     = array_keys( $order_list_items_all );

?>

<div class="olistener" data-audio="<?php echo olistener_get_audio(); ?>">

    <div class="olistener-section olistener-checker">
        <div class="olistener-loading"><span class="dashicons dashicons-search"></span></div>
        <div class="olistener-actions">

            <div class="olistener-bubble-wrap olistener-action olistener-controller" data-classes="dashicons-controls-play dashicons-controls-pause" aria-label="<?php esc_html_e( 'Start or stop notifier', 'woc-order-alert' ); ?>">
                <div class="olistener-bubble"><?php esc_html_e( 'Click here to start the Notifier', 'woc-order-alert' ) ?></div>
                <span class="dashicons dashicons-controls-play"></span>
            </div>
            <div class="olistener-action olistener-volume active tt--top" data-classes="dashicons-controls-volumeon dashicons-controls-volumeoff" aria-label="<?php esc_html_e( 'Volume on or mute', 'woc-order-alert' ); ?>">
                <span class="dashicons dashicons-controls-volumeon"></span>
            </div>
            <div class="olistener-action olistener-reset tt--top" aria-label="<?php esc_html_e( 'Reset notifier', 'woc-order-alert' ); ?>">
                <span class="dashicons dashicons-image-rotate"></span>
            </div>
        </div>
    </div>

    <div class="olistener-section olistener-orders">
        <div class="olistener-row">
			<?php foreach ( $order_list_items as $item_key ) : ?>
				<?php printf( '<div class="olistener-row-item">%s</div>', olistener()->get_args_option( $item_key, '', $order_list_items_all ) ); ?>
			<?php endforeach; ?>
        </div>
    </div>

</div>
