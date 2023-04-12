<?php
namespace WPO\WC\PDF_Invoices_Pro;

use WPO\WC\PDF_Invoices\Compatibility\WC_Core as WCX;
use WPO\WC\PDF_Invoices\Compatibility\Order as WCX_Order;
use WPO\WC\PDF_Invoices\Compatibility\Product as WCX_Product;

use WPO\WC\PDF_Invoices\Documents\Sequential_Number_Store;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( !class_exists( '\\WPO\\WC\\PDF_Invoices_Pro\\Functions' ) ) :

class Functions {
	public function __construct() {
		$this->pro_settings = get_option( 'wpo_wcpdf_settings_pro' );

		add_filter( 'wpo_wcpdf_document_classes', array( $this, 'register_documents' ), 10, 1 );
		add_filter( 'woocommerce_email_attachments', array( $this, 'attach_static_file' ), 99, 3);
		add_filter( 'wpo_wcpdf_template_file', array( $this, 'pro_template_files' ), 10, 2 );
		add_filter( 'wpo_wcpdf_process_order_ids', array( $this, 'credit_notes_order_ids' ), 10, 2 );
		add_filter( 'wpo_wcpdf_email_attachment_order', array( $this, 'refund_email_object' ), 10, 3 );
		add_filter( 'wpo_wcpdf_custom_attachment_condition', array( $this, 'restrict_credit_notes_attachment' ), 10, 4 );

		add_filter( 'wpo_wcpdf_billing_address', array( $this, 'billing_address_filter' ), 10, 2 );
		add_filter( 'wpo_wcpdf_shipping_address', array( $this, 'shipping_address_filter' ), 10, 2 );

		// register Partially Refunded alias for Refunded Order email
		add_filter( 'wpo_wcpdf_attach_documents', array( $this, 'register_partially_refunded_email_id' ), 10, 1 );

		// always process invoice before credit note if both are attached to the same email
		add_filter( 'wpo_wcpdf_document_types_for_email', array( $this, 'credit_note_attachment_priority' ), 10, 3 );

		// document specific filters
		// Packing Slip
		add_action( 'wpo_wcpdf_init_document', array( $this, 'init_packing_slip' ), 10, 1 );
		add_filter( 'wpo_wcpdf_order_items_data', array( $this, 'subtract_refunded_qty' ), 10, 3 );
		add_filter( 'wpo_wcpdf_before_order_data', array( $this, 'packing_slip_number_date' ), 10, 2 );
		add_filter( 'wpo_wcpdf_order_items_data', array( $this, 'hide_virtual_products' ), 10, 3 );

		// Credit Note
		add_action( 'wpo_wcpdf_process_template', array( $this, 'positive_credit_note' ) );
		add_filter( 'wpo_wcpdf_after_order_data', array( $this, 'original_invoice_number' ), 10, 2 );
		add_filter( 'woocommerce_get_formatted_order_total', array( $this, 'refund_taxes_simple_template' ), 10, 4 );
		add_action( 'wpo_wcpdf_before_html', array( $this, 'credit_note_maybe_use_order_items' ), 10, 2 );
		add_action( 'wpo_wcpdf_after_html', array( $this, 'credit_note_dont_use_order_items' ), 10, 2 );

		// apply title & filename settings
		add_action( 'init', array( $this, 'filter_document_titles' ), 999999 );
		add_filter( 'wpo_wcpdf_filename', array( $this, 'override_document_filename' ), 10, 4 );

		// Keep PDF on server functions
		if ( version_compare( WPO_WCPDF()->version, '2.4.7', '>' ) && version_compare( PHP_VERSION, '7.1', '>=' ) ) {
			add_action( 'wpo_wcpdf_pdf_created', array( $this, 'store_pdf_file_in_archive' ), 10, 2 );
			add_action( 'wpo_wcpdf_delete_document', array( $this, 'unlink_archived_pdf' ), 10, 1 );
			add_filter( 'wpo_wcpdf_load_pdf_file_path', array( $this, 'load_archived_pdf_file_path' ), 10, 2 );
			add_filter( 'wpo_wcpdf_pdf_data', array( $this, 'store_bulk_documents_in_archive' ), 10, 2 );
			add_action( 'wpo_wcpdf_regenerate_document', array( $this, 'regenerate_archived_pdf' ), 10, 2 );
		}

		// Fix Credit Note / Refund conflict in WooCommerce German Market
		add_action( 'wpo_wcpdf_before_html', array( $this, 'remove_wgm_refund_filters' ), 10, 2 );
	}

	public function register_documents( $documents ) {
		// Load pro document abstract
		include_once( dirname( __FILE__ ) . '/documents/abstract-wcpdf-pro-document.php' );
		// Load Proforma & Credit Note
		$documents['\WPO\WC\PDF_Invoices\Documents\Proforma']		= include( 'documents/class-wcpdf-proforma.php' );
		$documents['\WPO\WC\PDF_Invoices\Documents\Credit_Note']	= include( 'documents/class-wcpdf-credit-note.php' );
		return $documents;
	}

