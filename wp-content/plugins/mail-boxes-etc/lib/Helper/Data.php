<?php

class Mbe_Shipping_Helper_Data
{
    private $_mbeMediaDir = 'mbe';
    private $_csvDir = 'csv';
	const MBE_LOG_WS = 'mbe_ws.log';
	const MBE_LOG_PLUGIN = 'mbe_shipping.log';
//    const MBE_SHIPPING_PREFIX = "mbe_shipping_";
	const MBE_CSV_PACKAGES_RESERVED_CODE = 'settings';
	const MBE_WC_ELINK_SETTINGS_PREFIX = 'woocommerce_wf_mbe_shipping_';
    const MBE_ELINK_SETTINGS = self::MBE_WC_ELINK_SETTINGS_PREFIX . 'settings';
	const MBE_SETTINGS_CSV_SHIPMENTS = MBE_ESHIP_ID . '_' . self::XML_PATH_SHIPMENTS_CSV;
	const MBE_SETTINGS_CSV_PACKAGE = MBE_ESHIP_ID . '_' . self::XML_PATH_PACKAGES_CSV;
	const MBE_SETTINGS_CSV_PACKAGE_PRODUCT = MBE_ESHIP_ID . '_' . self::XML_PATH_PACKAGES_PRODUCT_CSV;
    const SHIPMENT_SOURCE_TRACKING_NUMBER = "woocommerce_mbe_tracking_number";
    const SHIPMENT_SOURCE_TRACKING_FILENAME = 'woocommerce_mbe_tracking_filename';
	const SHIPMENT_SOURCE_RETURN_TRACKING_NUMBER = "woocommerce_mbe_return_tracking_number";

	const MBE_CSV_PACKAGES_TABLE_NAME = MBE_ESHIP_ID . '_' . 'standard_packages';
	const MBE_CSV_PACKAGES_PRODUCT_TABLE_NAME = MBE_ESHIP_ID . '_' . 'standard_packages_products';
	const MBE_CSV_RATES_TABLE_NAME = MBE_ESHIP_ID . '_' . 'shipping_rate';
	const MBE_CSV_PACKAGE_TEMPLATE = 'mbe_package_csv_template.csv';
	const MBE_CSV_PACKAGE_PRODUCT_TEMPLATE = 'mbe_package_product_csv_template.csv';
	const MBE_CSV_RATES_TEMPLATE_CSV = 'mbe_csv_template.csv';

    //MAIN
    const CONF_DEBUG = 'mbe_debug';
    const XML_PATH_ENABLED = 'mbe_active';
    const XML_PATH_COUNTRY = 'country';
    //WS
    const XML_PATH_WS_URL = 'url';
    const XML_PATH_WS_USERNAME = 'username';
    const XML_PATH_WS_PASSWORD = 'password';
	const XML_PATH_MBE_USERNAME = 'mbe_username';
	const XML_PATH_MBE_PASSWORD = 'mbe_password';
    //OPTIONS
    const XML_PATH_DESCRIPTION = 'description';
    const XML_PATH_DEFAULT_SHIPMENT_TYPE = 'default_shipment_type';
    const XML_PATH_ALLOWED_SHIPMENT_SERVICES = 'allowed_shipment_services';
    const XML_PATH_SHIPMENT_CONFIGURATION_MODE = 'default_shipment_mode';
    const XML_PATH_DEFAULT_LENGTH = 'default_length';
    const XML_PATH_DEFAULT_WIDTH = 'default_width';
    const XML_PATH_DEFAULT_HEIGHT = 'default_height';
    const XML_PATH_MAX_PACKAGE_WEIGHT = 'max_package_weight';
    const XML_PATH_MAX_SHIPMENT_WEIGHT = 'max_shipment_weight';
    const XML_PATH_HANDLING_TYPE = 'handling_type';
    const XML_PATH_HANDLING_ACTION = 'handling_action';
    const XML_PATH_HANDLING_FEE = 'handling_fee';
    const XML_PATH_HANDLING_FEE_ROUNDING = 'handling_fee_rounding';
    const XML_PATH_HANDLING_FEE_ROUNDING_AMOUNT = 'handling_fee_rounding_amount';
    const XML_PATH_SALLOWSPECIFIC = 'sallowspecific';
    const XML_PATH_SPECIFICCOUNTRY = 'specificcountry';
    const XML_PATH_SORT_ORDER = 'sort_order';
    const XML_PATH_MAXIMUM_TIME_FOR_SHIPPING_BEFORE_THE_END_OF_THE_DAY = 'maximum_time_for_shipping_before_the_end_of_the_day';
    const XML_PATH_SPECIFICERRMSG = 'specificerrmsg';
    const XML_PATH_WEIGHT_TYPE = 'weight_type';
    const XML_PATH_SHIPMENTS_CLOSURE_MODE = 'shipments_closure_mode';
    const XML_PATH_SHIPMENTS_CLOSURE_TIME = 'shipments_closure_time';
    const XML_PATH_SHIPMENTS_CREATION_MODE = 'shipments_creation_mode';
    const XML_PATH_ADD_TRACK_ID = 'mbe_add_track_id';
    const XML_PATH_SHIPMENT_CUSTOM_LABEL = 'mbe_custom_label';
// STANDARD PACKAGES CSV
	const XML_PATH_CSV_STANDARD_PACKAGE_USE_CSV = 'use_packages_csv';
	const XML_PATH_CSV_STANDARD_PACKAGE_DEFAULT = 'packages_default';

    const XML_PATH_SHIPMENTS_CSV = 'shipments_csv';
    const XML_PATH_SHIPMENTS_CSV_FILE = 'shipments_csv_file';

	const XML_PATH_PACKAGES_CSV = 'packages_csv';
	const XML_PATH_PACKAGES_CSV_FILE = 'packages_csv_file';
	const XML_PATH_PACKAGES_PRODUCT_CSV = 'packages_product_csv';
	const XML_PATH_PACKAGES_PRODUCT_CSV_FILE = 'packages_product_csv_file';

