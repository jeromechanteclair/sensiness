<?php
namespace WPO\WC\PDF_Invoices_Pro;

use WPO\WC\PDF_Invoices\Compatibility\WC_Core as WCX;
use WPO\WC\PDF_Invoices\Compatibility\Order as WCX_Order;
use WPO\WC\PDF_Invoices\Compatibility\Product as WCX_Product;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( !class_exists( '\\WPO\\WC\\PDF_Invoices_Pro\\Writepanels' ) ) :

class Writepanels {
	public function __construct() {
		// hide credit note button for non-refunded orders
		add_filter( 'wpo_wcpdf_meta_box_actions', array( $this, 'credit_note_button_visibility' ), 10, 1 );
		add_filter( 'wpo_wcpdf_listing_actions', array( $this, 'credit_note_button_visibility' ), 10, 2 );
		add_filter( 'wpo_wcpdf_myaccount_actions', array( $this, 'my_account_button_visibility' ), 10, 2 );

		add_action( 'wcpdf_invoice_number_column_end', array( $this, 'credit_note_number_column_data' ), 10, 1 );

		add_filter( 'woocommerce_resend_order_emails_available', array( $this, 'pro_email_order_actions' ), 90, 1 );
		add_action( 'wpo_wcpdf_meta_box_end', array( $this, 'edit_numbers_dates' ), 10, 2 );
		add_action( 'wpo_wcpdf_on_save_invoice_order_data', array( $this,'save_numbers_dates' ), 10, 3 );

		add_action( 'wpo_wcpdf_meta_box_after_document_data', array( $this, 'regenerate_order_data_button_for_stored_pdf' ), 10, 2 );	
	}

	/**
	 * Remove credit note button if order is not refunded
	 */
	public function credit_note_button_visibility ($actions, $order = '' ) {
		if (empty($order)) {
			global $post_id;
			$order = wc_get_order( $post_id );
		}

		if ($order) {
			$refunds = $order->get_refunds();

			if ( empty( $refunds ) ) {
				unset($actions['credit-note']);
			} else {
				// only show credit note button when there is also an invoice for this order
				$invoice = wcpdf_get_invoice( $order );
				if ( $invoice && $invoice->exists() === false ) {
					unset($actions['credit-note']);
				}
			}
		} else {
			unset($actions['credit-note']);
		}

		return $actions;
	}

	/**
	 * Display download buttons (Proforma & Credit Note) on My Account page
	 */
	public function my_account_button_visibility( $actions, $order ) {
		$order_id = WCX_Order::get_id( $order );

		$documents = array(
			'proforma'     => 'no_invoice',
			'packing-slip' => 'never',
		);
		
		foreach ( $documents as $document_type => $default_visibility ) {
			$document = wcpdf_get_document( $document_type, $order );
			if ( $document && $document->is_enabled() ) {
				// check my account button settings
				$button_setting = $document->get_setting('my_account_buttons', $default_visibility);
				switch ($button_setting) {
					case 'no_invoice':
						$document_allowed = isset($actions['invoice']) ? false: true;
						break;
					case 'available':
						$document_allowed = $document->exists();
						break;
					case 'always':
						$document_allowed = true;
						break;
					case 'never':
						$document_allowed = false;
						break;
					case 'custom':
						$allowed_statuses = $button_setting = $document->get_setting('my_account_restrict', array());
						if ( !empty( $allowed_statuses ) && in_array( WCX_Order::get_status( $order ), array_keys( $allowed_statuses ) ) ) {
							$document_allowed = true;
						} else {
							$document_allowed = false;
						}
						break;
				}

				if ($document_allowed) {
					$actions[ $document_type ] = array(
						'url'  => wp_nonce_url( admin_url( 'admin-ajax.php?action=generate_wpo_wcpdf&template_type=' . $document_type . '&order_ids=' . $order_id . '&my-account' ), 'generate_wpo_wcpdf' ),
						'name' => apply_filters( 'wpo_wcpdf_myaccount_' . $document->slug . '_button', $document->get_title(), $document ),
					);
				}
			}
		}

		// show credit note button when credit note is available and invoice is too
		if ( version_compare( WOOCOMMERCE_VERSION, '2.2.7', '>=' ) ) {
			$refunds = $order->get_refunds();
			// if there's at least one credit note, we'll take them all...
			if ( !empty( $refunds ) && isset( $actions['invoice'] ) ) {
				$first_refund = current( $refunds );
				$credit_note = wcpdf_get_document( 'credit-note', $first_refund );
				if ( $credit_note && $credit_note->exists() && $credit_note->is_enabled() ) {
					$actions['credit-note'] = array(
						'url'  => wp_nonce_url( admin_url( 'admin-ajax.php?action=generate_wpo_wcpdf&template_type=credit-note&order_ids=' . $order_id . '&my-account' ), 'generate_wpo_wcpdf' ),
						'name' => apply_filters( 'wpo_wcpdf_myaccount_credit_note_button', $credit_note->get_title(), $credit_note ),
					);
				}
			}
		}

		return $actions;
	}

