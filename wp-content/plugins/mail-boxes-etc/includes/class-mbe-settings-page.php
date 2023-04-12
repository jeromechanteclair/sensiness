<?php
defined( 'ABSPATH' ) || exit;

include_once( __DIR__ . '/class-mbe-csv-to-table.php' );
include_once( __DIR__ . '/class-mbe-csv-shipping-to-table.php' );
include_once( __DIR__ . '/class-mbe-csv-package-to-table.php' );
include_once( __DIR__ . '/class-mbe-csv-package-product-to-table.php' );

if ( class_exists( 'Mbe_Settings', false ) ) {
	return new Mbe_Settings();
}

class Mbe_Settings extends WC_Settings_Page {

	protected $id;
	protected $helper;
	protected $customer;
	protected $ws;
	protected $selectedServices;
	protected $availableShipping;
	private $csv_shipping_to_table;
	private $csv_package_to_table;
	private $csv_package_product_to_table;

	public function __construct() {

		$this->helper                       = new Mbe_Shipping_Helper_Data();
		$this->ws                           = new Mbe_Shipping_Model_Ws();
		$this->csv_shipping_to_table        = new Mbe_Shipping_CsvShippingToTable();
		$this->csv_package_to_table         = new Mbe_Shipping_CsvPackageToTable();
		$this->csv_package_product_to_table = new Mbe_Shipping_CsvPackageProductToTable();
		$this->customer                     = $this->ws->getCustomer();
		$this->selectedServices             = $this->helper->getAllowedShipmentServices();
		$this->availableShipping            = $this->ws->getAllowedShipmentServices( $this->customer );


		$this->id    = MBE_ESHIP_ID;
		$this->label = __( MBE_ESHIP_PLUGIN_NAME . ' (v.' . MBE_ESHIP_PLUGIN_VERSION . ')', 'mail-boxes-etc' );

		/**
		 * Define settings custom types
		 */
		add_action( 'woocommerce_admin_field_mbebutton', array( $this, 'type_mbebutton' ) );
		add_action( 'woocommerce_admin_field_file', array( $this, 'type_file' ) );
		add_action( 'woocommerce_admin_field_info_colspan', array( $this, 'type_info_colspan' ) );
		add_action( 'woocommerce_admin_field_hidden', array( $this, 'type_text' ) );

		/**
		 *    Define all hooks instead of inheriting from parent
		 */
		add_action( 'woocommerce_sections_csv', array( $this, 'output_sections_csv' ) );

		parent::__construct();

	}

	public function save() {
		global $current_section;

		// reload and save Customer data to cache
		$this->ws->cacheCustomerData();

		if ( Mbe_Shipping_Helper_Data::MBE_COURIER_MODE_CSV == $this->helper->getOption( Mbe_Shipping_Helper_Data::XML_PATH_COURIER_CONFIG_MODE )
		     && $current_section === 'mbe_courier' ) {
			$this->csv_shipping_to_table->run();
		}
		if ( $this->helper->isCsvStandardPackageEnabled() && $current_section === 'mbe_packages' ) {
			$this->csv_package_to_table->run();
			$this->csv_package_product_to_table->run();
		}

		parent::save();
	}

	/**
	 * Get own sections.
	 *
	 * @return array
	 */
	protected function get_own_sections() {
		return
			[
				''              => __( 'Welcome', 'mail-boxes-etc' ),
				'mbe_general'   => __( 'General', 'mail-boxes-etc' ),
				'mbe_courier'   => __( 'Couriers and services', 'mail-boxes-etc' ),
				'mbe_packages'  => __( 'Packages', 'mail-boxes-etc' ),
				'mbe_shipments' => __( 'Shipping', 'mail-boxes-etc' ),
				'mbe_markup'    => __( 'Markup', 'mail-boxes-etc' ),
				'mbe_debug'     => __( 'Debug', 'mail-boxes-etc' ),

			];
	}

	/**
	 * Output the HTML for the settings.
	 */
	public function output() {
		global $current_section;

		$settings = $this->get_settings_for_section( $current_section );
		switch ( $current_section ) {
			case '':
				$this->output_welcome_page();
				break;
			default:
				WC_Admin_Settings::output_fields( $settings );
				break;
		}
	}

	protected function output_welcome_page()
	{
		$GLOBALS['hide_save_button'] = true;
		include_once __DIR__.'/settings-welcome.php';
	}

