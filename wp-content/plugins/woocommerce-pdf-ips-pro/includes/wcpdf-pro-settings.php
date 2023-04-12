<?php
namespace WPO\WC\PDF_Invoices_Pro;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( !class_exists( '\\WPO\\WC\\PDF_Invoices_Pro\\Settings' ) ) :

class Settings {
	public $pro_settings;

	public function __construct() {
		$this->pro_settings = get_option( 'wpo_wcpdf_settings_pro' );

		add_action( 'admin_enqueue_scripts', array( $this, 'load_scripts_styles' ) );
		add_action( 'admin_notices', array( $this, 'pro_template_check' ) );
		add_action( 'admin_notices', array( $this, 'wc_version_check' ) );

		add_action( 'wp_ajax_wcpdf_i18n_get_translations', array($this, 'get_translations' ));
		add_action( 'wp_ajax_wcpdf_i18n_save_translations', array($this, 'save_translations' ));

		add_action( 'wpo_wcpdf_settings_tabs', array( $this, 'settings_tab' ) );
		add_action( 'admin_init', array( $this, 'init_settings' ) );
		add_action( 'wpo_wcpdf_settings_output_pro', array( $this, 'output' ), 10, 1 );

		add_filter( 'wpo_wcpdf_settings_fields_general', array( $this, 'settings_fields_general_i18n' ), 10, 1 );

		add_action( 'admin_init', array( $this, 'add_pro_document_settings' ), 1 );
	}

