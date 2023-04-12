<?php
namespace WPO\WC\PDF_Invoices_Pro;

use WooCommerce_PDF_Invoices;
use WPO\WC\PDF_Invoices_Pro\Cloud\Cloud_API;
use WPO\WC\PDF_Invoices_Pro\Cloud\Dropbox\Dropbox_API;
use WPO\WC\PDF_Invoices_Pro\Cloud\Gdrive\Gdrive_API;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( !class_exists( '\\WPO\\WC\\PDF_Invoices_Pro\\Cloud_Storage' ) ) :

/**
 * Cloud Storage Class
 * 
 * @class       \WPO\WC\PDF_Invoices_Pro\Cloud\Gdrive\Gdrive_API
 * @version     1.0
 * @category    Class
 * @author      Alexandre Faustino
 */

class Cloud_Storage {

	public $settings_name = 'cloud_storage_settings';
	public $settings_option = 'wpo_wcpdf_cloud_storage_settings';
	public $cloud_services;

	/**
	 * Construct
	 * 
	 * @return	void
	 */
	public function __construct() {
		// Registers settings
		add_action( 'admin_init', array( $this, 'init_settings' ) );
		// hook into main pdf plugin settings
		add_filter( 'wpo_wcpdf_settings_tabs', array( $this, 'settings_tab' ) );
		// add unlink button
		add_action( 'wpo_wcpdf_after_settings_page', array( $this, 'unlink' ), 10, 1 );

		// Get cloud services
		$this->cloud_services = array();
		foreach( Cloud_API::available_cloud_services() as $cloud_service ) {
			if( $cloud_service['active'] === true ) {
				$this->cloud_services[$cloud_service['slug']] = $cloud_service['name'];
			}
		}

		add_action( 'wpo_wcpdf_email_attachment', array( $this, 'upload_attachment'), 10, 3 );
		add_action( 'woocommerce_order_status_changed', array( $this, 'upload_by_status'), 10, 4 );
		add_action( 'load-edit.php', array($this, 'bulk_export') );
		add_action( 'load-edit.php', array($this, 'export_queue') );

		// Enqueue scripts
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		// Export bulk actions
		add_action(	'admin_footer', array( $this, 'export_actions' ) );
		// Upload queue
		add_action( 'admin_notices', array( $this, 'upload_queue' ) );
	}

	/**
	 * Add Cloud Storage settings tab to the PDF Invoice settings page
	 * @param  array $tabs slug => Title
	 * @return array $tabs with Cloud Storage
	 */
	public function settings_tab( $tabs ) {
		if (count($this->cloud_services) == 1) {
			$tabs['cloud_storage'] = array_pop($this->cloud_services);
		} else {
			$tabs['cloud_storage'] = __('Cloud storage', 'wpo_wcpdf_pro');
		}
		return $tabs;
	}

