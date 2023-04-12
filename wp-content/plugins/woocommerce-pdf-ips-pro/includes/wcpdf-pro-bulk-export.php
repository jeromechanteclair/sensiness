<?php
namespace WPO\WC\PDF_Invoices_Pro;

use WPO\WC\PDF_Invoices_Pro\Cloud\Cloud_API;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( !class_exists( '\\WPO\\WC\\PDF_Invoices_Pro\\Bulk_Export' ) ) :

class Bulk_Export {
	public function __construct() {
		// hook into main pdf plugin settings
		add_filter( 'wpo_wcpdf_settings_tabs', array( $this, 'settings_tab' ) );

		add_action( 'admin_enqueue_scripts', array( $this, 'load_scripts_styles' ) ); // Load scripts & styles

		// bulk export page
		add_action( 'wpo_wcpdf_after_settings_page', array( $this, 'bulk_export_tab' ), 10, 1 );

		// Bulk export ajax actions
		add_action( 'wp_ajax_wpo_wcpdf_export_get_order_ids', array($this, 'ajax_get_order_ids' ));
		add_action( 'wp_ajax_wpo_wcpdf_export_bulk', array($this, 'save_bulk' ));
		add_action( 'wp_ajax_wpo_wcpdf_zip_bulk', array($this, 'zip_bulk' ));

		// query vars for get_orders by document date
		add_filter( 'woocommerce_order_data_store_cpt_get_orders_query', array( $this, 'document_date_query_vars' ), 10, 3 );
	}

	/**
	 * add Bulk Export settings tab to the PDF Invoice settings page
	 * @param  array $tabs slug => Title
	 * @return array $tabs with Bulk Export
	 */
	public function settings_tab( $tabs ) {
		// if (WPO_WCPDF_Dropbox()->api->is_enabled() !== false) {
			$tabs['bulk_export'] = __('Bulk export','wpo_wcpdf_pro');
		// }

		return $tabs;
	}

	/**
	 * Scrips & styles for settings page
	 */
	public function load_scripts_styles($hook) {
		$tab = isset($_GET['tab']) ? $_GET['tab'] : '';
		$page = isset($_GET['page']) ? $_GET['page'] : '';
		if( $page != 'wpo_wcpdf_options_page' || $tab != 'bulk_export') {
			return;
		}

		wp_enqueue_style(
			'woocommerce-pdf-ips-pro-jquery-ui-style',
			'https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css'
		);
		wp_enqueue_script(
			'woocommerce-pdf-pro-bulk',
			plugins_url( 'js/pro-bulk-export.js' , dirname(__FILE__) ),
			array( 'jquery', 'jquery-ui-datepicker' ),
			WPO_WCPDF_PRO_VERSION
		);
		wp_localize_script(
			'woocommerce-pdf-pro-bulk',
			'woocommerce_pdf_pro_bulk',
			array(
				'ajax_url'	=> admin_url( 'admin-ajax.php' ),
				'nonce'		=> wp_create_nonce('wpo_wcpdf_pro_bulk'),
			)
		);

	}

	public function bulk_export_tab($tab) {
		if ( $tab == 'bulk_export' ) {
			if( ! is_null( WPO_WCPDF_Pro()->cloud_api ) ) {
				$cloud_api_is_enabled = Cloud_API::is_enabled();
				$cloud_service_name = Cloud_API::$service_name;
				$cloud_service_slug = Cloud_API::service_enabled();
			}
			
			include( WPO_WCPDF_Pro()->plugin_path() . '/includes/views/bulk-export.php' );
		}
	}

