<?php

class Mbe_Shipping_Helper_Rates
{
    protected $_rate_table_name;
	protected $helper;


    public function __construct()
    {
	    $this->helper = new Mbe_Shipping_Helper_Data();
	    $this->_rate_table_name = $this->helper->getShipmentCsvTable();
    }

    public function uninstallRatesTable()
    {
        global $wpdb;
        $sql = "DROP TABLE IF EXISTS `" . $this->_rate_table_name . "`";
	    return $wpdb->query($sql);
    }

    public function truncate()
    {
        global $wpdb;
        $truncateSql = " TRUNCATE `" . $this->_rate_table_name . "` ";
	    return $wpdb->query($truncateSql);
    }

    public function insertRate($country, $region, $city, $zip, $zipTo, $weightFrom, $weightTo, $price, $deliveryType)
    {
        global $wpdb;

	    $city = esc_sql($city);
	    $region = esc_sql($region);
	    $country = esc_sql($country);
	    $zip = esc_sql($zip);
	    $zipTo = esc_sql($zipTo);
	    $weightFrom = esc_sql($weightFrom);
	    $weightTo = esc_sql($weightTo);
	    $price = esc_sql($price);
	    $deliveryType = esc_sql($deliveryType);

        $sql = "
                INSERT INTO `" . $this->_rate_table_name . "` (
                    `country`,`region`,`city`,`zip`,`zip_to`,`weight_from`,`weight_to`,`price`,`delivery_type`
                ) 
                VALUES (
                    '" . $country . "',
                    '" . $region . "',
                    '" . $city . "',
                    '" . $zip . "',
                    '" . $zipTo . "',
                    " . $weightFrom . ",
                    " . $weightTo . ",
                    " . $price . ",
                    '" . $deliveryType . "'
                );
            ";

	    return $wpdb->query($sql);
    }


    public function useCustomRates($country)
    {
        global $wpdb;
        $result = false;
	    $country = esc_sql($country);

        $helper = new Mbe_Shipping_Helper_Data();

        if ($helper->getShipmentsCsvMode() == Mbe_Shipping_Helper_Data::MBE_CSV_MODE_DISABLED) {

        }
        elseif ($helper->getShipmentsCsvMode() == Mbe_Shipping_Helper_Data::MBE_CSV_MODE_TOTAL) {
            $result = true;
        }
        elseif ($helper->getShipmentsCsvMode() == Mbe_Shipping_Helper_Data::MBE_CSV_MODE_PARTIAL) {
            $sql = "SELECT * FROM `" . $this->_rate_table_name . "` WHERE `country` = '" . $country . "'";
            $rates = $wpdb->get_results($sql,"ARRAY_A");
            if (is_array($rates) && count($rates) > 0) {
                $result = true;
            }
        }
        return $result;
    }

    public function applyInsuranceToRate($rate, $insuranceValue)
    {
        $result = $rate;

        $helper = new Mbe_Shipping_Helper_Data();

        $helper->getShipmentsCsvInsurancePercentage();

        $percentageValue = $helper->getShipmentsCsvInsurancePercentage() / 100 * (float)$insuranceValue;
        $fixedValue = $helper->getShipmentsCsvInsuranceMin();

        if ($percentageValue < $fixedValue) {
            $result += $fixedValue;
        }
        else {
            $result += $percentageValue;
        }
        return $result;
    }


