<?php

class Mbe_Shipping_Model_Carrier {

	const SHIPMENT_CONFIGURATION_MODE_ONE_SHIPMENT_PER_ITEM = 1;
	const SHIPMENT_CONFIGURATION_MODE_ONE_SHIPMENT_PER_SHOPPING_CART_WEIGHT_MULTI_PARCEL = 2;
	const SHIPMENT_CONFIGURATION_MODE_ONE_SHIPMENT_PER_SHOPPING_CART_ITEMS_MULTI_PARCEL = 3;

	const HANDLING_TYPE_PER_SHIPMENT = "S";
	const HANDLING_TYPE_PER_PARCEL = "P";

	const HANDLING_TYPE_FIXED = "F";

	const MBE_CACHE_ID = 'mbe_cache_getrates';


	/**
	 * Carrier's code, as defined in parent class
	 *
	 * @var string
	 */
	protected $_code = 'mbe_shipping';

	/** @var $logger Mbe_Shipping_Helper_Logger */
	protected $logger = null;
	/** @var $shippingHelper Mbe_Shipping_Helper_Data */
	protected $shippingHelper = null;

	protected $allowedShipmentServicesArray;


	public function __construct() {
		$this->shippingHelper               = new Mbe_Shipping_Helper_Data();
		$this->logger                       = new Mbe_Shipping_Helper_Logger();
		$this->allowedShipmentServicesArray = $this->shippingHelper->getAllowedShipmentServicesArray(); // set a local parameter to be updated (for uap) outside getRates
	}


	public function getRequestWeight( $request ) {
		$result = 0;
		foreach ( $request['contents'] as $item ) {
			$result += ( (float) $item['data']->get_weight() * $item['quantity'] );
		}

		return $result;
	}