	public function register_partially_refunded_email_id( $attach_documents ) {
		foreach ($attach_documents as $document_type => $attach_to_email_ids) {
			if (in_array('customer_refunded_order', $attach_to_email_ids)) {
				$attach_documents[$document_type][] = 'customer_partially_refunded_order';
			}
		}
		return $attach_documents;
	}

	/**
	 * Make sure credit notes are always processed last, so that invoices may be generated before it
	 * @param  array  $document_types  list of documents to attach
	 * @param  string $email_id        id/slug of the email
	 * @param  object $order           order object
	 * @return array  $document_types  reorderded list of documents to attach
	 */
	public function credit_note_attachment_priority( $document_types, $email_id, $order ) {
		$credit_note_key = array_search('credit-note', $document_types);
		if ( $credit_note_key !== false ) {
			unset($document_types[$credit_note_key]);
			$document_types[$credit_note_key] = 'credit-note';
		}
		return $document_types;
	}

	/**
	 * Attach static file to WooCommerce emails of choice
	 * @param  array  $attachments  list of attachment paths
	 * @param  string $email_id     id/slug of the email
	 * @param  object $order        order object
	 * @return array  $attachments  including static file
	 */
	public function attach_static_file( $attachments, $email_id, $order ) {
		if (empty($this->pro_settings['static_file'])) {
			return $attachments;
		}

		// get file ids to attach
		$static_files = $this->pro_settings['static_file'];

		// get settings
		$attach_to_email_ids = isset( $this->pro_settings['static_file_attach_to_email_ids'] ) ? array_keys( $this->pro_settings['static_file_attach_to_email_ids'] ) : array();
		if (in_array('customer_refunded_order', $attach_to_email_ids)) {
			$attach_to_email_ids[] = 'customer_partially_refunded_order';
		}

		if ( is_subclass_of( $order, '\WC_Abstract_Order') ) {
			// fake $template_type for attachment condition filter
			$template_type = 'static_file';
			// use this filter to add an extra condition - return false to disable the file attachment
			$attach_files = apply_filters('wpo_wcpdf_custom_attachment_condition', true, $order, $email_id, $template_type );
		}

		if ( in_array( $email_id, $attach_to_email_ids ) && $attach_files ) {
			foreach ($static_files as $number => $static_file) {
				if ( isset( $static_file['id'] ) ) {
					$file_id = apply_filters( 'wpml_object_id', $static_file['id'], 'attachment', true );
					$file_path = get_attached_file( $file_id );
					$template_type = isset($template_type) ? $template_type : null;
					$attach_file = apply_filters( 'wpo_wcpdf_attach_static_file', true, $order, $email_id, $template_type, $static_file, $number, $file_path ); // $number starts from 0 and ends in 2
					if ( file_exists( $file_path ) && $attach_file ) {
						$attachments[] = $file_path;
					}
				}
			}
		}

		return $attachments;
	}

	/**
	 * Set file locations for pro document types
	 */
	public function pro_template_files( $template, $template_type ) {
		// bail out if file already exists in default or custom path!
		if( file_exists( $template ) ){
			return $template;
		}

		$pro_template = WPO_WCPDF_Pro()->plugin_path() . '/templates/Simple/' . $template_type . '.php';

		if( file_exists( $pro_template ) ){
			// default to bundled Simple template
			return $pro_template;
		} else {
			// unknown document type! This will inevitably throw an error unless there's another filter after this one.
			return $template;
		}
	}

	public function credit_notes_order_ids($order_ids, $template_type) {
		if ($template_type == 'credit-note') {
			$credit_notes_order_ids = array();
			foreach ($order_ids as $order_id) {
				if ( get_post_type( $order_id ) == 'shop_order_refund' ) {
					$credit_notes_order_ids[] =  $order_id;
				} else {
					if ( $order = WCX::get_order( $order_id ) ) {
						$refunds = $order->get_refunds();
						foreach ($refunds as $key => $refund) {
							$credit_notes_order_ids[] = WCX_Order::get_id( $refund );
						}
					}
				}
			}
			return apply_filters( 'wpo_wcpdf_credit_notes_order_ids', $credit_notes_order_ids, $order_ids );
		} else {
			return $order_ids;
		}
	}

	/**
	 * Use refund order object for refund email attachments
	 */

	public function refund_email_object( $order, $email, $document_type = null ) {
		if( !empty( $email ) && !empty( $email->refund ) && $document_type == 'credit-note' ) {
			$order = $email->refund;
		}
		return $order;
	}

