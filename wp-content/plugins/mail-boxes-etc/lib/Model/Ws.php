<?php

class Mbe_Shipping_Model_Ws
{
    private $helper;
    protected $logger = null;
    private $ws;
    protected $wsUrl;
    protected $wsUsername;
    protected $wsPassword;
    protected $system;
	protected $customer;

    public function __construct()
    {
        $this->helper = new Mbe_Shipping_Helper_Data();
        $this->logger = new Mbe_Shipping_Helper_Logger();

	    $debug = $this->helper->debug();
	    $this->ws = new MbeWs($debug);
	    $this->wsUrl = $this->helper->getWsUrl();
	    $this->wsUsername = $this->helper->getWsUsername();
	    $this->wsPassword = $this->helper->getWsPassword();
	    $this->system = $this->helper->getCountry();
	    $this->customer = $this->getCustomer();

	    if ($debug) {
		    $this->helper->checkDir(MBE_ESHIP_PLUGIN_LOG_DIR);
	    }
    }

    public function getCustomer()
    {
	    $cacheKey = md5($this->wsUrl . $this->wsUsername . $this->wsPassword . $this->system);
	    if (get_transient($cacheKey) !== false) {
		    return get_transient($cacheKey);
	    }
	    return $this->cacheCustomerData();
    }

    public function getCustomerPermission($permissionName)
    {
        $result = null;
        if ($this->customer) {
            $result = $this->customer->Permissions->$permissionName??null;
        }

        return $result;
    }

    public function getAvailableOptions()
    {
        $result = false;
        if ($this->wsUrl && $this->wsUsername && $this->wsPassword) {
            $result = $this->ws->getAvailableOptions($this->wsUrl, $this->wsUsername, $this->wsPassword);
        }

        return $result;
    }

    public function getAllowedShipmentServices($customer)
    {


        $result = array();
        if ($this->wsUrl && $this->wsUsername && $this->wsPassword) {
            //$customer = $this->ws->getCustomer($this->wsUrl, $this->wsUsername, $this->wsPassword, $this->system);
            if ($customer && $customer->Enabled) {
                if (isset($customer->Permissions->enabledServices)) {
                    $enabledServices = $customer->Permissions->enabledServices;
                    $enabledServicesDesc = $customer->Permissions->enabledServicesDesc;

                    $enabledServicesArray = explode(",", $enabledServices);
                    $enabledServicesDescArray = explode(",", $enabledServicesDesc);

                    for ($i = 0; $i < count($enabledServicesArray); $i++) {
                        $service = $enabledServicesArray[$i];
                        $serviceDesc = $enabledServicesDescArray[$i];


                        $serviceDesc .= ' (' . $service . ')';

                        $currentShippingType = array(
                            'value' => $service,
                            'label' => $serviceDesc,
                        );

                        if (!in_array($currentShippingType, $result)) {
                            array_push($result, $currentShippingType);
                        }

                        //SHIPPING WITH INSURANCE
                        if (isset($customer->Permissions->canSpecifyInsurance) && $customer->Permissions->canSpecifyInsurance) {
                            $currentShippingWithInsuranceType = array(
                                'value' => $this->helper->convertShippingCodeWithInsurance($service),
                                'label' => $this->helper->convertShippingLabelWithInsurance($serviceDesc),
                            );
                            if (!in_array($currentShippingWithInsuranceType, $result)) {
                                array_push($result, $currentShippingWithInsuranceType);
                            }
                        }

                    }
                }
            }
        }
        return $result;
    }

    public function getLabelFromShipmentType($shipmentCode)
    {
        $result = $shipmentCode;
        $allowedShipmentServices = $this->getAllowedShipmentServices($this->customer);
        foreach ($allowedShipmentServices as $allowedShipmentService) {
            if ($allowedShipmentService["value"] == $shipmentCode) {
                $result = $allowedShipmentService["label"];
                break;
            }
        }
        return $result;
    }