	public function collectRates( $request ) {

		if ( $this->shippingHelper->isEnabledCustomMapping() ) {
			// If custom mapping is used no MBE methods will be available
			return [];
		} else {
			$this->logger->log( 'collectRates' );

			if ( ! $this->shippingHelper->isEnabled() ) {
				$this->logger->log( 'module disabled' );

				return false;
			}
			$this->logger->log( 'module enabled' );


			$destCountry = $request['destination']['country'];
			$destRegion  = $request['destination']['state'];
			//TODO:verify
			$city = $request['destination']['city'];

			$destPostCode = $request['destination']['postcode'];

			$this->logger->log( "Destination: COUNTRY: " . $destCountry . " - REGION CODE: " . $destRegion . " - POSTCODE: " . $destPostCode );


			$shipmentConfigurationMode = $this->shippingHelper->getShipmentConfigurationMode();


			$shipments = array();
			//$baseSubtotalInclTax = $request['contents_cost'];//wrong because it is without taxes
			$baseSubtotalInclTax = 0;
			foreach ( $request['contents'] as $item ) {
				$baseSubtotalInclTax += $item['line_total'] + $item['line_tax'];
			}

			$boxesDimensionWeight             = [];
			$boxesSingleParcelDimensionWeight = [];

			$this->logger->log( "SHIPMENTCONFIGURATIONMODE: " . $shipmentConfigurationMode );

			if ( $shipmentConfigurationMode == self::SHIPMENT_CONFIGURATION_MODE_ONE_SHIPMENT_PER_ITEM ) {

				$iteration = 1;
				foreach ( $request['contents'] as $item ) {
					$boxesDimensionWeight             = [];
					$boxesSingleParcelDimensionWeight = [];

					// Retrieve the product info using the new box structure
					$this->shippingHelper->getBoxesArray(
						$boxesDimensionWeight,
						$boxesSingleParcelDimensionWeight,
						$item['data']->get_weight(),
						$this->shippingHelper->
						getPackageInfo( $item['data']->get_sku() )
					);

					for ( $i = 1; $i <= $item['quantity']; $i ++ ) {
						$this->logger->log( "Product Iteration: " . $iteration );
//						$weight = $item['data']->get_weight();
						if ( $this->shippingHelper->getShipmentsInsuranceMode() == Mbe_Shipping_Helper_Data::MBE_INSURANCE_WITH_TAXES ) {
							if ( version_compare( WC()->version, '3', '>=' ) ) {
								$insuranceValue = wc_get_price_including_tax( $item["data"] );
							} else {
								$insuranceValue = $item["data"]->get_price_including_tax();
							}
						} else {
							if ( version_compare( WC()->version, '3', '>=' ) ) {
								$insuranceValue = wc_get_price_excluding_tax( $item["data"] );
							} else {
								$insuranceValue = $item["data"]->get_price_excluding_tax();
							}
						}
						// $boxesDimensionWeight is used directly, since we use 1 box for each shipment and this method is run for every item (1 item - 1 box - 1 shipment)
						$shipments = $this->getRates( $destCountry, $destRegion, $city, $destPostCode, $baseSubtotalInclTax, $boxesDimensionWeight, 1, $shipments, $iteration, $insuranceValue );
						$iteration ++;
					}
				}
			} elseif ( $shipmentConfigurationMode == self::SHIPMENT_CONFIGURATION_MODE_ONE_SHIPMENT_PER_SHOPPING_CART_WEIGHT_MULTI_PARCEL ) {
				$insuranceValue = 0;

				foreach ( $request['contents'] as $item ) {
					$packageInfo = $this->shippingHelper->getPackageInfo( $item['data']->get_sku() );

					$itemQty = $item['quantity'];

					for ( $i = 1; $i <= $itemQty; $i ++ ) {
						$boxesDimensionWeight = $this->shippingHelper->getBoxesArray(
							$boxesDimensionWeight,
							$boxesSingleParcelDimensionWeight,
							$item['data']->get_weight(),
							$packageInfo
						);
					}

					$insuranceValue += $this->getSubtotalForInsurance( $item );
				}

				$boxesMerged = $this->shippingHelper->mergeBoxesArray(
					$boxesDimensionWeight,
					$boxesSingleParcelDimensionWeight
				);

				$numBoxes = $this->shippingHelper->countBoxesArray( $boxesMerged );

				$this->logger->log( "Num Boxes: " . $numBoxes );

				$shipments = $this->getRates(
					$destCountry, $destRegion, $city, $destPostCode, $baseSubtotalInclTax, $boxesMerged, $numBoxes, [], 1, $insuranceValue
				);
			} elseif ( $shipmentConfigurationMode == self::SHIPMENT_CONFIGURATION_MODE_ONE_SHIPMENT_PER_SHOPPING_CART_ITEMS_MULTI_PARCEL ) {
				$numBoxes       = 0;
				$insuranceValue = 0;

				foreach ( $request['contents'] as $item ) {
					$insuranceValue += $this->getSubtotalForInsurance( $item );
					$numBoxes       += $item['quantity'];
					for ( $i = 1; $i <= $item['quantity']; $i ++ ) {
						$this->shippingHelper->getBoxesArray(
							$boxesDimensionWeight,
							$boxesSingleParcelDimensionWeight,
							$item['data']->get_weight(),
							$this->shippingHelper->getPackageInfo( $item['data']->get_sku(), true )
						);
					}
				}
				$this->logger->log( "Num Boxes: " . $numBoxes );
				// $boxesSingleParcelDimensionWeight is used directly, since we always use 1 box for each item (we're not using packages CSV)
				$shipments = $this->getRates( $destCountry, $destRegion, $city, $destPostCode, $baseSubtotalInclTax, $boxesSingleParcelDimensionWeight, $numBoxes, array(), 1, $insuranceValue );

			} else {
				$this->logger->log( 'SHIPMENT CONFIGURATION MODE - Value not set or incorrect, try to save the settings page again '.($shipmentConfigurationMode?' - '.$shipmentConfigurationMode:''), true);
			}


			//TODO- remove subzone if it is the same for all shipping methods

			$subZones = array();
			foreach ( $shipments as $shipment ) {

				if ( ! in_array( $shipment->subzone_id, $subZones ) ) {
					array_push( $subZones, $shipment->subzone_id );
				}

			}
			$useSubZone = false;
			if ( count( $subZones ) > 1 ) {
				$useSubZone = true;
			}

			$result = array();
			if ( empty( $shipments ) ) {
				$errorTitle = 'Unable to retrieve shipping methods';
				$this->logger->log( $errorTitle );
			} else {
				foreach ( $shipments as $shipment ) {
					$showUapService = false;
					if ( $shipment->method === MBE_UAP_SERVICE ) {
						$showUapService = $this->isUapEnabled( $this->shippingHelper->mergeBoxesArray(
							$boxesDimensionWeight,
							$boxesSingleParcelDimensionWeight
						), $request['contents'] );
					}

					// Add the Rate to the list. In case of MDP Rate, add it only if the uap conditions are valid
					if ( $shipment->method !== MBE_UAP_SERVICE || $showUapService ) {
						if ( $useSubZone ) {
							$currentRate = $this->_getRate( $shipment->title_full, $shipment->shipment_code, $shipment->price );
						} else {
							$currentRate = $this->_getRate( $shipment->title, $shipment->shipment_code, $shipment->price );
						}
						$result[] = $currentRate;
					}
				}
			}
		}

		return $result;
	}