	protected function get_settings_for_mbe_general_section() {
		$sectionId    = 'mbe_general_login';
		$loginMode    = $this->helper->getLoginMode();
		$loginFields  = [];
		switch ( $loginMode ) {
			case Mbe_Shipping_Helper_Data::MBE_LOGIN_MODE_SIMPLE:
				$loginFields = [
					[
						'id'          => $this->id . '_' . 'country',
						'title'       => __( 'Country', 'mail-boxes-etc' ),
						'type'        => 'select',
						'options'     => [
							'IT' => __( 'Italy', 'mail-boxes-etc' ),
							'ES' => __( 'Spain', 'mail-boxes-etc' ),
							'DE' => __( 'Germany', 'mail-boxes-etc' ),
							'FR' => __( 'France', 'mail-boxes-etc' ),
							'AT' => __( 'Austria', 'mail-boxes-etc' ),
							'PL' => __( 'Poland', 'mail-boxes-etc' ),
							'HR' => __( 'Croatia', 'mail-boxes-etc' )
						],
						'description' => __( 'Select your MBE Center\'s country', 'mail-boxes-etc' ),
						'desc_tip'    => true,
					],
					[
						'id'    => $this->id . '_' . 'mbe_username',
						'title' => __( 'Login MBE Online', 'mail-boxes-etc' ),
						'type'  => 'text',
					],
					[
						'id'    => $this->id . '_' . 'mbe_password',
						'title' => __( 'Password MBE Online', 'mail-boxes-etc' ),
						'type'  => 'password'
					],
					[
						'id'         => $this->id . '_' . 'mbe_sign_in',
						'title'      => '',
						'type'       => 'mbebutton',
						'caption'    => __( 'Sign in', 'mail-boxes-etc' ),
						'class'      => 'button-secondary',
						'confirm'    => false,
//	                    'confirm_txt' => __( 'Do you really want to generate new credentials and invalidate the old ones?', 'mail-boxes-etc' ),
						'onclick'    => get_admin_url() . 'admin-post.php?action=mbe_sign_in',
						'parameters' => '{"' . $this->id . '_' . Mbe_Shipping_Helper_Data::XML_PATH_MBE_USERNAME . '":"","' . $this->id . '_' . Mbe_Shipping_Helper_Data::XML_PATH_MBE_PASSWORD . '":"","' . $this->id . '_' . 'country":""}',
						'blank'      => false,
						'lock'       => true,
					],
					[
						'type' => 'info',
						'text' => __('Or proceed with the', 'mail-boxes-etc') . ' ' . ' <a href="admin-post.php?action=mbe_goto_advanced_login">' . __( 'Advanced configuration', 'mail-boxes-etc' ) . '</a>'
					]
				];
				break;
			case Mbe_Shipping_Helper_Data::MBE_LOGIN_MODE_ADVANCED:
				$loginDisabled = $this->helper->getLoginLinkAdvanced() ? [] : [ 'disabled' => 'disabled' ];
				$loginFields   = [
					[
						'id'                => $this->id . '_' . 'country',
						'title'             => __( 'Country', 'mail-boxes-etc' ),
						'type'              => 'select',
						'options'           => [
							'IT' => __( 'Italy', 'mail-boxes-etc' ),
							'ES' => __( 'Spain', 'mail-boxes-etc' ),
							'DE' => __( 'Germany', 'mail-boxes-etc' ),
							'FR' => __( 'France', 'mail-boxes-etc' ),
							'AT' => __( 'Austria', 'mail-boxes-etc' ),
							'PL' => __( 'Poland', 'mail-boxes-etc' ),
							'HR' => __( 'Croatia', 'mail-boxes-etc' )
						],
						'desc'              => __( 'Select your MBE Center\'s country', 'mail-boxes-etc' ),
						'desc_tip'          => true,
//						'custom_attributes' => array_merge( $loginDisabled )
					],
					[
						'id'                => $this->id . '_' . 'url',
						'title'             => __( 'MBE Online web-service URL', 'mail-boxes-etc' ). '*',
						'type'              => 'text',
						'desc'              => __( 'Please contact your MBE Center', 'mail-boxes-etc' ),
						'custom_attributes' => array_merge( $loginDisabled )
					],
					[
						'id'                => $this->id . '_' . Mbe_Shipping_Helper_Data::XML_PATH_WS_USERNAME,
						'title'             => __( 'Login '.MBE_ESHIP_PLUGIN_NAME.' *', 'mail-boxes-etc' ),
//						'desc'              => __( 'provided by the MBE Center or created by the customer on MBE Online, if enabled', 'mail-boxes-etc' ),
						'type'              => 'text',
						'custom_attributes' => array_merge( [ 'autocomplete' => 'off' ], $loginDisabled )
					],
					[
						'id'                => $this->id . '_' . Mbe_Shipping_Helper_Data::XML_PATH_WS_PASSWORD,
						'title'             => __( 'Passphrase '.MBE_ESHIP_PLUGIN_NAME.' *', 'mail-boxes-etc' ),
//						'desc'              => __( 'provided by the MBE Center or created by the customer on MBE Online, if enabled', 'mail-boxes-etc' ),
						'type'              => 'password',
						'custom_attributes' => array_merge( [ 'autocomplete' => 'off' ], $loginDisabled )
					],
					[
						'id'      => $this->id . '_' . 'mbe_reset_login',
						'title'   => '',
						'type'    => 'mbebutton',
						'caption' => __( 'Reset', 'mail-boxes-etc' ),
						'class'   => 'button-secondary',
						'confirm' => false,
//	                    'confirm_txt' => __( 'Do you really want to generate new credentials and invalidate the old ones?', 'mail-boxes-etc' ),
						'onclick' => get_admin_url() . 'admin-post.php?action=mbe_reset_login',
						'blank'   => false,
						'lock'    => false,
						'description' =>__('Click here if you want to reset the information entered in the form above', 'mail-boxes-etc'),
					],
				];
				break;
		}

		$loginSection = [
			[
				'title' => __( 'MBE services', 'mail-boxes-etc' ),
				'type'  => 'title',
				'desc'  => __('The '.MBE_ESHIP_PLUGIN_NAME.' module, free, easy to install and configure, connects directly to your e-commerce, allowing you to offer different types of shipping and service levels to your customers, all characterized by the quality of Mail Boxes Etc.', 'mail-boxes-etc'),
				'id'    => $sectionId
			],
		];
		$loginSection = array_merge( $loginSection, $loginFields, [
			[
				'type' => 'sectionend',
				'id'   => $sectionId
			]
		] );


		$sectionId       = 'mbe_general_activate';
		$activateSection = [
			[
				'title' => __( 'Configuration Preferences', 'mail-boxes-etc' ),
				'type'  => 'title',
				'desc'  => '',
				'id'    => $sectionId
			],
			[
				'id'      => $this->id . '_' . 'mbe_active',
				'title'   => __( 'Enable', 'mail-boxes-etc' ),
				'type'    => 'select',
				'options' => array( 0 => __( 'No', 'mail-boxes-etc' ), 1 => __( 'Yes', 'mail-boxes-etc' ) ),
				'label'   => __( 'Enable', 'mail-boxes-etc' ),
				'desc'    => __( 'Setting to "Enabled" will enable MBE shipping options for the buyers of your eCommerce.', 'mail-boxes-etc' ),
			],
			[ 'type' => 'sectionend', 'id' => $sectionId ]
		];

		return array_merge( $loginSection, $activateSection );
	}

