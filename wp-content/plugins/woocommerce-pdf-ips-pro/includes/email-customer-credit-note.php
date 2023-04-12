<?php
use WPO\WC\PDF_Invoices\Compatibility\WC_Core as WCX;
use WPO\WC\PDF_Invoices\Compatibility\Order as WCX_Order;
use WPO\WC\PDF_Invoices\Compatibility\Product as WCX_Product;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'WC_Email_Customer_Credit_Note' ) ) :

/**
 * Customer Credit Note
 *
 * An email sent to the customer via admin.
 *
 * @class 		WC_Email_Customer_Credit_Note
 * @author 		WP Overnight
 * @extends 	WC_Email
 */
class WC_Email_Customer_Credit_Note extends WC_Email {

	var $find;
	var $replace;

	/**
	 * Constructor
	 */
	function __construct() {

		$this->id             = 'customer_credit_note';
		$this->title          = __( 'Customer Credit Note', 'wpo_wcpdf_pro' );
		$this->description    = __( 'Customer Credit Note emails can be sent to the user with the PDF Credit Note attached.', 'wpo_wcpdf_pro' );

		$this->template_html  = 'emails/customer-credit-note.php';
		$this->template_plain = 'emails/plain/customer-credit-note.php';
		$this->template_base  = trailingslashit( dirname(__FILE__) );

		$this->subject        = __( 'Credit Note for order {order_number} from {order_date}', 'wpo_wcpdf_pro');
		$this->heading        = __( 'Credit Note for order {order_number}', 'wpo_wcpdf_pro');
		$this->body           = __( 'A refund has been issued for your order, attached to this email you will find a credit note with the details.', 'wpo_wcpdf_pro');

		// Trigger according to settings
		$trigger_refunded = $this->get_option( 'trigger' );
		if ( isset($trigger_refunded) && $trigger_refunded == 'yes' ) {
			add_action( 'woocommerce_order_status_refunded', array( $this, 'trigger' ) );
		}

		// Call parent constructor
		parent::__construct();

		$this->subject        = $this->get_option( 'subject', $this->subject );
		$this->heading        = $this->get_option( 'heading', $this->heading );
		$this->body           = $this->get_option( 'body', $this->body );

		if ( class_exists('Polylang') ) {
			$this->register_strings();
		}
	}

	/**
	 * trigger function.
	 *
	 * @access public
	 * @return void
	 */
	function trigger( $order ) {

		if ( ! is_object( $order ) ) {
			$order = wc_get_order( absint( $order ) );
		}

		if ( class_exists('Polylang') ) {
			$this->translate_strings( $order );
		}

		if ( $order ) {
			$this->object 		= $order;
			$this->recipient	= WCX_Order::get_prop( $this->object, 'billing_email', 'view' );

			$this->find['order-date']      = '{order_date}';
			$this->find['order-number']    = '{order_number}';

			$order_date = WCX_Order::get_prop( $this->object, 'date_created' );
			$this->replace['order-date']   = $order_date->date_i18n( wc_date_format() );
			$this->replace['order-number'] = $this->object->get_order_number();
		}

		if ( ! $this->get_recipient() ) {
			return;
		}

		if ( $this->is_automatic() && ! $this->is_enabled() ) {
			return;
		}
		
		if ( version_compare( WOOCOMMERCE_VERSION, '2.2', '<' ) ) {
			return;
		}

		$refunds = $order->get_refunds();
		if ( empty( $refunds ) ) {
			return;
		}

		$result = $this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
		do_action( 'wpo_wcpdf_pro_email_sent', $result, $this->id, $order );
	}

	/**
	 * get_subject function.
	 *
	 * @access public
	 * @return string
	 */
	function get_subject() {
		return apply_filters( 'woocommerce_email_subject_customer_credit_note', $this->format_string( $this->subject ), $this->object );
	}

	/**
	 * get_heading function.
	 *
	 * @access public
	 * @return string
	 */
	function get_heading() {
		return apply_filters( 'woocommerce_email_heading_customer_credit_note', $this->format_string( $this->heading ), $this->object );
	}

	/**
	 * get_body function.
	 *
	 * @access public
	 * @return string
	 */
	function get_body() {
		return apply_filters( 'woocommerce_email_body_customer_credit_note', $this->format_string( $this->body ), $this->object );
	}