	/**
	 * If credit notes attachment is enabled for invoice email, and an invoice email is sent when
	 * the order is not refunded, an empty credit note would otherwise be attached.
	 * This method prevents that from happening.
	 *
	 * In addition, this method prevents the attachment of credit notes for orders without an invoice
	 */
	public function restrict_credit_notes_attachment ( $condition, $order, $status, $template_type ) {
		// only process credit notes
		if ( $template_type != 'credit-note' ) {
			return $condition;
		}

		// prevent attachment for older versions
		if ( version_compare( WOOCOMMERCE_VERSION, '2.2', '<' ) ) {
			return false;
		}

		// get refunds & check for invoice
		if ( is_callable( array( $order, 'get_type' ) ) && $order->get_type() == 'shop_order_refund' ) {
			$refunds = array( $order );
			$parent_order_id = method_exists( $order, 'get_parent_id') ? $order->get_parent_id() : wp_get_post_parent_id( WCX_Order::get_id( $order ) );
			$invoice = wcpdf_get_invoice( array( $parent_order_id ) );
		} elseif ( is_callable( array( $order, 'get_refunds' ) ) ) {
			$refunds = $order->get_refunds();
			$invoice = wcpdf_get_invoice( $order );
		}

		// only attach credit note pdf when there are refunds
		if ( empty( $refunds ) ) {
			return false;
		}

		// only attach credit note when there is an invoice for this order
		if ( $invoice && $invoice->exists() === false ) {
			return false;
		}

		return $condition;
	}

	/**
	 * filters addresses when replacement placeholders configured via plugin settings!
	 */
	public function billing_address_filter( $original_address, $document ) {
		return $this->address_replacements( $original_address, $document, 'billing' );
	}

	public function shipping_address_filter( $original_address, $document ) {
		return $this->address_replacements( $original_address, $document, 'shipping' );
	}

	public function address_replacements( $original_address, $document, $type ) {
		if ( !isset( $this->pro_settings[$type.'_address'] ) || empty( $this->pro_settings[$type.'_address'] ) ) {
			// nothing set, use default woocommerce formatting
			return $original_address;
		}

		// get the address format from the settings
		$address = nl2br( $this->pro_settings[$type.'_address'] );

		// backwards compatibility for old settings using [placeholder] instead of {{placeholder}}
		$address = str_replace( array('[',']'), array('{{','}}'), $address);

		// load the order
		$order = &$document->order;

		$address = $this->make_replacements( $address, $order );

		preg_match_all('/\{\{.*?\}\}/', $address, $placeholders_used);
		$placeholders_used = array_shift($placeholders_used); // we only need the first match set

		// remove empty placeholder lines, but preserve user-defined empty lines
		if (isset($this->pro_settings['remove_whitespace'])) {
			// break formatted address into lines
			$address = explode("\n", $address);
			// loop through address lines and check if only placeholders (remove HTML formatting first)
			foreach ($address as $key => $address_line) {
				// strip html tags for checking
				$clean_line = trim(strip_tags($address_line));
				// clean zero-width spaces
				$clean_line = str_replace("\xE2\x80\x8B", "", $clean_line);
				if (empty($clean_line)) {
					continue; // user defined newline!
				}
				// check without leftover placeholders
				$clean_line = trim( str_replace($placeholders_used, '', $clean_line) );

				// remove empty lines
				if (empty($clean_line)) {
					unset($address[$key]);
				}
			}

			// glue address lines back together
			$address = implode("\n", $address);
		}

		// remove leftover placeholders
		$address = str_replace($placeholders_used, '', $address);

		return $address;
	}