    const XML_PATH_SHIPMENTS_CSV_MODE = 'shipments_csv_mode';
    const XML_PATH_SHIPMENTS_CSV_INSURANCE_MIN = 'mbe_shipments_csv_insurance_min';
    const XML_PATH_SHIPMENTS_CSV_INSURANCE_PERCENTAGE = 'mbe_shipments_csv_insurance_per';
    const XML_PATH_SHIPMENTS_INSURANCE_MODE = 'mbe_shipments_ins_mode';


    const XML_PATH_THRESHOLD = 'mbelimit';

    const XML_PATH_CAN_CREATE_COURIER_WAYBILL = 'mbe_can_create_courier_waybill';

    //const

    const MBE_SHIPMENT_STATUS_CLOSED = "Closed";
    const MBE_SHIPMENT_STATUS_OPEN = "Opened";

    const MBE_CLOSURE_MODE_AUTOMATICALLY = 'automatically';
    const MBE_CLOSURE_MODE_MANUALLY = 'manually';

    const MBE_CREATION_MODE_AUTOMATICALLY = 'automatically';
    const MBE_CREATION_MODE_MANUALLY = 'manually';


    const MBE_CSV_MODE_DISABLED = 'disabled';
    const MBE_CSV_MODE_TOTAL = 'total';
    const MBE_CSV_MODE_PARTIAL = 'partial';
    const MBE_INSURANCE_WITH_TAXES = 'insurance_with_taxes';
    const MBE_INSURANCE_WITHOUT_TAXES = 'insurance_without_taxes';
    const MBE_SHIPPING_WITH_INSURANCE_CODE_SUFFIX = '_INSURANCE';
    const MBE_SHIPPING_WITH_INSURANCE_LABEL_SUFFIX = ' + Insurance';
    const MBE_SHIPPING_TRACKING_SEPARATOR = ',';
    const MBE_SHIPPING_TRACKING_SEPARATOR__OLD = '--';

	// New settings Courier Mode
	const MBE_COURIER_MODE_CSV = 1;
	const MBE_COURIER_MODE_MAPPING = 2;
	const MBE_COURIER_MODE_SERVICES = 3;
	const XML_PATH_COURIER_CONFIG_MODE = 'mbe_courier_config_mode';

	//New settings Login Mode
	const XML_PATH_LOGIN_MODE = 'mbe_login_mode';
	const XML_PATH_LOGIN_LINK_ADV = 'mbe_login_link_advanced';
	const MBE_LOGIN_MODE_SIMPLE = 1;
	const MBE_LOGIN_MODE_ADVANCED = 2;

	protected $csv_package_model;
	protected $csv_package_product_model;
	protected $_options;


	public function __construct()
    {
//        $this->_options = get_option(self::MBE_SETTINGS);
	    $this->csv_package_model = new Mbe_Shipping_Model_Csv_Package();
	    $this->csv_package_product_model = new Mbe_Shipping_Model_Csv_Package_Product();
    }

    public function checkMbeDir($path)
    {
        $this->checkDir($path);
        if (!file_exists($path)) {
            mkdir($path);
        }
    }

    public function checkDir($dir)
    {
        if (!file_exists($dir)) {
            mkdir($dir, 0755, true);
        }
    }

    //TODO: check if is necessary to specify storeid or get current storeid
    public function isEnabled()
    {
        return $this->getOption(self::XML_PATH_ENABLED);
    }

    public function debug()
    {
        $result = ($this->getOption(self::CONF_DEBUG) == 1);
        return $result;
    }


    public function enabledCountry($country = null)
    {
        if ($this->getSallowspecific()) {
            return in_array($country, $this->getSpecificcountry());
        }
        return true;
    }

    public function getOption($field, $default=false)
    {
//		Load Options as WC
	    if ( !class_exists(WC_Admin_Settings::class)) {
		    require_once(WP_PLUGIN_DIR . '/woocommerce/includes/admin/class-wc-admin-settings.php');
	    }
	    return WC_Admin_Settings::get_option( MBE_ESHIP_ID . '_' . $field, $default);
    }

    public function setOption($field, $value)
    {
        return update_option( MBE_ESHIP_ID . '_' . $field, $value);
    }

	public function setPostOption($field, $value)
	{
		if (isset($_POST[$field])) {
			$_POST[$field] = $value;
			return true;
		}
		return false;
	}



    public function getCountry()
    {
        return $this->getOption(self::XML_PATH_COUNTRY);
    }


    public function getWsUrl()
    {
        return $this->getOption(self::XML_PATH_WS_URL);
    }

    public function getWsUsername()
    {
        return $this->getOption(self::XML_PATH_WS_USERNAME);
    }

    public function getWsPassword()
    {
        return $this->getOption(self::XML_PATH_WS_PASSWORD);
    }

	public function setWsUsername($value)
	{
		return $this->setOption(self::XML_PATH_WS_USERNAME, $value);
	}

	public function setWsPassword($value)
	{
		return $this->setOption(self::XML_PATH_WS_PASSWORD, $value);
	}

	public function setWsUrl($countryCode='it')
	{
		return $this->setOption(self::XML_PATH_WS_URL, 'https://api.mbeonline.'. strtolower($countryCode) .'/ws/e-link.wsdl');
	}

	public function getMbeUsername()
	{
		return $this->getOption(self::XML_PATH_MBE_USERNAME);
	}

	public function getMbePassword()
	{
		return $this->getOption(self::XML_PATH_MBE_PASSWORD);
	}

	public function getLoginMode()
	{
		return $this->getOption(self::XML_PATH_LOGIN_MODE,self::MBE_LOGIN_MODE_SIMPLE);
	}