	/**
	 * General cloud storage settings define by the user
	 * 
	 * @return	void
	 */
	public function init_settings() {	
		// Create option in wp_options.
		if ( false == get_option( $this->settings_option ) ) {
			add_option( $this->settings_option );
		}
	
		// Section.
		add_settings_section(
			$this->settings_name,
			__( 'Cloud storage settings', 'wpo_wcpdf_pro' ),
			array( &$this, 'section_options_callback' ),
			$this->settings_option
		);

		add_settings_field(
			'enabled',
			__( 'Enable', 'wpo_wcpdf_pro' ),
			array( &$this, 'checkbox_element_callback' ),
			$this->settings_option,
			$this->settings_name,
			array(
				'menu'			=> $this->settings_option,
				'id'			=> 'enabled',
			)
		);

		add_settings_field(
			'cloud_service',
			__( 'Cloud service', 'wpo_wcpdf_pro' ),
			array( &$this, 'select_element_callback' ),
			$this->settings_option,
			$this->settings_name,
			array(
				'menu'			=> $this->settings_option,
				'id'			=> 'cloud_service',
				'options' 		=> $this->cloud_services,
			)
		);

		add_settings_field(
			'auto_upload',
			__( 'Upload all email attachments', 'wpo_wcpdf_pro' ),
			array( &$this, 'checkbox_element_callback' ),
			$this->settings_option,
			$this->settings_name,
			array(
				'menu'			=> $this->settings_option,
				'id'			=> 'auto_upload',
			)
		);

		// prepare data for per status upload settings
		$order_statuses = array( '-' => '-' ) + $this->get_order_statuses();
		$documents = $this->get_pdf_documents();
		$per_status_upload_items = array();
		foreach ($documents as $template_type => $name) {
			$per_status_upload_items[$template_type] = array(
				'name'			=> $name,
				'options'		=> $order_statuses,
			);
		}

		add_settings_field(
			'per_status_upload',
			__( 'Upload by order status', 'wpo_wcpdf_pro' ),
			array( &$this, 'multiple_select_callback' ),
			$this->settings_option,
			$this->settings_name,
			array(
				'menu'			=> $this->settings_option,
				'id'			=> 'per_status_upload',
				'items'			=> $per_status_upload_items,
				'description'	=> __( 'If you are already emailing the documents, leave these settings empty to avoid slowing down your site (use the setting above instead)', 'wpo_wcpdf_pro' ),
			)
		);			

		add_settings_field(
			'access_type',
			__( 'Destination folder', 'wpo_wcpdf_pro' ),
			array( &$this, 'select_element_callback' ),
			$this->settings_option,
			$this->settings_name,
			array(
				'menu'			=> $this->settings_option,
				'id'			=> 'access_type',
				'options' 		=> array(
					'app_folder'		=> __( 'App folder (restricted access)' , 'wpo_wcpdf_pro' ),
					'root_folder'		=> __( 'Main cloud service folder' , 'wpo_wcpdf_pro' ),
				),
				'description'	=> __( 'Note: Reauthorization is required after changing this setting!' , 'wpo_wcpdf_pro' ),
				'custom'		=> array(
					'type'			=> 'text_element_callback',
					'custom_option'	=> 'root_folder',
					'args'			=> array(
						'menu'			=> $this->settings_option,
						'id'			=> 'destination_folder',
						'size'			=> '40',
						'description'	=> __( 'Enter a subfolder to use (optional)', 'wpo_wcpdf_pro' ),
					),
				),
			)
		);

		add_settings_field(
			'year_month_folders',
			__( 'Organize uploads in folders by year/month', 'wpo_wcpdf_pro' ),
			array( &$this, 'checkbox_element_callback' ),
			$this->settings_option,
			$this->settings_name,
			array(
				'menu'			=> $this->settings_option,
				'id'			=> 'year_month_folders',
			)
		);

		add_settings_field(
			'api_log',
			__( 'Log all communication (debugging only!)', 'wpo_wcpdf_pro' ),
			array( &$this, 'checkbox_element_callback' ),
			$this->settings_option,
			$this->settings_name,
			array(
				'menu'			=> $this->settings_option,
				'id'			=> 'api_log',
				'description'	=> '<a href="'.esc_url_raw( admin_url( 'admin.php?page=wc-status&tab=logs' ) ).'" target="_blank">'.__( 'View logs', 'wpo_wcpdf_pro' ).'</a>',
			)
		);

		// Register settings.
		register_setting( $this->settings_option, $this->settings_option, array( &$this, 'validate_options' ) );

		// Register defaults if settings empty (might not work in case there's only checkboxes and they're all disabled)
		$option_values = get_option($this->settings_option);
		if ( empty( $option_values ) ) {
			// $this->default_settings( 'wpo_wcpdf_dropbox_settings' );
		}
	}

	/**
	 * Button to unlink cloud service account
	 * 
	 * @return	void
	 */
	public function unlink( $tab ) {
		// check if enabled
		if ( Cloud_API::is_enabled() === false ) {
			return;
		}
		// remove API details if requested
		if ( isset($_REQUEST['wpo_wcpdf_unlink_'.Cloud_API::service_enabled()]) ) {
			delete_option( 'wpo_wcpdf_'.Cloud_API::service_enabled().'_api_settings', '' );
			wp_redirect( remove_query_arg( 'wpo_wcpdf_unlink_'.Cloud_API::service_enabled() ) );
			exit();
		}
		// display unlink button if we have an access token
		$service_api_settings = get_option( 'wpo_wcpdf_'.Cloud_API::service_enabled().'_api_settings' );
		if ( $tab =='cloud_storage' && isset($service_api_settings['access_token'])) {
			if ( !empty($service_api_settings['account_info']) ) {
				printf('<hr><div class="wcpdf-cloud-service-connection"><strong>&#10004; '.__('Connected to', 'wpo_wcpdf_pro').' '.Cloud_API::$service_name.':</strong> %s</div>', $service_api_settings['account_info']);
			}
			$unlink_url = add_query_arg( 'wpo_wcpdf_unlink_'.Cloud_API::service_enabled(), 'true' );
			printf('<a href="%s" class="button">'.__('Unlink %s account','wpo_wcpdf_pro').'</a>', $unlink_url, Cloud_API::$service_name );
		}
	}

	/**
	 * Get a list of WooCommerce order statuses (without the wc- prefix)
	 *
	 * @return  array status slug => status name
	 */
	public function get_order_statuses() {
		if ( version_compare( WOOCOMMERCE_VERSION, '2.2', '<' ) ) {
			$statuses = (array) get_terms( 'shop_order_status', array( 'hide_empty' => 0, 'orderby' => 'id' ) );
			foreach ( $statuses as $status ) {
				$order_statuses[esc_attr( $status->slug )] = esc_html__( $status->name, 'woocommerce' );
			}
		} else {
			$statuses = wc_get_order_statuses();
			foreach ( $statuses as $status_slug => $status ) {
				$status_slug   = 'wc-' === substr( $status_slug, 0, 3 ) ? substr( $status_slug, 3 ) : $status_slug;
				$order_statuses[$status_slug] = $status;
			}
		}

		return $order_statuses;
	}

	/**
	 * Get a list of PDF documents
	 *
	 * @return  array document slug => document name
	 */
	public function get_pdf_documents() {
		$documents = WPO_WCPDF()->documents->get_documents();
		$document_list = array();
		foreach ($documents as $document) {
			$document_list[$document->get_type()] = $document->get_title();
		}

		return $document_list;
	}