	/**
	 * Display Credit Note Number in Shop Order column (if available)
	 * @param  string $column column slug
	 */
	public function credit_note_number_column_data( $order ) {
		$refunds = $order->get_refunds();
		foreach ($refunds as $key => $refund) {
			$refund_id = WCX_Order::get_id( $refund );
			$credit_note = wcpdf_get_document( 'credit-note', $refund );
			if ( $credit_note && is_callable( array( $credit_note, 'get_number' ) ) && $credit_note_number = $credit_note->get_number( 'credit-note' ) ) {
				$credit_note_numbers[] = $credit_note_number;
				$title = $credit_note->get_title();
			}
		}

		if ( isset($credit_note_numbers) ) {
			?>
			<br/><?php echo $title; ?>:<br/>
			<?php
			echo implode(', ', $credit_note_numbers);
		}
	}

	public function edit_numbers_dates( $order, $class = null ) {
		// bail if null
		if( is_null( $class ) ) return;
		
		// Credit note
		if ( version_compare( WOOCOMMERCE_VERSION, '2.2', '>=' ) ) {
			$refunds = $order->get_refunds();
			if ( !empty( $refunds ) ) {
				foreach ($refunds as $key => $refund) {
					$credit_note = wcpdf_get_document( 'credit-note', $refund );
					if ( $credit_note && $credit_note->exists() ) {
						$refund_id = WCX_Order::get_id( $refund );

						// data
						$data = array(
							'number' => array(
								'label'  => __( 'Credit Note Number:', 'woocommerce-pdf-invoices-packing-slips' ),
								'name'   => "_wcpdf_{$credit_note->slug}_number[{$refund_id}]",
							),
							'date'   => array(
								'label'  => __( 'Credit Note Date:', 'woocommerce-pdf-invoices-packing-slips' ),
								'name'   => "_wcpdf_{$credit_note->slug}_date[{$refund_id}]",
							),
						);
						
						// output
						$class->output_number_date_edit_fields( $credit_note, $data );
					}
				}
			}
		}

		// Proforma invoice
		$proforma = wcpdf_get_document( 'proforma', $order );
		if ( $proforma && $proforma->exists() ) {
			// data
			$data = array(
				'number' => array(
					'label'  => __( 'Proforma Invoice Number:', 'woocommerce-pdf-invoices-packing-slips' ),
				),
				'date'   => array(
					'label'  => __( 'Proforma Invoice Date:', 'woocommerce-pdf-invoices-packing-slips' ),
				),
			);
			// output
			$class->output_number_date_edit_fields( $proforma, $data );
		}

		// Packing slip
		$packing_slip = wcpdf_get_document( 'packing-slip', $order );
		if ( $packing_slip && $packing_slip->exists() ) {
			$data = array(
				'number' => array(
					'label'  => __( 'Packing Slip Number:', 'woocommerce-pdf-invoices-packing-slips' ),
				),
				'date'   => array(
					'label'  => __( 'Packing Slip Date:', 'woocommerce-pdf-invoices-packing-slips' ),
				),
			);
			// output
			$class->output_number_date_edit_fields( $packing_slip, $data );
		}

	}

