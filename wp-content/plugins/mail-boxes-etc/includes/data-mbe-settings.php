<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$ws                = new Mbe_Shipping_Model_Ws();
$customer          = $ws->getCustomer();
$availableShipping = $ws->getAllowedShipmentServices( $customer );
$serviceOptions    = null;
//if ($ws->isCustomerActive()) {
if ( $customer->Enabled ) {
	if ( $availableShipping ) {
		foreach ( $availableShipping as $key => $array ) {
			$serviceOptions[ $array['value'] ] = $array['label'];
		}
	} else {
		$serviceOptions = array( '' => __( 'Set ws parameters and save to retrieve available shipment types', 'mail-boxes-etc' ) );
	}
} else {
	$serviceOptions = array( '' => __( 'Your user is not active', 'mail-boxes-etc' ) );
}


$shipmentTypeOptions = array( 'GENERIC' => __( 'Generic', 'mail-boxes-etc' ) );

if ( $customer && $customer->Permissions->canChooseMBEShipType ) {
	$shipmentTypeOptions['ENVELOPE'] = __( 'Envelope', 'mail-boxes-etc' );
}

$this->update_option( 'mbe_can_create_courier_waybill', (int) $customer->Permissions->canCreateCourierWaybill );

$countries_obj = new WC_Countries();
$countries     = version_compare( WC()->version, '2.1', '>=' ) ? $countries_obj->__get( 'countries' ) : $countries_obj->get_allowed_countries();

// Closure mode options
$closureModeOptions = array(
	Mbe_Shipping_Helper_Data::MBE_CLOSURE_MODE_AUTOMATICALLY => __( 'Automatically', 'mail-boxes-etc' ),
	Mbe_Shipping_Helper_Data::MBE_CLOSURE_MODE_MANUALLY      => __( 'Manually', 'mail-boxes-etc' ),
);

$closureTimeOptions = array();
for ( $i = 0; $i < 24; $i ++ ) {
	$closureTimeOptions[ $i . ':00:00' ] = $i . ':00';
	$closureTimeOptions[ $i . ':30:00' ] = $i . ':30';
}

// Closure mode options
$creationModeOptions = array(
	Mbe_Shipping_Helper_Data::MBE_CREATION_MODE_AUTOMATICALLY => __( 'Automatically', 'mail-boxes-etc' ),
	Mbe_Shipping_Helper_Data::MBE_CREATION_MODE_MANUALLY      => __( 'Manually', 'mail-boxes-etc' ),
);


$customInputs     = array();
$helper           = new Mbe_Shipping_Helper_Data();
$selectedServices = $helper->getAllowedShipmentServices();

if ( ! empty( $selectedServices ) ) {
	foreach ( $selectedServices as $t ) {
		$index         = array_search( $t, array_column( $availableShipping, 'value' ) );
		$shippingLabel = "";
		if ( isset( $availableShipping[ $index ]['label'] ) ) {
			$shippingLabel = $availableShipping[ $index ]['label'];
		}
		$labelDom       = sprintf( __( 'Free shipping Thresholds %s', 'mail-boxes-etc' ), $shippingLabel ) . ' - ' . __( 'Domestic', 'mail-boxes-etc' );
		$customInputs[] = array( 'label' => $labelDom, 'name' => 'mbelimit_' . strtolower( $t ) . '_dom' );
		$labelWw        = sprintf( __( 'Free shipping Thresholds %s', 'mail-boxes-etc' ), $shippingLabel ) . ' - ' . __( 'Rest of the world', 'mail-boxes-etc' );
		$customInputs[] = array( 'label' => $labelWw, 'name' => 'mbelimit_' . strtolower( $t ) . '_ww' );
	}
}


$csvModeOptions = array(
	Mbe_Shipping_Helper_Data::MBE_CSV_MODE_DISABLED => __( 'Disabled', 'mail-boxes-etc' ),
	Mbe_Shipping_Helper_Data::MBE_CSV_MODE_PARTIAL  => __( 'Partial', 'mail-boxes-etc' ),
	Mbe_Shipping_Helper_Data::MBE_CSV_MODE_TOTAL    => __( 'Total', 'mail-boxes-etc' ),
);