	/**
	 * Checkbox field callback.
	 *
	 * @param  array $args Field arguments.
	 *
	 * @return string	  Checkbox field.
	 */
	public function checkbox_element_callback( $args ) {
		$menu = $args['menu'];
		$id = $args['id'];
	
		$options = get_option( $menu );
	
		if ( isset( $options[$id] ) ) {
			$current = $options[$id];
		} else {
			$current = isset( $args['default'] ) ? $args['default'] : '';
		}
	
		$disabled = (isset( $args['disabled'] )) ? ' disabled' : '';
		$html = sprintf( '<input type="checkbox" id="%1$s" name="%2$s[%1$s]" value="1"%3$s %4$s/>', $id, $menu, checked( 1, $current, false ), $disabled );
	
		// Displays option description.
		if ( isset( $args['description'] ) ) {
			$html .= sprintf( '<p class="description">%s</p>', $args['description'] );
		}
	
		echo $html;
	}

	/**
	 * Text element callback.
	 * 
	 * @param  array $args Field arguments.
	 * @return string	   Text input field.
	 */
	public function text_element_callback( $args ) {
		$menu = $args['menu'];
		$id = $args['id'];
		$size = isset( $args['size'] ) ? $args['size'] : '25';
	
		$options = get_option( $menu );
	
		if ( isset( $options[$id] ) ) {
			$current = $options[$id];
		} else {
			$current = isset( $args['default'] ) ? $args['default'] : '';
		}
	
		$html = sprintf( '<input type="text" id="%1$s" name="%2$s[%1$s]" value="%3$s" size="%4$s"/>', $id, $menu, $current, $size );
	
		// Displays option description.
		if ( isset( $args['description'] ) ) {
			$html .= sprintf( '<p class="description">%s</p>', $args['description'] );
		}
	
		echo $html;
	}

	/**
	 * Select element callback.
	 *
	 * @param  array $args Field arguments.
	 *
	 * @return string	  Select field.
	 */
	public function select_element_callback( $args ) {
		$menu = $args['menu'];
		$id = $args['id'];
	
		$options = get_option( $menu );
	
		if ( isset( $options[$id] ) ) {
			$current = $options[$id];
		} else {
			$current = isset( $args['default'] ) ? $args['default'] : '';
		}
	
		printf( '<select id="%1$s" name="%2$s[%1$s]">', $id, $menu );

		foreach ( $args['options'] as $key => $label ) {
			printf( '<option value="%s"%s>%s</option>', $key, selected( $current, $key, false ), $label );
		}

		echo '</select>';

		if (isset($args['custom'])) {
			$custom = $args['custom'];

			?>
			<script type="text/javascript">
			jQuery(document).ready(function($) {
				$( '#<?php echo $id;?>' ).change(function() {
					var selection = $('#<?php echo $id;?>').val();
					// console.log(selection);
					if ( selection == '<?php echo $custom['custom_option'];?>' ) {
						$( '#<?php echo $id;?>_custom_wrapper' ).show();
					} else {
						$( '#<?php echo $id;?>_custom_wrapper' ).hide();
					}
				});
				$( '#<?php echo $id;?>' ).change();
			});
			</script>
			<div id="<?php echo $id;?>_custom_wrapper">
			<?php

			switch ($custom['type']) {
				case 'text_element_callback':
					$this->text_element_callback( $custom['args'] );
					break;	
				default:
					break;
			}
			echo '</div>';
		}
	
		// Displays option description.
		if ( isset( $args['description'] ) ) {
			printf( '<p class="description">%s</p>', $args['description'] );
		}
	}

	/**
	 * Multiple select element callback.
	 *
	 * @param  array $args Field arguments.
	 *
	 * @return string	  Select field.
	 */
	public function multiple_select_callback ( $args ) {
		$menu = $args['menu'];
		$id = $args['id'];
	
		$options = get_option( $menu );
		// echo '<pre>';var_dump($options);echo '</pre>';die();
	
		if ( isset( $options[$id] ) ) {
			$current = $options[$id];
		} else {
			$current = isset( $args['default'] ) ? $args['default'] : '';
		}


		echo '<table>';
		foreach ($args['items'] as $template_type => $document_setting) {
			extract($document_setting); // name, options

			printf( '<tr><td style="padding:0;">%1$s:</td><td style="padding:0;"><select id="%2$s" name="%2$s[%3$s][%4$s]">', $name, $menu, $id, $template_type );
	
			foreach ( $options as $key => $label ) {
				$current_selected = isset($current[$template_type])?$current[$template_type]:'';
				printf( '<option value="%s"%s>%s</option>', $key, selected( $current_selected, $key, false ), $label );
			}
	
			echo '</select></td></tr>';
		}
		echo '</table>';
	
		// Displays option description.
		if ( isset( $args['description'] ) ) {
			printf( '<p class="description">%s</p>', $args['description'] );
		}
	}

	/**
	 * Section null callback.
	 *
	 * @return void.
	 */
	public function section_options_callback() {}

