<?php

class Mbe_Shipping_Model_Csv_Package_Product  implements Mbe_Shipping_Csv_Entity_Model_Interface
{

	public function getTableName() {
		global $wpdb;
		return $wpdb->prefix . Mbe_Shipping_Helper_Data::MBE_CSV_PACKAGES_PRODUCT_TABLE_NAME;
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