	public function output( $section ) {
		settings_fields( "wpo_wcpdf_settings_pro" );
		do_settings_sections( "wpo_wcpdf_settings_pro" );

		submit_button();
	}
	/**
	 * Register settings
	 */
	public function init_settings() {
		// Register settings.
		$page = $option_group = $option_name = 'wpo_wcpdf_settings_pro';

		// load invoice to reuse method to get wc emails
		$invoice = wcpdf_get_invoice( null );

		$settings_fields = array(
			/**
			 * Static files section.
			 */
			array(
				'type'		=> 'section',
				'id'		=> 'static_files',
				'title'		=> __( 'Static files', 'wpo_wcpdf_pro' ),
				'callback'	=> 'section',
			),
			array(
				'type'			=> 'setting',
				'id'			=> 'static_file',
				'title'			=> __( 'Static files', 'wpo_wcpdf_pro' ),
				'callback'		=> array( $this, 'multiple_file_upload_callback' ),
				'section'		=> 'static_files',
				'args'			=> array(
					'option_name'			=> $option_name,
					'id'					=> 'static_file',
					'uploader_title'		=> __( 'Select a file to attach', 'wpo_wcpdf_pro' ),
					'uploader_button_text'	=> __( 'Set file', 'wpo_wcpdf_pro' ),
					'remove_button_text'	=> __( 'Remove file', 'wpo_wcpdf_pro' ),
				)
			),
			array(
				'type'			=> 'setting',
				'id'			=> 'static_file_attach_to_email_ids',
				'title'			=> __( 'Attach to:', 'woocommerce-pdf-invoices-packing-slips' ),
				'callback'		=> 'multiple_checkboxes',
				'section'		=> 'static_files',
				'args'			=> array(
					'option_name'	=> $option_name,
					'id'			=> 'static_file_attach_to_email_ids',
					'fields' 		=> $invoice->get_wc_emails(),
					'description'	=> !is_writable( WPO_WCPDF()->main->get_tmp_path( 'attachments' ) ) ? '<span class="wpo-warning">' . sprintf( __( 'It looks like the temp folder (<code>%s</code>) is not writable, check the permissions for this folder! Without having write access to this folder, the plugin will not be able to email invoices.', 'woocommerce-pdf-invoices-packing-slips' ), WPO_WCPDF()->main->get_tmp_path( 'attachments' ) ).'</span>':'',
				)
			),


			/**
			 * Address customization section
			 */
			
			array(
				'type'		=> 'section',
				'id'		=> 'address_customization',
				'title'		=> __( 'Address customization', 'wpo_wcpdf_pro' ),
				'callback'	=> array( $this, 'custom_address_fields_section_callback' ),
			),
			array(
				'type'			=> 'setting',
				'id'			=> 'billing_address',
				'title'			=> __( 'Billing address', 'wpo_wcpdf_pro' ),
				'callback'		=> 'textarea',
				'section'		=> 'address_customization',
				'args'			=> array(
					'option_name'	=> $option_name,
					'id'			=> 'billing_address',
					'width'			=> '42',
					'height'		=> '8',
				)
			),
			array(
				'type'			=> 'setting',
				'id'			=> 'shipping_address',
				'title'			=> __( 'Shipping address', 'wpo_wcpdf_pro' ),
				'callback'		=> 'textarea',
				'section'		=> 'address_customization',
				'args'			=> array(
					'option_name'	=> $option_name,
					'id'			=> 'shipping_address',
					'width'			=> '42',
					'height'		=> '8',
				)
			),
			array(
				'type'			=> 'setting',
				'id'			=> 'remove_whitespace',
				'title'			=> __( 'Remove empty lines', 'wpo_wcpdf_pro' ),
				'callback'		=> 'checkbox',
				'section'		=> 'address_customization',
				'args'			=> array(
					'option_name'	=> $option_name,
					'id'			=> 'remove_whitespace',
					'description'	=> __( 'Enable this option if you want to remove empty lines left over from empty address/placeholder replacements', 'wpo_wcpdf_pro' ),
				)
			),
			array(
				'type'			=> 'setting',
				'id'			=> 'placeholders_allow_line_breaks',
				'title'			=> __( 'Allow line breaks within custom fields', 'wpo_wcpdf_pro' ),
				'callback'		=> 'checkbox',
				'section'		=> 'address_customization',
				'args'			=> array(
					'option_name'	=> $option_name,
					'id'			=> 'placeholders_allow_line_breaks',
				)
			),

		);

		if ( class_exists('SitePress') || class_exists('Polylang') ) {
			$languages = array();
			if ( function_exists('icl_get_languages') ) {
				foreach (icl_get_languages('skip_missing=0') as $lang => $data) {
					$languages[$data['language_code']] = $data['native_name'];
				}
			}

			$multilingual_settings_fields = array(
				/**
				 * Address customization section
				 */
				
				array(
					'type'		=> 'section',
					'id'		=> 'multilingual',
					'title'		=> __( 'Multilingual settings', 'wpo_wcpdf_pro' ),
					'callback'	=> 'section',
				),
				array(
					'type'			=> 'setting',
					'id'			=> 'document_language',
					'title'			=> __( 'Document language', 'wpo_wcpdf_pro' ),
					'callback'		=> 'select',
					'section'		=> 'multilingual',
					'args'			=> array(
						'option_name'	=> $option_name,
						'id'			=> 'document_language',
						'options' 		=> array(
							'order'		=> __( 'Order/customer language' , 'wpo_wcpdf_pro' ),
							'admin'		=> __( 'Site default language' , 'wpo_wcpdf_pro' ),
						) + $languages,
					)
				),
			);
			$settings_fields = array_merge($settings_fields, $multilingual_settings_fields);
		}

		// allow plugins to alter settings fields
		$settings_fields = apply_filters( 'wpo_wcpdf_settings_fields_pro', $settings_fields, $page, $option_group, $option_name );
		WPO_WCPDF()->settings->add_settings_fields( $settings_fields, $page, $option_group, $option_name );
		return;

	}