	/**
	 * Validate options.
	 *
	 * @param  array $input options to valid.
	 *
	 * @return array		validated options.
	 */
	public function validate_options( $input ) {
		// Create our array for storing the validated options.
		$output = array();
	
		// Loop through each of the incoming options.
		foreach ( $input as $key => $value ) {
	
			// Check to see if the current option has a value. If so, process it.
			if ( isset( $input[$key] ) ) {
				if ( is_array( $input[$key] ) ) {
					foreach ( $input[$key] as $sub_key => $sub_value ) {
						$output[$key][$sub_key] = $input[$key][$sub_key];
					}
				} else {
					$output[$key] = $input[$key];
				}
			}
		}

		// get general settings
		$last_settings = get_option( $this->settings_option );

		// unlink app if access_type changed
		$last_access_type = isset($last_settings['access_type']) ? $last_settings['access_type'] : null;
		$new_access_type  = isset($input['access_type'])         ? $input['access_type']         : null;
		if ( ($last_access_type != $new_access_type) && isset($last_settings['cloud_service']) ) {
			delete_option( 'wpo_wcpdf_'.$last_settings['cloud_service'].'_api_settings' );
		}

		// Return the array processing any additional functions filtered by this action.
		return apply_filters( 'wpo_wcpdf_cloud_storage_validate_input', $output, $input );
	}

	/**
	 * Upload PDF to cloud service during/after email attachment
	 * 
	 * @return	void
	 */
	public function upload_attachment( $file, $document_type = '', $document = null ) {
		// check if we have a cloud service
		if ( empty($cloud_service_slug = Cloud_API::service_enabled()) ) {
			return;
		}

		// get service api settings
		$service_settings = get_option( $this->settings_option );
		
		// check if upload enabled
		if ( !isset($service_settings['auto_upload']) || $service_settings['auto_upload'] == 0 || Cloud_API::is_enabled() === false ) {
			return;
		}

		if ( !empty($document) && !empty($document->order) ) {
			$this->upload_to_service( $file, 'attachment', $document->order, $document->get_type() );
		} else {
			$this->upload_to_service( $file, 'attachment', null, null );			
		}
	}

	/**
	 * Upload PDF to cloud service during/after email attachment
	 * 
	 * @return	void
	 */
	public function upload_by_status( $order_id, $old_status, $new_status, $order ) {
		// check if we have a cloud service
		if ( empty($cloud_service_slug = Cloud_API::service_enabled()) ) {
			return;
		}

		// get service api settings
		$service_settings = get_option( $this->settings_option );

		// check if upload enabled
		if ( empty($service_settings['per_status_upload']) || Cloud_API::is_enabled() === false ) {
			return;
		}

		foreach ($service_settings['per_status_upload'] as $template_type => $upload_status) {
			// check if new status matches upload status for document
			if ( $new_status == $upload_status ) {
				// check if free order + free invoice disabled
				if ( function_exists('wcpdf_get_document') ) { // 2.0+
					$document_settings = WPO_WCPDF()->settings->get_document_settings( $template_type );
					$free_disabled = isset( $document_settings['disable_free'] );
				} else { // 1.X
					$main_general_settings = get_option('wpo_wcpdf_general_settings');
					$free_disabled = isset( $main_general_settings['disable_free'] );
				}

				if ( $free_disabled ) {
					$order_total = $order->get_total();
					if ( $order_total == 0 ) {
						continue;
					}
				}

				// prevent creation of credit note for orders without an invoice
				// 2.0+ only
				if ( function_exists('wcpdf_get_invoice') && $template_type == 'credit-note' ) {
					$invoice = wcpdf_get_invoice( $order );
					if ( $invoice && $invoice->exists() === false ) {
						continue;
					}
				}

				$file = $this->create_pdf_file( $order_id, $template_type );
				// upload file to cloud service
				$upload_response = $this->upload_to_service( $file, 'status', $order, $template_type );			
			}
		}
	}

	/**
	 * Upload PDF to cloud service
	 * 
	 * @return	array
	 */
	public function upload_to_service( $file, $context = 'attachment', $order = null, $document_type = null ) {
		// check if enabled
		if ( Cloud_API::is_enabled() === false ) {
			return;
		}

		// check if we have a cloud service
		if ( empty($cloud_service_slug = Cloud_API::service_enabled()) ) {
			return;
		}

		Cloud_API::log( 'info', 'Upload to '.$cloud_service_slug.' initiated' );

		// get settings
		$destination_folder = $this->get_destination_folder( $order, $document_type );

		// get service api settings
		$service_api_settings = get_option( 'wpo_wcpdf_'.$cloud_service_slug.'_api_settings' );

		// check if authorized
		if ( !empty($service_api_settings) && isset($service_api_settings['access_token']) && !empty($service_access_token = $service_api_settings['access_token']) ) {
			if (!empty($file) && file_exists($file)) {

				$result = $this->upload_service_selection( $file, $destination_folder, $cloud_service_slug );

				if ( isset($result['error']) ) {
					Cloud_API::log( 'error', "{$cloud_service_slug} upload permission denied" );

					// there was an error uploading the file, copy file to queue
					$this->queue_file( $file, $order, $document_type );

					return array( 'error' => __( 'Cloud service upload permission denied', 'wpo_wcpdf_pro' ) );
				} else {
					return $result;
				}

			} else {
				Cloud_API::log( 'error', "file does not exist: {$file}" );
				return array( 'error' => __( 'File does not exist', 'wpo_wcpdf_pro' ) );
			}
		} else {
			Cloud_API::log( 'error', "no access token" );
			// we don't have credentials, so we're storing the file in the queue
			$this->queue_file( $file, $order, $document_type );
			
			return array( 'error' => __( 'Cloud service credentials not set', 'wpo_wcpdf_pro' ) );
		}
	}

