<?php

use MbeExceptions\FileUploadException;
use MbeExceptions\ValidationException;

class Mbe_Shipping_CsvPackageToTable extends Mbe_Shipping_CsvFileToTable
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
		$this->csvEntityModelClass = Mbe_Shipping_Model_Csv_Package::class;
		$this->currentFileWpOption = MBE_ESHIP_ID . '_' . Mbe_Shipping_Helper_Data::XML_PATH_PACKAGES_CSV_FILE;
		parent::__construct();
//		$this->packagesCollectionFactory = $packagesCollectionFactory;

		$this->csvHeaderDefinitions = [
			"package_code" => "package_code",
			"package_label" => "package_label",
			"height" => "height",
			"width" => "width",
			"length" => "length",
			"max_weight" => "max_weight"
		];
	}

	public function run()
	{
		if (parent::save(
			Mbe_Shipping_Helper_Data::MBE_SETTINGS_CSV_PACKAGE,
			$this->shippingHelper->getCurrentCsvPackagesDir()
		)) {
			$this->afterSave();
			return true;
		}
		return false;


	}

	protected function afterSave() {
		// If the packages list is new,set the first value as default or disable the csvPackage if there are no packages
		if ($this->fileUpdated) {
			$csvEntityModel  = new \Mbe_Shipping_Model_Csv_Package();
			$defaultPackages = $csvEntityModel->getStandardPackages();

			if (count($defaultPackages)> 0) {
				$this->shippingHelper->setCsvStandardPackageDefault($defaultPackages[0]['id']);
			}
// else {
//				// Check done also in Mbe\Shipping\Observer\AdminSettingObserver. Kept to be sure it will be disabled
//				$this->shippingHelper->disableCsvStandardPackage();
//			}
		}
		parent::afterSave();
	}



	protected function _getAllowedExtensions()
	{
		return ['csv' => 'text/csv'];
	}

	protected function _getValidationCallbacks()
	{
		return ['reservedname' => 'useReservedCode'];
	}

	/**
	 * @throws ValidationException
	 */
	public function useReservedCode($filePath)
	{
		$csvArray = $this->csvHelper->readFile($filePath, $this->csvHeaderDefinitions);

		$reservedCode = Mbe_Shipping_Helper_Data::MBE_CSV_PACKAGES_RESERVED_CODE;
		$haystack = array_column($csvArray, 'package_code');

		if (array_search($reservedCode, $haystack) !== false) {
			throw new ValidationException(
				__(__('Reserved package code cannot be used:') .' '. $reservedCode)
			);
		}
	}
}