	public function setLoginMode($simple=true)
	{
		if ($simple) {
			return $this->setOption(self::XML_PATH_LOGIN_MODE, self::MBE_LOGIN_MODE_SIMPLE);
		} else {
			return $this->setOption(self::XML_PATH_LOGIN_MODE, self::MBE_LOGIN_MODE_ADVANCED);
		}
	}

	public function getLoginLinkAdvanced()
	{
		return $this->getOption(Mbe_Shipping_Helper_Data::XML_PATH_LOGIN_LINK_ADV);
	}

	public function getDescription()
    {
        return $this->getOption(self::XML_PATH_DESCRIPTION);
    }

    public function getDefaultShipmentType()
    {
        return $this->getOption(self::XML_PATH_DEFAULT_SHIPMENT_TYPE);
    }

    public function getAllowedShipmentServices()
    {
        return $this->getOption(self::XML_PATH_ALLOWED_SHIPMENT_SERVICES);
    }

    public function convertShippingCodeWithInsurance($code)
    {
        return $code . self::MBE_SHIPPING_WITH_INSURANCE_CODE_SUFFIX;
    }

    public function convertShippingLabelWithInsurance($label)
    {
        return $label . self::MBE_SHIPPING_WITH_INSURANCE_LABEL_SUFFIX;
    }

    public function convertShippingCodeWithoutInsurance($code)
    {
        return str_replace(self::MBE_SHIPPING_WITH_INSURANCE_CODE_SUFFIX, "", $code);
    }

    public function isShippingWithInsurance($code)
    {
        $result = false;
        /*
        $shippingSuffix = substr($code, -strlen(self::MBE_SHIPPING_WITH_INSURANCE_CODE_SUFFIX));
        if ($shippingSuffix == self::MBE_SHIPPING_WITH_INSURANCE_CODE_SUFFIX) {
            $result = true;
        }
        */
        if (strpos($code, self::MBE_SHIPPING_WITH_INSURANCE_CODE_SUFFIX) !== false) {
            $result = true;
        }
        return $result;
    }


    public function getAllowedShipmentServicesArray()
    {
        $allowedShipmentServicesArray = $this->getAllowedShipmentServices()?:[];
        /*$ws = new Mbe_Shipping_Model_Ws();
        $canSpecifyInsurance = $ws->getCustomerPermission('canSpecifyInsurance');

        $result = array();
        foreach ($allowedShipmentServicesArray as $item) {
            $canAdd = true;
            if (!$canSpecifyInsurance) {
                if (strpos($item, self::MBE_SHIPPING_WITH_INSURANCE_CODE_SUFFIX) !== false) {
                    $canAdd = false;
                }
            }
            if ($canAdd) {
                array_push($result, $item);
            }
        }

        return $result;*/
        return $allowedShipmentServicesArray;
    }

    public function getShipmentConfigurationMode()
    {
        return $this->getOption(self::XML_PATH_SHIPMENT_CONFIGURATION_MODE);
    }

    public function getDefaultLength()
    {
        return $this->getOption(self::XML_PATH_DEFAULT_LENGTH);
    }

    public function getDefaultWidth()
    {
        return $this->getOption(self::XML_PATH_DEFAULT_WIDTH);
    }

    public function getDefaultHeight()
    {
        return $this->getOption(self::XML_PATH_DEFAULT_HEIGHT);
    }

    public function getCanCreateCourierWaybill()
    {
	    $ws = new Mbe_Shipping_Model_Ws();
		return $ws->getCustomerPermission('canCreateCourierWaybill');
    }

//    public function getMaxPackageWeight()
//    {
//        $result = $this->getOption(self::XML_PATH_MAX_PACKAGE_WEIGHT);
//        $ws = new Mbe_Shipping_Model_Ws();
//
//        if ($this->getDefaultShipmentType() == "ENVELOPE") {
//            $maxParcelWeight = 0.5;
//        }
//        else {
//            $maxParcelWeight = 0;//$ws->getCustomerPermission("maxParcelWeight");
//        }
//
//        if ($maxParcelWeight > 0 && $maxParcelWeight < $result) {
//            $result = $maxParcelWeight;
//        }
//        return $result;
//    }

	/**
	 * Check the package weight and replace it if necessary,
	 * return the value using the woocommerce unit of measure
	 *
	 * @param $baseWeight
	 * @param $type
	 *
	 * @return float|mixed|null
	 */
	protected function checkMaxWeight($baseWeight, $type)
	{
		$ws = new Mbe_Shipping_Model_Ws();
		if ( $this->getDefaultShipmentType() == "ENVELOPE" ) {
			$maxWeight = wc_get_weight(
				0.5,
				get_option( 'woocommerce_weight_unit' ),
				'kg'
			);
		} else {
			$maxWeight = wc_get_weight(
				$ws->getCustomerPermission( $type ),
				get_option( 'woocommerce_weight_unit' ),
				'kg'
			);
		}

		if ($maxWeight > 0 && $maxWeight < $baseWeight) {
			$baseWeight = $maxWeight;
		}
		return $baseWeight;
	}

	public function getMaxPackageWeight($storeId = null)
	{
		$result = $this->getOption(self::XML_PATH_MAX_PACKAGE_WEIGHT);
		return $this->checkMaxWeight($result, 'maxParcelWeight');
	}


    public function getMaxShipmentWeight()
    {
        $result = $this->getOption(self::XML_PATH_MAX_SHIPMENT_WEIGHT);
	    return $this->checkMaxWeight($result, 'maxShipmentWeight');
    }

    public function getHandlingType()
    {
        return $this->getOption(self::XML_PATH_HANDLING_TYPE);
    }

    public function getHandlingAction()
    {
        return $this->getOption(self::XML_PATH_HANDLING_ACTION);
    }

    public function getHandlingFee()
    {
	    $handlingFee = $this->getOption(self::XML_PATH_HANDLING_FEE);
	    return !empty($handlingFee)?$handlingFee:0;
    }