	public function make_replacements( $text, $order ) {
		$order_id = WCX_Order::get_id( $order );
		// load parent order for refunds
		if ( get_post_type( $order_id ) == 'shop_order_refund' && $parent_order_id = wp_get_post_parent_id( $order_id ) ) {
			$parent_order = WCX::get_order( $parent_order_id );
		}

		// make an index of placeholders used in the text
		preg_match_all('/\{\{.*?\}\}/', $text, $placeholders_used);
		$placeholders_used = array_shift($placeholders_used); // we only need the first match set

		// load countries & states
		$countries = new \WC_Countries;

		// loop through placeholders and make replacements
		foreach ($placeholders_used as $placeholder) {
			$placeholder_clean = trim($placeholder,"{{}}");

			// first try to read data from order, fallback to parent order (for refunds)
			$data_sources = array( 'order', 'parent_order' );
			foreach ($data_sources as $data_source) {
				if (empty($$data_source)) {
					continue;
				}
				// special treatment for country & state
				$country_placeholders = array( 'shipping_country', 'billing_country' );
				$state_placeholders = array( 'shipping_state', 'billing_state' );
				foreach ( array_merge($country_placeholders, $state_placeholders) as $country_state_placeholder ) {
					if ( strpos( $placeholder_clean, $country_state_placeholder ) !== false ) {
						// check if formatting is needed
						if ( strpos($placeholder_clean, '_code') !== false ) {
							// no country or state formatting
							$placeholder_clean = str_replace('_code', '', $placeholder_clean);
							$format = false;
						} else {
							$format = true;
						}

						$country_or_state = WCX_Order::get_prop( $$data_source, $placeholder_clean );

						if ($format === true) {
							// format country or state
							if (in_array($placeholder_clean, $country_placeholders)) {
								$country_or_state = ( $country_or_state && isset( $countries->countries[ $country_or_state ] ) ) ? $countries->countries[ $country_or_state ] : $country_or_state;
							} elseif (in_array($placeholder_clean, $state_placeholders)) {
								// get country for address
								$country = WCX_Order::get_prop( $$data_source, str_replace( 'state', 'country', $placeholder_clean ) );
								$country_or_state = ( $country && $country_or_state && isset( $countries->states[ $country ][ $country_or_state ] ) ) ? $countries->states[ $country ][ $country_or_state ] : $country_or_state;
							}
						}

						if ( !empty( $country_or_state ) ) {
							$text = str_replace($placeholder, $country_or_state, $text);
							continue 3;
						}
					}
				}

				// Custom placeholders
				$custom = '';
				switch ($placeholder_clean) {
					case 'site_title':
						$custom = get_bloginfo();
						break;
					case 'order_number':
						if ( method_exists( $$data_source, 'get_order_number' ) ) {
							$custom = ltrim($$data_source->get_order_number(), '#');
						} else {
							$custom = '';
						}
						break;
					case 'order_status':
						if ( version_compare( WOOCOMMERCE_VERSION, '2.2', '>=' ) ) {
							$custom = wc_get_order_status_name( $$data_source->get_status() );
						} else {
							$status = get_term_by( 'slug', $$data_source->status, 'shop_order_status' );
							$custom = __( $status->name, 'woocommerce' );
						}
						break;							
					case 'order_date':
						$order_date = WCX_Order::get_prop( $$data_source, 'date_created' );
						$custom = $order_date->date_i18n( wc_date_format() );
						break;
					case 'order_time':
						$order_date = WCX_Order::get_prop( $$data_source, 'date_created' );
						$custom = $order_date->date_i18n( wc_time_format() );
						break;
					case 'date_completed':
						if ( $date = WCX_Order::get_prop( $$data_source, 'date_completed' ) ) {
							$custom = $date->date_i18n( wc_date_format() );
						}
						break;
					case 'date_paid':
						if ( $date = WCX_Order::get_prop( $$data_source, 'date_paid' ) ) {
							$custom = $date->date_i18n( wc_date_format() );
						}
						break;
					case 'order_total':
						$custom = method_exists( $$data_source, 'get_total' ) ? $$data_source->get_total() : '';
						break;
					default:
						break;
				}
				if ( !empty( $custom ) ) {
					$text = str_replace($placeholder, $custom, $text);
					continue 2;
				}

				// Order Properties
				if (in_array($placeholder_clean, array('shipping_address', 'billing_address'))) {
					$placeholder_clean = "formatted_{$placeholder_clean}";
				}
				$property_meta_keys = array(
					'_order_currency'		=> 'currency',
					'_order_tax'			=> 'total_tax',
					'_order_total'			=> 'total',
					'_order_version'		=> 'version',
					'_order_shipping'		=> 'shipping_total',
					'_order_shipping_tax'	=> 'shipping_tax',
				);
				if (in_array($placeholder_clean, array_keys($property_meta_keys))) {
					$property_name = $property_meta_keys[$placeholder_clean];
				} else {
					$property_name = str_replace('-', '_', sanitize_title( ltrim($placeholder_clean, '_') ) );
				}
				// The parameter for address getters is not actually context, but the default/empty value
				$context = in_array($property_name, array('formatted_shipping_address', 'formatted_billing_address')) ? '-' : 'view';
				$prop = trim( WCX_Order::get_prop( $$data_source, $property_name, $context ) );
				if ( !empty( $prop ) ) {
					$text = str_replace($placeholder, $prop, $text);
					continue 2;
				}

				// Order Meta
				if ( !$this->is_order_prop( $placeholder_clean ) ) {
					$meta = WCX_Order::get_meta( $$data_source, $placeholder_clean, true, 'view' );
					if ( !empty( $meta ) ) {
						$text = str_replace($placeholder, $meta, $text);
						continue 2;
					} else {
						// Fallback to hidden meta
						$meta = WCX_Order::get_meta( $$data_source, "_{$placeholder_clean}", true, 'view' );
						if ( !empty( $meta ) ) {
							$text = str_replace($placeholder, $meta, $text);
							continue 2;
						}
					}
				}

			}
		}

		return $text;
	}

	/**
	 * Replacement function for PDF document specific placeholders (numbers, dates)
	 */
	public function make_document_replacements( $text, $document ) {
		if (empty($document) || empty($document->order)) {
			return;
		}

		// make an index of placeholders used in the text
		preg_match_all('/\{\{.*?\}\}/', $text, $placeholders_used);
		$placeholders_used = array_shift($placeholders_used); // we only need the first match set

		// loop through placeholders and make replacements
		foreach ($placeholders_used as $placeholder) {
			$placeholder_clean = trim($placeholder,"{{}}");

			$replacement = '';
			switch ($placeholder_clean) {
				case 'document_date':
					if ( $date = $document->get_date() ) {
						$replacement = $date->date_i18n( wc_date_format() );
					}
					break;
				case 'document_number':
					if ( $number = $document->get_number() ) {
						$replacement = $number->get_formatted();
					}
					break;
				case 'invoice_number':
					$replacement = $document->get_invoice_number();
					break;
				case 'proforma_number':
					if ( $number = $document->get_number('proforma') ) {
						$replacement = $number->get_formatted();
					}
					break;
				case 'credit_note_number':
					if ( $number = $document->get_number('credit-note') ) {
						$replacement = $number->get_formatted();
					}
					break;
				default:
					break;
			}
			if ( !empty( $replacement ) ) {
				$text = str_replace($placeholder, $replacement, $text);
				continue;
			}

		}

		return $text;
	}

