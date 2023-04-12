<?php

interface Mbe_Shipping_Csv_Entity_Model_Interface
{
	public function getTableName();
	public function truncate();
	public function insertRow($row);
	public function tableExists();
}