$insuranceModeOptions = array(
	Mbe_Shipping_Helper_Data::MBE_INSURANCE_WITH_TAXES    => __( 'With Taxes', 'mail-boxes-etc' ),
	Mbe_Shipping_Helper_Data::MBE_INSURANCE_WITHOUT_TAXES => __( 'Without Taxes', 'mail-boxes-etc' ),
);

$shipmentsCsvFileUrl = "";
if ( $helper->getShipmentsCsvFileUrl() ) {
	$shipmentsCsvFileUrl = sprintf( __( '<a href="%s" target="_blank">Download current file</a>', 'mail-boxes-etc' ), $helper->getShipmentsCsvFileUrl() );
}

$shipmentsCsvTemplateFileUrl = sprintf( __( '<a href="%s" target="_blank">Download template file</a>', 'mail-boxes-etc' ), $helper->getShipmentsCsvTemplateFileUrl() );
/**
 * Array of settings
 */
$result      = array(
	'mbe_version' => array(
		'title' => 'v.' . MBE_E_LINK_PLUGIN_VERSION,
		'type'  => 'hidden',
	),
	'mbe_debug' => array(
		'title'       => __( 'Debug', 'mail-boxes-etc' ),
		'type'        => 'select',
		'options'     => array( 1 => __( 'Yes', 'mail-boxes-etc' ), 0 => __( 'No', 'mail-boxes-etc' ) ),
		'label'       => __( 'Debug', 'mail-boxes-etc' ),
		'description' => __( 'Activate Debug mode to save MBE e-Link logs', 'mail-boxes-etc' ),
		'default'     => 0,
	)
);
$debugButton = [];
if ( is_file( $helper->getLogPluginPath() ) || is_file( $helper->getLogWsPath() ) ) {
	$debugButton = array(
		'debug_download' => array(
			'title'   => __( 'Download debug files', 'mail-boxes-etc' ),
			'type'    => 'mbebutton',
			'caption' => __( 'Download now', 'mail-boxes-etc' ),
			'class'   => 'button-secondary',
			'confirm' => false,
			'onclick' => get_admin_url() . 'admin-post.php?action=mbe_download_log_files',
			'blank'   => true,
		),
		'debug_delete'   => array(
			'title'       => __( 'Delete debug files', 'mail-boxes-etc' ),
			'type'        => 'mbebutton',
			'caption'     => __( 'Delete now', 'mail-boxes-etc' ),
			'class'       => 'button-secondary',
			'confirm'     => true,
			'onclick'     => get_admin_url() . 'admin-post.php?action=mbe_delete_log_files',
			'blank'       => false,
			'confirm_txt' => __( 'Do you want to permanentely delete the log files ?' ),
		),
	);
}
$result = array_merge( $result, $debugButton );
$result = array_merge( $result, array(
	'mbe_active'                => array(
		'title'   => __( 'Enable', 'mail-boxes-etc' ),
		'type'    => 'select',
		'options' => array( 1 => __( 'Yes', 'mail-boxes-etc' ), 0 => __( 'No', 'mail-boxes-etc' ) ),
		'label'   => __( 'Enable', 'mail-boxes-etc' ),
		'default' => 0,
	),
	'country'                   => array(
		'title'       => __( 'Country', 'mail-boxes-etc' ),
		'type'        => 'select',
		'options'     => array(
			'IT' => __( 'Italy', 'mail-boxes-etc' ),
			'ES' => __( 'Spain', 'mail-boxes-etc' ),
			'DE' => __( 'Germany', 'mail-boxes-etc' ),
			'FR' => __( 'France', 'mail-boxes-etc' ),
			'AT' => __( 'Austria', 'mail-boxes-etc' ),
			'PL' => __( 'Poland', 'mail-boxes-etc' ),
			'HR' => __( 'Croatia', 'mail-boxes-etc' )
		),
		'description' => __( 'Select your MBE Center\'s country', 'mail-boxes-etc' ),
		'desc_tip'    => true,
	),
	'url'                       => array(
		'title'       => __( 'OnlineMBE web-service URL', 'mail-boxes-etc' ),
		'type'        => 'text',
		'description' => __( 'Please contact your MBE Center', 'mail-boxes-etc' ),
	),
	'username'                  => array(
		'title' => __( 'Login MBE e-LINK *', 'mail-boxes-etc' ),
		'description' => __('provided by the MBE Center or created by the customer on MBE Online, if enabled', 'mail-boxes-etc' ),
		'type'  => 'text',
	),
	'password'                  => array(
		'title' => __( 'Passphrase MBE e-LINK *', 'mail-boxes-etc' ),
		'description' => __('provided by the MBE Center or created by the customer on MBE Online, if enabled', 'mail-boxes-etc' ),
		'type'  => 'password',
	),
	'description'               => array(
		'title' => __( 'MBE shipping description', 'mail-boxes-etc' ),
		'type'  => 'text',
	),
	'default_shipment_type'     => array(
		'title'   => __( 'Default Shipment type', 'mail-boxes-etc' ),
		'label'   => __( 'Default Shipment type', 'mail-boxes-etc' ),
		'type'    => 'select',
		'options' => $shipmentTypeOptions,
	),
	'allowed_shipment_services' => array(
		'title'       => __( 'MBE Services', 'mail-boxes-etc' ),
		'label'       => __( 'MBE Services', 'mail-boxes-etc' ),
		'type'        => 'multiselect',
		'options'     => $serviceOptions,
		'description' => __( 'Select MBE Services available to your Customers', 'mail-boxes-etc' ),
	),
) );