    public function getHandlingFeeRounding()
    {
        return $this->getOption(self::XML_PATH_HANDLING_FEE_ROUNDING);
    }

    public function getHandlingFeeRoundingAmount()
    {
        $result = 1;
        if ($this->getOption(self::XML_PATH_HANDLING_FEE_ROUNDING_AMOUNT) == 2) {
            $result = 0.5;
        }
        return $result;
    }

    public function getSallowspecific()
    {
        return $this->getOption(self::XML_PATH_SALLOWSPECIFIC);
    }

    public function getSpecificcountry()
    {
        return $this->getOption(self::XML_PATH_SPECIFICCOUNTRY);
    }

    public function getSortOrder()
    {
        return $this->getOption(self::XML_PATH_SORT_ORDER);
    }

    public function getMaximumTimeForShippingBeforeTheEndOfTheDay()
    {
        return $this->getOption(self::XML_PATH_MAXIMUM_TIME_FOR_SHIPPING_BEFORE_THE_END_OF_THE_DAY);
    }

    public function getSpecificerrmsg()
    {
        return $this->getOption(self::XML_PATH_SPECIFICERRMSG);
    }

    public function getWeightType()
    {
        return $this->getOption(self::XML_PATH_WEIGHT_TYPE);
    }

    public function getShipmentsClosureMode()
    {
        return $this->getOption(self::XML_PATH_SHIPMENTS_CLOSURE_MODE);
    }

    public function getShipmentsCreationMode()
    {
        return $this->getOption(self::XML_PATH_SHIPMENTS_CREATION_MODE);
    }

    public function getShipmentsClosureTime()
    {
        return $this->getOption(self::XML_PATH_SHIPMENTS_CLOSURE_TIME);
    }

    public function isCreationAutomatically()
    {
        return $this->getShipmentsCreationMode() == self::MBE_CREATION_MODE_AUTOMATICALLY;
    }

    public function isClosureAutomatically()
    {
        return $this->getShipmentsClosureMode() == self::MBE_CLOSURE_MODE_AUTOMATICALLY;
    }

    public function hasTracking($post_id = null)
    {
        $post_id = is_null($post_id) ? (int)$_GET['post'] : (int)$post_id;
        $value = get_post_meta($post_id, self::SHIPMENT_SOURCE_TRACKING_NUMBER, true);
        return !empty($value);
    }


    public function round($value)
    {
        $result = $value;
        $handlingFeeRounding = $this->getHandlingFeeRounding();
        $handlingFeeRoundingAmount = $this->getHandlingFeeRoundingAmount();

        if ($handlingFeeRounding == 2) {

            if ($handlingFeeRoundingAmount == 1) {
                $result = round($value, 0);
            }
            else {
                $result = round($value, 2);
            }

        }
        elseif ($handlingFeeRounding == 3) {
            if ($handlingFeeRoundingAmount == 1) {
                $result = floor($value);
            }
            else {
                $result = floor($value * 2) / 2;
            }
        }
        elseif ($handlingFeeRounding == 4) {
            if ($handlingFeeRoundingAmount == 1) {
                $result = ceil($value);
            }
            else {
                $result = ceil($value * 2) / 2;
            }
        }
        return $result;
    }

    public function getNameFromLabel($label)
    {
        $name = $label;
        $name = strtolower($name);
        $name = str_replace(" ", "_", $name);
        $name = str_replace(" ", "_", $name);
        return $name;
    }

    public function getThresholdByShippingServrice($shippingService)
    {
        $shippingService = strtolower($shippingService);
        return $this->getOption(self::XML_PATH_THRESHOLD . "_" . $shippingService);
    }


    public function getTrackingStatus($trackingNumber)
    {
        $result = self::MBE_SHIPMENT_STATUS_OPEN;

        $mbeDir = $this->mbeUploadDir();
        $filePath = $mbeDir . DIRECTORY_SEPARATOR . 'MBE_' . $trackingNumber . "_closed.pdf";

        if (file_exists($filePath)) {
            $result = self::MBE_SHIPMENT_STATUS_CLOSED;
        }
        return $result;
    }

    public function isShippingOpen($shipmentId)
    {
        $result = true;
        $tracks = $this->getTrackings($shipmentId);
        foreach ($tracks as $track) {
            $result = $result && $this->isTrackingOpen($track);
        }
        return $result;
    }

    public function getFileNames($orderId)
    {
        $result = array();
        $files = get_post_meta($orderId, self::SHIPMENT_SOURCE_TRACKING_FILENAME, true);
        if ($files != '') {
            if (strpos($files, self::MBE_SHIPPING_TRACKING_SEPARATOR) !== false) {
                $result = explode(self::MBE_SHIPPING_TRACKING_SEPARATOR, $files);
            }
            else {
                $result = explode(self::MBE_SHIPPING_TRACKING_SEPARATOR__OLD, $files);
            }
        }
        return $result;
    }

    public function getTrackings($shipmentId)
    {
        $tracking = get_post_meta($shipmentId, self::SHIPMENT_SOURCE_TRACKING_NUMBER, true);
        if (strpos($tracking, self::MBE_SHIPPING_TRACKING_SEPARATOR) !== false) {
            $value = explode(self::MBE_SHIPPING_TRACKING_SEPARATOR, $tracking);

        }
        else {
            $value = explode(self::MBE_SHIPPING_TRACKING_SEPARATOR__OLD, $tracking);
        }

        return is_array($value) ? (array_filter($value, function ($value) {
            return $value !== '';
        })) : $value;
    }

    public function getTrackingsString($shipmentId)
    {
        $result = get_post_meta($shipmentId, self::SHIPMENT_SOURCE_TRACKING_NUMBER, true);
        //compatibility replace
        if (strpos($result, self::MBE_SHIPPING_TRACKING_SEPARATOR__OLD) !== false) {
            $result = str_replace(self::MBE_SHIPPING_TRACKING_SEPARATOR__OLD, self::MBE_SHIPPING_TRACKING_SEPARATOR, $result);
        }
        return $result;
    }