	protected function get_settings_for_mbe_courier_section() {
		$serviceOptions = null;
		if ( $this->customer && $this->customer->Enabled ) {
			if ( $this->availableShipping ) {
				foreach ( $this->availableShipping as $key => $array ) {
					$serviceOptions[ $array['value'] ] = $array['label'];
				}
			} else {
				$serviceOptions = array( '' => __( 'Set ws parameters and save to retrieve available shipment types', 'mail-boxes-etc' ) );
			}
		} else {
			$serviceOptions = array( '' => __( 'Your user is not active', 'mail-boxes-etc' ) );
		}

		$sectionId   = 'mbe_courier_selection';
		$modeSection = [
			[
				'title' => '',
				'type'  => 'title',
				'desc'  => __( 'For the plugin to work correctly, at least one option must be selected, and the services available are those set by the MBE Center on the MOL user page on HUB. Subsequently, it is possible to define a custom name for each MBE service selected in the field seen above. This set of fields is automatically generated dynamically, based on the values selected in the "Enabled MBE Services" list', 'mail-boxes-etc' ),
				'id'    => $sectionId
			],
			[
				'id'      => $this->id . '_' . 'mbe_courier_config_mode',
				'title'   => __( 'Configuration mode', 'mail-boxes-etc' ),
				'type'    => 'select',
				'options' => [
					Mbe_Shipping_Helper_Data::MBE_COURIER_MODE_CSV      => __( 'Custom prices via csv', 'mail-boxes-etc' ),
					Mbe_Shipping_Helper_Data::MBE_COURIER_MODE_MAPPING  => __( 'Default shipping methods mapping', 'mail-boxes-etc' ),
					Mbe_Shipping_Helper_Data::MBE_COURIER_MODE_SERVICES => __( 'MBE services', 'mail-boxes-etc' ),
				],
				'label'   => __( 'Configuration mode', 'mail-boxes-etc' ),
				'default' => Mbe_Shipping_Helper_Data::MBE_COURIER_MODE_SERVICES,
			],
			[ 'type' => 'sectionend', 'id' => $sectionId ]
		];

		$sectionId        = 'mbe_courier_config';
		$configDescr      = '';
		$configFields     = [];
		$selectedServices = $this->helper->getAllowedShipmentServices();
		switch ( $this->helper->getOption( Mbe_Shipping_Helper_Data::XML_PATH_COURIER_CONFIG_MODE ) ) {
			case Mbe_Shipping_Helper_Data::MBE_COURIER_MODE_CSV:
				$csvModeOptions = array(
					Mbe_Shipping_Helper_Data::MBE_CSV_MODE_PARTIAL => __( 'Partial', 'mail-boxes-etc' ),
					Mbe_Shipping_Helper_Data::MBE_CSV_MODE_TOTAL   => __( 'Total', 'mail-boxes-etc' ),
				);

				$insuranceModeOptions = array(
					Mbe_Shipping_Helper_Data::MBE_INSURANCE_WITH_TAXES    => __( 'With Taxes', 'mail-boxes-etc' ),
					Mbe_Shipping_Helper_Data::MBE_INSURANCE_WITHOUT_TAXES => __( 'Without Taxes', 'mail-boxes-etc' ),
				);

				$configFields = [
					[
						'id'    => $this->id . '_' . 'shipments_csv',
						'title' => __( 'Custom prices via csv - File upload', 'mail-boxes-etc' ),
						'type'  => 'file',
					],
					[
						'id'    => $this->id . '_' . 'shipments_csv_file',
						'title' => '',
						'type'  => 'hidden',
					],
					[
						'id'                => $this->id . '_' . 'shipments_csv_download',
						'title'             => '',
						'type'              => 'mbebutton',
						'caption'           => __( 'Download current file', 'mail-boxes-etc' ),
						'class'             => ( !is_file( $this->helper->getShipmentsCsvFileDir() ) ? 'disabled ' : '' ) . 'button-secondary',
						'confirm'           => false,
						'onclick'           => get_admin_url() . 'admin-post.php?action=mbe_download_shipping_file&mbe_filetype=shipping',
						'blank'             => true,
						'custom_attributes' => [ ( !is_file( $this->helper->getShipmentsCsvFileDir() ) ? 'disabled' : '' ) => '' ]
					],
					[
						'id'      => $this->id . '_' . 'shipment_csv_template_download',
						'title'   => '',
						'type'    => 'mbebutton',
						'caption' => __( 'Download template file', 'mail-boxes-etc' ),
						'class'   => 'button-secondary',
						'confirm' => false,
						'onclick' => get_admin_url() . 'admin-post.php?action=mbe_download_shipping_file&mbe_filetype=shipping-template',
						'blank'   => true,
					],
					[
						'id'      => $this->id . '_' . Mbe_Shipping_Helper_Data::XML_PATH_SHIPMENTS_CSV_MODE,
						'title'   => __( 'Custom prices via csv - File mode', 'mail-boxes-etc' ),
						'type'    => 'select',
						'options' => $csvModeOptions,
					],
					[
						'id'    => $this->id . '_' . 'mbe_shipments_csv_insurance_min',
						'title' => __( 'Custom prices via csv - Min price for insurance extra-service', 'mail-boxes-etc' ),
						'type'  => 'text',
					],
					[
						'id'    => $this->id . '_' . 'mbe_shipments_csv_insurance_per',
						'title' => __( 'Custom prices via csv - Percentage for insurance extra-service price calculation', 'mail-boxes-etc' ),
						'type'  => 'text',
					],
					[
						'id'      => $this->id . '_' . 'mbe_shipments_ins_mode',
						'title'   => __( 'Insurance extra-service - Declared value calculation', 'mail-boxes-etc' ),
						'type'    => 'select',
						'options' => $insuranceModeOptions,
					]
				];
				break;
			case Mbe_Shipping_Helper_Data::MBE_COURIER_MODE_MAPPING:
				$defaultMethods = WC()->shipping()->get_shipping_methods();
				// Remove mbe_shipping from default methods
				if ( isset( $defaultMethods[ MBE_ESHIP_ID ] ) ) {
					unset( $defaultMethods[ MBE_ESHIP_ID ] );
				}
				$selectedOptions = [];
//				$configFields[]  = [
//					'type' => 'info_colspan',
//					'text' => 'Associate MBE services with your couriers: below you can define the couriers to be associated with the MBE services you selected in the previous point.',
//					'css'  => '',
//				];
				// Remove UPS Delivery point method as it doesn't make sense as a mapping
				foreach ( array_diff( $selectedServices, [ MBE_UAP_SERVICE ] ) as $key => $value ) {
					$index = array_search( $value, array_column( $this->availableShipping, 'value' ) );
					if ( isset( $this->availableShipping[ $index ]['label'] ) ) {
						$selectedOptions[ $value ] = __( $this->availableShipping[ $index ]['label'], 'mail-boxes-etc' );
					}
				}
				foreach ( $defaultMethods as $default_method ) {
					if ( isset( $default_method->id ) ) {
						$configFields [] = [
							'id'      => $this->id . '_' . 'mbe_custom_mapping_' . strtolower( $default_method->id ),
							'title'   => __( 'Custom mapping for', 'mail-boxes-etc' ) . ' ' . $default_method->method_title,
							'type'    => 'select',
							'default' => '',
							'desc'    => __( 'Select the custom mapping for the default shipping method. Leave blank if you don\'t want to map it', 'mail-boxes-etc' ),
							'options' => ( array_merge( [ '' => '' ], $selectedOptions ) )
						];
					}
				}
				break;
			case Mbe_Shipping_Helper_Data::MBE_COURIER_MODE_SERVICES:
				$configDescr = __( "Introductory description of MBE shipments: \n - MBE Standard: is a service that offers you the possibility to ship in Italy and throughout Europe and is the ideal solution for individuals and companies who want to guarantee their customers reliability and punctuality.\n - MBE Express: is a service that guarantees the delivery of your shipments, in Italy, on average in two working days (within 48 hours of collection)\n - MBE Delivery Point: it is a service that allows you to send objects, packages, documents and much more, in a convenient and fast way from an MBE Center of your choice, to one of the many authorized and authorized collection points, both in Italy than abroad.", 'mail-boxes-etc' );
				if ( ! empty( $selectedServices ) ) {
					// Custom label for selected methods
					foreach ( $selectedServices as $t ) {
						$index = array_search( $t, array_column( $this->availableShipping, 'value' ) );
						if ( isset( $this->availableShipping[ $index ]['label'] ) ) {
							$configFields [] = [
								'id'    => $this->id . '_' . 'mbe_custom_label_' . strtolower( $t ),
								'title' => __( 'Custom name for', 'mail-boxes-etc' ) . ' ' . $this->availableShipping[ $index ]['label'],
								'type'  => 'text',
								'desc'  => __( "Insert the custom name for the shipment method. Leave blank if you don't want to change the default value", 'mail-boxes-etc' ),
							];
						}
					}
				}
				break;
		}
		$configSection = [
			[
				'title' => __( 'Configuration Preferences', 'mail-boxes-etc' ),
				'type'  => 'title',
				'desc'  => $configDescr,
				'id'    => $sectionId
			],
			[
				'id'      => $this->id . '_' . 'allowed_shipment_services',
				'title'   => __( 'MBE services', 'mail-boxes-etc' ),
				'label'   => __( 'MBE services', 'mail-boxes-etc' ),
				'type'    => 'multiselect',
				'class'   => 'wc-enhanced-select',
				'options' => $serviceOptions,
				'desc'    => __( 'Select all MBE services that you intend to offer to the buyers of your eCommerce for shipping. For the plugin to work correctly, at least one option must be selected', 'mail-boxes-etc' ),
			],
		];
		$configSection = array_merge( $configSection, $configFields, [
			[
				'type' => 'sectionend',
				'id'   => $sectionId
			]
		] );

		return array_merge( $modeSection, $configSection );
	}