if ( ! empty( $selectedServices ) ) {
	// Custom label for selected methods
	$customLabels = [];
	foreach ( $selectedServices as $t ) {
		$index = array_search( $t, array_column( $availableShipping, 'value' ) );
		if ( isset( $availableShipping[ $index ]['label'] ) ) {
			$customLabels[ 'mbe_custom_label_' . strtolower( $t ) ] = array(
				'title'       => __( 'Custom name for', 'mail-boxes-etc' ) . ' ' . $availableShipping[ $index ]['label'],
				'type'        => 'text',
				'description' => __( 'Insert the custom name for the shipment method. Leave blank if you don\'t want to change the default value', 'mail-boxes-etc' ),
			);
		}
	}
	$result = array_merge( $result, $customLabels );

	// Default method mapping
	$result['mbe_enable_custom_mapping'] =
		array(
			'title'       => __( 'Default shipping methods mapping', 'mail-boxes-etc' ),
			'type'        => 'select',
			'default'     => 'no',
			'options'     => array( 'yes' => __( 'Yes', 'mail-boxes-etc' ), 'no' => __( 'No', 'mail-boxes-etc' ) ),
			'label'       => __( 'Enable default methods mapping', 'mail-boxes-etc' ),
			'description' => __( 'Enable the function to map default shipping methods to MBE services (MBE methods will not be available on the frontend)', 'mail-boxes-etc' ),
		);

	if ( $helper->isEnabledCustomMapping() ) {
		$defaultMethods  = WC()->shipping()->get_shipping_methods();
		// Remove mbe_shipping from default methods
		if (isset($defaultMethods['wf_mbe_shipping'])) {
			unset($defaultMethods['wf_mbe_shipping']);
		}
		$customMapping   = [];
		$selectedOptions = [];
		// Remove UPS Delivery point method as it doesn't make sense as a mapping
		foreach ( array_diff($selectedServices,[MBE_UAP_SERVICE]) as $key => $value ) {
			$index = array_search( $value, array_column( $availableShipping, 'value' ) );
			if ( isset( $availableShipping[ $index ]['label'] ) ) {
				$selectedOptions[ $value ] = __( $availableShipping[ $index ]['label'], 'mail-boxes-etc' );
			}
		}
		foreach ( $defaultMethods as $default_method ) {
			if ( isset( $default_method->id ) ) {
				$customMapping[ 'mbe_custom_mapping_' . strtolower( $default_method->id ) ] = array(
					'title'       => __( 'Custom mapping for', 'mail-boxes-etc' ) . ' ' . $default_method->method_title,
					'type'        => 'select',
					'default'     => '',
					'description' => __( 'Select the custom mapping for the default shipping method. Leave blank if you don\'t want to map it', 'mail-boxes-etc' ),
					'options'     => ( array_merge( [ '' => '' ], $selectedOptions ) )
				);
			}
		}
		$result = array_merge( $result, $customMapping );
	}

}