    private function convertInsuranceShipping($shippingList)
    {
        $result = false;
        if ($shippingList) {
            $newShippingList = array();
            foreach ($shippingList as $shipping) {

                if ($shipping->InsuranceAvailable) {
                    $newShipping = $shipping;
                    $newShipping->Service = $this->helper->convertShippingCodeWithInsurance($newShipping->Service);
                    $newShipping->ServiceDesc = $this->helper->convertShippingLabelWithInsurance($newShipping->ServiceDesc);
                    array_push($newShippingList, $newShipping);
                }
            }
            if (!empty($newShippingList)) {
                $result = $newShippingList;
            }
        }
        return $result;
    }

	public function estimateShipping($country, $region, $postCode, $weight, $boxes, $insuranceValue)
	{
		$this->logger->log('ESTIMATESHIPPING');
//        $weight = $this->helper->convertWeight($weight);

		$wsUrl = $this->helper->getWsUrl();
		$wsUsername = $this->helper->getWsUsername();
		$wsPassword = $this->helper->getWsPassword();
		$system = $this->helper->getCountry();

		$result = false;

		if ($wsUrl && $wsUsername && $wsPassword) {
			$items = $this->setItems($weight);

			$shipmentType = $this->helper->getDefaultShipmentType();

			$this->logger->logVar($items, 'ESTIMATESHIPPING ITEMS');

			// ASSEGNO A SERVICE TUTTI I CORRIERI
			// DA ULTIMARE LA FIX: $service = $this->$helper->getAllowedShipmentServices();

			//Shipping without insurance
			$resultWithoutInsurance = $this->ws->estimateShipping(
				$wsUrl, $wsUsername, $wsPassword, $shipmentType, $system, $country, $region, $postCode, $items, false, $insuranceValue
			);

			//Shipping with insurance
			$resultWithInsurance = $this->ws->estimateShipping(
				$wsUrl, $wsUsername, $wsPassword, $shipmentType, $system, $country, $region, $postCode, $items, true, $insuranceValue
			);
			$resultWithInsurance = $this->convertInsuranceShipping($resultWithInsurance);

			if ($resultWithInsurance && $resultWithoutInsurance) {
				$result = array_merge($resultWithInsurance, $resultWithoutInsurance);
			}
			else {
				if ($resultWithInsurance) {
					$result = $resultWithInsurance;
				}
				if ($resultWithoutInsurance) {
					$result = $resultWithoutInsurance;
				}
			}
		}

		return $result;
	}

	public function createShipping($country, $region, $postCode, $weight, $boxes, $products, $service, $subzone, $notes, $firstName, $lastName, $companyName, $address, $phone, $city, $email, $goodsValue = 0.0, $reference = "", $isCod = false, $codValue = 0.0, $insurance = false, $insuranceValue = 0.0)
	{
//		$weight = $this->helper->convertWeight($weight, 'kg');
		$this->logger->log('CREATE SHIPPING');
		$this->logger->logVar($goodsValue, 'GOODS VALUE');
		$this->logger->logVar(func_get_args(), 'CREATE SHIPPING ARGS');

		$wsUrl = $this->helper->getWsUrl();
		$wsUsername = $this->helper->getWsUsername();
		$wsPassword = $this->helper->getWsPassword();
		$system = $this->helper->getCountry();
		$result = false;
		if ($wsUrl && $wsUsername && $wsPassword) {
			$items = $this->setItems($weight);

			$shipmentType = $this->helper->getDefaultShipmentType();

			$this->logger->logVar($items, 'CREATE SHIPPING ITEMS');

			$shipperType = $this->getShipperType();
			$result = $this->ws->createShipping(
				$wsUrl, $wsUsername, $wsPassword, $shipmentType, $service, $subzone, $system, $notes, $firstName, $lastName, $companyName,
				$address, $phone, $city, $region, $country, $postCode, $email, $items, $products, $shipperType, $goodsValue, $reference,
				$isCod, $codValue, $insurance, $insuranceValue
			);
		}
		return $result;
	}
    public function getShipperType()
    {
        $shipperType = "MBE";
        $canCreateCourierWaybill= $this->helper->getCanCreateCourierWaybill()??0;

        if ($canCreateCourierWaybill) {
            $shipperType = "COURIERLDV";
        }

        return $shipperType;
    }

