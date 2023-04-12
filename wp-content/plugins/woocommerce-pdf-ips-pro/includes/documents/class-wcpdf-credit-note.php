<?php
namespace WPO\WC\PDF_Invoices\Documents;

use WPO\WC\PDF_Invoices\Compatibility\WC_Core as WCX;
use WPO\WC\PDF_Invoices\Compatibility\Order as WCX_Order;
use WPO\WC\PDF_Invoices\Compatibility\Product as WCX_Product;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( !class_exists( '\\WPO\\WC\\PDF_Invoices\\Documents\\Credit_Note' ) ) :

/**
 * Credit Note Document
 * 
 * @class       \WPO\WC\PDF_Invoices\Documents\Credit_Note
 * @version     2.0
 * @category    Class
 * @author      Ewout Fernhout
 */

class Credit_Note extends Pro_Document {
	/**
	 * Init/load the order object.
	 *
	 * @param  int|object|WC_Order $order Order to init.
	 */
	public function __construct( $order = 0 ) {
		// set properties
		$this->type		= 'credit-note';
		$this->title	= __( 'Credit Note', 'wpo_wcpdf_pro' );
		$this->icon		= WPO_WCPDF_Pro()->plugin_url() . "/images/credit-note.svg";

		// Call parent constructor
		parent::__construct( $order );
	}

	public function get_title() {
		// override/not using $this->title to allow for language switching!
		return apply_filters( "wpo_wcpdf_{$this->slug}_title", __( 'Credit Note', 'wpo_wcpdf_pro' ), $this );
	}

	public function get_filename( $context = 'download', $args = array() ) {
		$order_ids = isset($args['order_ids']) ? $args['order_ids'] : array( $this->order_id );
		$order_count = count( $order_ids );

		$name = _n( 'credit-note', 'credit-notes', $order_count, 'wpo_wcpdf_pro' );

		if ( $order_count == 1 ) {
			if ( isset( $this->settings['display_number'] ) ) {
				$suffix = (string) $this->get_number();
			} else {
				if ( empty( $this->order ) ) {
					$order = WCX::get_order ( $order_ids[0] );
					$suffix = method_exists( $order, 'get_order_number' ) ? $order->get_order_number() : intval($order_ids[0]);
				} else {
					$suffix = method_exists( $this->order, 'get_order_number' ) ? $this->order->get_order_number() : $this->order->get_id();
				}
			}
		} else {
			$suffix = date('Y-m-d'); // 2020-11-11
		}

		$filename = $name . '-' . $suffix . '.pdf';

		// Filter filename
		$filename = apply_filters( 'wpo_wcpdf_filename', $filename, $this->get_type(), $order_ids, $context );

		// sanitize filename (after filters to prevent human errors)!
		return sanitize_file_name( $filename );
	}