	private function getRates( $destCountry, $destRegion, $city, $destPostCode, $baseSubtotalInclTax, $weight, $boxes, $oldResults = array(), $iteration = 1, $insuranceValue = 0 ) {
		$shipmentsCache = json_decode( wp_cache_get( self::MBE_CACHE_ID ) );
		$cachedId       = md5( serialize( [
			$destCountry,
			$destRegion,
			$city,
			$destPostCode,
			$baseSubtotalInclTax,
			$weight,
			$boxes
		] ) );
		if ( ! empty( $shipmentsCache ) && $shipmentsCache->id === $cachedId ) {
			// return cached rates
			$this->logger->log( "getRates from cache" );
			$shipments = $shipmentsCache->data;
		} else {
			// delete cache if exists but not for the same request
			if ( ! empty( $shipmentsCache ) ) {
				wp_cache_delete( self::MBE_CACHE_ID );
			}
			$this->logger->log( "getRates" );

			$ws = new Mbe_Shipping_Model_Ws();

			$ratesHelper = new Mbe_Shipping_Helper_Rates();

			if ( $ratesHelper->useCustomRates( $destCountry ) ) {
//				$totalWeight = $weight;
				$totalWeight = $this->shippingHelper->totalWeightBoxesArray( $weight );

				$shipments = $ratesHelper->getCustomRates( $destCountry, $destRegion, $city, $destPostCode, $totalWeight, $insuranceValue );
			} else {
				$shipments = $ws->estimateShipping( $destCountry, $destRegion, $destPostCode, $weight, $boxes, $insuranceValue );
			}
			$this->logger->logVar( $shipments, 'ws estimateShipping result' );
			// set cache
			wp_cache_add( self::MBE_CACHE_ID, wp_json_encode( [ 'id' => $cachedId, 'data' => $shipments ] ) );
		}

		$result = null;
		if ( $shipments ) {
			$newResults = [];
			foreach ( $shipments as $shipment ) {
				$shipmentMethod = $shipment->Service;

				$shipmentMethodKey = $shipment->Service . "_" . $shipment->IdSubzone;

				if ( in_array( $shipmentMethod, $this->allowedShipmentServicesArray ) ) {
					$shipmentTitle = __( $shipment->ServiceDesc, 'mail-boxes-etc' );
					if ( $shipment->SubzoneDesc ) {
						$shipmentTitle .= " - " . __( $shipment->SubzoneDesc, 'mail-boxes-etc' );
					}

					$shipmentPrice = $shipment->NetShipmentTotalPrice;

					$shipmentPrice = $this->applyFee( $shipmentPrice, $boxes );


					$shippingThreshold = $this->shippingHelper->getThresholdByShippingServrice( $shipmentMethod . ( $this->shippingHelper->getCountry() === $destCountry ? '_dom' : '_ww' ) );

					if ( $shippingThreshold != '' && $baseSubtotalInclTax >= $shippingThreshold ) {
						$shipmentPrice = 0;
					}


					$customLabel            = esc_html( $this->shippingHelper->getShippingMethodCustomLabel( $shipment->Service ) );
					$current                = new stdClass();
					$current->title         = $customLabel ?: ( __( $shipment->ServiceDesc, 'mail-boxes-etc' ) );
					$current->title_full    = $shipmentTitle;
					$current->method        = $shipmentMethod;
					$current->price         = $shipmentPrice;
					$current->subzone       = $shipment->SubzoneDesc;
					$current->subzone_id    = $shipment->IdSubzone;
					$current->shipment_code = $shipmentMethodKey;


					$newResults[ $shipmentMethodKey ] = $current;

				}
			}
			if ( $iteration == 1 ) {
				$result = $newResults;
			} else {

				foreach ( $newResults as $newResultKey => $newResult ) {
					if ( array_key_exists( $newResultKey, $oldResults ) ) {
						$newResult->price        += $oldResults[ $newResultKey ]->price;
						$result[ $newResultKey ] = $newResult;
					}

				}
			}

			return $result;
		}
		return [];
	}