	/**
	 * Selects the correct cloud service to upload
	 * 
	 * @return	array
	 */
	public function upload_service_selection( $file, $destination_folder, $cloud_service_slug )
	{
		if( $cloud_service_slug == 'dropbox' ) {
			$dropbox = new Dropbox_API;
			return $dropbox->upload( $file, $destination_folder );
		} elseif( $cloud_service_slug == 'gdrive' ) {
			$gdrive = new Gdrive_API;
			return $gdrive->upload( $file, $destination_folder );
		} elseif( $cloud_service_slug == 'onedrive' ) {
			// onedrive here
		} else {
			return;
		}
	}

	/**
	 * Export PDFs in bulk from the order actions drop down
	 * 
	 * @return void
	 */
	public function bulk_export() {
		// check if enabled
		if ( Cloud_API::is_enabled() === false ) {
			return;
		}
	 	global $typenow;
		if( $typenow == 'shop_order' ) {
			// Check if all parameters are set
			if( ( empty( $_GET['order_ids'] ) && empty($_REQUEST['post']) ) || empty( $_GET['action'] ) ) {
				return;
			}

			// Check the user privileges
			if( !current_user_can( 'manage_woocommerce_orders' ) && !current_user_can( 'edit_shop_orders' ) && !isset( $_GET['my-account'] ) ) {
				return;
			}
			
			// convert order_ids to array if set
			if ( isset( $_GET['order_ids'] ) ) {
				$order_ids = (array) explode('x',$_GET['order_ids']);
			} else {
				$order_ids = (array) $_REQUEST['post'];
			}
			
			if(empty($order_ids)) {
				return;
			}

			// Process oldest first: reverse $order_ids array
			$order_ids = array_reverse($order_ids);
			
			// get the action
			$wp_list_table = _get_list_table( 'WP_Posts_List_Table' );
			$action = $wp_list_table->current_action();

			switch ( $action ) {
				case 'cloud_service_export_invoices':
					$this->bulk_export_page( $order_ids, 'invoice' );
					break;
				case 'cloud_service_export_packing_slips':
					$this->bulk_export_page( $order_ids, 'packing-slip' );
					break;
				case 'cloud_service_export_process':
					$template_type = $_GET['template'];
					$this->bulk_export_process( $order_ids, $template_type );
					break;
				default:
					return;
			}

			exit();
		}
	}

	/**
	 * Process export queue
	 * 
	 * @return void
	 */
	public function export_queue() {
		// check if enabled
		if ( Cloud_API::is_enabled() === false ) {
			return;
		}

	 	global $typenow;
		if( $typenow == 'shop_order' ) {
			$action = isset($_GET['action']) ? $_GET['action'] : '';

			// Check action
			if( $action != 'cloud_service_upload_queue' &&  $action != 'cloud_service_clear_queue' && $action != 'cloud_service_queue_process' ) {
				return;
			}

			// Check the user privileges
			if( !current_user_can( 'manage_woocommerce_orders' ) && !current_user_can( 'edit_shop_orders' ) && !isset( $_GET['my-account'] ) ) {
				return;
			}
			
			switch ( $action ) {
				case 'cloud_service_upload_queue':
					$this->queue_page( 'upload' );
					break;
				case 'cloud_service_clear_queue':
					$this->queue_page( 'clear' );
					break;
				case 'cloud_service_queue_process':
					$do = $_GET['do'];
					$this->queue_process( $do );
					break;
				default:
					return;
			}

			exit();
		}
	}

	/**
	 * Displays the queue notification modal
	 * 
	 * @return	void
	 */
	public function queue_page ( $do ) {
		// create url/path to process page
		$action_args = array (
			'action'	=> 'cloud_service_queue_process',
			'do'		=> $do,
		);

		$new_page = add_query_arg( $action_args, remove_query_arg( 'action' ) );

		// render pre-export page (waiting page with spinner)
		if ( $do == 'upload' ) {
			$message = sprintf( __( 'Please wait while your queued PDF documents are being uploaded to %s...', 'wpo_wcpdf_pro' ), Cloud_API::$service_name );
		} else {
			$message = __( 'Please wait while the upload queue is being cleared', 'wpo_wcpdf_pro' );
		}

		$service_name = Cloud_API::$service_name;
		$plugin_url = WPO_WCPDF_PRO()->plugin_url();
		
		include( WPO_WCPDF_Pro()->plugin_path().'/includes/cloud/templates/template-bulk-export-page.php');
	}