	protected function get_settings_for_mbe_packages_section() {
		$sectionId = 'mbe_packages_standard';
		$settings  = [
			[
				'title' => __( 'Standard Configuration', 'mail-boxes-etc' ),
				'type'  => 'title',
				'desc'  => '',
				'id'    => $sectionId
			],
		];
		if ( ! $this->helper->useCsvStandardPackages() ) {
			$standardSizes = [
				[
					'id'    => $this->id . '_' . 'default_length',
					'title' => __( 'Default Package Length', 'mail-boxes-etc' ),
					'desc'  => __('Refer to the average length of shipments normally made. It can contain decimal numbers', 'mail-boxes-etc'),
					'type'  => 'text',
				],
				[
					'id'    => $this->id . '_' . 'default_width',
					'title' => __( 'Default Package Width', 'mail-boxes-etc' ),
					'desc'  => __('Refer to the average width of shipments normally made. It can contain decimal numbers', 'mail-boxes-etc'),
					'type'  => 'text',
				],
				[
					'id'    => $this->id . '_' . 'default_height',
					'title' => __( 'Default Package Height', 'mail-boxes-etc' ),
					'desc'  => __('Refer to the average height of shipments normally made. It can contain decimal numbers', 'mail-boxes-etc'),
					'type'  => 'text',
				],
				[
					'id'    => $this->id . '_' . 'max_package_weight',
					'title' => __( 'Maximum Package Weight', 'mail-boxes-etc' ) . ' (' . esc_html( get_option( 'woocommerce_weight_unit' ) ) . ')',
					'type'  => 'text',
					'desc'  => __( 'Check if any limitation is applied with your MBE Center', 'mail-boxes-etc' ),
				],
				[
					'id'    => $this->id . '_' . 'max_shipment_weight',
					'title' => __( 'Maximum shipment weight', 'mail-boxes-etc' ) . ' (' . esc_html( get_option( 'woocommerce_weight_unit' ) ) . ')',
					'type'  => 'text',
					'desc'  => __('Indicate the maximum weight of the shipment , intended as the sum of the weights of all packages shipped. In case of Envelope shipping, a default value will be applied 0.5 kg (not editable).','mail-boxes-etc'),
				]
			];
		} else {
			$standardSizes = [
				[
					'id'      => $this->id . '_' . 'csv_packages_default',
					'title'   => __( 'Default shipping package', 'mail-boxes-etc' ),
					'type'    => 'select',
					'label'   => __( 'Default shipping package', 'mail-boxes-etc' ),
					'desc'    => __( 'Set the package to be used as default if the product doesn\'t have a related one', 'mail-boxes-etc' ),
					'options' => $this->helper->getStandardPackagesForSelect()
				],
			];
		}
		$settings        = array_merge( $settings, $standardSizes );
		$settings[]      = [
			'id'      => $this->id . '_' . 'use_packages_csv',
			'title'   => __( 'Csv for standard packages', 'mail-boxes-etc' ),
			'type'    => 'select',
			'options' => [ 0 => __( 'No', 'mail-boxes-etc' ), 1 => __( 'Yes', 'mail-boxes-etc' ) ],
			'label'   => __( 'Csv for standard packages', 'mail-boxes-etc' ),
			'desc'    => __( 'Load the standard packages via csv file', 'mail-boxes-etc' ),
			'default' => 0,
		];
		$settings[] = [ 'type' => 'sectionend', 'id' => $sectionId ];
		$advancedSection = [];
		$csvSection      = [];

		if ( $this->helper->getShipmentConfigurationMode() == 2 ) {

			$sectionId  = 'mbe_packages_csv';
			if ( $this->helper->isCsvStandardPackageEnabled() ) {
				$csvButtons = [
					[
						'id'    => $this->id . '_' . 'packages_csv',
						'title' => __( 'Packages via csv - File upload', 'mail-boxes-etc' ),
						'type'  => 'file',
					],
					[
						'id'    => $this->id . '_' . 'packages_csv_file',
						'title' => '',
						'type'  => 'hidden'
					],
					[
						'id'                => $this->id . '_' . 'packages_csv_download',
						'title'             => '',
						'type'              => 'mbebutton',
						'caption'           => __( 'Download current file' , 'mail-boxes-etc' ),
						'class'             => ( !is_file( $this->helper->getCurrentCsvPackagesDir()  ) ? 'disabled ' : '' ) . 'button-secondary',
						'confirm'           => false,
						'onclick'           => get_admin_url() . 'admin-post.php?action=mbe_download_standard_package_file&mbe_filetype=package',
						'blank'             => true,
						'custom_attributes' => [ ( !is_file( $this->helper->getCurrentCsvPackagesDir()  ) ? 'disabled' : '' ) => '' ]
					],
					[
						'id'      => $this->id . '_' . 'packages_template_download',
						'title'   => '',
						'type'    => 'mbebutton',
						'caption' => __( 'Download template file', 'mail-boxes-etc' ),
						'class'   => 'button-secondary',
						'confirm' => false,
						'onclick' => get_admin_url() . 'admin-post.php?action=mbe_download_standard_package_file&mbe_filetype=package-template',
						'blank'   => true,
					],
					[
						'id'    => $this->id . '_' . 'packages_product_csv',
						'title' => __( 'Packages for products via csv - File upload', 'mail-boxes-etc' ),
						'type'  => 'file',
					],
					[
						'id'    => $this->id . '_' . 'packages_product_csv_file',
						'title' => '',
						'type'  => 'hidden',
					],
					[
						'id'                => $this->id . '_' . 'packages_product_csv_download',
						'title'             => '',
						'type'              => 'mbebutton',
						'caption'           => __( 'Download current file', 'mail-boxes-etc' ),
						'class'             => ( !is_file($this->helper->getCurrentCsvPackagesProductDir() ) ? 'disabled ' : '' ) . 'button-secondary',
						'confirm'           => false,
						'onclick'           => get_admin_url() . 'admin-post.php?action=mbe_download_standard_package_file&mbe_filetype=package-product',
						'blank'             => true,
						'custom_attributes' => [ ( !is_file( $this->helper->getCurrentCsvPackagesProductDir() ) ? 'disabled' : '' ) => '' ]
					],
					[
						'id'      => $this->id . '_' . 'packages_product_template_download',
						'title'   => '',
						'type'    => 'mbebutton',
						'caption' => __( 'Download template file', 'mail-boxes-etc' ),
						'class'   => 'button-secondary',
						'confirm' => false,
						'onclick' => get_admin_url() . 'admin-post.php?action=mbe_download_standard_package_file&mbe_filetype=package-product-template',
						'blank'   => true,
					],
				];

				$csvSection = [
					[
						'title' => __( 'CSV for standard packages', 'mail-boxes-etc' ),
						'type'  => 'title',
						'desc'  => __( '', 'mail-boxes-etc' ),
						'id'    => $sectionId,
					],
				];
				$csvSection = array_merge( $csvSection, $csvButtons, [
					[
						'type' => 'sectionend',
						'id'   => $sectionId
					]
				] );

				$sectionId       = 'mbe_packages_advanced';
				$advancedSection = [
					[
						'title' => __( 'Advanced configuration', 'mail-boxes-etc' ),
						'type'  => 'title',
						'desc'  => __( 'In this section it will be possible to update the loaded list for your standard packages and for your products', 'mail-boxes-etc' ),
						'id'    => $sectionId
					],
					[
						'id'      => $this->id . '_' . 'packages_csv_edit',
						'title'   => '',
						'type'    => 'mbebutton',
						'caption' => __( 'Edit Packages list', 'mail-boxes-etc' ),
						'class'   => 'button-secondary',
						'confirm' => false,
						'onclick' => get_admin_url() . 'admin.php?page=woocommerce_mbe_csv_tabs&csv=packages',
						'blank'   => false,
					],
					[
						'id'      => $this->id . '_' . 'packages_product_csv_edit',
						'title'   => '',
						'type'    => 'mbebutton',
						'caption' => __( 'Edit Packages for Products list', 'mail-boxes-etc' ),
						'class'   => 'button-secondary',
						'confirm' => false,
						'onclick' => get_admin_url() . 'admin.php?page=woocommerce_mbe_csv_tabs&csv=packages-products',
						'blank'   => false,
					],
					[ 'type' => 'sectionend', 'id' => $sectionId ]
				];
			}
		}

		return array_merge( $settings, $csvSection, $advancedSection );

	}