    public function mustCloseShipments()
    {
	    $canCreateCourierWaybill= $this->helper->getCanCreateCourierWaybill()??0;

	    return !$canCreateCourierWaybill;
    }

    public function getCustomerMaxParcelWeight()
    {
        return $this->customer->Permissions->maxParcelWeight;
    }

    public function getCustomerMaxShipmentWeight()
    {
        return $this->customer->Permissions->maxShipmentWeight;
    }

    public function isCustomerActive()
    {
        $result = false;
        if ($this->customer) {
            $result = $this->customer->Enabled;
        }
        return $result;
    }

    public function closeShipping(array $shipmentIds)
    {
        $trackingNumbers = array();
        foreach ($shipmentIds as $shipmentId) {
            $tracks = $this->helper->getTrackings($shipmentId);
            foreach ($tracks as $track) {
                array_push($trackingNumbers, $track);
            }
        }
        $this->closeTrackingNumbers($trackingNumbers);

    }

    public function closeTrackingNumbers(array $trackingNumbers)
    {
        $this->logger->log('CLOSE SHIPPING');



        $result = false;

        if ($this->wsUrl && $this->wsUsername && $this->wsPassword) {
            $result = $this->ws->closeShipping($this->wsUrl, $this->wsUsername, $this->wsPassword, $this->system, $trackingNumbers);

            if ($result) {
                foreach ($trackingNumbers as $trackingNumber) {
                    $filePath = $this->helper->getTrackingFilePath($trackingNumber);
                    file_put_contents($filePath, $result->Pdf);
                }
            }
        }

        return $result;
    }

	public function  cacheCustomerData()
	{
		$result = false;
		if ( $this->wsUrl && $this->wsUsername && $this->wsPassword ) {
			$result = $this->ws->getCustomer( $this->wsUrl, $this->wsUsername, $this->wsPassword, $this->system );
			if ( $result ) {
				set_transient(
					md5( $this->wsUrl . $this->wsUsername . $this->wsPassword . $this->system ),
					$result,
					12 * HOUR_IN_SECONDS
				);
			}
			$this->logger->logVar( $result, 'WS getCustomer' );
		}

		return $result;
	}

	public function setItems($boxesWeight)
	{
		$items = [];
		foreach ($boxesWeight as $box) {
			foreach ($box['weight'] as $weight) {
				$item = new \stdClass;
				$item->Weight = $this->helper->convertWeight($weight);
				$item->Dimensions = new \stdClass;
				$item->Dimensions->Lenght = $box['dimensions']['length'];
				$item->Dimensions->Height = $box['dimensions']['height'];
				$item->Dimensions->Width = $box['dimensions']['width'];
				$items[] = $item;
			}
		}
		return $items;
	}

	public function returnShipping($toReturnIds)
	{
		$result = [];
		foreach ($toReturnIds as $toReturnId) {
			$tracks = $this->helper->getTrackings($toReturnId);
			if (!empty($tracks)) {
//  			foreach ($tracks as $track) {
//	    			array_push($trackingNumbers, $track);
//		    	}
//      		return $this->returnTrackingNumbers($trackingNumbers);
	            $result[$toReturnId] = $this->returnTrackingNumbers([$tracks[0]]); // TODO : only manage the 1st order's shipment for now
			}
		}
		return $result;
	}

	public function returnTrackingNumbers(array $trackingNumbers)
	{
		$this->logger->logVar( $trackingNumbers, 'RETURN SHIPPING ');

		$returnTracking = [];

		if ($this->wsUrl && $this->wsUsername && $this->wsPassword) {
			foreach ( $trackingNumbers as $trackingNumber ) {
				$result = $this->ws->returnShipping($this->wsUrl, $this->wsUsername, $this->wsPassword, $this->system, $trackingNumber);
				if (!empty($result)) {
					$returnTracking[$trackingNumber] = $result->MasterTrackingMBE;
				} else {
					// error on creation of the return shipment
					$returnTracking[$trackingNumber] = false;
				}
			}
		}
		return $returnTracking;
	}
}