	public function is_order_prop( $key ) {
		// Taken from WC class
		$order_props = array(
			// Abstract order props
			'parent_id',
			'status',
			'currency',
			'version',
			'prices_include_tax',
			'date_created',
			'date_modified',
			'discount_total',
			'discount_tax',
			'shipping_total',
			'shipping_tax',
			'cart_tax',
			'total',
			'total_tax',
			// Order props
			'customer_id',
			'order_key',
			'billing_first_name',
			'billing_last_name',
			'billing_company',
			'billing_address_1',
			'billing_address_2',
			'billing_city',
			'billing_state',
			'billing_postcode',
			'billing_country',
			'billing_email',
			'billing_phone',
			'shipping_first_name',
			'shipping_last_name',
			'shipping_company',
			'shipping_address_1',
			'shipping_address_2',
			'shipping_city',
			'shipping_state',
			'shipping_postcode',
			'shipping_country',
			'payment_method',
			'payment_method_title',
			'transaction_id',
			'customer_ip_address',
			'customer_user_agent',
			'created_via',
			'customer_note',
			'date_completed',
			'date_paid',
			'cart_hash',
		);
		return in_array($key, $order_props);
	}

	/**
	 * Wrapper for str_replace that applies nl2br when required
	 * @param  string $find    string to replace
	 * @param  string $replace replacement
	 * @param  string $text    source text
	 * @return string $text    modified text
	 */
	public function replace_text( $find, $replace, $text ) {
		if (isset($this->pro_settings['placeholders_allow_line_breaks']) && is_string($text)) {
			$text = nl2br( wptexturize( $text ) );
		}

		$text = str_replace($find, $replace, $text);
		return $text;
	}

	public function init_packing_slip( $document ) {
		if ( $document->type == 'packing-slip' ) {
			// Init packing slip number
			$this->init_packing_slip_number( $document );
		}
	}

	public function init_packing_slip_number( $packing_slip ) {
		$number_store_method = WPO_WCPDF()->settings->get_sequential_number_store_method();
		$number_store_name = apply_filters( 'wpo_wcpdf_packing_slip_number_store', 'packing_slip_number' ); // legacy filter
		$number_store_name = apply_filters( 'wpo_wcpdf_document_sequential_number_store', $number_store_name, $this );
		$number_store = new Sequential_Number_Store( $number_store_name, $number_store_method );
		// reset invoice number yearly
		if ( isset( $packing_slip->settings['reset_number_yearly'] ) ) {
			$current_year = date("Y");
			$last_number_year = $number_store->get_last_date('Y');
			// check if we need to reset
			if ( $current_year != $last_number_year ) {
				$number_store->set_next( 1 );
			}
		}

		$packing_slip->set_date( current_time( 'timestamp', true ) );
		$date = $packing_slip->get_date();
		
		$number = $number_store->increment( $packing_slip->order_id, $date->date_i18n( 'Y-m-d H:i:s' ) );
		$packing_slip->set_number( $number );
		
		$packing_slip->save();

		// make sure we return the object
		$number = $packing_slip->get_number();

		return compact( 'number', 'date');
	}

	public function packing_slip_number_date( $document_type, $order ) {
		$packing_slip_settings = WPO_WCPDF()->settings->get_document_settings( 'packing-slip' );
		if ( $document_type == 'packing-slip' && ( isset( $packing_slip_settings['display_date'] ) || isset( $packing_slip_settings['display_number'] ) ) ) {
			$packing_slip = wcpdf_get_document( 'packing-slip', $order );

			// Packing Slip Number
			if ( ! $number = $packing_slip->get_number('packing-slip') ) {
				// create number if non-existent
				extract( $this->init_packing_slip_number( $packing_slip ) ); // creates $number, $date
			} else {
				$date = $packing_slip->get_date();
			}

			if ( isset( $packing_slip_settings['display_number'] ) && $number ) {
				?>
				<tr class="packing-slip-number">
					<th><?php _e( 'Packing Slip Number:', 'wpo_wcpdf_pro' ); ?></th>
					<td><?php echo $number->get_formatted(); ?></td>
				</tr>
				<?php
			}
			// Packing Slip Date
			if ( isset( $packing_slip_settings['display_date'] ) && $date ) {
				?>
				<tr class="packing-slip-date">
					<th><?php _e( 'Packing Slip Date:', 'wpo_wcpdf_pro' ); ?></th>
					<td><?php echo $date->date_i18n( apply_filters( 'wpo_wcpdf_date_format', wc_date_format(), $packing_slip ) ); ?></td>
				</tr>
				<?php
			}
		}
	}