	public function add_pro_document_settings() {
		// add extra packing slip settings
		add_filter( 'wpo_wcpdf_settings_fields_documents_packing_slip', array($this, 'pro_packing_slip_settings' ), 9, 4 );

		// add title, filename and keep PDF settings
		$documents = WPO_WCPDF()->documents->get_documents('all');
		foreach ($documents as $document) {
			add_filter( 'wpo_wcpdf_settings_fields_documents_'.$document->slug, function( $settings_fields, $page, $option_group, $option_name ) use ( $document ) {
				$new_setting = 	array(
					array(
						'type'			=> 'setting',
						'id'			=> 'title',
						'title'			=> __( 'Document title', 'wpo_wcpdf_pro' ),
						'callback'		=> 'i18n_wrap',
						'section'		=> 'custom',
						'args'			=> array(
							'callback'			=> 'text_input',
							'size'				=> '72',
							'option_name'		=> $option_name,
							'id'				=> 'title',
							'placeholder'		=> $document->get_title(),
						)
					),
					array(
						'type'			=> 'setting',
						'id'			=> 'filename',
						'title'			=> __( 'PDF filename', 'wpo_wcpdf_pro' ),
						'callback'		=> 'i18n_wrap',
						'section'		=> 'custom',
						'args'			=> array(
							'callback'			=> 'text_input',
							'size'				=> '72',
							'option_name'		=> $option_name,
							'id'				=> 'filename',
							// 'placeholder'		=> $document->get_type().'-######.pdf',
							'description'		=> 
								__( 'Leave empty to use default. Placeholders like {{document_number}} and {{order_number}} can be used to include document numbers in the filename.', 'wpo_wcpdf_pro' ) .
								sprintf('<span class="filename-warning-text" style="display:none;">%s</span>', __( 'Warning! Your filename does not contain a unique identifier ({{order number}}, {{document number}}), this can lead to attachment mixups!', 'wpo_wcpdf_pro' ) ) ,
						)
					),
				);

				if ( version_compare( WPO_WCPDF()->version, '2.4.7', '>' ) && version_compare( PHP_VERSION, '7.1', '>=' ) ) {
					$keep_pdf = array(
						'type' 			=> 'setting',
						'id'			=> 'archive_pdf',
						'title'			=> __( 'Keep PDF on server', 'wpo_wcpdf_pro' ),
						'callback'		=> 'checkbox',
						'section'		=> 'custom',
						'args'			=> array(
							'option_name'	=> $option_name,
							'id'			=> 'archive_pdf',
							'description'	=> __( 'Stores the PDF when generated for the first time and reloads this copy each time the document is requested. Please note this can take up considerable disk space on you server.' , 'wpo_wcpdf_pro' ),
						)
					);
					array_push( $new_setting, $keep_pdf );
				}

				$settings_fields = $this->move_setting_after_id( $settings_fields, $new_setting, 'enabled' );
				return $settings_fields;
			}, 10, 4 );
		}
	}

