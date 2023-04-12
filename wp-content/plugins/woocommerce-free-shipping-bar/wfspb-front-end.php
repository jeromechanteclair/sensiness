<?php
/**
 * Class WFSPB_FrontEnd
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

class WFSPB_FrontEnd {
	protected $settings;
	public $is_ajax_checkout_update;
	public $count_mini_cart_run;

	public function __construct() {
		$this->settings            = new WFSPB_Data();
		$this->count_mini_cart_run = false;

		if ( is_admin() ) {
			return;
		}
		if ( $this->settings->get_option( 'enable' ) ) {
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_script_for_main_bar' ), 9999 );
			add_action( 'wp_head', array( $this, 'show_bar_conditional' ), PHP_INT_MAX );

			/*Single product page*/
			add_action( 'woocommerce_after_add_to_cart_button', array( $this, 'show_bar_bottom' ), 90 );

			/*Cart page*/
			switch ( $this->settings->get_option( 'position_cart' ) ) {
				case 0:
					/*Above Process Button*/
					add_action( 'woocommerce_proceed_to_checkout', array( $this, 'show_bar_bottom' ) );
					break;
				case 2:
					/*Before Cart Table*/
					add_action( 'woocommerce_before_cart_table', array( $this, 'show_bar_bottom' ) );
					break;
				default:
					/*Below Process Button*/
					add_action( 'woocommerce_after_cart_totals', array( $this, 'show_bar_bottom' ) );
			}
			/*Mini cart*/
			switch ( $this->settings->get_option( 'position_mini_cart' ) ) {
				case 1:
					/*At the top on mini cart*/
					add_action( 'woocommerce_before_mini_cart', array( $this, 'show_bar_in_mini_cart' ) );
					break;
				default:
					/*At the bottom on mini cart*/
					add_action( 'woocommerce_after_mini_cart', array( $this, 'show_bar_in_mini_cart' ) );
            }