	protected function get_settings_for_mbe_shipments_section() {
		$sectionId = 'mbe_shipments_1';

//			$countries_obj = new WC_Countries();
//			$countries     = version_compare( WC()->version, '2.1', '>=' ) ? $countries_obj->__get( 'countries' ) : $countries_obj->get_allowed_countries();

		// Closure mode options
		$closureModeOptions = array(
			Mbe_Shipping_Helper_Data::MBE_CLOSURE_MODE_AUTOMATICALLY => __( 'Automatically', 'mail-boxes-etc' ),
			Mbe_Shipping_Helper_Data::MBE_CLOSURE_MODE_MANUALLY      => __( 'Manually', 'mail-boxes-etc' ),
		);
		// Closure time options
		$closureTimeOptions = array();
		for ( $i = 0; $i < 24; $i ++ ) {
			$closureTimeOptions[ $i . ':00:00' ] = $i . ':00';
			$closureTimeOptions[ $i . ':30:00' ] = $i . ':30';
		}

		// Creation mode options
		$creationModeOptions = array(
			Mbe_Shipping_Helper_Data::MBE_CREATION_MODE_AUTOMATICALLY => __( 'Automatically', 'mail-boxes-etc' ),
			Mbe_Shipping_Helper_Data::MBE_CREATION_MODE_MANUALLY      => __( 'Manually', 'mail-boxes-etc' ),
		);

		$shipmentTypeOptions = array( 'GENERIC' => __( 'Generic', 'mail-boxes-etc' ) );
		if ( $this->customer && $this->customer->Permissions->canChooseMBEShipType ) {
			$shipmentTypeOptions['ENVELOPE'] = __( 'Envelope', 'mail-boxes-etc' );
		}

		return [
			[
				'title' => '',
				'type'  => 'title',
				'desc'  => '',
				'id'    => $sectionId,
			],
			[
				'id'      => $this->id . '_' . 'sallowspecific',
				'title'   => __( 'Ship to Applicable Countries', 'mail-boxes-etc' ),
				'desc'    => __( 'Choose the countries for which you want to enable shipping', 'mail-boxes-etc' ),
				'type'    => 'select',
				'options' => array(
					'0' => __( 'All Allowed Countries', 'mail-boxes-etc' ),
					'1' => __( 'Specific Countries', 'mail-boxes-etc' ),
				),
			],
			[
				'id'    => $this->id . '_' . 'specificcountry',
				'title' => __( 'Country', 'mail-boxes-etc' ),
				'type'  => 'multi_select_countries',
//					'options' => $countries,
			],
			[
				'id'      => $this->id . '_' . 'default_shipment_mode',
				'title'   => __( 'Shipment configuration mode', 'mail-boxes-etc' ),
				'desc'    => __( 'WARNING: activating the option \'Create one Shipment per Item\' with COD payment, the shopping cart\'s amount will be split and charged evenly on each shipment (based on number of items, not on their value)', 'mail-boxes-etc' ),
				'label'   => __( 'Shipment configuration mode', 'mail-boxes-etc' ),
				'type'    => 'select',
				'options' => [
					'1' => __( 'Create one Shipment per Item', 'mail-boxes-etc' ),
					'2' => __( 'Create one Shipment per shopping cart (parcels calculated based on weight)', 'mail-boxes-etc' ),
					'3' => __( 'Create one Shipment per shopping cart with one parcel per Item', 'mail-boxes-etc' ),
				],
			],
			[
				'id'      => $this->id . '_' . 'default_shipment_type',
				'title'   => __( 'Default Shipment type', 'mail-boxes-etc' ),
				'label'   => __( 'Default Shipment type', 'mail-boxes-etc' ),
				'type'    => 'select',
				'options' => $shipmentTypeOptions,
			],
			[
				'id'      => $this->id . '_' . 'shipments_closure_mode',
				'title'   => __( 'Daily shipments closure - Mode', 'mail-boxes-etc' ),
				'type'    => 'select',
				'options' => $closureModeOptions,
			],
			[
				'id'      => $this->id . '_' . 'shipments_closure_time',
				'title'   => __( 'Daily shipments closure schedule (automatic mode only)', 'mail-boxes-etc' ),
				'type'    => 'select',
				'options' => $closureTimeOptions,
			],
			[
				'id'      => $this->id . '_' . 'shipments_creation_mode',
				'title'   => __( 'Shipments creation in MBE Online - Mode', 'mail-boxes-etc' ),
				'type'    => 'select',
				'options' => $creationModeOptions,
			],
			[
				'id'      => $this->id . '_' . 'mbe_add_track_id',
				'title'   => __( 'Add tracking id to email', 'mail-boxes-etc' ),
				'type'    => 'select',
				'options' => array( 1 => __( 'Yes', 'mail-boxes-etc' ), 0 => __( 'No', 'mail-boxes-etc' ) ),
				'label'   => __( 'Add tracking id to email', 'mail-boxes-etc' ),
				'desc'    => __( 'Select if you want to add the tracking code to the email order detail', 'mail-boxes-etc' ),
				'default' => 0,
			],
			[ 'type' => 'sectionend', 'id' => $sectionId ]
		];
	}