    public function getTrackingSetting() {
	    return $this->getOption(self::XML_PATH_ADD_TRACK_ID);
    }

    public function isTrackingOpen($trackingNumber)
    {
        return $this->getTrackingStatus($trackingNumber) == self::MBE_SHIPMENT_STATUS_OPEN;
    }

    public function mbeUploadDir()
    {
        $value = wp_upload_dir();
        $path = $value['basedir'] . DIRECTORY_SEPARATOR . $this->_mbeMediaDir;
        $this->checkMbeDir($path);
        return $path;
    }

	public function mbeUploadUrl()
	{
		$value = wp_upload_dir();
		return $value['baseurl'] . DIRECTORY_SEPARATOR . $this->_mbeMediaDir;
	}

    public function mbeCsvUploadDir()
    {
        $result = $this->mbeUploadDir() . DIRECTORY_SEPARATOR . $this->_csvDir;
        $this->checkMbeDir($result);
        return $result;
    }

    public function getMbeCsvUploadUrl()
    {
	    $wpUploadDir = wp_upload_dir();
        return $wpUploadDir['baseurl']. DIRECTORY_SEPARATOR . $this->_mbeMediaDir . '/' . $this->_csvDir;
    }

    public function getShipmentFilePath($shipmentIncrementId, $ext)
    {
        return $this->mbeUploadDir() . DIRECTORY_SEPARATOR . $shipmentIncrementId . "." . $ext;
    }

    public function getTrackingFilePath($trackingNumber)
    {
        $mbeDir = $this->mbeUploadDir();
        $filePath = $mbeDir . DIRECTORY_SEPARATOR . 'MBE_' . $trackingNumber . "_closed.pdf";
        return $filePath;
    }

    public function isMbeShippingCustomMapping($shippingMethod)
    {
    	if ($this->isEnabledCustomMapping()) {
		    $defaultMethods = WC()->shipping()->get_shipping_methods();
		    $customMapping  = [];
		    foreach ( $defaultMethods as $default_method ) {
			    $mapping = $this->getOption( 'mbe_custom_mapping_' . strtolower( $default_method->id ) );
			    if ( ! empty( $mapping ) ) {
				    $customMapping[] = strtolower( $default_method->id ) . ':' . $mapping;
			    }
		    }
		    if ( array_search( $shippingMethod, $customMapping ) !== false ) {
			    return true;
		    }
	    }
	    return false;
    }

    public function isMbeShipping($order)
    {
        $shippingMethod = $this->getShippingMethod($order);
	    // Handle already shipped orders added with custom mapping, if custom mapping is currently disabled.
	    $customMappingOrder = !empty($this->getTrackings($order->get_id())) && (get_post_meta( $order->get_id(), woocommerce_mbe_tracking_admin::SHIPMENT_SOURCE_TRACKING_CUSTOM_MAPPING,true) === 'yes');
	    // Check if the method is an MBE one (old or new) or if is a custom mapped default one
	    if (
        	preg_match('/'.MBE_ESHIP_ID.'|wf_mbe_shipping/', $shippingMethod)
	        || $this->isMbeShippingCustomMapping($shippingMethod)
		    || $customMappingOrder
        ) {
            return true;
        }
        return false;
    }

	/**
	 * @param $order
	 *
	 * @return false|string
	 * @throws Exception
	 */
    public function getShippingMethod($order)
    {
        if (version_compare(WC()->version, '2.1', '>=')) {
            $order_item_id = null;
            foreach ($order->get_items('shipping') as $key => $item) {
                $order_item_id = $key;
            }

            if ($order_item_id) {
                $shippingMethod = wc_get_order_item_meta($order_item_id, 'method_id', true);
                if ($this->isEnabledCustomMapping()) {
	                $customMapping = $this->getOption( 'mbe_custom_mapping_' . $shippingMethod );
	                if ( ! empty( $customMapping ) ) {
		                $shippingMethod = $shippingMethod . ':' . $customMapping;
	                }
                }
                return $shippingMethod;
            } else {
                return false;
            }
        }
    }

    public function getServiceName($order)
    {
        if (version_compare(WC()->version, '2.1', '>=')) {
            foreach ($order->get_items('shipping') as $key => $item) {
                return $item['name'];
            }
        }
        else {
	        $orderId = $this->getOrderId($order);
            return get_post_meta($orderId, '_shipping_method_title', true);
        }
    }

	public function getOrderId($order)
	{
		if (version_compare(WC()->version, '3', '>=')) {
			$orderId = $order->get_id();
		}
		else {
			$orderId = $order->id;
		}
		return $orderId;
	}

    public function getProduct($id_product)
    {
        if (version_compare(WC()->version, '2.1', '>=')) {
            return new WC_Product($id_product);
        }
        else {
            return get_product($id_product);
        }
    }

    public function getProductVariation($id_product)
    {
        return new WC_Product_Variation($id_product);
    }

    public function getProductFromItem($item)
    {
        $product_id = $item['product_id'];
        $variation_id = $item['variation_id'];
        if ($variation_id) {
            $product = $this->getProductVariation($variation_id);
        }
        else {
            $product = $this->getProduct($product_id);
        }
        return $product;
    }

    public function getProductSkuFromItem($item)
    {
        $product = $this->getProductFromItem($item);
        $sku = $product->get_sku();
        return $sku;
    }