	/**
	 * Handle AJAX request
	 */
	public function ajax_get_order_ids() {
		check_ajax_referer( 'wpo_wcpdf_pro_bulk', 'security' );

		if ( !isset($_POST['status_filter']) && !(isset($_POST['date_type']) && $_POST['date_type'] == 'document_date') ) {
			$return = array(
				'error'	=> __('No orders found!', 'wpo_wcpdf_pro'),
				'posted'=> var_export($_POST,true),
			);
			echo json_encode($return);
			exit();
		}

		// get in utc timestamp for WC3.1+
		$utc_timestamp = version_compare( WOOCOMMERCE_VERSION, '3.1', '>=' ) ? true : false;
		// get dates from input
		$args = array(
			'date_after'    => $this->get_date_string_from_input( 'date_from', 'hour_from', 'minute_from', false, $utc_timestamp ),
			'date_before'   => $this->get_date_string_from_input( 'date_to', 'hour_to', 'minute_to', true, $utc_timestamp ),
			'date_type'     => $_POST['date_type'],
			'document_type' => $_POST['document_type'],
			'statuses'      => $_POST['status_filter'],
		);


		$order_ids = $this->get_orders_by_status( $args );

		if (empty($order_ids)) {
			$return = array(
				'error'	=> __('No orders found!', 'wpo_wcpdf_pro'),
			);
			echo json_encode($return);
			exit();
		} else {
			echo json_encode(array_values($order_ids));
			exit();
		}
	}

	public function save_bulk() {
		check_ajax_referer( 'wpo_wcpdf_pro_bulk', 'security' );
		if (empty($_POST['order_ids'])) {
			$return = array(
				'error'	=> __('No orders found!', 'wpo_wcpdf_pro'),
			);
			echo json_encode($return);
			exit();
		}

		$order_ids = $_POST['order_ids'];
		$template_type = $_POST['template_type'];
		$kip_free = isset( $_POST['skip_free'] ) && $_POST['skip_free'] == 'true' ? true : false;
		$only_existing = isset( $_POST['only_existing'] ) && $_POST['only_existing'] == 'true' ? true : false;

		// Allows an external bulk handler to hook in here, before any of the
		// logic below is being executed, effectively short circuiting the routine
		do_action( 'wpo_wcpdf_export_bulk_save_bulk_handler', [
			'order_ids' => $order_ids,
			'template_type' => $template_type,
			'skip_free' => $kip_free,
			'only_existing' => $only_existing
		]);

		// create transient with file list of this export
		// @TODO: use unique transient name to allow parallel downloads
		$transient = "wpo_wcpdf_bulk_export_files_{$template_type}";
		$filelist = get_transient( $transient );
		if ( !is_array( $filelist ) ) {
			$filelist = array();
		}

		$return = array();
		$success = array();
		$errors = array();

		// turn off deprecation notices during bulk creation
		add_filter( 'wcpdf_disable_deprecation_notices', '__return_true' );

		foreach ($order_ids as $order_id) {
			// create pdf
			$order = wc_get_order( $order_id );
			// check skip free setting
			if ( $kip_free && method_exists( $order, 'get_total' ) && $order->get_total() == 0 ) {
				continue;
			}

			// check only existing setting
			if ($only_existing) {
				$document = wcpdf_get_document( $template_type, $order );
				if ( $document && $document->exists() === false ) {
					continue;
				}
			} else {
				$document = wcpdf_get_document( $template_type, $order, true );
			}

			if ( !$document ) {
				continue;
			}

			// try to create the PDF
			try {
				$pdf_path = $this->create_pdf_file( $document );
			// catch any errors that might could happen
			} catch ( \Exception $e ) {
				wcpdf_log_error( $e->getMessage(), 'critical', $e );
				continue;
			} catch ( \Dompdf\Exception $e ) {
				wcpdf_log_error( 'DOMPDF exception: '.$e->getMessage(), 'critical', $e );
				continue;
			} catch ( \Error $e ) {
				wcpdf_log_error( $e->getMessage(), 'critical', $e );
				continue;
			}

			if ($_POST['export_mode'] == 'cloud_service') {
				// initiate object
				$cloud_storage = new Cloud_Storage;
				// upload file to cloud service
				$upload_response = $cloud_storage->upload_to_service( $pdf_path, 'export' );

				if ( !empty( $upload_response['error'] ) ) {
					// Houston, we have a problem
					$errors[$order_id] = $upload_response['error'];
				} else {
					$filelist[] = $success[$order_id] = $pdf_path;
				}
			} else {
				$filelist[] = $success[$order_id] = $pdf_path;
			}
		}

		set_transient( $transient, $filelist, DAY_IN_SECONDS );
		// re-enable deprecation notices
		remove_filter( 'wcpdf_disable_deprecation_notices', '__return_true' );

		$return['success'] = $success;
		$return['transient'] = $transient;
		$return['filename'] = sanitize_file_name( $template_type.'.zip' );

		echo json_encode($return);
		exit();
	}

