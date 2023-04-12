<?php
namespace WPO\WC\PDF_Invoices_Pro\Legacy;

use WPO\WC\PDF_Invoices\Compatibility\WC_Core as WCX;
use WPO\WC\PDF_Invoices\Compatibility\Order as WCX_Order;
use WPO\WC\PDF_Invoices\Compatibility\Product as WCX_Product;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( !class_exists( '\\WPO\\WC\\PDF_Invoices_Pro\\Legacy\\Legacy_Functions' ) ) :

class Legacy_Functions {
	public function __construct() {
		$this->pro_settings = get_option( 'wpo_wcpdf_pro_settings' );
		// add_filter( 'wpo_wcpdf_filename', array( $this, 'build_filename' ), 5, 4 );
		// add_action( 'wpo_wcpdf_process_template_order', array( $this, 'set_numbers_dates' ), 10, 2 );
		// add_filter( 'wpo_wcpdf_proforma_number', array( $this, 'format_proforma_number' ), 20, 4 );
		// add_filter( 'wpo_wcpdf_credit_note_number', array( $this, 'format_credit_note_number' ), 20, 4 );
		add_filter( 'wpo_wcpdf_template_name', array( $this, 'pro_template_names' ), 5, 2 );

	}

	/**
	 * Redirect document function calls directly to document object
	 */
	public function __call( $name, $arguments ) {
		if ( is_object( \WPO\WC\PDF_Invoices\Legacy\WPO_WCPDF_Legacy()->export->document ) && is_callable( array( \WPO\WC\PDF_Invoices\Legacy\WPO_WCPDF_Legacy()->export->document, $name ) ) ) {
			return call_user_func_array( array( \WPO\WC\PDF_Invoices\Legacy\WPO_WCPDF_Legacy()->export->document, $name ), $arguments );
		} else {
			throw new \Exception("Call to undefined method ".__CLASS__."::{$name}()", 1);
		}
	}

	public function get_number( $document_type, $order_id = '' ) {
		if ( is_object( \WPO\WC\PDF_Invoices\Legacy\WPO_WCPDF_Legacy()->export->document ) ) {
			return \WPO\WC\PDF_Invoices\Legacy\WPO_WCPDF_Legacy()->export->document->get_formatted_number( $document_type );
		}
	}

	public function get_date( $document_type, $order_id = '' ) {
		if ( is_object( \WPO\WC\PDF_Invoices\Legacy\WPO_WCPDF_Legacy()->export->document ) ) {
			return \WPO\WC\PDF_Invoices\Legacy\WPO_WCPDF_Legacy()->export->document->get_formatted_date( $document_type );
		}
	}

	/**
	 * Filter to get template name for template type/slug
	 */
	public function pro_template_names ( $template_name, $template_type ) {
		switch ( $template_type ) {
			case 'proforma':
				$template_name = apply_filters( 'wpo_wcpdf_proforma_title', __( 'Proforma Invoice', 'wpo_wcpdf_pro' ) );
				break;
			case 'credit-note':
				$template_name = apply_filters( 'wpo_wcpdf_credit_note_title', __( 'Credit Note', 'wpo_wcpdf_pro' ) );
				break;
		}

		return $template_name;
	}

	/**
	 * 
	 */
	public function build_filename( $filename, $template_type, $order_ids, $context ) {
		if ( !in_array( $template_type, array( 'credit-note', 'proforma' ) ) ) {
			// we're not processing any of the pro documents
			return $filename;
		}

		global $wpo_wcpdf, $wpo_wcpdf_pro;

		$count = count( $order_ids );

		switch ($template_type) {	
			case 'proforma':
				$name = _n( 'proforma-invoice', 'proforma-invoices', $count, 'wpo_wcpdf_pro' );
				$number = $wpo_wcpdf_pro->get_number('proforma');
				break;		
			case 'credit-note':
				$name = _n( 'credit-note', 'credit-notes', $count, 'wpo_wcpdf_pro' );
				$number = $wpo_wcpdf_pro->get_number('credit-note');
				break;
		}

		if ( $count == 1 ) {
			$suffix = $number;			
		} else {
			$suffix = date('Y-m-d'); // 2020-11-11
		}

		return sanitize_file_name( $name . '-' . $suffix . '.pdf' );
	}