	/**
	 * Displays the bulk export notification modal
	 * 
	 * @return	void
	 */
	public function bulk_export_page ( $order_ids, $template_type ) {
		// create url/path to process page
		$action_args = array (
			'action'	=> 'cloud_service_export_process',
			'template'	=> $template_type,
		);
		
		$new_page = add_query_arg( $action_args, remove_query_arg( 'action' ) );

		// render pre-export page (waiting page with spinner)
		if ($template_type == 'invoice') {
			$message = sprintf( __( 'Please wait while your PDF invoices are being uploaded to %s...', 'wpo_wcpdf_pro' ), Cloud_API::$service_name );
		} else {
			$message = sprintf( __( 'Please wait while your PDF packing slips are being uploaded to %s...', 'wpo_wcpdf_pro' ), Cloud_API::$service_name );
		}

		$service_name = Cloud_API::$service_name;
		$plugin_url = WPO_WCPDF_PRO()->plugin_url();

		include( WPO_WCPDF_Pro()->plugin_path().'/includes/cloud/templates/template-bulk-export-page.php');
	}		

	/**
	 * Bulk export process
	 * 
	 * @return	void
	 */
	public function bulk_export_process ( $order_ids, $template_type ) {

		foreach ($order_ids as $order_id) {
			$order = wc_get_order( $order_id );
			// create pdf
			$pdf_path = $this->create_pdf_file( $order_id, $template_type );
			// upload file to cloud service
			$upload_response = $this->upload_to_service( $pdf_path, 'export', $order, $template_type );

			if ( !empty( $upload_response['error'] ) ) {
				// Houston, we have a problem
				$errors[$order_id] = $upload_response['error'];
			}
		}

		// render export done page
		if ( isset($errors) ) {
			$view_log = '<a href="'.esc_url_raw( admin_url( 'admin.php?page=wc-status&tab=logs' ) ).'" target="_blank">'.__( 'View logs', 'wpo_wcpdf_pro' ).'</a>';
			$message = sprintf( __( 'There were errors when trying to upload to %s, check the error log for details:', 'wpo_wcpdf_pro' ), Cloud_API::$service_name ) .'<br>'. $view_log;
		} else {
			switch ($template_type) {
				case 'invoice':
					$message = sprintf( __( 'PDF invoices successfully uploaded to %s!', 'wpo_wcpdf_pro' ), Cloud_API::$service_name );
					break;
				case 'packing-slip':
					$message = sprintf( __( 'PDF packing slips successfully uploaded to %s!', 'wpo_wcpdf_pro' ), Cloud_API::$service_name );
					break;
				default:
					$message = sprintf( __( 'PDF documents successfully uploaded to %s!', 'wpo_wcpdf_pro' ), Cloud_API::$service_name );
					break;
			}
		}

		$service_name = Cloud_API::$service_name;
		$plugin_url = WPO_WCPDF_Pro()->plugin_url();

		include( WPO_WCPDF_Pro()->plugin_path().'/includes/cloud/templates/template-bulk-export-process.php');		
	}

	/**
	 * Adds PDF file to queue
	 * 
	 * @return	void
	 */
	public function queue_file ( $file, $order = null, $document_type = null ) {
		$queue_folder = $this->get_queue_path();
		$filename = basename($file);
		$queue_file = $queue_folder . $filename;
		copy( $file, $queue_file );

		// store order reference in db if available
		if (!empty($order) && is_object($order)) {
			$cloud_service_queue = get_option( 'wpo_wcpdf_'.Cloud_API::$service_slug.'_queue', array() );
			if (!isset($cloud_service_queue[$queue_file])) {
				$order_id = method_exists($order, 'get_id') ? $order->get_id(): $order->id;
				$cloud_service_queue[$queue_file] = array(
					'order_id'		=> $order_id,
					'document_type'	=> $document_type,
				);
				update_option( 'wpo_wcpdf_'.Cloud_API::$service_slug.'_queue', $cloud_service_queue );
			}
		}

		Cloud_API::log( 'info', "file placed in queue: {$queue_file}" );
	}

	/**
	 * Gets the queue path
	 * 
	 * @return	string
	 */
	public function get_queue_path () {
		if ( ! function_exists('WPO_WCPDF') && empty( WPO_WCPDF()->main ) ) {
			return;
		} 

		$queue_path = trailingslashit( WPO_WCPDF()->main->get_tmp_path( Cloud_API::$service_slug ) );

		// make sure the queue path is protected!
		// create .htaccess file and empty index.php to protect in case an open webfolder is used!
		if ( !file_exists($queue_path . '.htaccess') || !file_exists($queue_path . 'index.php') ) {
			@file_put_contents( $queue_path . '.htaccess', 'deny from all' );
			@touch( $queue_path . 'index.php' );
		}
		return $queue_path;
	}

	/**
	 * Gets the queued files
	 * 
	 * @return	array
	 */
	public function get_queued_files ( $value = '' ) {
		// get list of all files in the queue folder
		$queue_folder = $this->get_queue_path();
		$queued_files = scandir($queue_folder);
		// remove . & ..
		$queued_files = array_diff($queued_files, array('.', '..', '.htaccess', 'index.php', '.DS_Store'));

		if (!count($queued_files) > 0) {
			// no files in queue;
			return false;
		} else {
			return $queued_files;
		}
	}