	protected function get_settings_for_mbe_markup_section() {
		$sectionId = 'mbe_markup_1';
		$settings  = [
			[
				'title' => '',
				'type'  => 'title',
				'desc'  => __( 'In this section you can define the markup to be made to shipments, both for the entire shipment and for the single package, and any rounding', 'mail-boxes-etc' ),
				'id'    => $sectionId,
			],
			[
				'id'      => $this->id . '_' . 'handling_type',
				'title'   => __( 'Markup - Application rule', 'mail-boxes-etc' ),
				'type'    => 'select',
				'options' => [
					'P' => __( 'Percentage', 'mail-boxes-etc' ),
					'F' => __( 'Fixed amount', 'mail-boxes-etc' ),
				],
			],
			[
				'id'      => $this->id . '_' . 'handling_action',
				'title'   => __( 'Markup - Amount', 'mail-boxes-etc' ),
				'type'    => 'select',
				'options' => [
					'S' => __( 'Shipment', 'mail-boxes-etc' ),
					'P' => __( 'Parcel', 'mail-boxes-etc' ),
				],
			],
			[
				'id'    => $this->id . '_' . 'handling_fee',
				'title' => __( 'Handling Fee', 'mail-boxes-etc' ),
				'type'  => 'text',
			],
			[
				'id'      => $this->id . '_' . 'handling_fee_rounding',
				'title'   => __( 'Markup - Apply rounding', 'mail-boxes-etc' ),
				'type'    => 'select',
				'options' => [
					'1' => __( 'No rounding', 'mail-boxes-etc' ),
					'2' => __( 'Round up or down automatically', 'mail-boxes-etc' ),
					'3' => __( 'Always round down', 'mail-boxes-etc' ),
					'4' => __( 'Always round up', 'mail-boxes-etc' ),
				],
			],
		];

		if ( $this->helper->getOption( 'handling_fee_rounding' ) <> '1' ) {
			$settings[] = [
				'id'      => $this->id . '_' . 'handling_fee_rounding_amount',
				'title'   => __( 'Markup - Rounding unit (in €)', 'mail-boxes-etc' ),
				'type'    => 'select',
				'options' => [
					'1' => '1',
					'2' => '0.5',
				],
			];
		}

		$settings[] = [ 'type' => 'sectionend', 'id' => $sectionId ];

//			$freeTresholds     = [];

		if ( ! empty( $this->selectedServices ) ) {
			$sectionId  = 'mbe_markup_freeship';
			$settings[] = [
				'title' => sprintf( __( 'Free shipping Thresholds %s', 'mail-boxes-etc' ), '' ),
				'type'  => 'title',
				'desc'  => '',
				'id'    => $sectionId,
			];

			foreach ( $this->selectedServices as $t ) {
				$index         = array_search( $t, array_column( $this->availableShipping, 'value' ) );
				$shippingLabel = "";
				if ( isset( $this->availableShipping[ $index ]['label'] ) ) {
					$shippingLabel = $this->availableShipping[ $index ]['label'];
				}
				$labelDom = sprintf( __( 'Free shipping Thresholds %s', 'mail-boxes-etc' ), $shippingLabel ) . ' - ' . __( 'Domestic', 'mail-boxes-etc' );
//					$freeTresholds[] = [ 'label' => $labelDom, 'name' => 'mbelimit_' . strtolower( $t ) . '_dom' ];
				$labelWw = sprintf( __( 'Free shipping Thresholds %s', 'mail-boxes-etc' ), $shippingLabel ) . ' - ' . __( 'Rest of the world', 'mail-boxes-etc' );
//					$freeTresholds[] = [ 'label' => $labelWw, 'name' => 'mbelimit_' . strtolower( $t ) . '_ww' ];

				$settings[] = [
					'id'    => $this->id . '_' . 'mbelimit_' . strtolower( $t ) . '_dom',
					'title' => __( $labelDom, 'mail-boxes-etc' ),
					'type'  => 'text',
				];
				$settings[] = [
					'id'    => $this->id . '_' . 'mbelimit_' . strtolower( $t ) . '_ww',
					'title' => __( $labelWw, 'mail-boxes-etc' ),
					'type'  => 'text',
				];
			}

			$settings[] = [ 'type' => 'sectionend', 'id' => $sectionId ];
		}

		return $settings;
	}

