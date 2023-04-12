<?php

class Mbe_Shipping_Helper_Ups_Uap {

	const MBE_UPS_ACCESS_LICENSE_NUMBER = "6CB804D87F868625";
	const MBE_UPS_USER_ID = "ITMBE0001Z";
	const MBE_UPS_PASSWORD = "1tMB3#ooo1";
	const MBE_UPS_URI_TEST = "https://wwwcie.ups.com/ups.app/xml/Locator";
	const MBE_UPS_URI_PROD = "https://onlinetools.ups.com/ups.app/xml/Locator";
	const MBE_UPS_UAP_MAXIMUM_LIST_ITEM_10 = "10";
	const MBE_UPS_UAP_SEARCH_RADIUS_20 = "20";
	const MBE_UPS_UAP_UNIT_OF_MEASUREMENT_KM ="KM";
	const MBE_UPS_OPTION_DROP_LOCATIONS_AND_WILL_CALL_LOCATIONS = 1;
	const MBE_UPS_OPTION_ALL_AVAILABLE_ADDITIONAL_SERVICES = 8;
	const MBE_UPS_OPTION_ALL_AVAILABLE_PROGRAM_TYPES = 16;
	const MBE_UPS_OPTION_ALL_AVAILABLE_ADDITIONAL_SERVICES_AND_PROGRAM_TYPES = 24;
	const MBE_UPS_OPTION_ALL_AVAILABLE_RETAIL_LOCATIONS = 32;
	const MBE_UPS_OPTION_ALL_AVAILABLE_RETAIL_LOCATIONS_AND_ADDITIONAL_SERVICES = 40;
	const MBE_UPS_OPTION_ALL_AVAILABLE_RETAIL_LOCATIONS_AND_PROGRAM_TYPES = 48;
	const MBE_UPS_OPTION_ALL_AVAILABLE_RETAIL_LOCATIONS_AND_ADDITIONAL_SERVICES_AND_PROGRAM_TYPES = 56;
	const MBE_UPS_OPTION_UPS_ACCESS_POINT_LOCATIONS = 64;

	/**
	 * @throws Exception
	 */
	public static function getUapList($filter, $simplyfied = true, $userId = null, $password = null, $accessLicenseNumber = null, $test = false)
	{
		$accessLicenseNumber = $accessLicenseNumber?:self::MBE_UPS_ACCESS_LICENSE_NUMBER;
		$userId = $userId?:self::MBE_UPS_USER_ID;
		$password = $password?:self::MBE_UPS_PASSWORD;

		$endpointurl = self::MBE_UPS_URI_PROD;
		if ($test) {
			$endpointurl = self::MBE_UPS_URI_TEST;
		}

//		try {
			$accessRequestXML = new SimpleXMLElement("<AccessRequest></AccessRequest>");
			$locatorRequestXML = new SimpleXMLElement("<LocatorRequest ></LocatorRequest >");

			$accessRequestXML->addChild("AccessLicenseNumber", $accessLicenseNumber);
			$accessRequestXML->addChild("UserId", $userId);
			$accessRequestXML->addChild("Password", $password);

			$request = $locatorRequestXML->addChild('Request');
			$request->addChild("RequestAction", "Locator");
			$request->addChild("RequestOption", $filter["RequestOption"] ?? self::MBE_UPS_OPTION_DROP_LOCATIONS_AND_WILL_CALL_LOCATIONS );

			$translate = $locatorRequestXML->addChild('Translate');
			$translate->addChild("LanguageCode", $filter["language"]??'EN');

//			if(!empty($filter["LocationID"])) {
//				$locatorRequestXML->addChild ( "LocationID", $filter["LocationID"]);
//			} else {
				$originAddress    = $locatorRequestXML->addChild( 'OriginAddress' );
				$addressKeyFormat = $originAddress->addChild( 'AddressKeyFormat' );
				$addressKeyFormat->addChild( "AddressLine", $filter["AddressLine1"]?:'' );
				$addressKeyFormat->addChild( "PostcodePrimaryLow", $filter["PostcodePrimaryLow"]?:'');
				$addressKeyFormat->addChild( "PoliticalDivision2", $filter["PoliticalDivision2"]?:'' );
				$addressKeyFormat->addChild( "PoliticalDivision1", $filter["PoliticalDivision1"]?:'' );
				$addressKeyFormat->addChild( "CountryCode", $filter["CountryCode"]?:'' );


				$unitOfMeasurement = $locatorRequestXML->addChild( 'UnitOfMeasurement' );
				$unitOfMeasurement->addChild( "Code", isset($filter["UnitOfMeasurement"])?$filter["UnitOfMeasurement"]: self::MBE_UPS_UAP_UNIT_OF_MEASUREMENT_KM );

				$LocationSearchCriteria = $locatorRequestXML->addChild( 'LocationSearchCriteria' );
				$LocationSearchCriteria->addChild( "MaximumListSize", $filter["MaximumListSize"] ?: self::MBE_UPS_UAP_MAXIMUM_LIST_ITEM_10 );
				$LocationSearchCriteria->addChild( "SearchRadius", $filter["SearchRadius"] ?: self::MBE_UPS_UAP_SEARCH_RADIUS_20 );

				$SortCriteria = $locatorRequestXML->addChild( 'SortCriteria' );
				$SortCriteria->addChild( 'SortType', "01" );
//			}

			$requestXML = $accessRequestXML->asXML() . $locatorRequestXML->asXML();

			$form = array(
				'http' => array(
					'method' => 'POST',
					'header' => 'Content-type: application/x-www-form-urlencoded',
					'content' => "$requestXML"
				)
			);

			$request = stream_context_create($form);
			$browser = fopen($endpointurl, 'rb', false, $request);
			if (!$browser) {
				throw new Exception("Connection failed.");
			}

			// get response
			$response = stream_get_contents($browser);
			fclose($browser);

			$upsUapList = [];

			if ( ! $response ) {
				throw new Exception("getUapList: Response Error.");
			} else {
				$xmlResponse = simplexml_load_string($response);
				unset($response);
				unset($xmlResponse->Response);
				$xmlResponse = json_decode(json_encode($xmlResponse), true);
				if ($simplyfied && !empty($xmlResponse)) {
					$dropLocation = $xmlResponse['SearchResults']['DropLocation'];
					if(isset($dropLocation['LocationID']) && isset($dropLocation['AccessPointInformation'])) {
						$upsUapList[] = self::getUapItem($dropLocation);
					} else {
						foreach ( $dropLocation as $item ) {
							if(isset($item['AccessPointInformation'])) {
								$upsUapList[] = self::getUapItem($item);
							}
						}
					}
					return $upsUapList;
				}
				return $xmlResponse;
			}
//		} catch (Exception $ex) {
//			return $ex;
//		}
	}

	private static function getUapItem( $item )
	{
		return $item['AddressKeyFormat'] + [ 'LocationID' => $item['LocationID'] ] + [ 'PublicAccesPointID' => $item['AccessPointInformation']['PublicAccessPointID'] ] + [ 'Distance' => $item['Distance']['Value'] . ' ' . $item['Distance']['UnitOfMeasurement']['Code'] ] + [ 'StandardHoursOfOperation' => $item['StandardHoursOfOperation'] ];
	}

}