	public function subtract_refunded_qty ( $items_data, $order, $document_type ) {
		$packing_slip_settings = get_option( 'wpo_wcpdf_documents_settings_packing-slip' );

		if ( $document_type == 'packing-slip' && isset($packing_slip_settings['subtract_refunded_qty']) ) {

			foreach ($items_data as $key => &$item) {
				if ( empty($item['quantity']) || !is_numeric($item['quantity']) ) {
					continue;
				}
				// item_id is required! (introduced in 1.5.3 of main plugin)
				if ( isset( $item['item_id'] ) ) {
					$refunded_qty = $order->get_qty_refunded_for_item( $item['item_id'] );
					if ( version_compare( WOOCOMMERCE_VERSION, '2.6', '>=' ) ) {
						$item['quantity'] = $item['quantity'] + $refunded_qty;
					} else {
						$item['quantity'] = $item['quantity'] - $refunded_qty;
					}

				}

				if ( $item['quantity'] == 0 ) {
					//remove 0 qty items
					unset( $items_data[$key] );
				}
			}
		}
		return $items_data;
	}

	public function hide_virtual_products ( $items_data, $order, $document_type ) {
		$packing_slip_settings = get_option( 'wpo_wcpdf_documents_settings_packing-slip' );

		if ( $document_type == 'packing-slip' && isset( $packing_slip_settings['hide_virtual_products'] ) ) {

			foreach ( $items_data as $key => &$item ) {

				if ( !empty( $item['product'] ) ) {
					if ( $item['product']->is_virtual() !== false || $item['product']->is_downloadable() !== false ) {
						unset( $items_data[$key] );
					}
				}
	
			}

		}
		return $items_data;
	}

	/**
	 * Show positive prices on credit note following user settings
	 */
	public function positive_credit_note ( $template_type ) {
		$credit_note_settings = WPO_WCPDF()->settings->get_document_settings( 'credit-note' );
		if ( $template_type == 'credit-note' && isset( $credit_note_settings['positive_prices'] ) ) {
			add_filter( 'wc_price', array( $this, 'woocommerce_positive_prices' ), 10, 3 );
		}
	}

	public function woocommerce_positive_prices ( $formatted_price, $price, $args ) {
		if( strpos($formatted_price, '<bdi>') !== false ) {
			$formatted_price = str_replace('amount"><bdi>-', 'amount"><bdi>', $formatted_price);
		} else {
			$formatted_price = str_replace('amount">-', 'amount">', $formatted_price);
		}
		return $formatted_price;
	}

	public function original_invoice_number ($template_type, $order) {
		$credit_note_settings = WPO_WCPDF()->settings->get_document_settings( 'credit-note' );
		if ($template_type == 'credit-note' && isset( $credit_note_settings['original_invoice_number'] ) ) {
			$credit_note = wcpdf_get_document( 'credit-note', $order );
			if ( $credit_note && $credit_note->exists() ) {
				?>
				<tr class="invoice-number">
					<th><?php _e( 'Original Invoice Number:', 'wpo_wcpdf_pro' ); ?></th>
					<td><?php $credit_note->invoice_number(); ?></td>
				</tr>
				<?php
			}
		}
	}

	public function credit_note_maybe_use_order_items( $document_type, $document ) {
		$credit_note_settings = WPO_WCPDF()->settings->get_document_settings( 'credit-note' );
		if ($document_type == 'credit-note' && isset( $credit_note_settings['use_parent_data'] ) && !empty($document->order)) {
			$parent_order = wc_get_order( $document->order->get_parent_id() );
			$refund_items = $document->order->get_items();
			$refund_amount = round( abs( $document->order->get_amount() ), 2 );
			$original_amount = round( abs( $parent_order->get_total() ), 2 );
			if ( $refund_amount == $original_amount && empty($refund_items) ) {
				add_filter( 'woocommerce_order_get_items', array( $this, 'get_items_refund_parent' ),10,3);
				add_filter( 'wc_price', array( $this, 'wc_negative_prices' ), 99, 4 );
				foreach ($this->get_refund_parent_properties() as $property) {
					add_filter( "woocommerce_order_refund_get_{$property}", array( $this, 'use_refund_parent_properties' ), 10, 2 );
				}
			}
		}
	}

	public function credit_note_dont_use_order_items( $document_type, $document ) {
		remove_filter( 'woocommerce_order_get_items', array( $this, 'get_items_refund_parent' ),10,3);
		remove_filter( 'wc_price', array( $this, 'wc_negative_prices' ), 99, 4 );
		foreach ($this->get_refund_parent_properties() as $property) {
			remove_filter( "woocommerce_order_refund_get_{$property}", array( $this, 'use_refund_parent_properties' ), 10, 2 );
		}
	}

	public function get_items_refund_parent($items, $order, $types) {
		if ($order->get_type() == 'shop_order_refund') {
			$parent_order = wc_get_order( $order->get_parent_id() );
			$items = $parent_order->get_items($types);
			foreach ($items as $item_id => $item) {
				if ( is_callable( array(  $item, "set_quantity" ) ) ) {
					$items[$item_id]->set_quantity($item->get_quantity()*-1);
				}
			}
		}
		return $items;
	}