$result = array_merge( $result, array(
	'default_shipment_mode'        => array(
		'title'       => __( 'Shipment configuration mode', 'mail-boxes-etc' ),
		'description' => __( 'WARNING: activating the option \'Create one Shipment per Item\' with COD payment, the shopping cart\'s amount will be split and charged evenly on each shipment (based on number of items, not on their value)', 'mail-boxes-etc' ),
		'label'       => __( 'Shipment configuration mode', 'mail-boxes-etc' ),
		'type'        => 'select',
		'options'     => array(
			'1' => __( 'Create one Shipment per Item', 'mail-boxes-etc' ),
			'2' => __( 'Create one Shipment per shopping cart (parcels calculated based on weight)', 'mail-boxes-etc' ),
			'3' => __( 'Create one Shipment per shopping cart with one parcel per Item', 'mail-boxes-etc' ),
		),
	)
));
// CSV PACKAGES
if ($helper->getShipmentConfigurationMode()==2) {
	$result = array_merge( $result, array(
		'use_packages_csv' => [
			'title'       => __( 'Csv for standard packages', 'mail-boxes-etc' ),
			'type'        => 'select',
			'options'     => [ 0 => __( 'No', 'mail-boxes-etc' ), 1 => __( 'Yes', 'mail-boxes-etc' ) ],
			'label'       => __( 'Csv for standard packages', 'mail-boxes-etc' ),
			'description' => __( 'Load the standard packages via csv file', 'mail-boxes-etc' ),
			'default'     => 0,
		],
	));

	if ($helper->isCsvStandardPackageEnabled()) {
		$result = array_merge( $result, [
			'packages_csv' => [
				'title' => __( 'Packages via csv - File upload', 'mail-boxes-etc' ),
				'type'  => 'file',
			],
			'packages_csv_file' => [
				'title' => '',
				'type'  => 'hidden',
			],
			'packages_csv_download' => [
				'title'   => '',
				'type'    => 'mbebutton',
				'caption' => __( 'Download current file', 'mail-boxes-etc' ),
				'class'   => (empty($helper->getPackagesCsvFile())?'disabled ':'').'button-secondary',
				'confirm' => false,
				'onclick' => get_admin_url() . 'admin-post.php?action=mbe_download_standard_package_file&mbe_filetype=package',
				'blank'   => true,
				'custom_attributes' => [(empty($helper->getPackagesCsvFile())?'disabled':'') => '']
			],
			'packages_template_download' => [
				'title'   => '',
				'type'    => 'mbebutton',
				'caption' => __( 'Download template file', 'mail-boxes-etc' ),
				'class'   => 'button-secondary',
				'confirm' => false,
				'onclick' => get_admin_url() . 'admin-post.php?action=mbe_download_standard_package_file&mbe_filetype=package-template',
				'blank'   => true,
			],
			'packages_product_csv' => [
				'title' => __( 'Packages for products via csv - File upload', 'mail-boxes-etc' ),
				'type'  => 'file',
			],
			'packages_product_csv_file' => [
				'title' => '',
				'type'  => 'hidden',
			],
			'packages_product_csv_download' => [
				'title'   => '',
				'type'    => 'mbebutton',
				'caption' => __( 'Download current file', 'mail-boxes-etc' ),
				'class'   => (empty($helper->getPackagesProductCsvFile())?'disabled ':'').'button-secondary',
				'confirm' => false,
				'onclick' => get_admin_url() . 'admin-post.php?action=mbe_download_standard_package_file&mbe_filetype=package-product',
				'blank'   => true,
				'custom_attributes' => [(empty($helper->getPackagesProductCsvFile())?'disabled':'') => '']
			],
			'packages_product_template_download' => [
				'title'   => '',
				'type'    => 'mbebutton',
				'caption' => __( 'Download template file', 'mail-boxes-etc' ),
				'class'   => 'button-secondary',
				'confirm' => false,
				'onclick' => get_admin_url() . 'admin-post.php?action=mbe_download_standard_package_file&mbe_filetype=package-product-template',
				'blank'   => true,
			],
			'csv_packages_default' => [
				'title'       => __( 'Default shipping package', 'mail-boxes-etc' ),
				'type'        => 'select',
				'label'       => __( 'Default shipping package', 'mail-boxes-etc' ),
				'description' => __( 'Set the package to be used as default if the product doesn\'t have a related one', 'mail-boxes-etc' ),
				'options'     => $helper->getStandardPackagesForSelect()
			],
		]);
	}
}

