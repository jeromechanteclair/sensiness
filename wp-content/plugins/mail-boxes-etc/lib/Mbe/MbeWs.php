<?php

use MbeExceptions\HttpRequestException as HttpRequestException;

class MbeWs {
	private $_log;
	protected $helper;
	protected $pluginVersionMessage;
	protected $wsUrl;
	protected $apiToken;
	protected $apiCustomer;

	public function __construct( $log = false ) {
		$this->_log                 = $log;
		$this->helper               = new Mbe_Shipping_Helper_Data();
		$this->pluginVersionMessage = MBE_ESHIP_PLUGIN_NAME . ' version ' . MBE_ESHIP_PLUGIN_VERSION . ' :';
		$this->wsUrl       = $this->helper->getWsUrl();
		$this->apiToken    = null;
		$this->apiCustomer = null;
	}

    private function log($message)
    {
        if ($this->_log) {
            $row = date_format(new DateTime(), 'Y-m-d\TH:i:s\Z');
            $row .= " - ";
            $row .= $this->pluginVersionMessage . $message . "\n\r";
            file_put_contents($this->helper->getLogWsPath(), $row, FILE_APPEND);
        }
    }

    public function logVar($var, $message = null)
    {
        if ($this->_log) {
            if ($message) {
                $this->log($message);
            }
            $this->log(print_r($var, true));
        }
    }

    function generateRandomString($length = 10)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    public function getCustomer($ws, $username, $password, $system)
    {
        $this->log('GET CUSTOMER');
        $result = false;

        try {
            $soapClient = new MbeSoapClient($ws, array('encoding' => 'utf-8', 'trace' => 1), $username, $password, false);
            $internalReferenceID = $this->generateRandomString();

            //WS ARGS
            $args = new stdClass;
            $args->RequestContainer = new stdClass;

            $args->RequestContainer->Action = "GET";

            $args->RequestContainer->SystemType = $system;

            $args->RequestContainer->Customer = new stdClass;
            $args->RequestContainer->Customer->Login = "";

            $args->RequestContainer->Credentials = new stdClass;
            $args->RequestContainer->Credentials->Username = $username;
            $args->RequestContainer->Credentials->Passphrase = $password;

            $args->RequestContainer->InternalReferenceID = $internalReferenceID;

            $args->RequestContainer->Action = "GET";

            $this->logVar($args, 'GET CUSTOMER ARGS');
            $soapResult = $soapClient->__soapCall("ManageCustomerRequest", array($args));

            $lastResponse = $soapClient->__getLastResponse();
            $this->logVar($lastResponse, 'GET CUSTOMER RESPONSE');

            if (isset($soapResult->RequestContainer->Errors)) {
                $this->logVar($soapResult->RequestContainer->Errors, 'GET CUSTOMER ERRORS');
            }

            if (isset($soapResult->RequestContainer->Status) && $soapResult->RequestContainer->Status == "OK") {
                //if (isset($soapResult->RequestContainer->InternalReferenceID) && $soapResult->RequestContainer->InternalReferenceID == $internalReferenceID) {
                $result = $soapResult->RequestContainer->Customer;
                //}
            }
        }
        catch (Exception $e) {
            $this->log('GET CUSTOMER EXCEPTION');
            $this->log($e->getMessage());
        }
        $this->logVar($result, 'GET CUSTOMER RESULT');
        return $result;
    }