    public function getProductTitleFromItem($item)
    {
        $product = $this->getProductFromItem($item);
        if (version_compare(WC()->version, '3.0', '>=')) {
            $title = $product->get_name();
        }
        else {
            $title = $product->get_title();
        }

        if (version_compare(WC()->version, '3.0', '>=')) {
            $productType = $product->get_type();
        }
        else {
            $productType = $product->product_type;
        }

        $variationsString = '';
        if ($productType == 'variation') {
            if (version_compare(WC()->version, '3.0', '>=')) {
                $available_variations = $product->get_variation_attributes();
            }
            else {
                $available_variations = $product->variation_data;
            }
            foreach ($available_variations as $key => $value) {
                $attributeLabel = str_replace('attribute_', '', $key);
                if ($variationsString != '') {
                    $variationsString .= ', ';
                }
                $variationsString .= $attributeLabel . ': ' . $value;
            }
        }

        if ($variationsString) {
            $title .= ' (' . $variationsString . ')';
        }

        return $title;
    }

    public function getShipmentsCsv()
    {
        return $this->getOption(self::XML_PATH_SHIPMENTS_CSV);
    }

	public function getShipmentCsvTable()
	{
		global $wpdb;
		return $wpdb->prefix . self::MBE_CSV_RATES_TABLE_NAME;
	}

    public function getShipmentsCsvFile()
    {
        return $this->getOption(self::XML_PATH_SHIPMENTS_CSV_FILE);
    }

    public function getShipmentsCsvFileUrl()
    {
        $result = $this->getShipmentsCsvFile();
        $wrongName =  $this->mbeUploadDir(). DIRECTORY_SEPARATOR . 'csv'.$result;
        if (file_exists($wrongName)) { //check for file with "bugged" name and move it to the right folder
	        if ( !rename( $wrongName, $this->mbeCsvUploadDir() . DIRECTORY_SEPARATOR . $result ) ) {
		        return false;
	        }
        }
        if ($result) {
            $result = $this->MbeCsvUploadDir() . DIRECTORY_SEPARATOR . $result;
        }
        return $result;
    }

	public function getShipmentsCsvFileDir()
	{
		$result = $this->getShipmentsCsvFile();
		$wrongName =  $this->mbeUploadDir(). DIRECTORY_SEPARATOR . 'csv'.$result;
		if (file_exists($wrongName)) { //check for file with "bugged" name and move it to the right folder
			if ( !rename( $wrongName, $this->mbeCsvUploadDir() . DIRECTORY_SEPARATOR . $result ) ) {
				return false;
			}
		}
		return $this->MbeCsvUploadDir() . DIRECTORY_SEPARATOR . $result;
	}

    public function getShipmentsCsvTemplateFileUrl()
    {
	    $source = MBE_ESHIP_PLUGIN_DIR . self::MBE_CSV_RATES_TEMPLATE_CSV;
	    $dest = $this->mbeCsvUploadDir() . DIRECTORY_SEPARATOR . self::MBE_CSV_RATES_TEMPLATE_CSV;
		if ($this->templateFileCreate( $dest, $source ) === '') {
			return  '';
		}
	    return $this->getMbeCsvUploadUrl() . DIRECTORY_SEPARATOR . self::MBE_CSV_RATES_TEMPLATE_CSV;
    }

	public function getShipmentsCsvTemplateFileDir()
	{
		$source = MBE_ESHIP_PLUGIN_DIR . self::MBE_CSV_RATES_TEMPLATE_CSV;
		$dest = $this->mbeCsvUploadDir() . DIRECTORY_SEPARATOR . self::MBE_CSV_RATES_TEMPLATE_CSV;
		return $this->templateFileCreate( $dest, $source );
	}

	public function getLogWsPath()
	{
		return MBE_ESHIP_PLUGIN_LOG_DIR . DIRECTORY_SEPARATOR . self::MBE_LOG_WS;
	}

	public function getLogPluginPath()
	{
		return MBE_ESHIP_PLUGIN_LOG_DIR . DIRECTORY_SEPARATOR . self::MBE_LOG_PLUGIN;
	}

    public function isEnabledCustomMapping()
    {
	    return $this->getOption(self::XML_PATH_COURIER_CONFIG_MODE)==self::MBE_COURIER_MODE_MAPPING;
//	    return $this->getOption('mbe_enable_custom_mapping') === 'yes';
    }

    public function getShipmentsCsvMode()
    {
	    if ($this->getOption(self::XML_PATH_COURIER_CONFIG_MODE)<>self::MBE_COURIER_MODE_CSV ) {
		    return self::MBE_CSV_MODE_DISABLED;
	    } else {
		    return $this->getOption(self::XML_PATH_SHIPMENTS_CSV_MODE);
	    }
    }

    public function getShipmentsCsvInsuranceMin()
    {
        return floatval($this->getOption(self::XML_PATH_SHIPMENTS_CSV_INSURANCE_MIN));
    }

    public function getShipmentsCsvInsurancePercentage()
    {
        return floatval($this->getOption(self::XML_PATH_SHIPMENTS_CSV_INSURANCE_PERCENTAGE));
    }

    public function getShipmentsInsuranceMode()
    {
        return $this->getOption(self::XML_PATH_SHIPMENTS_INSURANCE_MODE);
    }

    public function getShippingMethodCustomLabel($methodCode)
    {
    	$customLabel = trim($this->getOption(self::XML_PATH_SHIPMENT_CUSTOM_LABEL . '_' . strtolower($methodCode)));
    	if (!empty($customLabel)) {
    		return $customLabel;
	    }
		return false;
    }

	public function getPackagesCsvFile()
	{
		return $this->getOption(self::XML_PATH_PACKAGES_CSV_FILE);
	}

	public function getPackagesCsv()
	{
		return $this->getOption(self::XML_PATH_PACKAGES_CSV);
	}

	public function getCurrentCsvPackagesDir()
	{
		$packagesCsv = $this->getPackagesCsvFile();
		if (empty($packagesCsv)) {
			return '';
		}
		return $this->MbeCsvUploadDir() . '/' . $packagesCsv;
	}