	/**
	 * Initialise settings
	 */
	public function init_settings() {
		// Register settings.
		$page = $option_group = $option_name = 'wpo_wcpdf_documents_settings_credit-note';

		$settings_fields = array(
			array(
				'type'			=> 'section',
				'id'			=> 'credit_note',
				'title'			=> '',
				'callback'		=> 'section',
			),
			array(
				'type'			=> 'setting',
				'id'			=> 'enabled',
				'title'			=> __( 'Enable', 'woocommerce-pdf-invoices-packing-slips' ),
				'callback'		=> 'checkbox',
				'section'		=> 'credit_note',
				'args'			=> array(
					'option_name'		=> $option_name,
					'id'				=> 'enabled',
				)
			),
			array(
				'type'			=> 'setting',
				'id'			=> 'attach_to_email_ids',
				'title'			=> __( 'Attach to:', 'woocommerce-pdf-invoices-packing-slips' ),
				'callback'		=> 'multiple_checkboxes',
				'section'		=> 'credit_note',
				'args'			=> array(
					'option_name'	=> $option_name,
					'id'			=> 'attach_to_email_ids',
					'fields'		=> $this->get_wc_emails(),
					'description'	=> !is_writable( WPO_WCPDF()->main->get_tmp_path( 'attachments' ) ) ? '<span class="wpo-warning">' . sprintf( __( 'It looks like the temp folder (<code>%s</code>) is not writable, check the permissions for this folder! Without having write access to this folder, the plugin will not be able to email invoices.', 'woocommerce-pdf-invoices-packing-slips' ), WPO_WCPDF()->main->get_tmp_path( 'attachments' ) ).'</span>':'',
				)
			),
			array(
				'type'			=> 'setting',
				'id'			=> 'display_shipping_address',
				'title'			=> __( 'Display shipping address', 'woocommerce-pdf-invoices-packing-slips' ),
				'callback'		=> 'checkbox',
				'section'		=> 'credit_note',
				'args'			=> array(
					'option_name'		=> $option_name,
					'id'				=> 'display_shipping_address',
					'description'		=> __( 'Display shipping address (in addition to the default billing address) if different from billing address', 'woocommerce-pdf-invoices-packing-slips' ),
				)
			),
			array(
				'type'			=> 'setting',
				'id'			=> 'display_email',
				'title'			=> __( 'Display email address', 'woocommerce-pdf-invoices-packing-slips' ),
				'callback'		=> 'checkbox',
				'section'		=> 'credit_note',
				'args'			=> array(
					'option_name'		=> $option_name,
					'id'				=> 'display_email',
				)
			),
			array(
				'type'			=> 'setting',
				'id'			=> 'display_phone',
				'title'			=> __( 'Display phone number', 'woocommerce-pdf-invoices-packing-slips' ),
				'callback'		=> 'checkbox',
				'section'		=> 'credit_note',
				'args'			=> array(
					'option_name'		=> $option_name,
					'id'				=> 'display_phone',
				)
			),
			array(
				'type'			=> 'setting',
				'id'			=> 'display_date',
				'title'			=> __( 'Display credit note date', 'wpo_wcpdf_pro' ),
				'callback'		=> 'select',
				'section'		=> 'credit_note',
				'args'			=> array(
					'option_name'	=> $option_name,
					'id'			=> 'display_date',
					'options' 		=> array(
						''				=> __( 'No' , 'woocommerce-pdf-invoices-packing-slips' ),
						'1'				=> __( 'Credit Note Date' , 'wpo_wcpdf_pro' ),
						'order_date'	=> __( 'Refund Date' , 'wpo_wcpdf_pro' ),
					),
				)
			),
			array(
				'type'			=> 'setting',
				'id'			=> 'display_number',
				'title'			=> __( 'Display credit note number', 'wpo_wcpdf_pro' ),
				'callback'		=> 'checkbox',
				'section'		=> 'credit_note',
				'args'			=> array(
					'option_name'		=> $option_name,
					'id'				=> 'display_number',
				)
			),
			array(
				'type'			=> 'setting',
				'id'			=> 'number_sequence',
				'title'			=> __( 'Number sequence', 'wpo_wcpdf_pro' ),
				'callback'		=> 'radio_button',
				'section'		=> 'credit_note',
				'args'			=> array(
					'option_name'		=> $option_name,
					'id'				=> 'number_sequence',
					'options' 			=> array(
						'invoice_number'		=> __( 'Main invoice numbering' , 'wpo_wcpdf_pro' ),
						'credit_note_number'	=> __( 'Separate credit note numbering' , 'wpo_wcpdf_pro' ),
					),
					'default'			=> 'credit_note_number',
			)
			),
			array(
				'type'			=> 'setting',
				'id'			=> 'original_invoice_number',
				'title'			=> __( 'Show original invoice number', 'wpo_wcpdf_pro' ),
				'callback'		=> 'checkbox',
				'section'		=> 'credit_note',
				'args'			=> array(
					'option_name'	=> $option_name,
					'id'			=> 'original_invoice_number',
				)
			),
			array(
				'type'			=> 'setting',
				'id'			=> 'next_credit_note_number',
				'title'			=> __( 'Next credit note number (without prefix/suffix etc.)', 'wpo_wcpdf_pro' ),
				'callback'		=> 'next_number_edit',
				'section'		=> 'credit_note',
				'args'			=> array(
					'store'			=> 'credit_note_number',
					'size'			=> '10',
					'description'	=> __( 'This is the number that will be used for the next document. By default, numbering starts from 1 and increases for every new document. Note that if you override this and set it lower than the current/highest number, this could create duplicate numbers!', 'woocommerce-pdf-invoices-packing-slips' ),
				)
			),
			array(
				'type'			=> 'setting',
				'id'			=> 'number_format',
				'title'			=> __( 'Number format', 'woocommerce-pdf-invoices-packing-slips' ),
				'callback'		=> 'multiple_text_input',
				'section'		=> 'credit_note',
				'args'			=> array(
					'option_name'			=> $option_name,
					'id'					=> 'number_format',
					'fields'				=> array(
						'prefix'			=> array(
							'placeholder'	=> __( 'Prefix' , 'woocommerce-pdf-invoices-packing-slips' ),
							'size'			=> 20,
							'description'	=> __( 'to use the credit note year and/or month, use [credit_note_year] or [credit_note_month] respectively' , 'woocommerce-pdf-invoices-packing-slips' ),
						),
						'suffix'			=> array(
							'placeholder'	=> __( 'Suffix' , 'woocommerce-pdf-invoices-packing-slips' ),
							'size'			=> 20,
							'description'	=> '',
						),
						'padding'			=> array(
							'placeholder'	=> __( 'Padding' , 'woocommerce-pdf-invoices-packing-slips' ),
							'size'			=> 20,
							'type'			=> 'number',
							'description'	=> __( 'enter the number of digits here - enter "6" to display 42 as 000042' , 'woocommerce-pdf-invoices-packing-slips' ),
						),
					),
				)
			),
			array(
				'type'			=> 'setting',
				'id'			=> 'reset_number_yearly',
				'title'			=> __( 'Reset credit note number yearly', 'wpo_wcpdf_pro' ),
				'callback'		=> 'checkbox',
				'section'		=> 'credit_note',
				'args'			=> array(
					'option_name'		=> $option_name,
					'id'				=> 'reset_number_yearly',
				)
			),
			// array(
			// 	'type'			=> 'setting',
			// 	'id'			=> 'my_account_buttons',
			// 	'title'			=> __( 'Allow My Account download', 'woocommerce-pdf-invoices-packing-slips' ),
			// 	'callback'		=> 'select',
			// 	'section'		=> 'credit_note',
			// 	'args'			=> array(
			// 		'option_name'	=> $option_name,
			// 		'id'			=> 'my_account_buttons',
			// 		'options'		=> array(
			// 			'available'	=> __( 'Only when a credit note is already created/emailed' , 'woocommerce-pdf-invoices-packing-slips' ),
			// 			'custom'	=> __( 'Only for specific order statuses (define below)' , 'woocommerce-pdf-invoices-packing-slips' ),
			// 			'always'	=> __( 'Always' , 'woocommerce-pdf-invoices-packing-slips' ),
			// 			'never'		=> __( 'Never' , 'woocommerce-pdf-invoices-packing-slips' ),
			// 		),
			// 		'custom'		=> array(
			// 			'type'		=> 'multiple_checkboxes',
			// 			'args'		=> array(
			// 				'option_name'	=> $option_name,
			// 				'id'			=> 'my_account_restrict',
			// 				'fields'		=> $this->get_wc_order_status_list(),
			// 			),
			// 		),
			// 	)
			// ),
			array(
				'type'			=> 'setting',
				'id'			=> 'disable_free',
				'title'			=> __( 'Disable for free products', 'woocommerce-pdf-invoices-packing-slips' ),
				'callback'		=> 'checkbox',
				'section'		=> 'credit_note',
				'args'			=> array(
					'option_name'	=> $option_name,
					'id'			=> 'disable_free',
					'description'	=> __( "Disable automatic creation/attachment when only free products are ordered", 'woocommerce-pdf-invoices-packing-slips' ),
				)
			),
			array(
				'type'			=> 'setting',
				'id'			=> 'positive_prices',
				'title'			=> __( 'Use positive prices', 'wpo_wcpdf_pro' ),
				'callback'		=> 'checkbox',
				'section'		=> 'credit_note',
				'args'			=> array(
					'option_name'	=> $option_name,
					'id'			=> 'positive_prices',
					'description'	=> __( 'Prices in Credit Notes are negative by default, but some countries (like Germany) require positive prices.', 'wpo_wcpdf_pro' ),
				)
			),
			array(
				'type'			=> 'setting',
				'id'			=> 'use_parent_data',
				'title'			=> __( 'Use products & totals fallback', 'wpo_wcpdf_pro' ),
				'callback'		=> 'checkbox',
				'section'		=> 'credit_note',
				'args'			=> array(
					'option_name'	=> $option_name,
					'id'			=> 'use_parent_data',
					'description'	=> __( 'If orders are refunded without setting products, credit notes will not contain these details. This option provides a fallback method by using data from the original order for the credit note. This may cause issues in some setups, so testing is recommended.', 'wpo_wcpdf_pro' ),
				)
			),
			array(
				'type'			=> 'setting',
				'id'			=> 'use_latest_settings',
				'title'			=> __( 'Always use most current settings', 'woocommerce-pdf-invoices-packing-slips' ),
				'callback'		=> 'checkbox',
				'section'		=> 'credit_note',
				'args'			=> array(
					'option_name'	=> $option_name,
					'id'			=> 'use_latest_settings',
					'description'	=> __( "When enabled, the document will always reflect the most current settings (such as footer text, document name, etc.) rather than using historical settings.", 'woocommerce-pdf-invoices-packing-slips' )
					                   . "<br>"
					                   . __( "<strong>Caution:</strong> enabling this will also mean that if you change your company name or address in the future, previously generated documents will also be affected.", 'woocommerce-pdf-invoices-packing-slips' ),
				)
			),
		);

		// allow plugins to alter settings fields
		$settings_fields = apply_filters( 'wpo_wcpdf_settings_fields_documents_credit_note', $settings_fields, $page, $option_group, $option_name );
		WPO_WCPDF()->settings->add_settings_fields( $settings_fields, $page, $option_group, $option_name );
		return;

	}

}

endif; // class_exists

return new Credit_Note();