//			add_action( 'woocommerce_after_mini_cart', [ $this, 'show_bar_in_mini_cart' ] );

			/*Checkout page*/
			switch ( $this->settings->get_option( 'position_checkout' ) ) {
				case 0:
					/*Before payment methods*/
					add_action( 'woocommerce_review_order_before_payment', array( $this, 'show_bar_bottom' ) );

					break;
				default:
					/*Below Process Button*/
					add_action( 'woocommerce_checkout_order_review', array( $this, 'show_bar_bottom' ), 30 );
			}
			add_filter( 'woocommerce_update_order_review_fragments', array( $this, 'before_payment_methods' ) );
		}
	}

	public function before_payment_methods( $data ) {
		$this->is_ajax_checkout_update                               = true;
		$html                                                        = $this->get_small_bar_ajax_html();
		$data['.woocommerce-free-shipping-bar-order.wfspb-checkout'] = '<div class="woocommerce-free-shipping-bar-order wfspb-checkout">' . $html . '</div>';
		$data['wfspb']                                               = '';

		return $data;
	}

	public function get_small_sc_bar_ajax_html() {
		$custom_message = get_option( 'wfspb_shortcode_params' );
		$result         = [];
		if ( ! empty( $custom_message ) && is_array( $custom_message ) ) {
			foreach ( $custom_message as $key => $attr ) {
				$result[ $key ] = $this->get_small_bar_ajax_html( $attr );
			}
		}

		return $result;
	}

	public function get_small_bar_ajax_html( $args = [] ) {
		$free_shipping    = $this->settings->get_free_shipping_min_amount();
		$order_min_amount = $free_shipping['min_amount'];
		$ignore_discounts = $free_shipping['ignore_discounts'];

//		if ( ! $order_min_amount ) {
//			return '';
//		}

		$total = $this->settings->get_total( $ignore_discounts );

		$message = $this->settings->get_full_message( $order_min_amount, $total, $args );

		if ( $order_min_amount > 0 ) {
			$width = round( ( $total / $order_min_amount * 100 ), 0 );
		} else {
			$width = 100;
		}

		$width = $width <= 100 ? $width : 100;

		$enable_progress = ! empty( $args['hide_processing_bar'] ) ? false : true;

		ob_start();
		?>
        <div class="woocommerce-free-shipping-bar-order-content">
			<?php if ( $message ) {
				?>
                <div class="woocommerce-free-shipping-bar-message">
					<?php echo wp_kses_post( wp_unslash( $message ) ); ?>
                </div>
				<?php
			} ?>

			<?php ob_start(); ?>
            <div class="woocommerce-free-shipping-bar-order-bar">
                <div class="woocommerce-free-shipping-bar-order-bar-inner"
                     style="width: <?php echo esc_attr( $width ) ?>%">
                </div>
            </div>
			<?php
			$progress = ob_get_clean();

			if ( $enable_progress ) {
				if ( wp_doing_ajax() ) {
					if ( ! $this->is_ajax_checkout_update || $this->is_ajax_checkout_update && $width !== 100 ) {
						echo wp_kses_post( $progress );
					}
				} else {
					if ( ( is_checkout() || is_cart() ) && $width && $width !== 100 ) {
						echo wp_kses_post( $progress );
					}
				}
			}
			?>
        </div>
		<?php

		return ob_get_clean();
	}

	public function get_message( $arg ) {
		$params   = $this->settings;
		$old_arg  = str_replace( '_', '-', $arg );
		$messages = $params->get_option( $old_arg );
		$lang     = 'default';

		if ( function_exists( 'wpml_get_current_language' ) ) {
			$lang = wpml_get_current_language();
		}

		if ( function_exists( 'pll_current_language' ) ) {
			$lang = pll_current_language();
		}

		$message = '';

		if ( is_array( $messages ) ) {
			$message = ! empty( $messages[ $lang ] ) ? $messages[ $lang ] : __( 'Please complete setting', 'woocommerce-free-shipping-bar' );
		}

		return $message ? $message : $params->get_option( $arg . '_' . $lang );
	}

	/**
	 * Show bar at bottom
	 */
	public function show_bar_bottom() {

		if ( ( is_checkout() || is_cart() || ( is_single() && $this->settings->get_option( 'show_single_product' ) ) ) && $this->settings->get_option( 'show_at_order_bottom' ) ) {
			$conditional_tags = $this->settings->get_option( 'conditional-tags' );
			$detect_mobile    = $this->settings->get_option( 'detect-mobile' );
			$agn_cart         = $this->settings->get_option( 'agn-cart' );
			$agn_checkout     = $this->settings->get_option( 'agn-checkout' );

			// Detect display on mobile or not
			$detect_mb = new WFSPB_Mobile_Detect();
			if ( $detect_mb->isMobile() ) {
				if ( $detect_mobile == 0 ) {
					return;
				}
			}

			if ( $conditional_tags ) {
				if ( stristr( $conditional_tags, "return" ) === false ) {
					$conditional_tags = "return (" . $conditional_tags . ");";
				}
				if ( eval( $conditional_tags ) ) {
					return;
				}
			}

			if ( $agn_cart ) {
				if ( is_cart() ) {
					return;
				}
			}

			if ( $agn_checkout ) {
				if ( is_checkout() ) {
					return;
				}
			}

			$this->bar_bottom_content();
		}
	}

	public function show_bar_in_mini_cart() {

		if ( ! $this->settings->get_option( 'bar_in_mini_cart' ) ) {
			return;
		}
		if ( $this->count_mini_cart_run ) {
			return;
		}
		$this->count_mini_cart_run = true;
		?>
        <ul class="wfspb_bar_in_mini_cart">
            <li><?php $this->bar_bottom_content(); ?></li>
        </ul>
		<?php
	}

	public function bar_bottom_content() {
		$free_shipping    = $this->settings->get_free_shipping_min_amount();
		$order_min_amount = $free_shipping['min_amount'];
		$ignore_discounts = $free_shipping['ignore_discounts'];
		$cache_compa      = $this->settings->get_option( 'cache_compa' ) ? 'wfspb-is-cache' : '';
		if ( ! $order_min_amount ) {
			return '';
		}
		if ( is_checkout() ) {
			?>
            <div class="woocommerce-free-shipping-bar-order wfspb-checkout"></div>
			<?php
			return;
		}

		if ( ! $order_min_amount && ! $cache_compa ) {
			return;
		}

		$total = $this->settings->get_total( $ignore_discounts );

		$width = $order_min_amount ? round( ( $total / $order_min_amount * 100 ), 0 ) : 0;
		$width = $width <= 100 ? $width : 100;

		$message = $this->settings->get_full_message( $order_min_amount, $total );

		?>
        <div class="woocommerce-free-shipping-bar-order <?php echo esc_attr( $cache_compa ) ?>">
            <div class="woocommerce-free-shipping-bar-order-content">
				<?php if ( $message ) {
					?>
                    <div class="woocommerce-free-shipping-bar-message">
						<?php echo wp_kses_post( wp_unslash( $message ) ); ?>
                    </div>
					<?php
				} ?>

				<?php ob_start(); ?>
                <div class="woocommerce-free-shipping-bar-order-bar">
                    <div class="woocommerce-free-shipping-bar-order-bar-inner"
                         style="width: <?php echo esc_attr( $width ) ?>%">
                    </div>
                </div>
				<?php
				$progress = ob_get_clean();

				if ( wp_doing_ajax() ) {
					echo wp_kses_post( $progress );
				} else {
					if ( is_singular( 'product' ) || is_checkout() || is_cart() && $width && $width !== 100 ) {
						echo wp_kses_post( $progress );
					}
				}
				?>
            </div>
        </div>
		<?php
	}

	/**
	 * Init Script
	 */
	public function enqueue_script_for_main_bar() {
		wp_register_script( 'woocommerce-free-shipping-bar-cache', plugins_url( 'woocommerce-free-shipping-bar/assets/js/woocommerce-free-shipping-bar-cache.js' ), array( 'jquery' ), WFSPB_VERSION, true );
		wp_register_script( 'woocommerce-free-shipping-bar', plugins_url( 'woocommerce-free-shipping-bar/assets/js/woocommerce-free-shipping-bar-frontend.js' ), array(
			'jquery',
			'wp-hooks'
		), WFSPB_VERSION, true );

		wp_register_style( 'woocommerce-free-shipping-bar', plugins_url( 'woocommerce-free-shipping-bar/assets/css/woocommerce-free-shipping-bar-frontend-style.css', 'woocommerce-free-shipping-bar' ) );
		wp_register_style( 'woocommerce-free-shipping-bar-style2', plugins_url( 'woocommerce-free-shipping-bar/assets/css/style-progress/style2.css', 'woocommerce-free-shipping-bar' ) );
		wp_register_style( 'woocommerce-free-shipping-bar-style3', plugins_url( 'woocommerce-free-shipping-bar/assets/css/style-progress/style3.css', 'woocommerce-free-shipping-bar' ) );

		$params = $this->settings;

		$conditional_tags     = $params->get_option( 'conditional-tags' );
		$agn_homepage         = $params->get_option( 'agn-homepage' );
		$agn_cart             = $params->get_option( 'agn-cart' );
		$agn_shop             = $params->get_option( 'agn-shop' );
		$agn_checkout         = $params->get_option( 'agn-checkout' );
		$agn_single_product   = $params->get_option( 'agn-single-product' );
		$agn_product_category = $params->get_option( 'agn-product-category' );
		$agn_product_tag      = $params->get_option( 'agn-product-tag' );

		if ( ( is_checkout() || is_cart() ) && $this->settings->get_option( 'show_at_order_bottom' ) || $this->settings->get_option( 'bar_in_mini_cart' ) ) {

		} else {
			if ( $agn_homepage ) {
				if ( is_front_page() ) {
					return;
				}

			}

			if ( $agn_cart ) {
				if ( is_cart() ) {
					return;
				}
			}

			if ( $agn_shop ) {
				if ( is_shop() ) {
					return;
				}
			}

			if ( $agn_checkout ) {
				if ( is_checkout() ) {
					return;
				}
			}

			if ( $agn_single_product ) {
				if ( is_product() ) {
					return;
				}
			}

			if ( $agn_product_category ) {
				if ( is_product_category() ) {
					return;
				}
			}

			if ( $agn_product_tag ) {
				if ( is_product_tag() ) {
					return;
				}
			}

			if ( $conditional_tags ) {
				if ( stristr( $conditional_tags, "return" ) === false ) {
					$conditional_tags = "return (" . $conditional_tags . ");";
				}
				if ( eval( $conditional_tags ) ) {
					return;
				}

			}
		}
		if ( $params->check_woo_shipping_zone() ) {
			$params->enqueue_script_frontend();
		}
	}

	public function show_bar_conditional() {

		$params = $this->settings;

		if ( ! is_admin() ) {
			$conditional_tags     = $params->get_option( 'conditional-tags' );
			$agn_homepage         = $params->get_option( 'agn-homepage' );
			$agn_cart             = $params->get_option( 'agn-cart' );
			$agn_shop             = $params->get_option( 'agn-shop' );
			$agn_checkout         = $params->get_option( 'agn-checkout' );
			$agn_single_product   = $params->get_option( 'agn-single-product' );
			$agn_product_category = $params->get_option( 'agn-product-category' );
			$agn_product_tag      = $params->get_option( 'agn-product-tag' );
			$detect_mobile        = $params->get_option( 'detect-mobile' );
			$show_at_order_bottom = $params->get_option( 'show_at_order_bottom' );
			if ( $show_at_order_bottom && ( is_checkout() || is_cart() || ( is_single() && $params->get_option( 'show_single_product' ) ) ) ) {
				return;
			}

			// Detect display on mobile or not
			$detect_mb = new WFSPB_Mobile_Detect();
			if ( $detect_mb->isMobile() ) {
				if ( $detect_mobile == 0 ) {
					return false;
				}
			}

			if ( $agn_homepage ) {
				if ( is_front_page() ) {
					return;
				}

			}

			if ( $agn_cart ) {
				if ( is_cart() ) {
					return;
				}
			}

			if ( $agn_shop ) {
				if ( is_shop() ) {
					return;
				}
			}

			if ( $agn_checkout ) {
				if ( is_checkout() ) {
					return;
				}
			}

			if ( $agn_single_product ) {
				if ( is_product() ) {
					return;
				}
			}

			if ( $agn_product_category ) {
				if ( is_product_category() ) {
					return;
				}
			}

			if ( $agn_product_tag ) {
				if ( is_product_tag() ) {
					return;
				}
			}

			if ( $conditional_tags ) {
				if ( stristr( $conditional_tags, "return" ) === false ) {
					$conditional_tags = "return (" . $conditional_tags . ");";
				}

				if ( eval( $conditional_tags ) ) {
					return;
				}
			}

			if ( $params->check_woo_shipping_zone() ) {
				echo $this->show_bar();
			}
		}
	}

	public function show_bar() {
		$params          = $this->settings;
		$close_message   = $params->get_option( 'close-message' );
		$initial_delay   = $params->get_option( 'initial-delay' );
		$position        = $params->get_option( 'position' );
		$enable_progress = $params->get_option( 'enable-progress' );
		$progress_effect = $params->get_option( 'progress_effect' );
		/*Time display bar*/
		$time_to_disappear  = $params->get_option( 'time-to-disappear' );
		$set_time_disappear = $params->get_option( 'set-time-disappear' );
		$progress_style     = $params->get_option( 'style' );
		$class_pos          = $position == 0 ? 'top_bar' : 'bottom_bar';

		$free_shipping    = $this->settings->get_free_shipping_min_amount();
		$order_min_amount = $free_shipping['min_amount'];
		$ignore_discounts = $free_shipping['ignore_discounts'];


		/**
		 * Check If min amount is empty
		 */

		$total = $this->settings->get_total( $ignore_discounts );
		if ( $total ) {
			$class_pos .= ' has_items';
		}

		$cache_compa = $params->get_option( 'cache_compa' );

		$hidden_class = $cache_compa ? ' wfspb-hidden' : '';

		if ( $order_min_amount === '' ) {
			return;
        }
		if ( $order_min_amount === '' && ! $cache_compa ) {
			return;
		}

		$message = $this->settings->get_full_message( $order_min_amount, $total );

		ob_start(); ?>

        <div id="wfspb-top-bar" class="displaying customized <?php echo esc_attr( $class_pos . $hidden_class ) ?>"
             style="<?php echo ! is_checkout() ? 'display:none;' : '' ?>"
			<?php echo $time_to_disappear && $set_time_disappear ? "data-time-disappear='{$set_time_disappear}'" : ''; ?> >
            <div class="wfspb-lining-layer">
				<?php echo $message; ?>
            </div>
			<?php

			if ( $enable_progress ) {
				if ( $order_min_amount == 0 ) {
					$current_percent = $total * 100;
				} else {
					$current_percent = ( $total * 100 ) / $order_min_amount;
				}
				$class = array();
				if ( ! $total || $current_percent >= 100 ) {
					$class[] = 'wfsb-hidden';
				}

				if ( $progress_style ) {
					$class[] = 'wfsb-style-' . $progress_style;
				}

				if ( $progress_effect ) {
					$progress_effect = 'wfsb-effect-' . $progress_effect;
				}
				?>
                <div id="wfspb-progress" class="<?php echo esc_attr( implode( ' ', $class ) ) ?>">
                    <div class="wfspb-progress-background <?php echo esc_attr( $progress_effect ) ?>">
                        <div id="wfspb-current-progress"
                             style="<?php echo intval( $current_percent ) > 0 ? 'width:' . $current_percent . '%' : '' ?>">
                            <div id="wfspb-label"><?php echo round( $current_percent, 0 ); ?>%</div>
                        </div>
                    </div>
                </div>
				<?php
			}

			if ( $close_message == 1 ) {
				echo '<div class="" id="wfspb-close"></div>';
			}
			?>

        </div>

		<?php
		$class        = $this->settings->get_option( 'gift_icon' ) ? 'wfspb-custom-image' : '';
		$show_giftbox = $this->settings->get_option( 'show-giftbox' );

		if ( $show_giftbox == 0 ) {
			return ob_get_clean();
		} ?>
        <div class="wfspb-gift-box <?php echo esc_attr( $class ); ?>"
             data-display="<?php echo esc_attr( $show_giftbox ); ?>" style="opacity: 0;">
			<?php if ( $this->settings->get_option( 'gift_icon' ) ) {
				$image_id = $this->settings->get_option( 'custom_icon' );
				if ( $image_id ) {
					echo wp_get_attachment_image( $image_id, 'full', false );
				}
			} else { ?>
                <img src="<?php echo esc_url( WFSPB_SHIPPING_IMAGES . 'free-delivery.png' ) ?>"/>
			<?php } ?>
        </div>

		<?php return ob_get_clean();
	}

}

new WFSPB_FrontEnd();