    public function estimateShipping($ws, $username, $password, $shipmentType, $system, $country, $region, $postCode, $items, $insurance = false, $insuranceValue = 0.00)
    {
        $this->log('ESTIMATE SHIPPING');
        $result = false;

        try {
            $soapClient = new MbeSoapClient($ws, array('encoding' => 'utf-8', 'trace' => 1), $username, $password);
            $internalReferenceID = $this->generateRandomString();

            //WS ARGS
            $args = new stdClass;
            $args->RequestContainer = new stdClass;
            $args->RequestContainer->System = $system;

            $args->RequestContainer->Credentials = new stdClass;
            $args->RequestContainer->Credentials->Username = $username;
            $args->RequestContainer->Credentials->Passphrase = $password;

            $args->RequestContainer->InternalReferenceID = $internalReferenceID;


            $args->RequestContainer->ShippingParameters = new stdClass;

            $args->RequestContainer->ShippingParameters->DestinationInfo = new stdClass;

            $args->RequestContainer->ShippingParameters->DestinationInfo->ZipCode = $postCode;
            $args->RequestContainer->ShippingParameters->DestinationInfo->City = $region;
            //$args->RequestContainer->ShippingParameters->DestinationInfo->State = $region;
            $args->RequestContainer->ShippingParameters->DestinationInfo->Country = $country;
            //$args->RequestContainer->ShippingParameters->DestinationInfo->idSubzone = "";

            $args->RequestContainer->ShippingParameters->ShipType = "EXPORT";

            $args->RequestContainer->ShippingParameters->PackageType = $shipmentType;

            $args->RequestContainer->ShippingParameters->Items = $items;

            $args->RequestContainer->ShippingParameters->Insurance = $insurance;
            if ($insurance) {
                $args->RequestContainer->ShippingParameters->InsuranceValue = $insuranceValue;
            }

            $this->logVar($args, 'ESTIMATE SHIPPING ARGS');

            $soapResult = $soapClient->__soapCall("ShippingOptionsRequest", array($args));

            $lastResponse = $soapClient->__getLastResponse();
            $this->logVar($lastResponse, 'ESTIMATE SHIPPING RESPONSE');

            if (isset($soapResult->RequestContainer->Errors)) {
                $this->logVar($soapResult->RequestContainer->Errors, 'ESTIMATE SHIPPING ERRORS');
            }

            if (isset($soapResult->RequestContainer->Status) && $soapResult->RequestContainer->Status == "OK") {
                if (isset($soapResult->RequestContainer->InternalReferenceID) && $soapResult->RequestContainer->InternalReferenceID == $internalReferenceID) {
                    if (isset($soapResult->RequestContainer->ShippingOptions->ShippingOption)) {
                        if (is_array($soapResult->RequestContainer->ShippingOptions->ShippingOption)) {
                            $result = $soapResult->RequestContainer->ShippingOptions->ShippingOption;
                        }
                        else {
                            $result = array($soapResult->RequestContainer->ShippingOptions->ShippingOption);
                        }
                    }
                }
            }

        }
        catch (Exception $e) {
            $this->log('ESTIMATE SHIPPING EXCEPTION');
            $this->log($e->getMessage());
        }
        $this->logVar($result, 'ESTIMATE SHIPPING RESULT');
        return $result;
    }

