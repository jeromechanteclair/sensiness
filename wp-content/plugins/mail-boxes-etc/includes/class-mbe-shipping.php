<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

include_once(__DIR__ . '/class-mbe-csv-to-table.php');
include_once(__DIR__ . '/class-mbe-csv-package-to-table.php');
include_once(__DIR__ . '/class-mbe-csv-package-product-to-table.php');

class mbe_shipping_method extends WC_Shipping_Method {

	private $shippingHelper;
//	private $logger;

	public function __construct(
    ) {
		$this->id                           = MBE_ESHIP_ID;
		$this->method_title                 = __( 'Mail Boxes', 'mail-boxes-etc' );
		$this->method_description           = __( 'Obtains  real time shipping rates and Print shipping labels via MBE Shipping API.', 'mail-boxes-etc' );
		$this->shippingHelper               = new Mbe_Shipping_Helper_Data();
//		$this->logger                       = new Mbe_Shipping_Helper_Logger();
		parent::__construct();
	}

	public function admin_messages() {
		settings_errors( 'myplugin-notices' );
	}

	public function calculate_shipping( $package = array() ) {
		if ( isset( $package['destination'] ) && isset( $package['destination']['country'] ) ) {
			if ( $this->shippingHelper->isEnabled() && $this->shippingHelper->enabledCountry( $package['destination']['country'] ) ) {
				$carrier = new Mbe_Shipping_Model_Carrier();
				$rates   = $carrier->collectRates( $package );

				// Order shipping services from cheaper
				uasort( $rates, function ( $a, $b ) {
					if ( $a['price'] == $b['price'] ) {
						return 0;
					}

					return ( $a['price'] < $b['price'] ) ? - 1 : 1;
				} );

				foreach ( $rates as $r ) {
					// FIXES woocommerce 3.4 compatibility
					$this->id = MBE_ESHIP_ID . ':' . $r['method'];
					$rate     = array(
						'id'    => $this->id . ':' . $r['method'],
						'label' => $r['label'],
						'cost'  => $r['price'],
						//                'calc_tax' => 'per_item'
					);
					// Register the rate
					$this->add_rate( $rate );
				}
			}
		}
	}

	// Remove the title from WC Settings > Shipping Tab
    public function has_settings() {
        return false;
    }

}