	public function create_pdf_file ( $document ) {
		$tmp_path = trailingslashit( WPO_WCPDF()->main->get_tmp_path('attachments') );

		// get pdf data & filename
		$pdf_data = $document->get_pdf();
		$pdf_filename = $document->get_filename();

		$pdf_path = $tmp_path . $pdf_filename;

		// save file
		file_put_contents ( $pdf_path, $pdf_data );

		return $pdf_path;
	}

	public function zip_bulk() {
		check_ajax_referer( 'wpo_wcpdf_pro_bulk', 'security' );

		@set_time_limit(0);

		if ( isset( $_GET['transient'] ) ) {
			$filelist = get_transient( $_GET['transient'] );
			delete_transient( $_GET['transient'] );
		} else {
			// legacy method using filelist from postdata
			$filelist = $_POST['files'];
			if (is_string($filelist) && strpos($filelist, '[') !== false ) {
				$filelist = json_decode(stripslashes($filelist));
			}
		}

		do_action( 'wpo_wcpdf_export_bulk_save_bulk_download', array(
			'filelist'      => $filelist,
			'template_type' => $_REQUEST['template_type'],
		) );

		$template_type = $_REQUEST['template_type'];
		$filename = sanitize_file_name( $template_type.'.zip' );

		try {
			if ( $zipfile = $this->create_zip( $filelist, $filename ) ) {
				if (headers_sent()) {
					echo 'HTTP header already sent';
				} else {
					if (function_exists('apache_setenv')) {
						apache_setenv('no-gzip', 1);
						apache_setenv('dont-vary', 1);
					}
					$output_compression = ini_get('zlib.output_compression');
					if ( $output_compression && $output_compression !== 'off') {
						$set_output_compression = ini_set('zlib.output_compression', 0);
						if ( $output_compression && $output_compression !== 'off' ) {
							throw new \Exception('zlib.output_compression needs to be turned off in PHP to create a zip file');
						}
					}
					ob_clean();
					ob_end_flush();
					header('Content-Description: File Transfer');
					header('Content-Type: application/x-zip');
					header('Content-Disposition: attachment; filename="'.$filename.'"');
					header('Content-Transfer-Encoding: binary');
					header('Connection: Keep-Alive');
					header('Expires: 0');
					header('Cache-Control: no-cache, must-revalidate, post-check=0, pre-check=0');
					header('Pragma: public');
					@readfile($zipfile);
					@unlink($zipfile); // destroy after reading
				}
			}	
		} catch (\Exception $e) {
			wcpdf_log_error( $e->getMessage(), 'critical' );
			echo $e->getMessage();
		}
		exit;
	}

	public function check_zip_archive() {
		if (!class_exists('\\ZipArchive')) {
			return false;
		} else {
			return true;
		}
	}