    public function createShipping($ws, $username, $password, $shipmentType, $service, $subZone, $system, $notes, $firstName, $lastName, $companyName, $address, $phone, $city, $state, $country, $postCode, $email, $items, $products, $shipperType = 'MBE', $goodsValue = 0.0, $reference = "", $isCod = false, $codValue = 0.0, $insurance = false, $insuranceValue = 0.0)
    {
        $this->log('CREATE SHIPPING');

        $this->logVar(func_get_args(), 'CREATE SHIPPING');

        $result = false;


        try {
            $soapClient = new MbeSoapClient($ws, array('encoding' => 'utf-8', 'trace' => 1), $username, $password);
            $internalReferenceID = $this->generateRandomString();

            //WS ARGS
            $args = new stdClass;
            $args->RequestContainer = new stdClass;
            $args->RequestContainer->System = $system;
            //$args->RequestContainer->Customer = new stdClass;
            //$args->RequestContainer->Customer->Login = "";

            $args->RequestContainer->Credentials = new stdClass;
            $args->RequestContainer->Credentials->Username = $username;
            $args->RequestContainer->Credentials->Passphrase = $password;

            $args->RequestContainer->InternalReferenceID = $internalReferenceID;

            //RequestContainer -> Recipient
            $args->RequestContainer->Recipient = new stdClass;

	        $recipientName = $firstName . " " . $lastName;
	        $RecipientCompanyName = $companyName;
	        $recipientName = mb_substr($recipientName, 0, 35,'UTF-8');
	        $RecipientCompanyName = mb_substr($RecipientCompanyName, 0, 35,'UTF-8');
	        if (empty($RecipientCompanyName)) {
		        $RecipientCompanyName = $recipientName;
	        }

            $args->RequestContainer->Recipient->Name = $recipientName;
            //$args->RequestContainer->Recipient->LastName = $lastName;
            $args->RequestContainer->Recipient->CompanyName = $RecipientCompanyName;
            $args->RequestContainer->Recipient->Address = $address;
            //$args->RequestContainer->Recipient->Phone = $phone;
            $args->RequestContainer->Recipient->Phone = mb_substr($phone, 0, 50, 'UTF-8');
            $args->RequestContainer->Recipient->ZipCode = $postCode;
            $args->RequestContainer->Recipient->City = $city;
            $args->RequestContainer->Recipient->State = $state;
            $args->RequestContainer->Recipient->Country = $country;
            $args->RequestContainer->Recipient->Email = $email;
            if ($subZone) {
                $args->RequestContainer->Recipient->SubzoneId = $subZone;
            }

            //RequestContainer -> Shipment
            $args->RequestContainer->Shipment = new stdClass;

            $args->RequestContainer->Shipment->ShipperType = $shipperType;//"MBE";//COURIERLDV - MBE
            $args->RequestContainer->Shipment->Description = "ECOMMERCE SHOP PURCHASE";
            $args->RequestContainer->Shipment->COD = $isCod;
            if ($isCod) {
                $args->RequestContainer->Shipment->CODValue = $codValue;
                $args->RequestContainer->Shipment->MethodPayment = "CASH";//CASH - CHECK
            }

            $args->RequestContainer->Shipment->Insurance = $insurance;
            if ($insurance) {
                $args->RequestContainer->Shipment->InsuranceValue = $insuranceValue;
            }

            $args->RequestContainer->Shipment->Service = $service;//SEE /SSE

            //$args->RequestContainer->Shipment->Courier = "";
            //$args->RequestContainer->Shipment->CourierService = "SEE";
            //$args->RequestContainer->Shipment->CourierAccount = "";
            $args->RequestContainer->Shipment->PackageType = $shipmentType;
            //$args->RequestContainer->Shipment->Value = 0;
            $args->RequestContainer->Shipment->Referring = $reference;

            $args->RequestContainer->Shipment->Items = $items;

			$args->RequestContainer->Shipment->ProformaInvoice = $this->generateProforma($products);

			$args->RequestContainer->Shipment->Products = $this->generateProducts($products);

	        $args->RequestContainer->Shipment->Value = $goodsValue;

	        $args->RequestContainer->Shipment->ShipmentOrigin = MBE_ESHIP_PLUGIN_NAME . " WooCommerce " . MBE_ESHIP_PLUGIN_VERSION;

	        $order_ship_UAP = wc_get_order($reference)->get_meta('woocommerce_mbe_uap_shipment');

	        if($order_ship_UAP === 'Yes') {
		        // Remove new line in case of UAP addresses
		        $args->RequestContainer->Recipient->Address = str_replace("\n", ' ', $address);
				// Set new UAP object for API
		        $args->RequestContainer->RecipientDeliveryPoint = clone $args->RequestContainer->Recipient;
		        $args->RequestContainer->RecipientDeliveryPoint->DeliveryPointId = wc_get_order($reference)->get_meta('woocommerce_mbe_uap_shipment_publicaccespointId');
	        }

            $args->RequestContainer->Shipment->Notes = mb_substr($notes, 0, 50, 'UTF-8');

            $this->logVar($args, 'CREATE SHIPPING ARGS');


            $soapResult = $soapClient->__soapCall("ShipmentRequest", array($args));


            $lastResponse = $soapClient->__getLastResponse();
            $this->logVar($lastResponse, 'CREATE SHIPPING RESPONSE');

            if (isset($soapResult->RequestContainer->Errors)) {

                $this->logVar($soapResult->RequestContainer->Errors, 'CREATE SHIPPING ERRORS');
            }
            if (isset($soapResult->RequestContainer->Status) && $soapResult->RequestContainer->Status == "OK") {
                if (isset($soapResult->RequestContainer->InternalReferenceID) && $soapResult->RequestContainer->InternalReferenceID == $internalReferenceID) {
                    $result = $soapResult->RequestContainer;
                }
            }

        }
        catch (Exception $e) {
            $this->log('CREATE SHIPPING EXCEPTION');
            $this->log($e->getMessage());
        }
        $this->logVar($result, 'CREATE SHIPPING RESULT');
        return $result;
    }