	/**
	 * get_content_html function.
	 *
	 * @access public
	 * @return string
	 */
	function get_content_html() {
		ob_start();
		wc_get_template(
			$this->template_html,
			array(
				'order' 		=> $this->object,
				'email_heading'	=> $this->get_heading(),
				'email_body'	=> $this->get_body(),
				'sent_to_admin'	=> false,
				'plain_text'	=> false,
				'email'			=> $this,
			), '',
			$this->template_base
		);
		return ob_get_clean();
	}

	/**
	 * get_content_plain function.
	 *
	 * @access public
	 * @return string
	 */
	function get_content_plain() {
		ob_start();
		wc_get_template(
			$this->template_plain,
			array(
				'order' 		=> $this->object,
				'email_heading' => $this->get_heading(),
				'email_body'    => $this->get_body(),
				'sent_to_admin' => false,
				'plain_text'    => true
			), '',
			$this->template_base
		);
		return ob_get_clean();
	}

	/**
	 * Initialise Settings Form Fields
	 *
	 * @access public
	 * @return void
	 */
	function init_form_fields() {
		$this->form_fields = array(
			'trigger' => array(
				'title' 		=> __( 'Automatically send', 'wpo_wcpdf_pro' ),
				'type'          => 'checkbox',
				'label'         => __( 'Automatically send email when order status is set to refunded', 'wpo_wcpdf_pro' ),
				'default'       => 'no'
			),
			'subject' => array(
				'title' 		=> __( 'Email subject', 'wpo_wcpdf_pro' ),
				'type' 			=> 'text',
				'description' 	=> sprintf( __( 'Defaults to <code>%s</code>', 'wpo_wcpdf_pro' ), $this->subject ),
				'placeholder' 	=> '',
				'default' 		=> ''
			),
			'heading' => array(
				'title' 		=> __( 'Email heading', 'wpo_wcpdf_pro' ),
				'type' 			=> 'text',
				'description' 	=> sprintf( __( 'Defaults to <code>%s</code>', 'wpo_wcpdf_pro' ), $this->heading ),
				'placeholder' 	=> '',
				'default' 		=> ''
			),
			'body' => array(
				'title' 		=> __( 'Email body text', 'wpo_wcpdf_pro' ),
				'css' 			=> 'width:100%; height: 75px;',
				'type' 			=> 'textarea',
				'description' 	=> sprintf( __( 'Defaults to <code>%s</code>', 'wpo_wcpdf_pro' ), $this->body ),
				'placeholder' 	=> '',
				'default' 		=> $this->body
			),
			'email_type' => array(
				'title' 		=> __( 'Email type', 'wpo_wcpdf_pro' ),
				'type' 			=> 'select',
				'description' 	=> __( 'Choose which format of email to send.', 'wpo_wcpdf_pro' ),
				'default' 		=> 'html',
				'class'			=> 'email_type',
				'options'		=> array(
					'plain' 		=> __( 'Plain text', 'wpo_wcpdf_pro' ),
					'html' 			=> __( 'HTML', 'wpo_wcpdf_pro' ),
					'multipart' 	=> __( 'Multipart', 'wpo_wcpdf_pro' ),
				)
			)
		);
	}

	function register_strings() {
		if (!function_exists('pll_register_string')) {
			return;
		}
		
		$string_slugs = array( 'subject', 'heading', 'body');
		// register strings
		foreach ($string_slugs as $string_slug) {
			$multiline = $string_slug === 'body' ? true : false;
			pll_register_string( 'woocommerce_customer_credit_note_'.$string_slug, $this->{$string_slug}, 'woocommerce-pdf-ips-pro', $multiline );
		}
	}

	function translate_strings( $order ) {
		WPO_WCPDF_Pro()->multilingual->switch_language( 'credit-note', WCX_Order::get_id( $order ) );
		$string_slugs = array( 'subject', 'heading', 'body');
		// translate strings
		foreach ($string_slugs as $string_slug) {
			$this->{$string_slug} = pll__( $this->{$string_slug} );
		}
	}

	function is_enabled() {
		return apply_filters( 'woocommerce_email_enabled_' . $this->id, $this->is_automatic(), $this->object );
	}

	function is_manual() {
		return !$this->is_automatic();
	}

	function is_automatic()	{
		$trigger_refunded = $this->get_option( 'trigger' );
		return isset($trigger_refunded) && $trigger_refunded == 'yes';
	}

	function is_customer_email() {
		return true;
	}

}

endif;

return new WC_Email_Customer_Credit_Note();
