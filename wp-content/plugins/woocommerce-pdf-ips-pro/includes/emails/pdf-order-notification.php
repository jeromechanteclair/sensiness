<?php
use WPO\WC\PDF_Invoices\Compatibility\Order as WCX_Order;
/**
 * Order Notification email
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$order_date = WCX_Order::get_prop( $order, 'date_created' );

?>

<?php do_action( 'woocommerce_email_header', $email_heading, $email ); ?>

<?php
// Some of the default actions are disabled in this email because they may result in unexpected output.
// For example when this email is sent for unpaid orders, woocommerce would still display payment
// instructions with the action below!
// do_action( 'woocommerce_email_before_order_table', $order, $sent_to_admin, $plain_text, $email );
?>

<h2><?php echo __( 'Order:', 'woocommerce' ) . ' ' . $order->get_order_number(); ?> (<?php printf( '<time datetime="%s">%s</time>', $order_date->date_i18n( 'c' ), $order_date->date_i18n( wc_date_format() ) ); ?>)</h2>

<p><?php echo $email_body; ?></p>

<?php if ( $include_items_table == 'yes' ) { ?>
<table cellspacing="0" cellpadding="6" style="width: 100%; border: 1px solid #eee;" border="1" bordercolor="#eee">
	<thead>
		<tr>
			<th scope="col" style="text-align:left; border: 1px solid #eee;"><?php _e( 'Product', 'woocommerce' ); ?></th>
			<th scope="col" style="text-align:left; border: 1px solid #eee;"><?php _e( 'Quantity', 'woocommerce' ); ?></th>
			<th scope="col" style="text-align:left; border: 1px solid #eee;"><?php _e( 'Price', 'woocommerce' ); ?></th>
		</tr>
	</thead>
	<tbody>
		<?php
			$items_table_args = array(
				'show_download_links'	=> $order->is_download_permitted(),
				'show_sku' 				=> true,
				'show_purchase_note'	=> $order->has_status( 'processing' ),
				// defaults:
				// 'show_image'			=> true,
				// 'image_size'			=> array( 32, 32 ),
				// 'plain_text'			=> false,
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
		?>
	</tbody>
	<tfoot>
		<?php
			if ( $totals = $order->get_order_item_totals() ) {
				$i = 0;
				foreach ( $totals as $total ) {
					$i++;
					?><tr>
						<th scope="row" colspan="2" style="text-align:left; border: 1px solid #eee; <?php if ( $i == 1 ) echo 'border-top-width: 4px;'; ?>"><?php echo $total['label']; ?></th>
						<td style="text-align:left; border: 1px solid #eee; <?php if ( $i == 1 ) echo 'border-top-width: 4px;'; ?>"><?php echo $total['value']; ?></td>
					</tr><?php
				}
			}
		?>
	</tfoot>
</table>
<?php } // endif items_table ?>

<?php // do_action( 'woocommerce_email_after_order_table', $order, $sent_to_admin, $plain_text, $email ); ?>

<?php do_action( 'woocommerce_email_order_meta', $order, $sent_to_admin, $plain_text, $email ); ?>

<?php if ( $include_customer_details == 'yes' ) { ?>
	<h2><?php _e( 'Customer details', 'woocommerce' ); ?></h2>

	<?php if ( $email = WCX_Order::get_prop( $order, 'billing_email', 'view' ) ) : ?>
		<p><strong><?php _e( 'Email:', 'woocommerce' ); ?></strong> <?php echo $email; ?></p>
	<?php endif; ?>
	<?php if ( $phone = WCX_Order::get_prop( $order, 'billing_phone', 'view' ) ) : ?>
		<p><strong><?php _e( 'Tel:', 'woocommerce' ); ?></strong> <?php echo $phone; ?></p>
	<?php endif; ?>

	<?php wc_get_template( 'emails/email-addresses.php', array( 'order' => $order ) ); ?>
<?php } // endif customer_details ?>

<?php do_action( 'woocommerce_email_footer', $email ); ?>