	/**
	 * Set number and date for pro documents
	 * @param  string $template_type
	 * @param  int    $order_id
	 * @return void
	 */
	public function set_numbers_dates( $template_type, $order_id ) {
		// check if we're processing one of the pro document types
		if ( !in_array( $template_type, array( 'proforma', 'credit-note' ) ) ) {
			return;
		}

		// name conversion for settings and meta compatibility (credit-note = credit_note)
		$template_type = str_replace('-', '_', $template_type);

		// get order
		$order = $this->get_order( $order_id );

		// get document date
		$date = WCX_Order::get_meta( $order, '_wcpdf_'.$template_type.'_date', true );
		if ( empty($date) ) {
			// first time this document is created for this order
			// set document date
			$date = current_time('mysql');
			WCX_Order::update_meta_data( $order, '_wcpdf_'.$template_type.'_date', $date );
		}

		// get document number
		$number = WCX_Order::get_meta( $order, '_wcpdf_'.$template_type.'_number', true );
		if ( empty( $number ) ) {
			// numbering system switch
			$numbering_system = isset( $this->pro_settings[$template_type.'_number'] ) ? $this->pro_settings[$template_type.'_number'] : 'separate';
			switch ($numbering_system) {
				case 'main':
					// making direct DB call to avoid caching issues
					global $wpdb;
					$next_invoice_number = $wpdb->get_var( $wpdb->prepare( "SELECT option_value FROM $wpdb->options WHERE option_name = %s LIMIT 1", 'wpo_wcpdf_next_invoice_number' ) );
					$next_invoice_number = apply_filters( 'wpo_wcpdf_next_invoice_number', $next_invoice_number, $order_id );

					// set document number
					$document_number = isset( $next_invoice_number ) ? $next_invoice_number : 1;

					// increase wpo_wcpdf_next_invoice_number
					$update_args = array(
						'option_value'	=> $document_number + 1,
						'autoload'		=> 'yes',
					);
					$result = $wpdb->update( $wpdb->options, $update_args, array( 'option_name' => 'wpo_wcpdf_next_invoice_number' ) );
					break;
				default:
				case 'separate':
					// set document number
					$document_number = isset( $this->pro_settings['next_'.$template_type.'_number'] ) ? $this->pro_settings['next_'.$template_type.'_number'] : 1;

					// increment next document number setting
					$this->pro_settings = get_option( 'wpo_wcpdf_pro_settings' );
					$this->pro_settings['next_'.$template_type.'_number'] += 1;
					update_option( 'wpo_wcpdf_pro_settings', $this->pro_settings );
					break;
			}

			WCX_Order::update_meta_data( $order, '_wcpdf_'.$template_type.'_number', $document_number );
			WCX_Order::update_meta_data( $order, '_wcpdf_formatted_'.$template_type.'_number', $this->get_number( $template_type, $order_id ) );
		}
	}

	// /**
	//  * Get the formatted document number for a template type
	//  * @param  string $template_type
	//  * @param  int    $order_id
	//  * @return formatted document number
	//  */
	// public function get_number( $template_type, $order_id = '' ) {
	// 	global $wpo_wcpdf;
	// 	if ( empty( $order_id ) ) {
	// 		$order_id = WCX_Order::get_id( $wpo_wcpdf->export->order );
	// 	}
	// 	$order = $this->get_order( $order_id );

	// 	// name conversion for settings and meta compatibility (credit-note = credit_note)
	// 	$template_type = str_replace('-', '_', $template_type);

	// 	// get number from post meta
	// 	// try parent first (=original proforma invoice for credit notes)
	// 	if ( $template_type != 'credit_note' && get_post_type( $order_id ) == 'shop_order_refund' && $parent_order_id = wp_get_post_parent_id( $order_id ) ) {
	// 		$parent_order = WCX::get_order( $parent_order_id );
	// 		$number = WCX_Order::get_meta( $parent_order, '_wcpdf_'.$template_type.'_number', true );
	// 	} else {
	// 		$number = WCX_Order::get_meta( $order, '_wcpdf_'.$template_type.'_number', true );
	// 	}

