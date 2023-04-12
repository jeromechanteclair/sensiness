<?php

class Mbe_Csv_Editor_Model_Factory {
	public function create($editorCsvType)
	{
		if(!empty($editorCsvType)) {
			switch ( strtolower( $editorCsvType ) ) {
				case 'packages':
					require_once( MBE_ESHIP_PLUGIN_DIR . 'includes/class-mbe-csv-editor-package.php' );

					return new \Mbe_Shipping_Csv_Editor_Package();
				case 'packages-products':
					require_once( MBE_ESHIP_PLUGIN_DIR . 'includes/class-mbe-csv-editor-package-product.php' );

					return new \Mbe_Shipping_Csv_Editor_Package_Product();
				default:
					return false;
			}
		}
		return false;
	}
}