	/**
	 * Display regenerate order data buttons when keep PDF is active
	 */
	public function regenerate_order_data_button_for_stored_pdf( $document, $order ) {

		$document_settings = $document->get_settings( true );
		if ( !isset( $document_settings['archive_pdf'] ) ) return;

		$parent_order = $archived = false;

		// If credit note
		if ( $document->get_type() == 'credit-note' ) {
			$parent_order = wc_get_order( $order->get_parent_id() );
		}

		// Get PDF file path
		$order_key = $parent_order ? $parent_order->get_order_key() : $order->get_order_key();
		$archive_path = WPO_WCPDF()->main->get_tmp_path( 'archive' );
		$filename = $order->get_meta( '_wpo_wcpdf_' . $document->slug . '_archived', true );

		// Check if PDF file exists on server
		if ( !empty( $filename ) && file_exists( $archive_path . '/' . $filename ) ) $archived = true;	
		clearstatcache();

		?>
		<div class="document-archived">
			<p class="form-field wcpdf_archived_document_data">	
				<p>
					<span><strong><?php echo $document->get_title() . ' ' . __( 'stored on server', 'wpo_wcpdf_pro' ); ?>:</strong></span>
					<span style="margin-right:10px;"><?php echo $archived ? __( 'Yes', 'wpo_wcpdf_pro' ) : __( 'No', 'wpo_wcpdf_pro' ) ; ?></span>
				</p>
			</p>
		</div>
		<?php
	}

	/**
	 * Process numbers & dates from order edit screen
	 */
	public function save_numbers_dates( $form_data, $order, $class = null ) {
		// bail if null
		if( is_null( $class ) ) return;

		// Proforma
		if ( $proforma = wcpdf_get_document( 'proforma', $order ) ) {
			$document_data = $class->process_order_document_form_data( $form_data, $proforma->slug );
			$proforma->set_data( $document_data, $order );
			$proforma->save();
		}

		// Packing Slip
		if ( $packing_slip = wcpdf_get_document( 'packing-slip', $order ) ) {
			$document_data = $class->process_order_document_form_data( $form_data, $packing_slip->slug );
			$packing_slip->set_data( $document_data, $order );
			$packing_slip->save();
		}

		// Credit Note
		$credit_note_data_list   = array();
		$credit_note_field_names = array(
			'_wcpdf_credit_note_number',
			'_wcpdf_credit_note_date',
		);

		foreach ($credit_note_field_names as $field_name) {
			if (isset($_POST[$field_name]) && is_array($_POST[$field_name])) {
				foreach ($_POST[$field_name] as $refund_id => $value) {
					$credit_note_data_list[$refund_id][$field_name] = $value;
				}
			}
		}

		foreach ( $credit_note_data_list as $refund_id => $credit_note_data ) {
			if ( $credit_note = wcpdf_get_document( 'credit-note', $order ) ) {
				$document_data = $class->process_order_document_form_data( $credit_note_data, $credit_note->slug );
				$credit_note->set_data( $document_data, $order );
				$credit_note->save();
			}
		}
	}

	/**
	 * Add credit note email to order actions list
	 */
	public function pro_email_order_actions ( $available_emails ) {
		global $post_id;

		$order_notification_settings = get_option( 'woocommerce_pdf_order_notification_settings' );
		if ( isset($order_notification_settings['recipient']) && !empty($order_notification_settings['recipient']) ) {
			// only add order notification action when a recipient is set!
			$available_emails[] = 'pdf_order_notification';
		}

		if ( version_compare( WOOCOMMERCE_VERSION, '2.2', '>=' ) ) {
			if ( $order = wc_get_order( $post_id ) ) {
				$refunds = $order->get_refunds();
				if ( !empty( $refunds ) ) {
					$available_emails[] = 'customer_credit_note';
				}
			}
		}

		return $available_emails;
	}

} // end class

endif; // end class_exists

return new Writepanels();