	public function use_refund_parent_properties( $value, $refund ) {
		$prop = str_replace( 'woocommerce_order_refund_get_', '', current_filter() );
		$parent_order = wc_get_order( $refund->get_parent_id() );
		return $parent_order->{"get_{$prop}"}();
	}

	public function wc_negative_prices( $formatted_price, $price, $args, $unformatted_price = null ) {
		if (empty($args['is_negative_price']) && !empty($unformatted_price)) {
			$args['is_negative_price'] = true;
			$formatted_price = wc_price( $unformatted_price * -1 , $args );
		}
		return $formatted_price;
	}

	public function get_refund_parent_properties() {
		return array(
			'discount_total',
			'discount_tax',
			'shipping_total',
			'shipping_tax',
			'cart_tax',
			'total',
			'total_tax',
		);
	}


	/**
	 * Add '(includes %s)' tax string to refund total
	 * @param  string $formatted_total formatted order/refund total
	 * @param  object $order           WC_Order object
	 * @return string                  formatted order/refund total with taxes added for refunds
	 */
	public function refund_taxes_simple_template( $formatted_total, $order, $tax_display = '', $display_refunded = true ) {
		// don't apply this if already filtered externally
		if (function_exists('woocommerce_get_formatted_refund_total')) {
			return $formatted_total;
		}

		// get order type: WC3.0 = 'shop_order_refund', WC2.6 = 'refund'
		$order_type = method_exists($order, 'get_type') ? $order->get_type() : $order->order_type;
		if ( $order_type == 'refund' || $order_type == 'shop_order_refund' ) {
			// Tax for inclusive prices.
			if ( wc_tax_enabled() ) {
				$tax_string_array = array();
				if ( 'itemized' == get_option( 'woocommerce_tax_total_display' ) ) {
					foreach ( $order->get_tax_totals() as $code => $tax ) {
						$tax_amount         = $tax->formatted_amount;
						$tax_string_array[] = sprintf( '%s %s', $tax_amount, $tax->label );
					}
				} else {
					$tax_amount         = $order->get_total_tax();
					// get currency from parent
					$parent_order_id = ( method_exists( $order, 'get_parent_id') ) ? $order->get_parent_id() : wp_get_post_parent_id( WCX_Order::get_id( $order ) );
					$parent_order = WCX::get_order( $parent_order_id );

					$tax_string_array[] = sprintf( '%s %s', wc_price( $tax_amount, array( 'currency' => WCX_Order::get_prop( $parent_order, 'currency' ) ) ), WC()->countries->tax_or_vat() );
				}
				if ( ! empty( $tax_string_array ) ) {
					$tax_string = ' <small class="includes_tax">' . sprintf( __( '(includes %s)', 'woocommerce' ), implode( ', ', $tax_string_array ) ) . '</small>';
					$formatted_total .= $tax_string;
				}
			}
		}

		return $formatted_total;
	}

	public function filter_document_titles() {
		$documents = WPO_WCPDF()->documents->get_documents('all');
		foreach ($documents as $_document) {
			add_filter( "wpo_wcpdf_{$_document->slug}_title", function( $title, $document = null ) use ( $_document ) {
				if (empty($document)) {
					$document = &$_document;
				}
				
				$custom_title = $document->get_settings_text( 'title', false, false );
				if ( !empty($document->order) && !empty(WPO_WCPDF_PRO()->multilingual) ) {
					$language_switcher = new Language_Switcher($document);
					$custom_title = $language_switcher->get_i18n_setting( 'title', $custom_title, $document );
				}
				if (!empty($custom_title)) {
					$title = $custom_title;
				}
				return $title;
			}, 10, 2 );
		}
	}

	public function override_document_filename( $filename, $document_type, $order_ids = array(), $context = '' ) {
		$document_settings = WPO_WCPDF()->settings->get_document_settings( $document_type );

		if ( !empty($document_settings['filename']) && !empty(array_filter($document_settings['filename'])) && count($order_ids) == 1 ) {
			$order = WCX::get_order ( $order_ids[0] );
			$document = wcpdf_get_document( $document_type, $order );
			$custom_filename = $document->get_settings_text( 'filename', false, false );
			if ( !empty($document->order) && !empty(WPO_WCPDF_PRO()->multilingual) ) {
				$language_switcher = new Language_Switcher($document);
				$custom_filename = $language_switcher->get_i18n_setting( 'filename', $custom_filename, $document );
			}

			if (!empty($custom_filename)) {
				// replace document numbers
				$custom_filename = $this->make_document_replacements( $custom_filename, $document );
				// replace order data
				$custom_filename = $this->make_replacements( $custom_filename, $order );
				$filename_parts = explode('.', $custom_filename);
				$extension = end( $filename_parts );
				if (strtolower($extension) != 'pdf' ) {
					$custom_filename .= '.pdf';
				}

				if (!empty(str_replace('.pdf', '', $custom_filename))) {
					return $custom_filename;
				}
			}

		}
		return $filename;
	}

