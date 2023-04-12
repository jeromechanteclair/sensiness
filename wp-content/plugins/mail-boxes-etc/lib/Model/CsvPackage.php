<?php

class Mbe_Shipping_Model_Csv_Package implements Mbe_Shipping_Csv_Entity_Model_Interface
{

	protected $packagesProductModel;

	public function __construct() {
		$this->packagesProductModel = new Mbe_Shipping_Model_Csv_Package_Product();
	}

	public function getTableName() {
		global $wpdb;
		return $wpdb->prefix . Mbe_Shipping_Helper_Data::MBE_CSV_PACKAGES_TABLE_NAME;
	}

	public function getPackageInfobyProduct( $productSku ) {
		global $wpdb;
		$main_table      = $this->getTableName();
		$packagesProduct = $this->packagesProductModel->getTableName();

		return $wpdb->get_results( $wpdb->prepare(
			"SELECT $main_table.*," .
			" $packagesProduct.id as id_product, $packagesProduct.single_parcel, $packagesProduct.custom_package " .
			" FROM $main_table " .
			" LEFT JOIN $packagesProduct ON " .
			" $main_table.package_code = $packagesProduct.package_code " .
			" WHERE $packagesProduct.product_sku = %s"
			, $productSku
		), ARRAY_A );
	}

	public function getStandardPackages() {
		global $wpdb;
		$main_table = $this->getTableName();
		$join_table = $this->packagesProductModel->getTableName();

		return $wpdb->get_results(
			"SELECT $main_table.package_label, $main_table.id  FROM $main_table" .
			" LEFT JOIN $join_table ON " .
			" $main_table.package_code = $join_table.package_code " .
			" WHERE $join_table.custom_package <> true OR $join_table.custom_package is null"
			, ARRAY_A );
	}

	public function getCsvPackages() {
		global $wpdb;
		$main_table = $this->getTableName();

		return $wpdb->get_results( "SELECT *  FROM $main_table", ARRAY_A );
	}

	public function getPackageInfobyId( $packageId ) {
		global $wpdb;
		$main_table = $this->getTableName();

		$a = $wpdb->get_results( $wpdb->prepare(
			"SELECT *, 0 as single_parcel, 0 as custom_package FROM $main_table " .
			" WHERE id = %d"
			, $packageId )
			, ARRAY_A );
		return $a;
	}

	public function tableExists()
	{
		global $wpdb;
		$main_table = $this->getTableName();

		if ( ! $wpdb->get_var( "SHOW TABLES LIKE '$main_table'" ) == $main_table ) {
			return false;
		}
		return true;
	}

	public function truncate() {
		global $wpdb;
		// Do not use real TRUNCATE to avoid reusing ids
		if ($this->tableExists() && !$wpdb->query('TRUNCATE ' . $this->getTableName())) {
			WC_Admin_Settings::add_error(__('Error while truncating table ') . $this->getTableName() . ' :' . $wpdb->last_error);
			return false;
		}
		return true;
	}

	public function insertRow( $row ) {
		global $wpdb;
		try {
			if (!$wpdb->insert($this->getTableName(), $row)) {
				WC_Admin_Settings::add_error(__('Error while adding data to the table ') . $this->getTableName() . ' :' . $wpdb->last_error);
				return false;
			}
		} catch (\Exception $e) {
			WC_Admin_Settings::add_error(__('Unexpected error') . ': ' . $e->getMessage());
		}
		return true;
	}
}