    public function closeShipping($ws, $username, $password, $system, $trackings)
    {
        $this->log('CLOSE SHIPPING');


        $result = false;


        try {
            $soapClient = new MbeSoapClient($ws, array('encoding' => 'utf-8', 'trace' => 1), $username, $password);
            $internalReferenceID = $this->generateRandomString();

            //WS ARGS
            $args = new stdClass;
            $args->RequestContainer = new stdClass;
            $args->RequestContainer->SystemType = $system;

            $args->RequestContainer->Credentials = new stdClass;
            $args->RequestContainer->Credentials->Username = $username;
            $args->RequestContainer->Credentials->Passphrase = $password;

            $args->RequestContainer->InternalReferenceID = $internalReferenceID;

            $masterTrackingsMBE = array();
            foreach ($trackings as $track) {
                array_push($masterTrackingsMBE, $track);
            }


            $args->RequestContainer->MasterTrackingsMBE = $masterTrackingsMBE;

            $this->logVar($args, 'CLOSE SHIPPING ARGS');


            $soapResult = $soapClient->__soapCall("CloseShipmentsRequest", array($args));

            $lastResponse = $soapClient->__getLastResponse();

            $this->logVar($lastResponse, 'CLOSE SHIPPING RESPONSE');

            if (isset($soapResult->RequestContainer->Errors)) {
                $this->logVar($soapResult->RequestContainer->Errors, 'CLOSE SHIPPING ERRORS');
            }

            if (isset($soapResult->RequestContainer->Status) && $soapResult->RequestContainer->Status == "OK") {
                $result = $soapResult->RequestContainer;
            }

        }
        catch (Exception $e) {
            $this->log('CLOSE SHIPPING EXCEPTION');
            $this->log($e->getMessage());
        }
        $this->logVar($result, 'CLOSE SHIPPING RESULT');
        return $result;
    }

	/**
	 * Generates a proForma object and remove Price from the product object, as it's not needed for the request
	 *
	 * @param $products
	 *
	 * @return array
	 */
	protected function generateProforma($products) {
		$proForma = [];
		foreach ( $products as $product ) {
			$item = new stdClass();
			$item->Amount = $product->Quantity;
			$item->Currency = mb_substr($product->Currency,0,10, 'UTF-8');
			$item->Value = $product->Price;
			$item->Unit = 'PCS';
			$item->Description = mb_substr($product->Description,0,35,'UTF-8');
			$proForma[] = $item;
		}
		return $proForma;
	}

	protected function generateProducts($products) {
		foreach ( $products as $product ) {
			unset( $product->Price );
			unset( $product->Currency );
		}
		return $products; // this is not really needed, as $products in the calling environment is updated when elements are unset, but we want to use the function return value directly
	}

