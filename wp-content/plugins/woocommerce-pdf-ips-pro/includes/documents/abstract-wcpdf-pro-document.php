<?php
namespace WPO\WC\PDF_Invoices\Documents;

use WPO\WC\PDF_Invoices\Compatibility\WC_Core as WCX;
use WPO\WC\PDF_Invoices\Compatibility\Order as WCX_Order;
use WPO\WC\PDF_Invoices\Compatibility\Product as WCX_Product;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( !class_exists( '\\WPO\\WC\\PDF_Invoices\\Documents\\Pro_Document' ) ) :

/**
 * Pro Document abstract
 * 
 * @class       \WPO\WC\PDF_Invoices\Documents\Pro_Document
 * @version     2.0
 * @category    Class
 * @author      Ewout Fernhout
 */

abstract class Pro_Document extends Order_Document_Methods {

	public function use_historical_settings() {
		$document_settings = get_option( 'wpo_wcpdf_documents_settings_'.$this->get_type() );
		// this setting is inverted on the frontend so that it needs to be actively/purposely enabled to be used
		if (!empty($document_settings) && isset($document_settings['use_latest_settings'])) {
			$use_historical_settings = false;
		} else {
			$use_historical_settings = true;
		}
		return apply_filters( 'wpo_wcpdf_document_use_historical_settings', $use_historical_settings, $this );
	}

	public function storing_settings_enabled() {
		return apply_filters( 'wpo_wcpdf_document_store_settings', true, $this );
	}

	public function init() {
		// store settings in order
		if ( $this->storing_settings_enabled() && !empty( $this->order ) ) {
			$common_settings = WPO_WCPDF()->settings->get_common_document_settings();
			$document_settings = get_option( 'wpo_wcpdf_documents_settings_'.$this->get_type() );
			$settings = (array) $document_settings + (array) $common_settings;
			WCX_Order::update_meta_data( $this->order, "_wcpdf_{$this->slug}_settings", $settings );
		}

		if ( isset( $this->settings['display_date'] ) && $this->settings['display_date'] == 'order_date' && !empty( $this->order ) ) {
			$this->set_date( WCX_Order::get_prop( $this->order, 'date_created' ) );
		} else {
			$this->set_date( current_time( 'timestamp', true ) );
		}

		$this->init_number();
	}

    public function exists() {
        return !empty( $this->data['number'] );
    }

	public function init_number() {
		$number_store_method = WPO_WCPDF()->settings->get_sequential_number_store_method();
		// Determine numbering system (main invoice number or separate document sequence)
		$number_store_name = isset( $this->settings['number_sequence'] ) ? $this->settings['number_sequence'] : "{$this->slug}_number";
		$number_store_name = apply_filters( 'wpo_wcpdf_document_sequential_number_store', $number_store_name, $this );
		$number_store = new Sequential_Number_Store( $number_store_name, $number_store_method );
		// reset invoice number yearly
		if ( isset( $this->settings['reset_number_yearly'] ) ) {
			$current_year = date("Y");
			$last_number_year = $number_store->get_last_date('Y');
			// check if we need to reset
			if ( $current_year != $last_number_year ) {
				$number_store->set_next( 1 );
			}
		}

		$document_date = $this->get_date();
		$document_number = $number_store->increment( $this->order_id, $document_date->date_i18n( 'Y-m-d H:i:s' ) );

		$this->set_number( $document_number );

		return $document_number;
	}

	public function get_formatted_number( $document_type ) {
		if ( $number = $this->get_number( $document_type ) ) {
			return $formatted_number = $number->get_formatted();
		} else {
			return '';
		}
	}

	public function number( $document_type ) {
		echo $this->get_formatted_number( $document_type );
	}

	public function get_formatted_date( $document_type ) {
		if ( $date = $this->get_date( $document_type ) ) {
			return $date->date_i18n( apply_filters( 'wpo_wcpdf_date_format', wc_date_format(), $this ) );
		} else {
			return '';
		}
	}

	public function date( $document_type ) {
		echo $this->get_formatted_date( $document_type );
	}


}

endif; // class_exists