	public function pro_packing_slip_settings( $settings_fields, $page, $option_group, $option_name ) {
		// load packing slip to reuse method to get wc emails
		$packing_slip = wcpdf_get_packing_slip( null );

		// insert attachments setting
		$attach_to_email_ids = array(
			array(
				'type'			=> 'setting',
				'id'			=> 'attach_to_email_ids',
				'title'			=> __( 'Attach to:', 'woocommerce-pdf-invoices-packing-slips' ),
				'callback'		=> 'multiple_checkboxes',
				'section'		=> 'packing_slip',
				'args'			=> array(
					'option_name'	=> $option_name,
					'id'			=> 'attach_to_email_ids',
					'fields' 		=> $packing_slip->get_wc_emails(),
					'description'	=> !is_writable( WPO_WCPDF()->main->get_tmp_path( 'attachments' ) ) ? '<span class="wpo-warning">' . sprintf( __( 'It looks like the temp folder (<code>%s</code>) is not writable, check the permissions for this folder! Without having write access to this folder, the plugin will not be able to email invoices.', 'woocommerce-pdf-invoices-packing-slips' ), WPO_WCPDF()->main->get_tmp_path( 'attachments' ) ).'</span>':'',
				)
			),
		);
		$settings_fields = $this->move_setting_after_id( $settings_fields, $attach_to_email_ids, 'enabled' );

		// packing slip number setting
		$number_setting = array(
			array(
				'type'			=> 'setting',
				'id'			=> 'display_date',
				'title'			=> __( 'Display packing slip date', 'wpo_wcpdf_pro' ),
				'callback'		=> 'checkbox',
				'section'		=> 'packing_slip',
				'args'			=> array(
					'option_name'		=> $option_name,
					'id'				=> 'display_date',
				)
			),
			array(
				'type'			=> 'setting',
				'id'			=> 'display_number',
				'title'			=> __( 'Display packing slip number', 'wpo_wcpdf_pro' ),
				'callback'		=> 'checkbox',
				'section'		=> 'packing_slip',
				'args'			=> array(
					'option_name'		=> $option_name,
					'id'				=> 'display_number',
				)
			),
			array(
				'type'			=> 'setting',
				'id'			=> 'next_invoice_number',
				'title'			=> __( 'Next packing slip number (without prefix/suffix etc.)', 'wpo_wcpdf_pro' ),
				'callback'		=> 'next_number_edit',
				'section'		=> 'packing_slip',
				'args'			=> array(
					'store'			=> 'packing_slip_number',
					'size'			=> '10',
					'description'	=> __( 'This is the number that will be used for the next document. By default, numbering starts from 1 and increases for every new document. Note that if you override this and set it lower than the current/highest number, this could create duplicate numbers!', 'woocommerce-pdf-invoices-packing-slips' ),
				)
			),
			array(
				'type'			=> 'setting',
				'id'			=> 'number_format',
				'title'			=> __( 'Number format', 'woocommerce-pdf-invoices-packing-slips' ),
				'callback'		=> 'multiple_text_input',
				'section'		=> 'packing_slip',
				'args'			=> array(
					'option_name'			=> $option_name,
					'id'					=> 'number_format',
					'fields'				=> array(
						'prefix'			=> array(
							'placeholder'	=> __( 'Prefix' , 'woocommerce-pdf-invoices-packing-slips' ),
							'size'			=> 20,
							// 'description'	=> __( 'to use the invoice year and/or month, use [invoice_year] or [invoice_month] respectively' , 'woocommerce-pdf-invoices-packing-slips' ),
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
				'id'			=> 'my_account_buttons',
				'title'			=> __( 'Allow My Account download', 'woocommerce-pdf-invoices-packing-slips' ),
				'callback'		=> 'select',
				'section'		=> 'packing_slip',
				'args'			=> array(
					'option_name'	=> $option_name,
					'id'			=> 'my_account_buttons',
					'options' 		=> array(
						'never'			=> __( 'Never' , 'woocommerce-pdf-invoices-packing-slips' ),
						'available'		=> __( 'Only when a packing slip is already created/emailed' , 'wpo_wcpdf_pro' ),
						'custom'		=> __( 'Only for specific order statuses (define below)' , 'woocommerce-pdf-invoices-packing-slips' ),
						'always'		=> __( 'Always' , 'woocommerce-pdf-invoices-packing-slips' ),
					),
					'custom'		=> array(
						'type'		=> 'multiple_checkboxes',
						'args'		=> array(
							'option_name'	=> $option_name,
							'id'			=> 'my_account_restrict',
							'fields'		=> $packing_slip->get_wc_order_status_list(),
						),
					),
				)
			),
			array(
				'type'			=> 'setting',
				'id'			=> 'reset_number_yearly',
				'title'			=> __( 'Reset packing slip number yearly', 'wpo_wcpdf_pro' ),
				'callback'		=> 'checkbox',
				'section'		=> 'packing_slip',
				'args'			=> array(
					'option_name'		=> $option_name,
					'id'				=> 'reset_number_yearly',
				)
			),
		);
		if ( version_compare( WPO_WCPDF()->version, '2.1.1', '>' ) ) {
			$settings_fields = $this->move_setting_after_id( $settings_fields, $number_setting, 'display_customer_notes' );
		}

		// insert refunded qty setting
		$subtract_refunded_qty = array(
			array(
				'type' 			=> 'setting',
				'id'			=> 'subtract_refunded_qty',
				'title'			=> __( 'Subtract refunded item quantities from packing slip', 'wpo_wcpdf_pro' ),
				'callback'		=> 'checkbox',
				'section'		=> 'packing_slip',
				'args'			=> array(
					'option_name'	=> $option_name,
					'id'			=> 'subtract_refunded_qty',
				)
			),
		);
		$settings_fields = $this->move_setting_after_id( $settings_fields, $subtract_refunded_qty, 'display_customer_notes' );

		// insert hide virtual and downloadable products setting
		$hide_virtual_products = array(
			array(
				'type' 			=> 'setting',
				'id'			=> 'hide_virtual_products',
				'title'			=> __( 'Hide virtual and downloadable products', 'wpo_wcpdf_pro' ),
				'callback'		=> 'checkbox',
				'section'		=> 'packing_slip',
				'args'			=> array(
					'option_name'	=> $option_name,
					'id'			=> 'hide_virtual_products',
				)
			),
		);
		$settings_fields = $this->move_setting_after_id( $settings_fields, $hide_virtual_products, 'display_customer_notes' );

		return $settings_fields;
	}

	public function settings_fields_general_i18n( $settings_fields ) {
		$i18n_wrap = array('shop_name','shop_address','footer','extra_1','extra_2','extra_3');
		foreach ($settings_fields as $key => $settings_field) {
			if ($settings_field['type'] == 'setting' && in_array($settings_field['id'], $i18n_wrap)) {
				$settings_field['args']['callback'] = $settings_field['callback'];
				$settings_field['callback'] = 'i18n_wrap';
				$settings_fields[$key] = $settings_field;
			}
		}

		return $settings_fields;
	}

	public function move_setting_after_id( $settings, $insert_settings, $after_setting_id ) {
		$pos = 1; // this is already +1 to insert after the actual pos
		foreach ($settings as $setting) {
			if ( isset( $setting['id'] ) && $setting['id'] == $after_setting_id ) {
				$section = $setting['section'];
				break;
			} else {
				$pos++;
			}
		}

		// replace section
		if (isset($section)) {
			foreach ($insert_settings as $key => $insert_setting) {
				$insert_settings[$key]['section'] = $section;
			}
		} else {
			$empty_section = array(
				array(
					'type'			=> 'section',
					'id'			=> 'custom',
					'title'			=> '',
					'callback'		=> 'section',
				),
			);
			$insert_settings = array_merge($empty_section,$insert_settings);
		}
		// insert our api settings
		$new_settings = array_merge( array_slice($settings, 0, $pos, true), $insert_settings, array_slice($settings, $pos, NULL, true));

		return $new_settings;
	}

	public function get_translations () {
		check_ajax_referer( 'wcpdf_i18n_translations', 'security' );
		if (empty($_POST)) {
			die();
		}
		extract($_POST);

		// $icl_get_languages = 'a:3:{s:2:"en";a:8:{s:2:"id";s:1:"1";s:6:"active";s:1:"1";s:11:"native_name";s:7:"English";s:7:"missing";s:1:"0";s:15:"translated_name";s:7:"English";s:13:"language_code";s:2:"en";s:16:"country_flag_url";s:43:"http://yourdomain/wpmlpath/res/flags/en.png";s:3:"url";s:23:"http://yourdomain/about";}s:2:"fr";a:8:{s:2:"id";s:1:"4";s:6:"active";s:1:"0";s:11:"native_name";s:9:"Français";s:7:"missing";s:1:"0";s:15:"translated_name";s:6:"French";s:13:"language_code";s:2:"fr";s:16:"country_flag_url";s:43:"http://yourdomain/wpmlpath/res/flags/fr.png";s:3:"url";s:29:"http://yourdomain/fr/a-propos";}s:2:"it";a:8:{s:2:"id";s:2:"27";s:6:"active";s:1:"0";s:11:"native_name";s:8:"Italiano";s:7:"missing";s:1:"0";s:15:"translated_name";s:7:"Italian";s:13:"language_code";s:2:"it";s:16:"country_flag_url";s:43:"http://yourdomain/wpmlpath/res/flags/it.png";s:3:"url";s:26:"http://yourdomain/it/circa";}}';
		// $icl_get_languages = unserialize($icl_get_languages);
		$icl_get_languages = icl_get_languages('skip_missing=0');
		$input_type = strtolower($input_type);

		$translations = get_option( 'wpo_wcpdf_translations' );

		printf( '<div id="%s-translations" class="translations">', $input_attributes['id'])
		?>
			<ul>
				<?php foreach ( $icl_get_languages as $lang => $data ) {
					$translation_id = $data['language_code'].'_'.$input_attributes['id'];
					printf('<li><a href="#%s">%s</a></li>', $translation_id, $data['native_name']);
				}
				?>
			</ul>
			<?php foreach ( $icl_get_languages as $lang => $data ) {
				$translation_id = $data['language_code'].'_'.$input_attributes['id'];
				$value = isset($translations[$input_attributes['name']][$data['language_code']]) ? $translations[$input_attributes['name']][$data['language_code']] : '';
				printf( '<div id="%s">', $translation_id );
				switch ( $input_type ) {
					case 'textarea':
						printf( '<textarea cols="%1$s" rows="%2$s" data-language="%3$s">%4$s</textarea>', $input_attributes['cols'], $input_attributes['rows'], $data['language_code'], $value);
						break;
					case 'input':
						printf( '<input type="text" size="%1$s" value="%2$s" data-language="%3$s"/>', $input_attributes['size'], $value, $data['language_code'] );
						break;
				}
				$spinner = '<div class="spinner"></div>';
				printf('<div><button class="wpo-wcpdf-i18n-translations-save button button-primary">%s</button>%s</div>', __( 'Save translations', 'wpo_wcpdf_pro' ), $spinner);
				echo '</div>';
			}
			?>
		
		</div>
		<?php

		die();
	}
	public function save_translations () {
		check_ajax_referer( 'wcpdf_i18n_translations', 'security' );
		if (empty($_POST)) {
			die();
		}
		extract($_POST);

		$translations = get_option( 'wpo_wcpdf_translations' );
		$translations[$setting] = $strings;
		update_option( 'wpo_wcpdf_translations', $translations );

		die();
	}

	/**
	 * Scripts & styles for settings page
	 */
	public function load_scripts_styles ( $hook ) {
		// only load on our own settings page
		// maybe find a way to refer directly to WPO\WC\PDF_Invoices\Settings::$options_page_hook ?
		global $post_type;
		if ( ! ( $hook == 'woocommerce_page_wpo_wcpdf_options_page' || $hook == 'settings_page_wpo_wcpdf_options_page' || $post_type == 'shop_order' ) ) {
			return;				
		} 

		wp_enqueue_script(
			'wcpdf-file-upload-js',
			plugins_url( 'js/file-upload.js', dirname(__FILE__) ),
			array(),
			WPO_WCPDF_PRO_VERSION
		);

		wp_enqueue_style(
			'wcpdf-pro-settings-styles',
			plugins_url( 'css/settings-styles.css', dirname(__FILE__) ),
			array(),
			WPO_WCPDF_PRO_VERSION
		);

		wp_enqueue_script(
			'wcpdf-pro-settings-js',
			plugins_url( 'js/pro-settings.js', dirname(__FILE__) ),
			array(),
			WPO_WCPDF_PRO_VERSION
		);

		if (class_exists('SitePress') || class_exists('Polylang')) {
			wp_enqueue_style(
				'wcpdf-i18n',
				plugins_url( 'css/wcpdf-i18n.css', dirname(__FILE__) ),
				array(),
				WPO_WCPDF_PRO_VERSION
			);
			wp_enqueue_script(
				'wcpdf-i18n-settings',
				plugins_url( 'js/wcpdf-i18n-settings.js', dirname(__FILE__) ),
				array( 'jquery', 'jquery-ui-tabs' ),
				WPO_WCPDF_PRO_VERSION
			);
			wp_localize_script(
				'wcpdf-i18n-settings',
				'wpo_wcpdf_i18n',
				array(  
					'ajaxurl'        => admin_url( 'admin-ajax.php' ), // URL to WordPress ajax handling page
					'nonce'          => wp_create_nonce('wcpdf_i18n_translations'),
					'translate_text' => __( 'Translate', 'wpo_wcpdf_pro' ),
					// 'icon'		=> plugins_url( 'images/wpml-icon.png', dirname(__FILE__) ),
				)
			);
		}

		wp_enqueue_media();
	}

	/**
	 * Warning for missing pro templates
	 */
	public function pro_template_check () {
		if ( isset($_GET['page']) && $_GET['page'] == 'wpo_wcpdf_options_page' ) {
			// check if template is not 'Simple' (templates are bundled) & pro templates don't exist
			$template_path = WPO_WCPDF()->settings->get_template_path();
			$template_not_simple = $template_path != str_replace( '\\', '/', WPO_WCPDF()->plugin_path() . '/templates/Simple' );
			$proforma = wcpdf_get_document( 'proforma', null );
			$proforma_no_template = $proforma->is_enabled() && !file_exists( $template_path . '/proforma.php' );
			$credit_note = wcpdf_get_document( 'credit-note', null );
			$credit_note_no_template = $credit_note->is_enabled() && !file_exists( $template_path . '/credit-note.php' );

			if ( $template_not_simple && ( $proforma_no_template || $credit_note_no_template ) ) {
				$pro_template_folder = str_replace( ABSPATH, '', WPO_WCPDF_Pro()->plugin_path() . '/templates/Simple/' ); 
				?>
				<div class="error">
					<p>
					<?php _e("<b>Warning!</b> Your WooCommerce PDF Invoices & Packing Slips template folder does not contain templates for credit notes and/or proforma invoices.", 'wpo_wcpdf_pro');?> <br />
					<?php printf( __("If you are using WP Overnight premium templates, please update to the latest version. Otherwise copy the template files located in %s and adapt them to your own template.", 'wpo_wcpdf_pro'), '<code>'.$pro_template_folder.'</code>'); ?><br />
					</p>
				</div>
				<?php
			} // file_exists check
		}
	}

	/**
	 * Check if WooCommerce version is up to date for credit notes
	 */
	public function wc_version_check () {
		if ( isset($_GET['page']) && $_GET['page'] == 'wpo_wcpdf_options_page' ) {
			if ( version_compare( WOOCOMMERCE_VERSION, '2.2.7', '<' ) ) {
				?>
				<div class="error">
					<p>
					<?php printf(__("<b>Important note:</b> WooCommerce 2.2.7 or newer is required to print credit notes. You are currently using WooCommerce %s", 'wpo_wcpdf_pro'), WOOCOMMERCE_VERSION); ?> <br />
					</p>
				</div>
				<?php
			}
		}
	}

	/**
	 * add Pro settings tab to the PDF Invoice settings page
	 * @param  array $tabs slug => Title
	 * @return array $tabs with Pro
	 */
	public function settings_tab( $tabs ) {
		$tabs['pro'] = __('Pro','wpo_wcpdf_pro');
		return $tabs;
	}

	/**
	 * File upload callback.
	 *
	 * @param  array $args Field arguments.
	 */
	public function file_upload_callback( $args ) {
		$menu = $args['menu'];
		$id = $args['id'];
		$options = get_option( $menu );
	
		if ( isset( $options[$id] ) ) {
			$current = $options[$id];
		} else {
			$current = array(
				'id'		=> '',
				'filename'	=> '',
			);
		}

		$uploader_title = $args['uploader_title'];
		$uploader_button_text = $args['uploader_button_text'];
		$remove_button_text = $args['remove_button_text'];

		printf( '<input id="%1$s_id" name="%2$s[%1$s][id]" value="%3$s" type="hidden"  />', $id, $menu, $current['id'] );
		printf( '<input id="%1$s_filename" name="%2$s[%1$s][filename]" size="50" value="%3$s" readonly="readonly" />', $id, $menu, $current['filename'] );
		if ( !empty($current['id']) ) {
			printf('<span class="button remove_file_button" data-input_id="%1$s">%2$s</span>', $id, $remove_button_text );
		}
		printf( '<span class="button upload_file_button %4$s" data-uploader_title="%1$s" data-uploader_button_text="%2$s" data-remove_button_text="%3$s" data-input_id="%4$s">%2$s</span>', $uploader_title, $uploader_button_text, $remove_button_text, $id );
	
		// Displays option description.
		if ( isset( $args['description'] ) ) {
			printf( '<p class="description">%s</p>', $args['description'] );
		}
	}

	/**
	 * Multiple file upload callback.
	 *
	 * @param  array $args Field arguments.
	 */
	public function multiple_file_upload_callback( $args ) {
		extract( WPO_WCPDF()->settings->callbacks->normalize_settings_args( $args ) );

		for ($i=0; $i < 3; $i++) {
			$file_id = isset($current[$i]) ? $current[$i]['id'] : '';
			$filename = isset($current[$i]) ? $current[$i]['filename'] : '';

			echo '<div class="static-file-row">';
			printf( '<input id="%1$s_%2$s_id" name="%3$s[%2$s][id]" value="%4$s" type="hidden" class="static-file-id"/>', $id, $i, $setting_name, $file_id );
			printf( '<input id="%1$s_%2$s_filename" name="%3$s[%2$s][filename]" size="50" value="%4$s" readonly="readonly" class="static-file-filename"/>', $id, $i, $setting_name, $filename );
			if ( !empty($file_id) ) {
				printf('<span class="button remove_file_button" data-input_id="%1$s_%2$s">%3$s</span>', $id, $i, $remove_button_text );
			}
			printf( '<span class="button upload_file_button %4$s" data-uploader_title="%1$s" data-uploader_button_text="%2$s" data-remove_button_text="%3$s" data-input_id="%4$s_%5$s">%2$s</span>', $uploader_title, $uploader_button_text, $remove_button_text, $id, $i );
			echo '</div>';
		}
	
		// Displays option description.
		if ( isset( $description ) ) {
			printf( '<p class="description">%s</p>', $description );
		}
	}

	/**
	 * Address customization callback.
	 *
	 * @return void.
	 */
	public function custom_address_fields_section_callback() {
		echo __( 'Here you can modify the way the shipping and billing address are formatted in the PDF documents as well as add custom fields to them.', 'wpo_wcpdf_pro').'<br/>';
		echo __( 'You can use the following placeholders in addition to regular text and html tags (like h1, h2, b):', 'wpo_wcpdf_pro').'<br/>';
		?>
		<table style="background-color:#eee;border:1px solid #aaa; margin:1em; padding:1em;">
			<tr>
				<th style="text-align:left; padding:5px 5px 0 5px;"><?php _e( 'Billing fields', 'wpo_wcpdf_pro' ); ?></th>
				<th style="text-align:left; padding:5px 5px 0 5px;"><?php _e( 'Shipping fields', 'wpo_wcpdf_pro' ); ?></th>
				<th style="text-align:left; padding:5px 5px 0 5px;"><?php _e( 'Custom fields', 'wpo_wcpdf_pro' ); ?></th>
			</tr>
			<tr>
				<td style="vertical-align:top; padding:5px;">
					{{billing_address}}<br/>
					{{billing_first_name}}<br/>
					{{billing_last_name}}<br/>
					{{billing_company}}<br/>
					{{billing_address_1}}<br/>
					{{billing_address_2}}<br/>
					{{billing_city}}<br/>
					{{billing_postcode}}<br/>
					{{billing_country}}<br/>
					{{billing_country_code}}<br/>
					{{billing_state}}<br/>
					{{billing_state_code}}<br/>
					{{billing_email}}<br/>
					{{billing_phone}}
				</td>
				<td style="vertical-align:top; padding:5px;">
					{{shipping_address}}<br/>
					{{shipping_first_name}}<br/>
					{{shipping_last_name}}<br/>
					{{shipping_company}}<br/>
					{{shipping_address_1}}<br/>
					{{shipping_address_2}}<br/>
					{{shipping_city}}<br/>
					{{shipping_postcode}}<br/>
					{{shipping_country}}<br/>
					{{shipping_country_code}}<br/>
					{{shipping_state}}<br/>
					{{shipping_state_code}}
				</td>
				<td style="vertical-align:top; padding:5px;">
					{{custom_fieldname}}
				</td>
			</tr>
		</table>
		<?php
		echo __( 'Leave empty to use the default formatting.', 'wpo_wcpdf_pro').'<br/>';
	}

} // end class

endif; // class_exists

return new Settings();