	public function applyFee( $value, $packages = 1 ) {
		$handlingType   = $this->shippingHelper->getHandlingType();
		$handlingAction = $this->shippingHelper->getHandlingAction();
		$handlingFee    = $this->shippingHelper->getHandlingFee();

		if ( $handlingAction == self::HANDLING_TYPE_PER_SHIPMENT ) {
			$packages = 1;
		}

		if ( self::HANDLING_TYPE_FIXED == $handlingType ) {
			//fixed
			$result = $value + $handlingFee * $packages;
		} else {
			//percent
			$result = $value * ( 100 + $handlingFee ) / 100;
		}

		return $result;

	}

	protected function _getRate( $title, $method, $price ) {
		$price = $this->shippingHelper->round( $price );

		return array( 'label' => $title, 'method' => $method, 'price' => $price );
	}

	public function isTrackingAvailable() {
		return true;
	}

	protected function getSubtotalForInsurance( $item ) {
		if ( $this->shippingHelper->getShipmentsInsuranceMode() == Mbe_Shipping_Helper_Data::MBE_INSURANCE_WITH_TAXES ) {
			if ( version_compare( WC()->version, '3', '>=' ) ) {
				$insuranceValue = wc_get_price_including_tax( $item["data"], array( 'qty' => $item['quantity'] ) );
			} else {
				$insuranceValue = $item["data"]->get_price_including_tax() * $item['quantity'];
			}
		} else {
			if ( version_compare( WC()->version, '3', '>=' ) ) {
				$insuranceValue = wc_get_price_excluding_tax( $item["data"], array( 'qty' => $item['quantity'] ) );
			} else {
				$insuranceValue = $item["data"]->get_price_excluding_tax() * $item['quantity'];
			}
		}

		return $insuranceValue;
	}

	/**
	 * @param $boxes
	 * If shipping mode is SHIPMENT_CONFIGURATION_MODE_ONE_SHIPMENT_PER_ITEM $boxes is the last one used for getRates, it's always a "settings" box as CSV it's not used for this mode,
	 * so it must be used for dimensions check only since it contains only 1 item
	 * In the other cases it's the proper list of boxes for the shipment
	 * @param $items
	 *
	 * @return bool
	 */
	private function isUapEnabled( $boxes, $items ) {
		$oneParcel = (
			( $this->shippingHelper->getShipmentConfigurationMode() == self::SHIPMENT_CONFIGURATION_MODE_ONE_SHIPMENT_PER_ITEM ) ||
			( $this->shippingHelper->countBoxesArray( $boxes ) === 1 &&
			  in_array( $this->shippingHelper->getShipmentConfigurationMode(), [
				  self::SHIPMENT_CONFIGURATION_MODE_ONE_SHIPMENT_PER_SHOPPING_CART_WEIGHT_MULTI_PARCEL,
				  self::SHIPMENT_CONFIGURATION_MODE_ONE_SHIPMENT_PER_SHOPPING_CART_ITEMS_MULTI_PARCEL
			  ] )
			)
		);

		// Check longest size of the last box used for getRates. This must be modified if CSV will be enabled for SHIPMENT_CONFIGURATION_MODE_ONE_SHIPMENT_PER_ITEM shipping mode
		// Nested if to avoid useless check
		if ( $oneParcel ) {
			$longestSize = $this->shippingHelper->longestSizeBoxesArray( $boxes );
			$box         = $boxes[ $this->shippingHelper->arrayKeyFirst( $boxes ) ]; // since it's one parcel we can check only the first element of the array
			if ( $longestSize <= MBE_UAP_LONGEST_LIMIT_97_CM ) {
//				Longest Size Ok
				if ( ( $longestSize + ( 2 * $box['dimensions']['width'] ) + ( 2 * $box['dimensions']['height'] ) ) <= MBE_UAP_TOTAL_SIZE_LIMIT_300_CM ) {
//					Total Size Ok
					$weightOk = true;
					if ( $this->shippingHelper->getShipmentConfigurationMode() == Mbe_Shipping_Model_Carrier::SHIPMENT_CONFIGURATION_MODE_ONE_SHIPMENT_PER_ITEM ) {
						foreach ( $items as $item ) {
							if ( $this->shippingHelper->convertWeight( $item['data']->get_weight() ) > MBE_UAP_WEIGHT_LIMIT_20_KG ) {
								$weightOk = false;
								break;
							}
						}
					} else {
						$weightOk = $this->shippingHelper->convertWeight( $this->shippingHelper->totalWeightBoxesArray( $boxes ) ) <= MBE_UAP_WEIGHT_LIMIT_20_KG;
					}
					if ( $weightOk ) {
						// All the checks are OK
						return true;
					}
				}
			}
		}

		return false;
	}

}