	public function create_zip($filelist, $zip_filename) {
		$zip = new \ZipArchive();
		$tmp_path = trailingslashit( WPO_WCPDF()->main->get_tmp_path('attachments') );
		@unlink($tmp_path . $zip_filename);
		if ($zip->open($tmp_path . $zip_filename, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== TRUE) {
			throw new \Exception('An error occurred creating your ZIP file.');
		}

		foreach ($filelist as $filepath) {
			if (is_readable($filepath)) {
				$add_result = $zip->addFile( $filepath, basename($filepath) );
			}
		}

		$closed = $zip->close();
		if ( $closed === true ) {
			return $tmp_path . $zip_filename;
		} else {
			throw new \Exception('ZIP file could not be saved.');
		}
	}

	public function get_orders_by_status ( $export_args ) {
		$args = array(
			'status'	=> $export_args['statuses'],
			'return'	=> 'ids',
			'type'		=> 'shop_order',
			'limit'		=> -1,
		);

		if ( isset($export_args['date_type']) && $export_args['date_type'] == 'document_date' ) {
			if ( $export_args['document_type'] == 'credit-note' ) {
				$args['type'] = 'shop_order_refund';
			}
			$document_slug = str_replace('-', '_', $export_args['document_type']); // querying documents functions may be more reliable but this works fine and prevents issues with UBL export
			$date_arg = "wcpdf_{$document_slug}_date";
		} else {
			$date_arg = 'date_created';
		}

		// for code readability
		$date_before = $export_args['date_before'];
		$date_after = $export_args['date_after'];

		if ( version_compare( WOOCOMMERCE_VERSION, '3.1', '>=' ) ) {
			// WC3.1+
			if ( $date_after && !$date_before ) {
				// after date
				$args[$date_arg] = '>='.$date_after;
			} elseif ( $date_before ) {
				if (!$date_after) {
					// before date
					$args[$date_arg] = '<='.$date_before;
				} else {
					// between dates
					$args[$date_arg] = $date_after.'...'.$date_before;
				}
			}
		} else {
			// WC3.0
			if( $date_after ) {
				$args['date_after'] = $date_after;
			}
			if( $date_before ) {
				$args['date_before'] = $date_before;
			}

		}

		// Allow 3rd parties to alter the arguments used to fetch the order IDs
		// @author Aelia
		$args = apply_filters( 'wpo_wcpdf_export_bulk_get_orders_args', $args );

		$order_ids = wc_get_orders( $args );

		// Allow 3rd parties to alter the list of order IDs returned by the query
		// @author Aelia
		$order_ids = apply_filters( 'wpo_wcpdf_export_bulk_order_ids', $order_ids, $args);

		// sort ids
		asort($order_ids);

		return $order_ids;
	}

	public function get_date_string_from_input( $date_key, $hour_key, $minute_key, $include_minute = false, $utc_timestamp = false ) {
		$date = filter_input( INPUT_POST, $date_key, FILTER_SANITIZE_STRING );
		$hour = filter_input( INPUT_POST, $hour_key, FILTER_SANITIZE_STRING );
		$minute = filter_input( INPUT_POST, $minute_key, FILTER_SANITIZE_STRING );

		if (empty($date)) {
			return false;
		}

		if( $date_key == 'date_to' && ! is_null( WPO_WCPDF_Pro()->cloud_api ) ) {
			// store last export date & time
			update_option( 'wpo_wcpdf_'.Cloud_API::service_enabled().'_last_export', array('date'=>$date,'hour'=>$hour,'minute'=>$minute) );
		}

		if (!empty($hour)) {
			$seconds = $include_minute ? '59' : '00';
			$date = sprintf("%s %02d:%02d:%02d", $date, $hour, $minute, $seconds);
		}

		if ($utc_timestamp) {
			// Convert local WP timezone to UTC.
			if ( 1 === preg_match( '/^(\d{4})-(\d{2})-(\d{2})T(\d{2}):(\d{2}):(\d{2})(Z|((-|\+)\d{2}:\d{2}))$/', $date, $date_bits ) ) {
				$offset    = ! empty( $date_bits[7] ) ? iso8601_timezone_to_offset( $date_bits[7] ) : wc_timezone_offset();
				$timestamp = gmmktime( $date_bits[4], $date_bits[5], $date_bits[6], $date_bits[2], $date_bits[3], $date_bits[1] ) - $offset;
			} else {
				$timestamp = wc_string_to_timestamp( get_gmt_from_date( gmdate( 'Y-m-d H:i:s', wc_string_to_timestamp( $date ) ) ) );
			}
			$date = $timestamp;
		}

		return $date;
	}

	public function document_date_query_vars( $wp_query_args, $query_vars, $order_store_cpt ) {
		foreach ( WPO_WCPDF()->documents->get_documents() as $document ) {
			if ( isset( $query_vars[ "wcpdf_{$document->slug}_date" ] ) && '' !== $query_vars[ "wcpdf_{$document->slug}_date" ] ) {
				$wp_query_args = $order_store_cpt->parse_date_for_wp_query( $query_vars[ "wcpdf_{$document->slug}_date" ], "_wcpdf_{$document->slug}_date", $wp_query_args );
			}
		}

		return $wp_query_args;
	}

} // end class

endif; // end class_exists

return new Bulk_Export();