	public function getCsvTemplatePackagesDir()
	{
		$source = MBE_ESHIP_PLUGIN_DIR . self::MBE_CSV_PACKAGE_TEMPLATE;
		$dest = $this->mbeCsvUploadDir() . DIRECTORY_SEPARATOR . self::MBE_CSV_PACKAGE_TEMPLATE;
		return $this->templateFileCreate( $dest, $source );
	}

	public function getPackagesProductCsv()
	{
		return $this->getOption(self::XML_PATH_PACKAGES_PRODUCT_CSV);
	}
	public function getPackagesProductCsvFile()
	{
		return $this->getOption(self::XML_PATH_PACKAGES_PRODUCT_CSV_FILE);
	}

	public function getCurrentCsvPackagesProductDir()
	{
		$packagesProductCsv = $this->getPackagesProductCsvFile();
		if (empty($packagesProductCsv)) {
			return '';
		}
		return $this->MbeCsvUploadDir() . '/' . $packagesProductCsv;
	}

	public function getCsvTemplatePackagesProductDir()
	{
		$source = MBE_ESHIP_PLUGIN_DIR . self::MBE_CSV_PACKAGE_PRODUCT_TEMPLATE;
		$dest = $this->mbeCsvUploadDir() . DIRECTORY_SEPARATOR . self::MBE_CSV_PACKAGE_PRODUCT_TEMPLATE;
		return $this->templateFileCreate( $dest, $source );
	}

	public function isCsvStandardPackageEnabled($storeId = null)
	{
		return $this->getOption(self::XML_PATH_CSV_STANDARD_PACKAGE_USE_CSV);
	}

	public function disableCsvStandardPackage()
	{
		$this->setOption(self::XML_PATH_CSV_STANDARD_PACKAGE_USE_CSV, false);
	}

	public function useCsvStandardPackages()
	{
		$standardPackagesCount = count( $this->csv_package_model->getCsvPackages() );
		return $this->isCsvStandardPackageEnabled()
		       && $standardPackagesCount > 0
		       && Mbe_Shipping_Model_Carrier::SHIPMENT_CONFIGURATION_MODE_ONE_SHIPMENT_PER_SHOPPING_CART_WEIGHT_MULTI_PARCEL == $this->getShipmentConfigurationMode();
	}

	public function getCsvStandardPackageDefault($storeId = null)
	{
		return $this->getOption(self::XML_PATH_CSV_STANDARD_PACKAGE_DEFAULT);
	}

	public function setCsvStandardPackageDefault($value)
	{
		$this->setOption(self::XML_PATH_CSV_STANDARD_PACKAGE_DEFAULT, $value);
	}

	/**
	 * Return the CSV package information related to a specific product, if any
	 * Otherwise it returns the CSV standard package selected as the default one
	 *
	 * @param null $productSku
	 * @return array|null
	 *
	 */
	protected function getCsvPackageInfo($productSku)
	{
		$packagesInfo = $this->csv_package_model->getPackageInfobyProduct( $productSku );
		if (count($packagesInfo) <= 0) {
			$packagesInfo = $this->csv_package_model->getPackageInfobyId( $this->getCsvStandardPackageDefault() );
		}
		$packageInfoResult = $packagesInfo[0];
		$packageInfoResult['max_weight'] = $this->checkMaxWeight($packageInfoResult['max_weight'], 'maxParcelWeight');

		return $packageInfoResult;
	}

	protected function getSettingsPackageInfo($singleParcel)
	{
		return [
			'id' => null,
			'package_code' => self::MBE_CSV_PACKAGES_RESERVED_CODE,
			'package_label' => 'Package from settings',
			'height' => $this->getDefaultHeight(),
			'width' => $this->getDefaultWidth(),
			'length' => $this->getDefaultLength(),
			'max_weight' => $this->getMaxPackageWeight(),
			'single_parcel' => $singleParcel,
			'custom_package' => false
		];
	}

	/**
	 * @param $productSku
	 * @param false $singleParcel // All the packages based on settings (not CSV) must be set as "single parcels"
	 * @return array|null
	 */
	public function getPackageInfo($productSku, $singleParcel = false)
	{
		if ($this->useCsvStandardPackages()) {
			return $this->getCsvPackageInfo($productSku);
		} else {
			return $this->getSettingsPackageInfo($singleParcel);
		}
	}

	public function getStandardPackagesForSelect()
	{
		return $this->toSelectArray( $this->csv_package_model->getStandardPackages(), 'id', 'package_label' );
	}

	public function getBoxesArray(&$boxesArray, &$boxesSingleParcelArray, $itemWeight, $packageInfo)
	{
		$itemWeight = (float)$itemWeight;
		if ($packageInfo['single_parcel'] || $packageInfo['custom_package']) {
			if (!isset($boxesSingleParcelArray[$packageInfo['package_code']])) {
				$boxesSingleParcelArray[$packageInfo['package_code']] = $this->addEmptyBoxType($packageInfo);
			}
			$boxesSingleParcelArray[$packageInfo['package_code']]['weight'][] = $itemWeight;
		} else {
			$canAddToExistingBox = false;
			if (!isset($boxesArray[$packageInfo['package_code']])) {
				$boxesArray[$packageInfo['package_code']] = $this->addEmptyBoxType($packageInfo);
			}
			$boxesPackage = &$boxesArray[$packageInfo['package_code']]; // by ref to simplify the code
			$boxesCount = count($boxesPackage['weight']);
			for ($j = 0; $j < $boxesCount; $j++) {
				$newWeight = (float)$boxesPackage['weight'][$j] + $itemWeight;
				if ($newWeight <= (float)$packageInfo['max_weight']) {
					$canAddToExistingBox = true;
					$boxesPackage['weight'][$j] = $newWeight;
					break;
				}
			}
			if (!$canAddToExistingBox) {
				$boxesPackage['weight'][] = $itemWeight;
			}
		}
		return $boxesArray;
	}

