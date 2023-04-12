<?php

use MbeExceptions\ValidationException;

class Mbe_Shipping_Helper_Csv
{
    public function getCsvHeaderKnowDefinitions()
    {
        return $this->_csvHeaderKnowDefinitions;
    }

	/**
	 * @throws ValidationException
	 */
	public function readFile($importFilePath, $headerDefinition = [])
	{
		$file = fopen($importFilePath, "r");
		$i = 0;
		$header = null;
		$result = [];
		while (!feof($file)) {
			$currentRow = fgetcsv($file, 0, ",", '"');

			if (!empty($currentRow) && ([null]!==$currentRow)) {
				if ($i == 0) {
					//File heading
					$header = $currentRow;
				} else {
					try {
						$current  = $this->readCsvRowToArray( $headerDefinition, $header, $currentRow );
						$result[] = $current;
					} catch (ValidationException $e) {
						throw new ValidationException($e->getMessage() . ' file:'. basename($importFilePath)  );
					}
				}
			}
			$i++;
		}
		return $result;
	}

	/**
	 * @throws ValidationException
	 */
	function readCsvRowToArray($headerDefinition, $header, $row)
	{
		$result = [];
		if (is_array($row) && (count($row) === count($headerDefinition))) {
			foreach ($row as $index => $currentRowValue) {
				$headerName = $this->cleanString($header[$index]);
				$currentKey = $headerName;
				if (isset($headerDefinition[$headerName])) {
					$currentKey = $headerDefinition[$headerName];
				}
				$currentRowValue = trim($currentRowValue);
				/*
				$currentRowValue = str_replace("  ", " ", $currentRowValue);
				$currentRowValue = str_replace("  ", " ", $currentRowValue);
				$currentRowValue = str_replace("  ", " ", $currentRowValue);
				$currentRowValue = str_replace("„",createShipment "\"", $currentRowValue);
				$currentRowValue = str_replace("“", "\"", $currentRowValue);
				$currentRowValue = str_replace("”", "\"", $currentRowValue);
				*/
				$result[$currentKey] = $currentRowValue;
			}
		} else {
			throw new ValidationException(__(__('Expected ('. count($headerDefinition) .') columns, found ('. count($row) .').')));
		}
		return $result;
	}

    function cleanString($str)
    {
        $result = $str;
        $result = trim($result);
        $result = strtolower($result);
        $result = str_replace(" ", "_", $result);
        $result = str_replace(chr(160), "_", $result);
        $result = str_replace(chr(194), "_", $result);
        $result = str_replace(chr(195), "_", $result);
        $result = str_replace(",", "_", $result);
        $result = str_replace("/", "", $result);
        $result = str_replace("(", "", $result);
        $result = str_replace(")", "", $result);
        $result = str_replace("__", "_", $result);
        return $result;
    }
}