	public function returnShipping($ws, $username, $password, $system, $tracking)
	{
		$this->log('RETURN SHIPPING - ' . $tracking);

		$result = false;

		try {
			$soapClient = new MbeSoapClient($ws, array('encoding' => 'utf-8', 'trace' => 1), $username, $password, false);
			$internalReferenceID = 'RETURN-SHIPPING-'.$tracking;

			//WS ARGS
			$args = new stdClass;
			$args->RequestContainer = new stdClass;
			$args->RequestContainer->System = $system;

			$args->RequestContainer->Credentials = new stdClass;
			$args->RequestContainer->Credentials->Username = $username;
			$args->RequestContainer->Credentials->Passphrase = $password;

			$args->RequestContainer->InternalReferenceID = $internalReferenceID;

			$args->RequestContainer->MbeTracking = $tracking;
			$args->RequestContainer->CustomerAsReceiver = true;
			$args->RequestContainer->ShipmentOrigin = MBE_ESHIP_PLUGIN_NAME . " WooCommerce " . MBE_ESHIP_PLUGIN_VERSION;
			$args->RequestContainer->Referring = '';

			$this->logVar($args, 'RETURN SHIPPING ARGS');

			$soapResult = $soapClient->__soapCall("ShipmentReturnRequest", array($args));

			$lastResponse = $soapClient->__getLastResponse();

			$this->logVar($lastResponse, 'RETURN SHIPPING RESPONSE');

			if (isset($soapResult->RequestContainer->Errors)) {
				$this->logVar($soapResult->RequestContainer->Errors, 'RETURN SHIPPING ERRORS');
			}

			if (isset($soapResult->RequestContainer->Status) && $soapResult->RequestContainer->Status == "OK") {
				$result = $soapResult->RequestContainer;
			}
		}
		catch (Exception $e) {
			$this->log('RETURN SHIPPING EXCEPTION');
			$this->log($e->getMessage());
		}
		$this->logVar($result, 'RETURN SHIPPING RESULT');
		return $result;
	}

	public function getApiBearer( $user, $password ) {
		$response = $this->httpPostRequest(
			preg_replace( '/(ws\/e-link\.wsdl)$/i', '', $this->wsUrl ) . 'oauth/token',
			[
				'headers' => [
					'Accept'        => ' application/json, text/plain, */*',
					'Content-Type'  => ' application/x-www-form-urlencoded;charset=UTF-8',
					'Authorization' => ' Basic dGVsZXBvcnQtZmU6',
				],
				'body'    => 'grant_type=password&username=' . $user . '&password=' . $password
			]
		);
		$this->logVar( $response, 'API BEARER' );

		return $response;
	}

	public function getApiCustomer( $apiBearer ) {
		$response = $this->httpGetRequest(
			preg_replace( '/(ws\/e-link\.wsdl)$/i', '', $this->wsUrl ) . 'oauth/mine',
			[
				'headers' => [
					'Accept'          => ' application/json, text/plain, */*',
					'Accept-Language' => ' it,it-IT;q=0.9,en;q=0.8,en-GB;q=0.7,en-US;q=0.6',
					'Authorization'   => ' Bearer ' . $apiBearer->access_token,
				],
			]
		);
		$this->logVar( $response, 'API CUSTOMER' );

		return $response;
	}

	public function getApiToken( $user, $password ) {
		$apiBearer = $this->getApiBearer( $user, $password );
		if ( ! empty( $apiBearer ) ) {
			$apiCustomer = $this->getApiCustomer( $apiBearer );
			if ( ! empty( $apiCustomer ) ) {
				$response = $this->httpPostRequest(
					preg_replace( '/(ws\/e-link\.wsdl)$/i', '', $this->wsUrl ) . 'oauth/select-store',
					[
						'headers' => [
							'Accept'        => 'application/json, text/plain, */*',
							'Content-Type'  => 'application/json',
							'Authorization' => ' Bearer ' . $apiBearer->access_token,
						],
						'body'    => json_encode( $apiCustomer[0] )
					]
				);
				$this->logVar( $response, 'API TOKEN' );

				return $response;
			}
		}

		return null;
	}

