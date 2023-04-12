<?php
use WPO\WC\PDF_Invoices\Compatibility\WC_Core as WCX;
use WPO\WC\PDF_Invoices\Compatibility\Order as WCX_Order;
use WPO\WC\PDF_Invoices\Compatibility\Product as WCX_Product;


if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'WC_Email_PDF_Order_Notification' ) ) :

/**
 * Order Notification
 *
 * An email sent to the customer via admin.
 *
 * @class 		WC_Email_PDF_Order_Notification
 * @author 		WP Overnight
 * @extends 	WC_Email
 */
class WC_Email_PDF_Order_Notification extends WC_Email {

	var $find;
	var $replace;

	/**
	 * Constructor
	 */
	function __construct() {

		$this->id             = 'pdf_order_notification';
		$this->title          = __( 'Order Notification', 'wpo_wcpdf_pro' );
		$this->description    = __( 'Order Notification emails can be sent to specified email addresses, automatically & manually.', 'wpo_wcpdf_pro' );

		$this->template_html  = 'emails/pdf-order-notification.php';
		$this->template_plain = 'emails/plain/pdf-order-notification.php';
		$this->template_base  = trailingslashit( dirname(__FILE__) );

		$this->subject        = __( 'Order Notification for order {order_number} from {order_date}', 'wpo_wcpdf_pro');
		$this->heading        = __( 'Order Notification for order {order_number}', 'wpo_wcpdf_pro');
		$this->body           = __( 'An order has been placed.', 'wpo_wcpdf_pro');

		// Trigger according to settings
		$trigger = $this->get_option( 'trigger' );
		switch ($trigger) {
			case 'new_order':
				add_action( 'woocommerce_order_status_pending_to_processing_notification', array( $this, 'trigger' ), 10, 2 );
				add_action( 'woocommerce_order_status_pending_to_completed_notification', array( $this, 'trigger' ), 10, 2 );
				add_action( 'woocommerce_order_status_pending_to_on-hold_notification', array( $this, 'trigger' ), 10, 2 );
				add_action( 'woocommerce_order_status_failed_to_processing_notification', array( $this, 'trigger' ), 10, 2 );
				add_action( 'woocommerce_order_status_failed_to_completed_notification', array( $this, 'trigger' ), 10, 2 );
				add_action( 'woocommerce_order_status_failed_to_on-hold_notification', array( $this, 'trigger' ), 10, 2 );
				// WooCommerce Order Proposal compatibility
				add_action( 'woocommerce_order_status_order-proposal_to_processing_notification', array( $this, 'trigger' ), 10, 2 );
				add_action( 'woocommerce_order_status_order-proposal_to_completed_notification', array( $this, 'trigger' ), 10, 2 );
				add_action( 'woocommerce_order_status_order-proposal_to_on-hold_notification', array( $this, 'trigger' ), 10, 2 );
				break;
			case 'processing':
				add_action( 'woocommerce_order_status_processing_notification', array( $this, 'trigger' ), 10, 2 );
				break;
			case 'completed':
				add_action( 'woocommerce_order_status_completed_notification', array( $this, 'trigger' ), 10, 2 );
				// WooCommerce Subscriptions compatibility
				add_action( 'woocommerce_order_status_completed_renewal_notification', array( $this, 'trigger' ), 10, 1 );
				add_action( 'woocommerce_subscriptions_switch_completed_switch_notification', array( $this, 'trigger' ), 10, 1 );
				break;
			case 'paid':
				add_action( 'woocommerce_payment_complete_notification', array( $this, 'trigger' ), 10, 1 );
				break;
			case 'refunded':
				add_action( 'woocommerce_order_fully_refunded_notification', array( $this, 'trigger' ), 10, 2 );
				add_action( 'woocommerce_order_partially_refunded_notification', array( $this, 'trigger' ), 10, 2 );
				break;
		}

		// hook for custom triggers
		add_action( 'wpo_wcpdf_pro_send_order_notification', array( $this, 'trigger' ), 10, 2 );

		// Call parent constructor
		parent::__construct();

		$this->subject        = $this->get_option( 'subject', $this->subject );
		$this->heading        = $this->get_option( 'heading', $this->heading );
		$this->body           = $this->get_option( 'body', $this->body );
	}

