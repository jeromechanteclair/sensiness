<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WFSPB_Shortcode {
	public $settings;
	public $unique = false;

	public function __construct() {
		$this->settings = new WFSPB_Data();
		add_shortcode( 'woo_free_shipping_bar', array( $this, 'free_shipping_bar' ) );
	}

	public function free_shipping_bar( $atts ) {
		if ( ! $this->settings->get_option( 'enable' ) ) {
			return '';
		}

		if ( function_exists( 'WC' ) && is_callable( array( WC(), 'is_rest_api_request' ) ) ) {
			if ( WC()->is_rest_api_request() ) {
				return '';
			}
		}

		if ( is_admin() ) {
			return '';
		}

		static $unique = 0;
		$unique ++;

		$atts = shortcode_atts( array(
			'hide_processing_bar' => '',
			'error_message'       => '',
			'success_message'     => '',
			'announce_message'    => '',
			'purchased_message'   => ''
		), $atts, 'woo_free_shipping_bar' );

		$custom_message = array(
			'purchased_message' => $atts['purchased_message'],
			'announce_message'  => $atts['announce_message'],
			'success_message'   => $atts['success_message'],
			'error_message'     => $atts['error_message'],
		);

		$sc_params            = get_option( 'wfspb_shortcode_params' );
		$sc_params            = $sc_params ? $sc_params : [];
		$sc_params[ $unique ] = $atts;
		update_option( 'wfspb_shortcode_params', $sc_params );

		$this->settings->enqueue_script_frontend();

		$free_shipping    = $this->settings->get_free_shipping_min_amount();
		$order_min_amount = $free_shipping['min_amount'];
		$ignore_discounts = $free_shipping['ignore_discounts'];

//		if ( ! $order_min_amount ) {
//			return '';
//		}

		$total = $this->settings->get_total( $ignore_discounts );

		if( $order_min_amount > 0){
			$width = round( ( $total / $order_min_amount * 100 ), 0 );
		}else{
			$width = 100;
		}
		$width = $width <= 100 ? $width : 100;

		$message = $this->settings->get_full_message( $order_min_amount, $total, $custom_message );
		ob_start();
		?>
        <div id="wfspb-shortcode" data-wfspbsc="<?php echo esc_attr( $unique ) ?>">
            <div class="woocommerce-free-shipping-bar-order wfspb-is-shortcode wfspb-shortcode-<?php echo esc_attr( $unique ) ?>">
                <div class="woocommerce-free-shipping-bar-order-content">
                    <div class="woocommerce-free-shipping-bar-message">
						<?php echo wp_kses_post( wp_unslash($message) ); ?>
                    </div>
					<?php
					if ( empty( $atts['hide_processing_bar'] ) ) {
						?>
                        <div class="woocommerce-free-shipping-bar-order-bar">
                            <div class="woocommerce-free-shipping-bar-order-bar-inner"
                                 style="width: <?php echo esc_attr( $width ) ?>%">
                            </div>
                        </div>
						<?php
					}
					?>
                </div>
            </div>
        </div>
		<?php

		return ob_get_clean();
	}

}

new WFSPB_Shortcode();