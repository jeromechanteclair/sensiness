<?php
namespace WPO\WC\PDF_Invoices_Pro;

use WPO\WC\PDF_Invoices\Compatibility\WC_Core as WCX;
use WPO\WC\PDF_Invoices\Compatibility\Order as WCX_Order;
use WPO\WC\PDF_Invoices\Compatibility\Product as WCX_Product;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( !class_exists( '\\WPO\\WC\\PDF_Invoices_Pro\\Emails' ) ) :

class Emails {
	public function __construct() {
		add_filter( 'woocommerce_email_classes', array( $this, 'add_emails' ) );
		$this->email_actions = array (
			'woocommerce_order_status_processing',
			'woocommerce_payment_complete',
		);
		// register status actions to make sure triggers are pulled!
		$this->register_email_actions();

		add_action( 'admin_enqueue_scripts', array( $this, 'email_settings_scripts_styles' ), 10, 1 );
		add_filter( 'wp_mail', array( $this, 'phpmailer_allow_empty'), 20, 1 );
	}

	/**
	 * Register email actions (backwards compatible with WC 2.2 & 2.1)
	 *
	 * @access public
	 * @return void
	 */
	public function register_email_actions () {
		if ( version_compare( WOOCOMMERCE_VERSION, '2.3', '>=' ) ) {
			// use filter when possible
			add_filter( 'woocommerce_email_actions', array( $this, 'woocommerce_email_actions' ), 10, 1 );
		} else {
			// backwards compatible method
			global $woocommerce;
			foreach ( $this->email_actions as $action ) {
				add_action( $action, array( $woocommerce, 'send_transactional_email' ), 10, 10 );
			}

		}
	}

	/**
	 * Add email actions.
	 *
	 * @access public
	 * @return $email_actions
	 */
	public function woocommerce_email_actions ( $email_actions ) {
		return array_merge($email_actions, $this->email_actions);
	}

	public function add_emails ( $email_classes ) {
		// add our custom email classes to the list of email classes that WooCommerce loads
		if ( version_compare( WOOCOMMERCE_VERSION, '2.2', '>=' ) && !isset( $email_classes['WC_Email_Customer_Credit_Note'] ) ) {
			$email_classes['WC_Email_Customer_Credit_Note'] = include( 'email-customer-credit-note.php' );
		}
		if ( !isset( $email_classes['WC_Email_PDF_Order_Notification'] ) ) {
			$email_classes['WC_Email_PDF_Order_Notification'] = include( 'email-pdf-order-notification.php' );
		}
		return $email_classes;
	}

	public function email_settings_scripts_styles( $hook ) {
		if ( !isset($_GET['page']) || !isset($_GET['tab']) || !isset($_GET['section']) ) {
			return;
		}

		if ( $_GET['page'] == 'wc-settings' && $_GET['tab'] == 'email' && $_GET['section'] == 'wc_email_pdf_order_notification' ) {
			wp_enqueue_script(
				'wcpdf-pro-email-settings',
				plugins_url( 'js/pro-email-settings.js', dirname(__FILE__) ),
				array(),
				WPO_WCPDF_PRO_VERSION
			);
		}
	}

	/**
	 * PHP Mailer does not allow empty messages by default, but we offer the option to send the Order Notification with an empty body
	 */
	public function phpmailer_allow_empty( $mailArray ) {
		if ( empty( $mailArray['message'] ) ) {
			global $phpmailer;
			$wp_pre_55 = version_compare( get_bloginfo( 'version' ), '5.5-dev', '<' );
			if ( $wp_pre_55 && ! ( $phpmailer instanceof \PHPMailer ) ) {
				require_once ABSPATH . WPINC . '/class-phpmailer.php';
				require_once ABSPATH . WPINC . '/class-smtp.php';
				$phpmailer = new \PHPMailer( true );
			} elseif ( ! $wp_pre_55 && ! ( $phpmailer instanceof \PHPMailer\PHPMailer\PHPMailer ) ) {
				require_once ABSPATH . WPINC . '/PHPMailer/PHPMailer.php';
				require_once ABSPATH . WPINC . '/PHPMailer/SMTP.php';
				require_once ABSPATH . WPINC . '/PHPMailer/Exception.php';
				$phpmailer = new \PHPMailer\PHPMailer\PHPMailer( true );
			}

			$phpmailer->AllowEmpty = true;
		}

		return $mailArray;
	}

} // end class

endif; // end class_exists

return new Emails();