	/**
	 * trigger function.
	 *
	 * @access public
	 * @return void
	 */
	function trigger( $order_id, $order = false  ) {
		if ( $order_id && ! is_a( $order, 'WC_Order' ) ) {
			$order = wc_get_order( $order_id );
		}

		if ( $order ) {
			$this->object                  = $order;
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
		return apply_filters( 'woocommerce_email_subject_pdf_order_notification', $this->format_string( $this->subject ), $this->object );
	}

	/**
	 * get_heading function.
	 *
	 * @access public
	 * @return string
	 */
	function get_heading() {
		return apply_filters( 'woocommerce_email_heading_pdf_order_notification', $this->format_string( $this->heading ), $this->object );
	}

	/**
	 * get_body function.
	 *
	 * @access public
	 * @return string
	 */
	function get_body() {
		return apply_filters( 'woocommerce_email_body_pdf_order_notification', $this->format_string( $this->body ), $this->object );
	}

	/**
	 * get_content_html function.
	 *
	 * @access public
	 * @return string
	 */
	function get_content_html() {
		if ( $this->get_option( 'empty_body' ) == 'yes' ) {
			return "";
		} else {
			ob_start();
			wc_get_template(
				$this->template_html,
				array(
					'order'						=> $this->object,
					'email_heading'				=> $this->get_heading(),
					'email_body'				=> $this->get_body(),
					'sent_to_admin'				=> false,
					'plain_text'				=> false,
					'include_items_table'		=> $this->get_option( 'items_table' ),
					'include_customer_details'	=> $this->get_option( 'customer_details' ),
					'email'						=> $this,
				), '',
				$this->template_base
			);
			return ob_get_clean();
		}
	}

	/**
	 * get_content_plain function.
	 *
	 * @access public
	 * @return string
	 */
	function get_content_plain() {
		if ( $this->get_option( 'empty_body' ) == 'yes' ) {
			return "";
		} else {
			ob_start();
			wc_get_template(
				$this->template_plain,
				array(
					'order' 		=> $this->object,
					'email_heading' => $this->get_heading(),
					'email_body'    => $this->get_body(),
					'sent_to_admin' => false,
					'plain_text'    => true,
					'include_items_table'		=> $this->get_option( 'items_table' ),
					'include_customer_details'	=> $this->get_option( 'customer_details' )
				), '',
				$this->template_base
			);
			return ob_get_clean();
		}
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
				'title' 		=> __( 'Trigger', 'wpo_wcpdf_pro' ),
				'type' 			=> 'select',
				'description' 	=> __( "Choose the status that should trigger this email. Note that the 'Paid' status only works for automated payment gateways (Paypal, Stripe, etc), not for BACS, COD & Cheque.", 'wpo_wcpdf_pro' ),
				'default' 		=> 'none',
				'class'			=> 'trigger',
				'options'		=> array(
					'none' 			=> __( 'Manual', 'wpo_wcpdf_pro' ),
					'new_order' 	=> __( 'Order placed', 'wpo_wcpdf_pro' ),
					'processing' 	=> __( 'Order processing', 'wpo_wcpdf_pro' ),
					'completed' 	=> __( 'Order completed', 'wpo_wcpdf_pro' ),
					'paid' 			=> __( 'Order paid', 'wpo_wcpdf_pro' ),
					'refunded' 		=> __( 'Order refunded', 'wpo_wcpdf_pro' ),
				)
			),
			'recipient' => array(
				'title' 		=> __( 'Recipient(s)', 'wpo_wcpdf_pro' ),
				'type' 			=> 'text',
				'description' 	=> __( 'Enter recipients (comma separated) for this email. Use {customer} to send this email to the customer.', 'wpo_wcpdf_pro' ),
				'placeholder' 	=> '',
				'default' 		=> ''
			),
			'subject' => array(
				'title' 		=> __( 'Email subject', 'wpo_wcpdf_pro' ),
				'type' 			=> 'text',
				'description' 	=> sprintf( __( 'Defaults to <code>%s</code>', 'wpo_wcpdf_pro' ), $this->subject ),
				'placeholder' 	=> '',
				'default' 		=> ''
			),
			'empty_body' => array(
				'title' 		=> __( 'Empty body', 'wpo_wcpdf_pro' ),
				'type'          => 'checkbox',
				'label'         => __( "Don't include any text/html in the email body", 'wpo_wcpdf_pro' ),
				'default'       => 'no'
			),
			'heading' => array(
				'title' 		=> __( 'Email heading', 'wpo_wcpdf_pro' ),
				'type' 			=> 'text',
				'description' 	=> sprintf( __( 'Defaults to <code>%s</code>', 'wpo_wcpdf_pro' ), $this->heading ),
				'placeholder' 	=> '',
				'default' 		=> '',
				'custom_attributes' => array( 'data-body_only' => 'yes' ),
			),
			'body' => array(
				'title' 		=> __( 'Email body text', 'wpo_wcpdf_pro' ),
				'css' 			=> 'width:100%; height: 75px;',
				'type' 			=> 'textarea',
				'description' 	=> sprintf( __( 'Defaults to <code>%s</code>', 'wpo_wcpdf_pro' ), $this->body ),
				'placeholder' 	=> '',
				'default' 		=> $this->body,
				'custom_attributes' => array( 'data-body_only' => 'yes' ),
			),
			'items_table' => array(
				'title' 		=> __( 'Order items', 'wpo_wcpdf_pro' ),
				'type'          => 'checkbox',
				'label'         => __( 'Include order items table in email', 'wpo_wcpdf_pro' ),
				'default'       => 'yes',
				'custom_attributes' => array( 'data-body_only' => 'yes' ),
			),
			'customer_details' => array(
				'title' 		=> __( 'Customer details', 'wpo_wcpdf_pro' ),
				'type'          => 'checkbox',
				'label'         => __( 'Include customer details in email', 'wpo_wcpdf_pro' ),
				'default'       => 'yes',
				'custom_attributes' => array( 'data-body_only' => 'yes' ),
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

	function is_enabled() {
		return apply_filters( 'woocommerce_email_enabled_' . $this->id, $this->is_automatic(), $this->object );
	}

	function is_manual() {
		return !$this->is_automatic();
	}

	function is_automatic()	{
		$trigger = $this->get_option( 'trigger' );
		return isset($trigger) && $trigger != 'none';
	}

	function is_customer_email() {
		$recipient = $this->get_option( 'recipient' );
		return $recipient == '{customer}';
	}

	function get_recipient() {
		if (!empty($this->object)) {
			$recipient = str_replace('{customer}', WCX_Order::get_prop( $this->object, 'billing_email', 'view' ), $this->get_option( 'recipient' ) );
		} else {
			$recipient = $this->get_option( 'recipient' );
		}
		$recipient  = apply_filters( 'woocommerce_email_recipient_' . $this->id, $recipient, $this->object, $this );
		$recipients = array_map( 'trim', explode( ',', $recipient ) );
		$recipients = array_filter( $recipients, 'is_email' );
		return implode( ', ', $recipients );
	}
}

endif;

return new WC_Email_PDF_Order_Notification();
