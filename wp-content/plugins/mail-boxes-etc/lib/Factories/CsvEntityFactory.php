<?php

class Mbe_Csv_Entity_Model_Factory
{
	protected $context;
	protected $messageManager;
	protected $packagesModelFactory;
	protected $packagesProductModelFactory;

	public function create($entityHelperClass)
	{
		switch ($entityHelperClass) {
			case \Mbe_Shipping_Model_Csv_Package::class:
				return new \Mbe_Shipping_Model_Csv_Package();
			case \Mbe_Shipping_Model_Csv_Package_Product::class:
				return new \Mbe_Shipping_Model_Csv_Package_Product();
			case \Mbe_Shipping_Model_Csv_Shipping::class:
				return new \Mbe_Shipping_Model_Csv_Shipping();
		}
	}
}