	protected function get_settings_for_mbe_debug_section() {
		$sectionId = 'mbe_debug_1';
		$settings  = [
			[
				'title' => '',
				'type'  => 'title',
				'desc'  => '',
				'id'    => $sectionId,
			],
			[
				'id'      => $this->id . '_' . 'mbe_debug',
				'title'   => __( 'Debug', 'mail-boxes-etc' ),
				'type'    => 'select',
				'default' => 0,
				'options' => [ 1 => __( 'Yes', 'mail-boxes-etc' ), 0 => __( 'No', 'mail-boxes-etc' ) ],
				'label'   => __( 'Debug', 'mail-boxes-etc' ),
				'desc'    => __( 'Activate Debug mode to save '.MBE_ESHIP_PLUGIN_NAME.' logs', 'mail-boxes-etc' ),
			],
		];

		$debugButton = [];
		if ( is_file( $this->helper->getLogPluginPath() ) || is_file( $this->helper->getLogWsPath() ) ) {
			$debugButton[] =
				[
					'id'      => $this->id . '_' . 'debug_download',
					'title'   => __( 'Download debug files', 'mail-boxes-etc' ),
					'type'    => 'mbebutton',
					'caption' => __( 'Download now', 'mail-boxes-etc' ),
					'class'   => 'button-secondary',
					'confirm' => false,
					'onclick' => get_admin_url() . 'admin-post.php?action=mbe_download_log_files',
					'blank'   => true,
				];
			$debugButton[] =
				[
					'id'          => $this->id . '_' . 'debug_delete',
					'title'       => __( 'Delete debug files', 'mail-boxes-etc' ),
					'type'        => 'mbebutton',
					'caption'     => __( 'Delete now', 'mail-boxes-etc' ),
					'class'       => 'button-secondary',
					'confirm'     => true,
					'onclick'     => get_admin_url() . 'admin-post.php?action=mbe_delete_log_files',
					'blank'       => false,
					'confirm_txt' => __( 'Do you want to permanentely delete the log files ?' ),
				];
		}
		$settings   = array_merge( $settings, $debugButton );
		$settings[] = [ 'type' => 'sectionend', 'id' => $sectionId ];

		return $settings;
	}

	public function output_sections() {
		global $current_section;

		$logoSrc  = '';
		$sections = $this->get_sections();

		if ( empty( $sections ) || 1 === count( $sections ) ) {
			return;
		}
		echo '<ul class="subsubsub">';

		$array_keys = array_keys( $sections );

		foreach ( $sections as $id => $label ) {
			$url       = admin_url( 'admin.php?page=wc-settings&tab=' . $this->id . '&section=' . sanitize_title( $id ) );
			$class     = ( $current_section === $id ? 'current' : '' );
			$color     = $class === 'current' ? 'red' : 'black';
			$separator = ( end( $array_keys ) === $id ? '' : '|' );
			$text      = esc_html( $label );
			echo "<li><a style='font-size: 14px; color:$color; padding-left:5px; padding-right:5px'  href='$url' class='$class'>$text</a> $separator </li>"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}

		echo '</ul><br class="clear" />';
	}

	public function type_mbebutton( $value ) {

		$field_key = $value['id'];

		$defaults = array(
			'title'             => '',
			'disabled'          => false,
			'class'             => '',
			'css'               => '',
			'placeholder'       => '',
			'type'              => 'text',
			'desc_tip'          => false,
			'description'       => '',
			'custom_attributes' => [],
			'caption'           => '',
			'onclick'           => '',
			'parameters'        => '',
			'blank'             => true,
			'confirm'           => false,
			'confirm_txt'       => '',
			'lock'              => false,
		);

		$data = wp_parse_args( $value, $defaults );

//			ob_start();
		?>
        <tr>
            <th scope="row" class="titledesc">
                <label for="<?php echo esc_attr( $field_key ); ?>"><?php echo esc_html( $data['title'] ); ?></label>
				<?php echo $this->get_tooltip_html( $data ); ?>
            </th>

            <td class="forminp forminp-<?php echo sanitize_title( $data['type'] ) ?>">

                <input
                        id="<?php echo esc_attr( $field_key ); ?>"
                        type="button"
                        value="<?php echo esc_attr( $data['caption'] ); ?>"
                        style="<?php echo esc_attr( $data['css'] ); ?>"
                        class="<?php echo esc_attr( $data['class'] ); ?>"
                        onclick="mbeButtonAction(
                                this,
                                '<?php echo esc_attr( $data['onclick'] ); ?>',
                                '<?php echo esc_attr( $data['blank'] ) ? '_blank' : '_self'; ?>',
                                '<?php echo esc_attr( $data['confirm'] ) ? 'true' : 'false'; ?>',
                                '<?php echo esc_attr( $data['confirm_txt'] ); ?>',
                                '<?php echo esc_attr( $data['lock'] ) ? 'true' : 'false'; ?>'
                                )"
					<?php echo $this->get_custom_attribute_html( $data ) ?>
                />
				<?php echo $this->get_description_html( $data ); ?>