	// 	// prepare filter data & filter
	// 	if ( $number ) {
	// 		if ( $template_type == 'credit_note' && $parent_order_id = wp_get_post_parent_id( $order_id ) ) {
	// 			$parent_order = WCX::get_order( $parent_order_id );
	// 			$order_number = $parent_order->get_order_number();
	// 			$order_date = WCX_Order::get_prop( $parent_order, 'date_created' );
	// 		} else {
	// 			$order_number = $order->get_order_number();
	// 			$order_date = WCX_Order::get_prop( $order, 'date_created' );
	// 		}
	// 		$mysql_order_date = $order_date->date( "Y-m-d H:i:s" );
	// 		return apply_filters( 'wpo_wcpdf_'.$template_type.'_number', $number, $order_number, $order_id, $mysql_order_date );
	// 	} else {
	// 		// no number for this order
	// 		return false;
	// 	}
	// }

	// /**
	//  * Return/Show document date 
	//  */
	// public function get_date( $template_type, $order_id = '' ) {
	// 	global $wpo_wcpdf;
	// 	if ( empty( $order_id ) ) {
	// 		$order_id = WCX_Order::get_id( $wpo_wcpdf->export->order );
	// 	}
	// 	$order = $this->get_order( $order_id );

	// 	// name conversion for settings and meta compatibility (credit-note = credit_note)
	// 	$template_type = str_replace('-', '_', $template_type);

	// 	// get document date from post meta
	// 	// try parent first (=original proforma invoice for credit notes)
	// 	if ( $template_type != 'credit_note' && get_post_type( $order_id ) == 'shop_order_refund' && $parent_order_id = wp_get_post_parent_id( $order_id ) ) {
	// 		$parent_order = WCX::get_order( $parent_order_id );
	// 		$date = WCX_Order::get_meta( $parent_order, '_wcpdf_'.$template_type.'_date', true );
	// 	} else {
	// 		$date = WCX_Order::get_meta( $order, '_wcpdf_'.$template_type.'_date', true );
	// 	}

	// 	if ( !empty($date) ) {
	// 		$formatted_date = date_i18n( get_option( 'date_format' ), strtotime( $date ) );
	// 	} else {
	// 		$formatted_date = false;
	// 	}

	// 	return $formatted_date;
	// }

	// /**
	//  * Format proforma invoice & credit note numbers
	//  * @param  int    $number       the plain, unformatted number
	//  * @param  string $order_number WooCommerce order number
	//  * @param  int    $order_id     Order ID
	//  * @param  string $order_date   mysql order date
	//  * @return string               Fotmatted number
	//  */
	// public function format_proforma_number( $number, $order_number, $order_id, $order_date ) {
	// 	return $this->format_number( 'proforma', $number, $order_number, $order_id, $order_date );
	// }

	// public function format_credit_note_number( $number, $order_number, $order_id, $order_date ) {
	// 	return $this->format_number( 'credit-note', $number, $order_number, $order_id, $order_date );
	// }

	// /**
	//  * Universal number formatting function
	//  */
	// public function format_number( $template_type, $number, $order_number, $order_id, $order_date ) {
	// 	// name conversion for settings and meta compatibility (credit-note = credit_note)
	// 	$template_type = str_replace('-', '_', $template_type);

	// 	// get format settings
	// 	$order_year = date_i18n( 'Y', strtotime( $order_date ) );
	// 	$order_month = date_i18n( 'm', strtotime( $order_date ) );
		
	// 	$formats['prefix'] = isset($this->pro_settings[$template_type.'_number_formatting_prefix'])?$this->pro_settings[$template_type.'_number_formatting_prefix']:'';
	// 	$formats['suffix'] = isset($this->pro_settings[$template_type.'_number_formatting_suffix'])?$this->pro_settings[$template_type.'_number_formatting_suffix']:'';
	// 	$formats['padding'] = isset($this->pro_settings[$template_type.'_number_formatting_padding'])?$this->pro_settings[$template_type.'_number_formatting_padding']:'';

	// 	// Replacements
	// 	foreach ($formats as $key => $value) {
	// 		$value = str_replace('[order_year]', $order_year, $value);
	// 		$value = str_replace('[order_month]', $order_month, $value);
	// 		$formats[$key] = $value;
	// 	}

	// 	// Padding
	// 	if ( ctype_digit( (string)$formats['padding'] ) ) {
	// 		$number = sprintf('%0'.$formats['padding'].'d', $number);
	// 	}

	// 	$formatted_number = $formats['prefix'] . $number . $formats['suffix'] ;

	// 	return $formatted_number;
	// }

} // end class

endif; // end class_exists

return new Legacy_Functions();