<?php

use MbeExceptions\FileUploadException;
use MbeExceptions\ValidationException;

class Mbe_Shipping_CsvPackageProductToTable extends Mbe_Shipping_CsvFileToTable
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
		$this->csvEntityModelClass = Mbe_Shipping_Model_Csv_Package_Product::class;
		$this->currentFileWpOption = MBE_ESHIP_ID . '_' . Mbe_Shipping_Helper_Data::XML_PATH_PACKAGES_PRODUCT_CSV_FILE;
		parent::__construct();
//		$this->packagesCollectionFactory = $packagesCollectionFactory;

		$this->csvHeaderDefinitions = [
			"package_code" => "package_code",
			"product_sku" => "product_sku",
			"single_parcel" => "single_parcel",
			"custom_package" => "custom_package"
		];
	}

	public function run()
	{
		if (parent::save(
			Mbe_Shipping_Helper_Data::MBE_SETTINGS_CSV_PACKAGE_PRODUCT,
			$this->shippingHelper->getCurrentCsvPackagesProductDir()
		)) {
			$this->afterSave();
			return true;
		}
		return false;
	}

	protected function afterSave() {
		if ( $this->fileUpdated ) {

			$csvEntityModel  = new \Mbe_Shipping_Model_Csv_Package();
			$defaultPackages = $csvEntityModel->getStandardPackages();

			if ( count( $defaultPackages ) > 0 && ! in_array( $this->shippingHelper->getCsvStandardPackageDefault(), array_column( $defaultPackages, 'id' ) ) ) {
				$this->shippingHelper->setCsvStandardPackageDefault( $defaultPackages[0]['id'] );
			}
		}
		parent::afterSave();
	}

	protected function _getAllowedExtensions()
	{
		return ['csv' => 'text/csv'];
	}

	protected function _getValidationCallbacks()
	{
		return ['noduplicatepackprod' => 'noDuplicateCustomPackageProduct'];
	}

	/**
	 * Check if a package that is marked as "custom" is used more than once
	 * @throws ValidationException
	 */
	public function noDuplicateCustomPackageProduct($filePath)
	{
		$csvArray = $this->csvHelper->readFile($filePath, $this->csvHeaderDefinitions);

		$customPackages = array_unique(array_column(array_filter(
			$csvArray,
			function ($v) {
				return $v['custom_package'] === '1';
			}
		), 'package_code'));

		$multiplePackages = array_keys(array_diff(array_count_values(array_column($csvArray, 'package_code')), [1]));

		$invalidPackages = array_intersect($multiplePackages, $customPackages);
		if ($invalidPackages) {
			throw new ValidationException(
				__(__('Custom package used more than once:').json_encode($invalidPackages))
			);
		}
	}
}