	/**
	 * Gets the destination folder(s)
	 * 
	 * @return	array
	 */
	public function get_destination_folder ( $order, $document_type ) {
		$general_settings = get_option( $this->settings_option );

		// get destination folder setting
		if ( isset($general_settings['access_type']) && $general_settings['access_type'] == 'root_folder' && !empty($general_settings['destination_folder']) ) {
			// format folder name
			// 1: forward slashes only
			$destination_folder = str_replace("\\", "/", $general_settings['destination_folder'] );
			// 2: start and end with slash
			$destination_folder = '/'.trim( $destination_folder, '\/').'/';
		} else {
			$destination_folder = '/';
		}

		// append year/month according to setting
		if ( isset($general_settings['year_month_folders']) ) {
			$year = date("Y");
			$month = date("m");
			$destination_folder = "{$destination_folder}{$year}/{$month}/";
		}

		// filters
		if( Cloud_API::service_enabled() == 'dropbox' ) {
			$destination_folder = apply_filters( 'wpo_wcpdf_dropbox_destination_folder', $destination_folder, $order, $document_type ); // legacy (v2.6.6)
		}
		$destination_folder = apply_filters( 'wpo_wcpdf_cloud_service_destination_folder', $destination_folder, $order, $document_type );

		return $destination_folder;
	}

	/**
	 * Creates the PDF file
	 * 
	 * @return	string
	 */
	public function create_pdf_file ( $order_id, $template_type ) {
		if ( function_exists('WPO_WCPDF') && !empty( WPO_WCPDF()->main ) ) {
			// wcpdf 2.0+
			// turn off deprecation notices during upload
			add_filter( 'wcpdf_disable_deprecation_notices', '__return_true' );

			$tmp_path = trailingslashit( WPO_WCPDF()->main->get_tmp_path('attachments') );

			$document = wcpdf_get_document( $template_type, (array) $order_id, true );
			if ( !$document ) {
				return false;
			}

			// get pdf data & filename
			$pdf_data = $document->get_pdf();
			$pdf_filename = $document->get_filename();

			// re-enable deprecation notices
			remove_filter( 'wcpdf_disable_deprecation_notices', '__return_true' );
		} else {
			// wcpdf 1.6.5 or older
			global $wpo_wcpdf;
			$pdf_data = $wpo_wcpdf->export->get_pdf( $template_type, (array) $order_id );

			// get temp path - 1.4 backwards compatibility
			$old_tmp = isset($wpo_wcpdf->export->debug_settings['old_tmp']);
			if ( $old_tmp || !method_exists( $wpo_wcpdf->export, 'tmp_path' ) ) {
				$tmp_path = WooCommerce_PDF_Invoices::$plugin_path . 'tmp/';
			} else {
				$tmp_path = $wpo_wcpdf->export->tmp_path('attachments');
			}

			// generate filename & path
			if ( method_exists( $wpo_wcpdf->export, 'build_filename' ) ) {
				$pdf_filename = $wpo_wcpdf->export->build_filename( $template_type, (array) $order_id, 'download' );
			} else {
				$display_number = $wpo_wcpdf->export->get_display_number( $order_id );
				$pdf_filename_prefix = __( $template_type, 'wpo_wcpdf' );
				$pdf_filename = $pdf_filename_prefix . '-' . $display_number . '.pdf';
				$pdf_filename = apply_filters( 'wpo_wcpdf_attachment_filename', $pdf_filename, $display_number, $order_id );
			}
		}

		$pdf_path = $tmp_path . $pdf_filename;

		// save file
		file_put_contents ( $pdf_path, $pdf_data );

		return $pdf_path;
	}

	/**
	 * Process to upload and clear queue
	 * 
	 * @return	void
	 */
	public function queue_process ( $do ) {
		// check if enabled
		if ( Cloud_API::is_enabled() === false ) {
			return;
		}

	 	switch ($do) {
	 		case 'upload':
				if ($queued_files = $this->get_queued_files()) {
					$cloud_service_queue = get_option( 'wpo_wcpdf_'.Cloud_API::$service_slug.'_queue', array() );
					foreach ($queued_files as $queued_file) {
						$file_path = $this->get_queue_path() . $queued_file;

						// load order if we have stored it
						if (!empty($cloud_service_queue[$file_path]) && is_array($cloud_service_queue[$file_path])) {
							$document_type = $cloud_service_queue[$file_path]['document_type'];
							$order_id = $cloud_service_queue[$file_path]['order_id'];
							$order = wc_get_order( $order_id );
						} else {
							$document_type = null;
							$order = null;
						}
						// upload file to cloud service
						$upload_response = $this->upload_to_service( $file_path, 'export', $order, $document_type );

						if ( !empty( $upload_response['error'] ) ) {
							// Houston, we have a problem
							$errors[] = $upload_response['error'];
						} else {
							// remove file
							unlink($file_path);
							// and from queue reference
							if (isset($cloud_service_queue[$file_path])) {
								unset($cloud_service_queue[$file_path]);
								update_option( 'wpo_wcpdf_'.Cloud_API::$service_slug.'_queue', $cloud_service_queue );
							}
						}
					}						
				}
	 			break;
	 		case 'clear':
	 			// delete all pdf files from queue folder
	 			$queue_path = $this->get_queue_path();
				array_map('unlink', ( glob( $queue_path.'*.pdf' ) ? glob( $queue_path.'*.pdf' ) : array() ) );
				// delete queue option
				delete_option( 'wpo_wcpdf_'.Cloud_API::$service_slug.'_queue' );
	 			break;
	 	}

		// render export done page
		if ( isset($errors) ) {
			$view_log = '<a href="'.esc_url_raw( admin_url( 'admin.php?page=wc-status&tab=logs' ) ).'" target="_blank">'.__( 'View logs', 'wpo_wcpdf_pro' ).'</a>';
			$message = sprintf( __( 'There were errors when trying to upload to %s, check the error log for details:', 'wpo_wcpdf_pro' ), Cloud_API::$service_name ) .'<br>'. $view_log;
		} elseif ($do == 'upload') {
			$message = sprintf( __( 'PDF documents successfully uploaded to %s!', 'wpo_wcpdf_pro' ), Cloud_API::$service_name );
		} else {
			$message = __( 'Upload queue successfully cleared!', 'wpo_wcpdf_pro' );
		}

		$service_name = Cloud_API::$service_name;
		$plugin_url = WPO_WCPDF_Pro()->plugin_url();

		include( WPO_WCPDF_Pro()->plugin_path().'/includes/cloud/templates/template-bulk-export-process.php');			
	}