            </td>
        </tr>
        <script>
            if (typeof mbeButtonAction !== "function") {
                function mbeButtonAction(button, url, target, confirmation, confirmationText, lock) {
                    let ok = false;
                    if (confirmation === 'true') {
                        if (confirm(confirmationText) === true) {
                            ok = true;
                        }
                    } else {
                        ok = true;
                    }
                    if (ok === true) {
                        if (lock === 'true') {
                            button.disabled = true;
                        }
                        let a = document.createElement('a');
                        a.target = target;
                        let parameters = '<?php echo $data['parameters'] ?>'
                        let queryString = '';
                        if (parameters.length) {
                            parameters = JSON.parse(parameters)
                            Object.entries(parameters).forEach((entry) => {
                                const [key, value] = entry;
                                parameters[key] = document.getElementById(key).value;
                            });
                            queryString = '&' + (new URLSearchParams(parameters).toString())
                        }
                        a.href = url + queryString;
                        a.click();
                    }
                }
            }

        </script>

		<?php
//			return ob_get_clean();


	}

	public function type_file( $value ) {
		$field_key = $value['id'];
		$defaults  = array(
			'title'             => '',
			'disabled'          => false,
			'class'             => '',
			'css'               => '',
			'placeholder'       => '',
			'type'              => 'text',
			'desc_tip'          => false,
			'desc'              => '',
			'custom_attributes' => array(),
		);

		$data = wp_parse_args( $value, $defaults );

//	        ob_start();
		?>
        <tr valign="top">
            <th scope="row" class="titledesc">
                <label for="<?php echo esc_attr( $field_key ); ?>"><?php echo wp_kses_post( $data['title'] ); ?><?php echo $this->get_tooltip_html( $data ); // WPCS: XSS ok.
					?></label>
            </th>
            <td class="forminp">
                <fieldset>
                    <legend class="screen-reader-text"><span><?php echo wp_kses_post( $data['title'] ); ?></span>
                    </legend>
                    <input class="input-text regular-input <?php echo esc_attr( $data['class'] ); ?>"
                           type="<?php echo esc_attr( $data['type'] ); ?>" name="<?php echo esc_attr( $field_key ); ?>"
                           id="<?php echo esc_attr( $field_key ); ?>" style="<?php echo esc_attr( $data['css'] ); ?>"
                           value=""
                           placeholder="<?php echo esc_attr( $data['placeholder'] ); ?>" <?php disabled( $data['disabled'], true ); ?> <?php echo $this->get_custom_attribute_html( $data ); // WPCS: XSS ok.
					?> />
					<?php echo $this->get_description_html( $data ); // WPCS: XSS ok.
					?>
                </fieldset>
            </td>
        </tr>
		<?php

//	        return ob_get_clean();
	}

	public function type_text( $value ) {
		$field_key = $value['id'];
		$optionKey = preg_replace( '/' . MBE_ESHIP_ID . '_/', '', $field_key, 1 );

		$defaults = array(
			'title'             => '',
			'disabled'          => false,
			'class'             => '',
			'css'               => '',
			'placeholder'       => '',
			'type'              => 'text',
			'desc_tip'          => false,
			'description'       => '',
			'custom_attributes' => [],
		);

		$data       = wp_parse_args( $value, $defaults );
		$displayRow = $data['type'] === 'hidden' ? 'display:none;' : '';
		?>
        <tr valign="top" style="<?php echo esc_attr( $displayRow ) ?>">
            <th scope="row" class="titledesc">
                <label for="<?php echo esc_attr( $field_key ); ?>"><?php echo wp_kses_post( $data['title'] ); ?><?php echo $this->get_tooltip_html( $data ); // WPCS: XSS ok.
					?></label>
            </th>
            <td class="forminp">
                <fieldset>
                    <legend class="screen-reader-text"><span><?php echo wp_kses_post( $data['title'] ); ?></span>
                    </legend>
                    <input class="input-text regular-input <?php echo esc_attr( $data['class'] ); ?>"
                           type="<?php echo esc_attr( $data['type'] ); ?>" name="<?php echo esc_attr( $field_key ); ?>"
                           id="<?php echo esc_attr( $field_key ); ?>" style="<?php echo esc_attr( $data['css'] ); ?>"
                           value="<?php echo esc_attr( $this->helper->getOption( $optionKey ) ); ?>"
                           placeholder="<?php echo esc_attr( $data['placeholder'] ); ?>" <?php disabled( $data['disabled'], true ); ?> <?php echo $this->get_custom_attribute_html( $data ); // WPCS: XSS ok.
					?> />
					<?php echo $this->get_description_html( $data ); // WPCS: XSS ok.
					?>
                </fieldset>
            </td>
        </tr>
		<?php
	}

	public function type_info_colspan( $value ) {
		$defaults = array(
			'text'  => '',
			'class' => '',
			'css'   => '',
		);

		$data = wp_parse_args( $value, $defaults );
		?>
        <tr>
            <td colspan="2" scope="row" class="<?php esc_attr( $data['class'] ) ?>"
                style="<?php esc_attr( $data['css'] ) ?>">
				<?php echo wp_kses_post( wpautop( wptexturize( $data['text'] ) ) ); ?>
            </td>
        </tr>
		<?php
	}

	public function get_custom_attribute_html( $data ) {
		$custom_attributes = array();

		if ( ! empty( $data['custom_attributes'] ) && is_array( $data['custom_attributes'] ) ) {
			foreach ( $data['custom_attributes'] as $attribute => $attribute_value ) {
				$custom_attributes[] = esc_attr( $attribute ) . '="' . esc_attr( $attribute_value ) . '"';
			}
		}

		return implode( ' ', $custom_attributes );
	}

	public function get_description_html( $data ) {
		if ( true === $data['desc_tip'] ) {
			$description = '';
		} elseif ( ! empty( $data['desc_tip'] ) ) {
			$description = $data['description'];
		} elseif ( ! empty( $data['description'] ) ) {
			$description = $data['description'];
		} else {
			$description = '';
		}

		return $description ? '<p class="description">' . wp_kses_post( $description ) . '</p>' . "\n" : '';
	}

	public function get_tooltip_html( $data ) {
		if ( true === $data['desc_tip'] ) {
			$tip = $data['description'];
		} elseif ( ! empty( $data['desc_tip'] ) ) {
			$tip = $data['desc_tip'];
		} else {
			$tip = '';
		}

		return $tip ? wc_help_tip( $tip, true ) : '';
	}

}

return new Mbe_Settings();