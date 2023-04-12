<?php

use MbeExceptions\FileUploadException;
use MbeExceptions\ValidationException;

class Mbe_Shipping_CsvShippingToTable extends Mbe_Shipping_CsvFileToTable
{

	/**
	 * Validate callbacks storage
	 *
	 * @var array
	 * @access protected
	 */
	protected $_validateCallbacks = [];

	protected $csvEntityModelClass;
	protected $csvHeaderDefinitions = [];


	public function __construct() {
		$this->csvEntityModelClass = Mbe_Shipping_Model_Csv_Shipping::class;
		$this->currentFileWpOption = MBE_ESHIP_ID . '_' . Mbe_Shipping_Helper_Data::XML_PATH_SHIPMENTS_CSV_FILE;
		parent::__construct();

		$this->csvHeaderDefinitions = [
			'country'       => 'country',
			'region'        => 'region',
			'city'          => 'city',
			'zip'           => 'zip',
			'zip_to'        => 'zip_to',
			'weight_from'   => 'weight_from',
			'weight_to'     => 'weight_to',
			'price'         => 'price',
			'delivery_type' => 'delivery_type',
		];
	}

	public function run()
	{
		return $this->save(
			Mbe_Shipping_Helper_Data::MBE_SETTINGS_CSV_SHIPMENTS,
			$this->shippingHelper->getCurrentCsvPackagesDir()
		);
	}

	protected function _getValidationCallbacks()
	{
		return ['datavalidation' => 'dataValidation'];
	}

	/**
	 * @throws ValidationException
	 */
	public function dataValidation($filePath)
	{
		$rates = $this->csvHelper->readFile($filePath, $this->csvHeaderDefinitions);

		$i      = 1;

		$allowedShipmentServicesArray = $this->shippingHelper->getAllowedShipmentServices();
		$maxShipmentWeight            = $this->shippingHelper->getMaxShipmentWeight();

		$errors=false;
		foreach ( $rates as $rate ) {
			if ( strlen( $rate["country"] ) > 2 ) {
				WC_Admin_Settings::add_error( sprintf( __( 'File upload error: row %d: "%s", COUNTRY column. Use destination Country in 2 character ISO format (e.g. IT for Italy, ES for Spain, DE for Germany)', 'mail-boxes-etc' ), $i, $rate["country"] ) );
				$errors = true;
			}

			if ( is_array( $allowedShipmentServicesArray ) && ! in_array( $rate["delivery_type"], $allowedShipmentServicesArray ) ) {
				WC_Admin_Settings::add_error( sprintf( __( 'File upload error: row %d: "%s", SHIPMENT TYPE column. Input code is not a valid MBE Service', 'mail-boxes-etc' ), $i, $rate["delivery_type"] ) );
				$errors = true;
			}

			if ( $maxShipmentWeight ) {
				if ( $rate["weight_from"] > $maxShipmentWeight ) {
					WC_Admin_Settings::add_error( sprintf( __( 'File upload error: row %d: "%s", WEIGHT column. Input weight exceeds allowed', 'mail-boxes-etc' ), $i, $rate["weight_from"] ) );
					$errors = true;
				}
				if ( $rate["weight_to"] > $maxShipmentWeight ) {
					WC_Admin_Settings::add_error( sprintf( __( 'File upload error: row %d: "%s", WEIGHT column. Input weight exceeds allowed', 'mail-boxes-etc' ), $i, $rate["weight_to"] ) );
					$errors = true;
				}
			}
			$i ++;
		}

		if($errors) {
			throw new ValidationException();
		}

	}
}