	public function getApiKey( $user, $password ) {
		$apiToken = $this->getApiToken( $user, $password );
		if ( ! empty( $apiToken ) ) {
			$response = $this->httpGetRequest(
				preg_replace( '/(ws\/e-link\.wsdl)$/i', '', $this->wsUrl ) . 'auth-registry/apikey?idEntity=' . $apiToken->{'legal-entity-id'} . '&legalEntityType=CUSTOMER',
				[
					'headers' => [
						'Accept'        => ' application/json, text/plain, */*',
						'Authorization' => ' Bearer ' . $apiToken->access_token
					]
				]
			);
			$this->logVar( $response, 'GET API KEY' );

			return $response;
		}

		return null;
	}

	public function deleteApiKey( $user, $password ) {
		$apiKey = $this->getApiKey( $user, $password );
		if ( ! empty( $apiKey->content ) ) {
			$apiToken = $this->getApiToken( $user, $password );
			if ( ! empty( $apiToken ) ) {
				$response = $this->httpDeleteRequest(
					preg_replace( '/(ws\/e-link\.wsdl)$/i', '', $this->wsUrl ) . 'auth-registry/apikey?apikey=' . $apiKey->content[0]->apiKey,
					[
						'headers' => [
							'Accept'        => ' application/json, text/plain, */*',
							'Authorization' => ' Bearer ' . $apiToken->access_token
						]
					]
				);
				$this->logVar( $response, 'DELETE API KEY' );
			}

			return true;
		}

		return false;
	}

	/**
	 * @throws Exception
	 */
	public function generateApiKey( $user, $password ) {
		$apiKeyDeleted = $this->deleteApiKey( $user, $password );
		if ( $apiKeyDeleted ) {
			$apiToken = $this->getApiToken( $user, $password );
			if ( ! empty( $apiToken ) ) {
				$response = $this->httpPostRequest(
					preg_replace( '/(ws\/e-link\.wsdl)$/i', '', $this->wsUrl ) . 'auth-registry/apikey',
					[
						'headers' => [
							'Accept'        => ' application/json, text/plain, */*',
							'Authorization' => ' Bearer ' . $apiToken->access_token
						],
						'body'    => 'legalEntityType=CUSTOMER&roleName=ONLINEMBE_USER&idEntity=' . $apiToken->{'legal-entity-id'} . '&username=' . $apiToken->username,
					]
				);
				$this->logVar( $response, 'GENERATE API KEY' );

				return $response;
			}
		}

		return null;
	}

	/**
	 * @throws Exception
	 */
	public function httpGetRequest( $url, $options ) {
		$response = wp_remote_get( $url, $options );
		return $this->checkHttpResponse($response, $url);
	}

	/**
	 * @throws Exception
	 */
	public function httpPostRequest( $url, $options ) {
		$response = wp_remote_post( $url, $options );
		return $this->checkHttpResponse($response, $url, 'POST');
	}

	/**
	 * @throws Exception
	 */
	public function httpDeleteRequest( $url, $options ) {
		$options['method'] = 'DELETE';
		$response          = wp_remote_request( $url, $options );
		return $this->checkHttpResponse($response, $url, 'DELETE');
	}

	/**
	 * @throws HttpRequestException
	 */
	protected function checkHttpResponse( $response, $url, $method = 'GET' ) {
		if ( ( ! is_wp_error( $response ) ) && ( preg_match( '(2[0-9]{2})', wp_remote_retrieve_response_code( $response ) ) ) ) {
			$responseBody = json_decode( $response['body'] );
			if ( empty( $response['body'] ) || json_last_error() === JSON_ERROR_NONE ) {
				return $responseBody;
			}
			throw new HttpRequestException( 'JSON Error: ' . json_last_error_msg() );
		}
		$message = wp_remote_retrieve_response_code( $response ) . ' - ' . wp_remote_retrieve_response_message( $response ) . ' - ' . wp_remote_retrieve_body( $response );
		$this->log( $method . ' Http Request: ' . $url . ' - ' . $message );
		throw new HttpRequestException( $message );
	}
}