// STANDARD PACKAGE SETTINGS
if (!$helper->useCsvStandardPackages()) {
	$result = array_merge( $result, array(
		'default_length'     => array(
			'title' => __( 'Default Package Length', 'mail-boxes-etc' ),
			'type'  => 'text',
		),
		'default_width'      => array(
			'title' => __( 'Default Package Width', 'mail-boxes-etc' ),
			'type'  => 'text',
		),
		'default_height'     => array(
			'title' => __( 'Default Package Height', 'mail-boxes-etc' ),
			'type'  => 'text',
		),
		'max_package_weight' => array(
			'title'       => __( 'Maximum Package Weight', 'mail-boxes-etc' ) . ' (' . esc_html( get_option( 'woocommerce_weight_unit' ) ) . ')',
			'type'        => 'text',
			'description' => __( 'Check if any limitation is applied with your MBE Center', 'mail-boxes-etc' ),
		)
	) );
}

$result = array_merge( $result, array(
	'max_shipment_weight'          => array(
		'title' => __( 'Maximum shipment weight', 'mail-boxes-etc' ) . ' (' . esc_html( get_option( 'woocommerce_weight_unit' ) ) . ')',
		'type'  => 'text',
	),
	'handling_type'                => array(
		'title'   => __( 'Markup - Application rule', 'mail-boxes-etc' ),
		'type'    => 'select',
		'options' => array(
			'P' => __( 'Percentage', 'mail-boxes-etc' ),
			'F' => __( 'Fixed amount', 'mail-boxes-etc' ),
		),
	),
	'handling_action'              => array(
		'title'   => __( 'Markup - Amount', 'mail-boxes-etc' ),
		'type'    => 'select',
		'options' => array(
			'S' => __( 'Shipment', 'mail-boxes-etc' ),
			'P' => __( 'Parcel', 'mail-boxes-etc' ),
		),
	),
	'handling_fee'                 => array(
		'title' => __( 'Handling Fee', 'mail-boxes-etc' ),
		'type'  => 'text',
	),
	'handling_fee_rounding'        => array(
		'title'   => __( 'Markup - Apply rounding', 'mail-boxes-etc' ),
		'type'    => 'select',
		'options' => array(
			'1' => __( 'No rounding', 'mail-boxes-etc' ),
			'2' => __( 'Round up or down automatically', 'mail-boxes-etc' ),
			'3' => __( 'Always round down', 'mail-boxes-etc' ),
			'4' => __( 'Always round up', 'mail-boxes-etc' ),
		),
	),
	'handling_fee_rounding_amount' => array(
		'title'   => __( 'Markup - Rounding unit (in €)', 'mail-boxes-etc' ),
		'type'    => 'select',
		'options' => array(
			'1' => '1',
			'2' => '0.5',
		),
	),
	'sallowspecific'               => array(
		'title'   => __( 'Ship to Applicable Countries', 'mail-boxes-etc' ),
		'type'    => 'select',
		'options' => array(
			'0' => __( 'All Allowed Countries', 'mail-boxes-etc' ),
			'1' => __( 'Specific Countries', 'mail-boxes-etc' ),
		),
	),
	'specificcountry'              => array(
		'title'   => __( 'Country', 'mail-boxes-etc' ),
		'type'    => 'multiselect',
		'options' => $countries,
	),
//	'sort_order' => array(
//		'title'           => __( 'Sort Order', 'mail-boxes-etc' ),
//		'type'            => 'text',
//	),
//	'mbe_shipping_specificerrmsg' => array(
//		'title'           => __( 'Displayed Error Message', 'mail-boxes-etc' ),
//		'type'            => 'textarea',
//	),
	'shipments_closure_mode'       => array(
		'title'   => __( 'OnlineMBE daily shipments closure - Mode', 'mail-boxes-etc' ),
		'type'    => 'select',
		'options' => $closureModeOptions,
	),
	'shipments_closure_time'       => array(
		'title'   => __( 'OnlineMBE daily time shipments closure (automatic mode only)', 'mail-boxes-etc' ),
		'type'    => 'select',
		'options' => $closureTimeOptions,
	),
	'shipments_creation_mode'      => array(
		'title'   => __( 'Shipments creation in OnlineMBE - Mode', 'mail-boxes-etc' ),
		'type'    => 'select',
		'options' => $creationModeOptions,
	),
	'mbe_add_track_id'             => array(
		'title'       => __( 'Add tracking id to email', 'mail-boxes-etc' ),
		'type'        => 'select',
		'options'     => array( 1 => __( 'Yes', 'mail-boxes-etc' ), 0 => __( 'No', 'mail-boxes-etc' ) ),
		'label'       => __( 'Add tracking id to email', 'mail-boxes-etc' ),
		'description' => __( 'Select if you want to add the tracking code to the email order detail', 'mail-boxes-etc' ),
		'default'     => 0,
	),
	'shipments_csv'                => array(
		'title' => __( 'Custom prices via csv - File upload', 'mail-boxes-etc' ),
		'type'  => 'file',
	),
	'shipments_csv_file'           => array(
		'title'       => '',
		'type'        => 'hidden',
		'description' => $shipmentsCsvFileUrl,
	),
	'shipments_csv_template_file'  => array(
		'title'       => '',
		'type'        => 'hidden',
		'description' => $shipmentsCsvTemplateFileUrl,
	),
	'shipments_csv_mode'           => array(
		'title'   => __( 'Custom prices via csv - File mode', 'mail-boxes-etc' ),
		'type'    => 'select',
		'options' => $csvModeOptions,
	),


	'mbe_shipments_csv_insurance_min' => array(
		'title' => __( 'Custom prices via csv - Min price for insurance extra-service', 'mail-boxes-etc' ),
		'type'  => 'text',
	),
	'mbe_shipments_csv_insurance_per' => array(
		'title' => __( 'Custom prices via csv - Percentage for insurance extra-service price calculation', 'mail-boxes-etc' ),
		'type'  => 'text',
	),
	'mbe_shipments_ins_mode'          => array(
		'title'   => __( 'Insurance extra-service - Declared value calculation', 'mail-boxes-etc' ),
		'type'    => 'select',
		'options' => $insuranceModeOptions,
	)
) );

// UAP Option
/*
if ( in_array( 'UST', ( explode( ',', $customer->Permissions->enabledCourierServices ) ? explode( ',', $customer->Permissions->enabledCourierServices ) : [] ) )
     && in_array( 'MDP', ( is_array( $selectedServices ) ? $selectedServices : [] ) )
     && $customer->Permissions->enabledShipUAP
) {
	$result['mbe_ship_to_UAP'] =
		array(
			'title'       => __( 'Ship to UAP', 'mail-boxes-etc' ),
			'type'        => 'select',
			'default'     => 0,
			'options'     => array( 1 => __( 'Yes', 'mail-boxes-etc' ), 0 => __( 'No', 'mail-boxes-etc' ) ),
			'label'       => __( 'Ship to UAP', 'mail-boxes-etc' ),
			'description' => __( 'Enable the option to ship to an UAP', 'mail-boxes-etc' ),
		);
}
*/

foreach ( $customInputs as $input ) {
	$result[ $input['name'] ] = array(
		'title' => __( $input['label'], 'mail-boxes-etc' ),
		'type'  => 'text',
	);
}

return $result;