    public function getCustomRates($country, $region, $city, $postCode, $weight, $insuranceValue)
    {
	    global $wpdb;
	    $result = array();
	    $newdata = array();

	    $postCode = esc_sql($postCode);
	    $city = esc_sql($city);
	    $region = esc_sql($region);
	    $country = esc_sql($country);
	    $zipSql = " '" . $postCode . "' BETWEEN zip AND zip_to";

        $helper = new Mbe_Shipping_Helper_Data();
	    $weight = $helper->convertWeight($weight, 'kg');
        $services = $helper->getAllowedShipmentServicesArray();

        foreach ($services as $service) {
            for ($j = 0; $j <= 7; $j++) {

                $sql = "SELECT * FROM `" . $this->_rate_table_name . "` ";

                switch ($j) {
                    case 0:
                        $sql .= "WHERE ";
                        $sql .= "`country` = '" . $country . "'";
                        $sql .= " AND ";
                        $sql .= "`region` = '" . $region . "'";
                        $sql .= " AND ";
                        $sql .= " STRCMP(LOWER(city),LOWER('" . $city . "')) = 0";
                        $sql .= " AND ";
                        $sql .= $zipSql;

                        break;

                    case 1:
                        $sql .= "WHERE ";
                        $sql .= "`country` = '" . $country . "'";
                        $sql .= " AND ";
                        $sql .= "`region` = '" . $region . "'";
                        $sql .= " AND ";
                        $sql .= "`city` = ''";
                        $sql .= " AND ";
                        $sql .= $zipSql;

                        break;
                    case 2:
                        $sql .= "WHERE ";
                        $sql .= "`country` = '" . $country . "'";
                        $sql .= " AND ";
                        $sql .= "`region` = '" . $region . "'";
                        $sql .= " AND ";
                        $sql .= "STRCMP(LOWER(city),LOWER('" . $city . "')) = 0";
                        $sql .= " AND ";
                        $sql .= " zip = '' ";

                        break;
                    case 3:
                        $sql .= "WHERE ";
                        $sql .= "`country` = '" . $country . "'";
                        $sql .= " AND ";
                        //$sql .= "`region` = '" . $region . "'";
                        $sql .= "`region` = ''";
                        $sql .= " AND ";
                        $sql .= "STRCMP(LOWER(city),LOWER('" . $city . "')) = 0";
                        $sql .= " AND ";
                        $sql .= $zipSql;

                        break;
                    case 4:
                        $sql .= "WHERE ";
                        $sql .= "`country` = '" . $country . "'";
                        $sql .= " AND ";
                        //$sql .= "`region` = '" . $region . "'";
                        $sql .= "`region` = ''";
                        $sql .= " AND ";
                        $sql .= "STRCMP(LOWER(city),LOWER('" . $city . "')) = 0";
                        $sql .= " AND ";
                        $sql .= "zip = ''";

                        break;
                    case 5:
                        $sql .= "WHERE ";
                        $sql .= "`country` = '" . $country . "'";
                        $sql .= " AND ";
                        //$sql .= "`region` = '" . $region . "'";
                        $sql .= "`region` = ''";
                        $sql .= " AND ";
                        $sql .= "city = ''";
                        $sql .= " AND ";
                        $sql .= $zipSql;

                        break;
                    case 6:
                        $sql .= "WHERE ";
                        $sql .= "`country` = '" . $country . "'";
                        $sql .= " AND ";
                        $sql .= "`region` = '" . $region . "'";
                        $sql .= " AND ";
                        $sql .= "city = ''";
                        $sql .= " AND ";
                        $sql .= "zip = ''";
                        break;
                    case 7:
                        $sql .= "WHERE ";
                        $sql .= "`country` = '" . $country . "'";
                        $sql .= " AND ";
                        $sql .= "`region` = ''";
                        $sql .= " AND ";
                        $sql .= "city = ''";
                        $sql .= " AND ";
                        $sql .= "zip = ''";
                        break;

                }
                $sql .= " AND weight_from <= " . $weight . " AND weight_to >=" . $weight;
                $sql .= " AND delivery_type = '" . $service . "'";

                $sql .= " ORDER BY country DESC, region DESC, zip DESC";


                $rows = $wpdb->get_results($sql, 'ARRAY_A');


                if (!empty($rows)) {
                    // have found a result or found nothing and at end of list!
                    foreach ($rows as $data) {
                        $newdata[$data["delivery_type"]] = $data;
                    }
                    break;
                }

            }
        }

        $ws = new Mbe_Shipping_Model_Ws();
        $helper = new Mbe_Shipping_Helper_Data();
        foreach ($newdata as $data) {
            $rate = new \stdClass;
            $rate->Service = $data["delivery_type"];
            $rate->ServiceDesc = $ws->getLabelFromShipmentType($data["delivery_type"]);
            $rate->SubzoneDesc = '';
            $rate->IdSubzone = '';

            $rate->NetShipmentTotalPrice = $data["price"];
            $result[] = $rate;

            //rate with insurance
            $rateWithInsurance = new \stdClass;
            $rateWithInsurance->Service = $helper->convertShippingCodeWithInsurance($data["delivery_type"]);
            $rateWithInsurance->ServiceDesc = $helper->convertShippingLabelWithInsurance($ws->getLabelFromShipmentType($data["delivery_type"]));;
            $rateWithInsurance->SubzoneDesc = '';
            $rateWithInsurance->IdSubzone = '';

            $rateWithInsurance->NetShipmentTotalPrice = $this->applyInsuranceToRate($data["price"], $insuranceValue);
            $result[] = $rateWithInsurance;
        }
        return $result;
    }
}