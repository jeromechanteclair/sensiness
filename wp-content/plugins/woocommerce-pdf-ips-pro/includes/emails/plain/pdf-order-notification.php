<?php
use WPO\WC\PDF_Invoices\Compatibility\Order as WCX_Order;

/**
 * Order Notification email (plain text)
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

echo $email_heading . "\n\n";

echo "****************************************************\n\n";

// Some of the default actions are disabled in this email because they may result in unexpected output.
// For example when credit notes are issued for unpaid orders/invoices, woocommerce will still display
// payment instructions!
// do_action( 'woocommerce_email_before_order_table', $order, $sent_to_admin, $plain_text );

echo sprintf( __( 'Order number: %s', 'woocommerce'), $order->get_order_number() ) . "\n";
$order_date = WCX_Order::get_prop( $order, 'date_created' );
echo sprintf( __( 'Order date: %s', 'woocommerce'), $order_date->date_i18n( wc_date_format() ) ) . "\n";

echo "\n";

echo $email_body;

echo "\n";

do_action( 'woocommerce_email_order_meta', $order, $sent_to_admin, $plain_text );

echo "\n";

if ( $include_customer_details == 'yes' ) {
	$items_table_args = array(
		'show_download_links'	=> $order->is_download_permitted(),
		'show_sku' 				=> true,
		'show_purchase_note'	=> $order->has_status( 'processing' ),
		'show_image'			=> false,
		'image_size'			=> array( 32, 32 ),
		'plain_text'			=> true,
	);

	if ( version_compare( WOOCOMMERCE_VERSION, '2.7', '>=' ) ) {
		// WC 2.7+
		echo wc_get_email_order_items( $order, $items_table_args );
	} elseif ( version_compare( WOOCOMMERCE_VERSION, '2.5', '>=' ) ) {
		// WC 2.5 & 2.6
		echo $order->email_order_items_table( $items_table_args );
	} else {
		// backwards compatible arguments (WC2.4 & older)
		/**
		 * @param bool $show_download_links (default: false)
		 * @param bool $show_sku (default: false)
		 * @param bool $show_purchase_note (default: false)
		 * @param bool $show_image (default: false)
		 * @param array $image_size (default: array( 32, 32 )
		 * @param bool plain text
		 */
		echo $order->email_order_items_table( $items_table_args['show_download_links'], $items_table_args['show_sku'], $items_table_args['show_purchase_note'], $items_table_args['show_image'], $items_table_args['image_size'], $items_table_args['plain_text'] );
	}

	echo "----------\n\n";

	if ( $totals = $order->get_order_item_totals() ) {
		foreach ( $totals as $total ) {
			echo $total['label'] . "\t " . $total['value'] . "\n";
		}
	}
}


echo "\n****************************************************\n\n";

// do_action( 'woocommerce_email_after_order_table', $order, $sent_to_admin, $plain_text );

if ( $include_customer_details == 'yes' ) {
	echo __( 'Customer details', 'woocommerce' ) . "\n";

	if ( $email = WCX_Order::get_prop( $order, 'billing_email', 'view' ) ) {
		echo __( 'Email:', 'woocommerce' ); echo $email . "\n";
	}

	if ( $phone = WCX_Order::get_prop( $order, 'billing_phone', 'view' ) ) {
		echo __( 'Tel:', 'woocommerce' ); ?> <?php echo $phone . "\n";
	}

	wc_get_template( 'emails/plain/email-addresses.php', array( 'order' => $order ) );

	echo "\n****************************************************\n\n";
}

echo apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) );