	/**
	 * Display notification about upload queue with link to process queue
	 * 
	 * @return void
	 */
	public function upload_queue() {
		$queue = $this->get_queued_files();
		if ( !empty($queue) && Cloud_API::is_enabled() && !empty(Cloud_API::$service_access_token)) {
			$files_count = count($queue);

			$upload_button	= '<a href="edit.php?post_type=shop_order&action=cloud_service_upload_queue" class="button-primary" id="cloud_service_upload_queue">'.__( 'Upload files', 'wpo_wcpdf_pro' ).'</a>';
			$clear_button	= '<a href="edit.php?post_type=shop_order&action=cloud_service_clear_queue"  class="button-primary" id="cloud_service_clear_queue" >'.__( 'Clear queue', 'wpo_wcpdf_pro' ).'</a>';

			// display message
			?>
				<div class="updated">
					<p><?php printf( __( 'There are %s unfinished uploads in your the upload queue from WooCommerce PDF Invoices & Packing Slips to %s.', 'wpo_wcpdf_pro' ), $files_count, Cloud_API::$service_name ); ?></p>
					<p><?php echo $upload_button . ' ' . $clear_button; ?></p>
				</div>
			<?php			

		}
	}

	/**
	 * Add cloud service actions to bulk action drop down menu
	 *
	 * Using Javascript until WordPress core fixes: http://core.trac.wordpress.org/ticket/16031
	 *
	 * @access public
	 * @return void
	 */
	public function export_actions() {
		global $post_type;
		
		if ( Cloud_API::is_enabled() !== false && 'shop_order' == $post_type ) {
			?>
			<script type="text/javascript">
			jQuery(document).ready(function() {
				jQuery('<option>').val('cloud_service_export_invoices').text('<?= __( 'PDF Invoices to', 'wpo_wcpdf_pro' ).' '.Cloud_API::$service_name; ?>').appendTo("select[name='action']");
				jQuery('<option>').val('cloud_service_export_invoices').text('<?= __( 'PDF Invoices to', 'wpo_wcpdf_pro' ).' '.Cloud_API::$service_name; ?>').appendTo("select[name='action2']");
				jQuery('<option>').val('cloud_service_export_packing_slips').text('<?= __( 'PDF Packing Slips to', 'wpo_wcpdf_pro' ).' '.Cloud_API::$service_name; ?>').appendTo("select[name='action']");
				jQuery('<option>').val('cloud_service_export_packing_slips').text('<?= __( 'PDF Packing Slips to', 'wpo_wcpdf_pro' ).' '.Cloud_API::$service_name; ?>').appendTo("select[name='action2']");
			});
			</script>
			<?php
		}
	}

	/**
	 * Enqueue scripts
	 * 
	 * @return	void
	 */
	public function enqueue_scripts()
	{
		global $post_type;

		if( $post_type == 'shop_order' ) {
			wp_enqueue_script(
				'wcpdf-pro-cloud-storage-export',
				plugins_url( 'js/pro-cloud-storage-export.js', dirname(__FILE__) ),
				array( 'jquery', 'thickbox' ),
				WPO_WCPDF_PRO_VERSION
			);
		}

		if ( $this->get_queued_files() ) {
			wp_enqueue_script(
				'wcpdf-pro-cloud-storage-queue',
				plugins_url( 'js/pro-cloud-storage-queue.js', dirname(__FILE__) ),
				array( 'jquery', 'thickbox' ),
				WPO_WCPDF_PRO_VERSION
			);
		}

		wp_enqueue_style(
			'wcpdf-pro-cloud-storage-styles',
			plugins_url( 'css/cloud-storage-styles.css', dirname(__FILE__) ),
			array(),
			WPO_WCPDF_PRO_VERSION
		);
	}

} // end class

endif; // end class_exists

return new Cloud_Storage();
