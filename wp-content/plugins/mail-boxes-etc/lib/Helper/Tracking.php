<?php

class Mbe_Shipping_Helper_Tracking
{
	const ITALIAN_URL = "https://www.mbe.it/it/tracking?c=";
	const SPAIN_URL = "https://www.mbe.es/es/tracking?c=";
	const GERMANY_URL = "https://www.mbe.de/de/tracking?c=";
	const FRANCE_URL = "https://www.mbefrance.fr/fr/suivi?c=";
	const POLSKA_URL = "https://www.mbe.pl/pl/tracking?c=";

    public function getTrackingUrlBySystem($system)
    {
        $result = "";
        if ($system == "IT") {
            $result = self::ITALIAN_URL;
        } elseif ($system == "ES") {
            $result = self::SPAIN_URL;
        } elseif ($system == "DE") {
            $result = self::GERMANY_URL;
        } elseif ($system == "AT") {
            $result = self::AUSTRIA_URL;
        } elseif ($system == "FR") {
            $result = self::FRANCE_URL;
        }
        return $result;
    }
}