	public function addEmptyBoxType($packageInfo)
	{
		return [
			'maxweight' => $packageInfo['max_weight'],
			'dimensions' => [
				'length' => $packageInfo['length'],
				'width' => $packageInfo['width'],
				'height' => $packageInfo['height']
			],
			'weight' => []
		];
	}

	/**
	 * This method returns an array for all the boxes grouped by box type
	 * @param $boxes
	 * @param $boxesSingleParcel
	 *
	 * @return mixed
	 */
	public function mergeBoxesArray($boxes, $boxesSingleParcel)
	{
		foreach ($boxes as $key => $value) {
			if (isset($boxesSingleParcel[$key])) {
				foreach ($boxesSingleParcel[$key]['weight'] as $item) {
					$boxes[$key]['weight'][] = $item;
				}
				// remove the merged package
				unset($boxesSingleParcel[$key]);
			}
		}
		//  append all the remaining packages
		return $boxes+$boxesSingleParcel; // array union to keep the key = packageId
	}

	public function countBoxesArray($boxesArray)
	{
		$count = 0;
		$countArray = array_column($boxesArray, 'weight');
		foreach ($countArray as $box) {
			$count += count($box);
		}
		return $count;
	}

	public function totalWeightBoxesArray($boxesArray) {
		$totalWeight = 0;
		if ( is_array( $boxesArray ) ) {
			foreach ( $boxesArray as $box ) {
				$totalWeight += array_sum( $box['weight'] );
			}
		}
		return $totalWeight;
	}

	/**
	 * Compare all the dimensions for all the boxes and returns the biggest one
	 * @param $boxesArray
	 *
	 * @return mixed
	 */
	public function longestSizeBoxesArray($boxesArray)
	{
		$sortArray = [];
		$dimensionsArray = array_column($boxesArray, 'dimensions');
		foreach ( $dimensionsArray as $item ) {
			rsort($item);
			$sortArray[] = $item[0];
		}
		rsort($sortArray);
		return $sortArray[0];
	}

	/**
	 * Convert a weight based on WordPress unit value to a custom unit
	 *
	 * @param $weight
	 * @param string $toUnit
	 *
	 * @return array|float|int
	 */
	public function convertWeight($weight, $toUnit = 'kg')
    {
	    if (is_array($weight)) {
		    foreach ($weight as $key=>$value) {
			    $weight[$key] = wc_get_weight($value, $toUnit);
		    }
		    return $weight;
	    } else {
		    return wc_get_weight($weight, $toUnit);
	    }

    }

	public function toSelectArray($result, $value = 'id', $label = 'label') {
		$listArray = [];
		foreach ( $result as $item ) {
			$listArray[ $item[$value] ] = $item[$label];
		}
		return $listArray;
	}

	protected function getMaxWeightByType($type) {
		$ws = new Mbe_Shipping_Model_Ws();
		if ( $this->getDefaultShipmentType() == "ENVELOPE" ) {
			$maxParcelWeight = wc_get_weight(
				0.5,
				get_option( 'woocommerce_weight_unit' ),
				'kg'
			);
		} else {
			$maxParcelWeight = wc_get_weight(
				$ws->getCustomerPermission( $type ),
				get_option( 'woocommerce_weight_unit' ),
				'kg'
			);
		}

		return $maxParcelWeight;
	}

	protected function templateFileCreate( string $dest, string $source ): string {
		if ( ! file_exists( $dest ) || ( file( $dest ) !== file( $source ) ) ) {
			if ( ! copy( $source, $dest ) ) {
				return '';
			}
		}
		return $dest;
	}


	public function isOnlineMBE()
	{
		return (strpos(strtolower($this->getWsUrl()),'onlinembe') !== false);
	}

	public function isReturned($post_id) {
		$post_id = is_null($post_id) ? (int)$_GET['post'] : (int)$post_id;
//		$value = explode(self::MBE_SHIPPING_TRACKING_SEPARATOR, get_post_meta($post_id, self::SHIPMENT_SOURCE_RETURN_TRACKING_NUMBER, true));
		return !empty(get_post_meta($post_id, self::SHIPMENT_SOURCE_RETURN_TRACKING_NUMBER, true));
	}

	public function arrayKeyFirst($array) {
		if (!function_exists('array_key_first')) {
			function array_key_first(array $array) {
				foreach($array as $key => $unused) {
					return $key;
				}
				return NULL;
			}
		} else {
			return array_key_first($array);
		}
	}

	function select_mbe_ids()
	{
		global $wpdb;
		$postmetaTableName = $wpdb->prefix . 'postmeta';
		$shippingMethods = MBE_ESHIP_ID.'|wf_mbe_shipping'; // search also for orders created with the old plugin

		if (version_compare(WC()->version, '2.1', '>=')) {
			return "SELECT order_id FROM {$wpdb->prefix}woocommerce_order_items AS oi INNER JOIN $wpdb->order_itemmeta AS oim ON oi.order_item_id = oim.order_item_id WHERE oim.meta_key = 'method_id' AND oim.meta_value REGEXP '{$shippingMethods}'";
		}
		else {
			return "SELECT post_id FROM {$postmetaTableName} AS pm WHERE pm.meta_key = '_shipping_method' AND pm.meta_value REGEXP '{$shippingMethods}'";
		}
	}

	function select_custom_mapping_ids()
	{
		// get orders with custom mapped shipping method
		$customMappingFilter = array(
			'post_type' => 'shop_order',
			'post_status' => 'wc-%',
			'nopaging' => 'true',
			'fields' => 'ids',
			'meta_key' => woocommerce_mbe_tracking_admin::SHIPMENT_SOURCE_TRACKING_CUSTOM_MAPPING,
			'meta_value' => 'yes',
		);

		$sqlFilter = new WP_Query($customMappingFilter);
		return $sqlFilter->request;

	}

}