	public function store_bulk_documents_in_archive( $pdf, $bulk_document ) {
		$document_settings = get_option( 'wpo_wcpdf_documents_settings_' . $bulk_document->type );

		if ( !isset( $bulk_document->is_bulk ) || !isset( $document_settings['archive_pdf'] ) ) return $pdf;

		$merger = new \WPO\WC\PDF_Invoices_Pro\Vendor\iio\libmergepdf\Merger;
		$pdfs = array();

		foreach ( $bulk_document->order_ids as $order_id ) {
			if ( $order = wc_get_order( $order_id ) ) {
				if ( $document = wcpdf_get_document( $bulk_document->type, $order ) ) {
					if ( $document->exists() ) {
						$pdfs[] = $document->get_pdf();
					}
				}
			}
		}
		if ( !empty( $pdfs ) ) {
			foreach ( $pdfs as $pdf ) {
				$merger->addRaw( $pdf );
			}
			return $merger->merge();
		} 
	}

	public function store_pdf_file_in_archive( $pdf, $document ) {
		$document_settings = $document->get_settings( true );
		if ( $order = $document->order ) {

			$parent_order = $refund_id = false;

			// If credit note
			if ( $document->get_type() == 'credit-note' ) {
				$refund_id = $order->get_id();
				$parent_order = wc_get_order( $order->get_parent_id() );
			}

			$order_key = $parent_order ? $parent_order->get_order_key() : $order->get_order_key();

			if ( isset( $document_settings['archive_pdf'] ) && empty( $order->get_meta( "_wpo_wcpdf_{$document->slug}_archived" ) ) ) {
				$archive_path = WPO_WCPDF()->main->get_tmp_path( 'archive' );
				$filename = $refund_id ? sprintf('%s-%s-%s.pdf', $document->slug, $refund_id, $order_key ) : sprintf('%s-%s-%s.pdf', $document->slug, $order->get_id(), $order_key );
				$filename = sanitize_file_name( apply_filters( 'wpo_wcpdf_filename_archived_pdf', $filename, $document ) );
				file_put_contents( $archive_path . '/' . $filename, $pdf, LOCK_EX );
				$order->update_meta_data( "_wpo_wcpdf_{$document->slug}_archived", $filename );
				$order->save_meta_data();
			} 
		}
	}

	public function unlink_archived_pdf( $document ) {
		if ( $order = $document->order ) {
			$order->delete_meta_data( "_wpo_wcpdf_{$document->slug}_archived" );
			$order->save_meta_data();
		}
	}

	public function regenerate_archived_pdf( $document ) {
		$document_settings = $document->get_settings( true );
		if ( isset( $document_settings['archive_pdf'] ) ) {
			$this->unlink_archived_pdf( $document );
			$document->get_pdf();
		}
	}

	public function load_archived_pdf_file_path( $pdf_file, $document ) {
		$document_settings = $document->get_settings( true );
		if ( $order = $document->order ) {

			$parent_order = $refund_id = false;

			// If credit note
			if ( $document->get_type() == 'credit-note' ) {
				$refund_id = $order->get_id();
				$parent_order = wc_get_order( $order->get_parent_id() );
			}

			if ( isset( $document_settings['archive_pdf'] ) && !empty( $order->get_meta( "_wpo_wcpdf_{$document->slug}_archived" ) ) ) {
				$archive_path = WPO_WCPDF()->main->get_tmp_path( 'archive' );
				$filename = $order->get_meta( "_wpo_wcpdf_{$document->slug}_archived", true );

				if ( !file_exists($archive_path . '/' . $filename ) ) {
					// Remove archived meta
					$order->delete_meta_data( "_wpo_wcpdf_{$document->slug}_archived" );
					$order->save_meta_data();
					// Add order note
					$note = $refund_id ? sprintf( __( '%s (refund #%s) was marked as archived but not found on the server. A new version has been uploaded.', 'wpo_wcpdf_pro' ), ucfirst( $document->get_title() ), $refund_id ) : sprintf( __( '%s was marked as archived but not found on the server. A new version has been uploaded.', 'wpo_wcpdf_pro' ), ucfirst( $document->get_title() ) );
					$parent_order ? $parent_order->add_order_note( $note ) : $order->add_order_note( $note );
				} else {
					$pdf_file = $archive_path . '/' . $filename;
				}
				clearstatcache();
			}
		}

		return $pdf_file;
	}

	public function remove_wgm_refund_filters( $document_type, $document ) {
		if ( class_exists('WGM_Template') && $document_type == 'credit-note') {
			remove_filter( 'woocommerce_get_formatted_order_total', array( 'WGM_Template', 'kur_review_order_item' ), 1, 1 );
			remove_filter( 'woocommerce_get_order_item_totals', array( 'WGM_Template', 'get_order_item_totals' ), 10, 2 );
			remove_filter( 'woocommerce_get_order_item_totals', array( 'WGM_Fee', 'add_tax_string_to_fee_order_item' ), 10, 2 );
			remove_filter( 'woocommerce_order_get_tax_totals', array( 'WGM_Fee', 'add_fee_to_order_tax_totals' ), 10, 2 );
		}
	}

} // end class

endif; // end class_exists

return new Functions();