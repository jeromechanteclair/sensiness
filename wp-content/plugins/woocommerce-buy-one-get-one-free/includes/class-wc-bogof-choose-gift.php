<?php
/**
 * Buy One Get One Free Choose your gift. Handles choose your gift actions.
 *
 * @package WC_BOGOF
 */

defined( 'ABSPATH' ) || exit;

/**
 * WC_BOGOF_Choose_Gift Class
 */
class WC_BOGOF_Choose_Gift {

	/**
	 * The choose your gift notice has been displayed?
	 *
	 * @var string
	 */
	private static $notice_displayed = false;

	/**
	 * Array of product to clear cache.
	 *
	 * @var array
	 */
	private static $parents = array();

	/**
	 * The cart hash set by wc_bogof_refer query parameter.
	 *
	 * @var array
	 */
	private static $cart_hash = false;

	/**
	 * Init hooks
	 */
	public static function init() {
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'load_scripts' ), 20 );
		// Product filters.
		add_action( 'wc_bogof_cart_rules_loaded', array( __CLASS__, 'init_hooks' ) );
		add_action( 'wc_bogof_before_choose_your_gift_loop', array( __CLASS__, 'add_price_filters' ) );
		add_action( 'wc_bogof_after_choose_your_gift_loop', array( __CLASS__, 'remove_price_filters' ) );

		// Notice.
		add_action( 'woocommerce_shortcode_before_product_cat_loop', array( __CLASS__, 'choose_your_gift_notice' ), 10 );
		add_action( 'woocommerce_before_shop_loop', array( __CLASS__, 'choose_your_gift_notice' ), 10 );
		add_action( 'woocommerce_before_single_product', array( __CLASS__, 'choose_your_gift_notice' ), 15 );
		add_action( 'woocommerce_before_cart', array( __CLASS__, 'choose_your_gift_notice' ), 15 );
		add_action( 'woocommerce_before_checkout_form', array( __CLASS__, 'choose_your_gift_notice' ), 15 );

		// AJAX action.
		add_action( 'wc_ajax_bogof_update_choose_your_gift', array( __CLASS__, 'update_choose_your_gift' ) );
		add_filter( 'woocommerce_add_to_cart_fragments', array( __CLASS__, 'add_to_cart_fragments' ) );

		// Redirect after add to cart.
		add_action( 'wc_bogof_free_item_added_to_cart', array( __CLASS__, 'free_item_added_to_cart' ) );

		// Display choose your gift after cart.
		add_action( 'woocommerce_after_cart', array( __CLASS__, 'choose_your_gift_after_cart' ), 5 );

		// Shortcode.
		add_shortcode( 'wc_choose_your_gift', array( __CLASS__, 'choose_your_gift' ) );
	}

	/**
	 * Init the choose your gift filters only when wc_bogo_refer exists.
	 */
	public static function init_hooks() {
		// phpcs:disable WordPress.Security.NonceVerification
		self::$cart_hash = isset( $_REQUEST['wc_bogo_refer'] ) ? wc_clean( $_REQUEST['wc_bogo_refer'] ) : false; // phpcs:ignore

		if ( ! self::$cart_hash && self::is_ajax() ) {

			$referer = wp_get_referer();
			$query   = wp_parse_url( $referer, PHP_URL_QUERY );
			wp_parse_str( $query, $params );

			self::$cart_hash = isset( $params['wc_bogo_refer'] ) ? $params['wc_bogo_refer'] : false;

			if ( ! self::$cart_hash && self::is_cart( $referer ) ) {
				// Quick view and AJAX add to cart support on cart page??
				$product_id      = self::get_first_set( $_REQUEST, 'product_id', 'product', 'add-to-cart' ); // phpcs:ignore WordPress.Security.NonceVerification
				self::$cart_hash = $product_id && WC_BOGOF_Cart::get_product_shop_free_quantity( absint( $product_id ) ) > 0;
			}
		}

		// phpcs:enable

		if ( ! self::$cart_hash || self::is_cart() || self::is_checkout() || self::is_choose_your_gift() ) {
			// No product filters for these pages.
			return;
		}

		// Add the product filters.
		self::add_product_filters();

		do_action( 'wc_bogof_choose_your_gift_init' );
	}

	/**
	 * Compare two URLs.
	 *
	 * @param string $a URL to compare.
	 * @param string $b URL to compare. Empty to comprare with the current URL.
	 * @return bool
	 */
	private static function is_page( $a, $b = false ) {
		if ( empty( $b ) ) {
			$b = isset( $_SERVER['REQUEST_URI'] ) ? wc_clean( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
		}
		return wp_parse_url( $a, PHP_URL_PATH ) === wp_parse_url( $b, PHP_URL_PATH );
	}

	/**
	 * Is cart page?
	 *
	 * @param string $url URL to compare. Empty to comprare with the current URL.
	 *
	 * @return bool
	 */
	private static function is_cart( $url = false ) {
		return self::is_page( wc_get_cart_url(), $url );
	}

	/**
	 * Is checkout page?
	 *
	 * @param string $url URL to compare. Empty to comprare with the current URL.
	 * @return bool
	 */
	private static function is_checkout( $url = false ) {
		return self::is_page( wc_get_checkout_url(), $url );
	}

	/**
	 * Is AJAX request?
	 *
	 * @return bool
	 */
	private static function is_ajax() {
		$is_ajax = ( defined( 'DOING_AJAX' ) && DOING_AJAX ) || ( isset( $_SERVER['HTTP_X_REQUESTED_WITH'] ) && 'xmlhttprequest' === strtolower( $_SERVER['HTTP_X_REQUESTED_WITH'] ) ); // phpcs:ignore
		if ( $is_ajax ) {
			$action = self::get_first_set( $_REQUEST, 'action', 'wc-ajax' ); // phpcs:ignore WordPress.Security.NonceVerification
			if ( $action && in_array( $action, array( 'get_refreshed_fragments', 'remove_from_cart', 'apply_coupon', 'remove_coupon' ), true ) ) {
				// No filters for WooCommerce AJAX actions.
				$is_ajax = false;
			}
		}
		return $is_ajax;
	}

	/**
	 * Returns the first value that isset from an array.
	 */
	private static function get_first_set() {
		$value = false;
		$args  = func_get_args();
		$data  = count( $args ) && is_array( $args[0] ) ? $args[0] : false;
		if ( false !== $data ) {
			$i   = 1;
			$max = count( $args );
			while ( $i < $max && ! isset( $data[ $args[ $i ] ] ) ) {
				$i++;
			}
			if ( $i < $max ) {
				$value = wc_clean( $data[ $args[ $i ] ] );
			}
		}
		return $value;
	}

	/**
	 * Is choose your gift page?
	 *
	 * @return bool
	 */
	public static function is_choose_your_gift() {
		if ( 'after_cart' === get_option( 'wc_bogof_cyg_display_on', 'after_cart' ) ) {
			return self::is_cart();
		} else {
			$page_id = absint( get_option( 'wc_bogof_cyg_page_id', 0 ) );
			return self::is_page( get_permalink( $page_id ) );
		}
	}

	/**
	 * Return the cart hash.
	 *
	 * @return string|bool
	 */
	public static function get_refer() {
		return self::$cart_hash;
	}

	/**
	 * Add product filters for single product.
	 */
	public static function add_product_filters() {
		add_action( 'wc_bogof_before_calculate_totals', array( __CLASS__, 'remove_price_filters' ) );
		add_action( 'wc_bogof_after_calculate_totals', array( __CLASS__, 'add_price_filters' ) );
		add_filter( 'woocommerce_add_to_cart_form_action', array( __CLASS__, 'add_to_cart_form_action' ), 9999 );
		add_action( 'woocommerce_before_add_to_cart_button', array( __CLASS__, 'before_add_to_cart_button' ), 9999 );
		add_filter( 'woocommerce_quantity_input_max', array( __CLASS__, 'quantity_input_max' ), 9999, 2 );
		add_filter( 'woocommerce_available_variation', array( __CLASS__, 'available_variation' ), 9999, 2 );
		add_action( 'shutdown', array( __CLASS__, 'clear_cache' ), -1 );
		self::add_price_filters();
	}

	/**
	 * Add the price filter.
	 */
	public static function add_price_filters() {
		add_filter( 'woocommerce_product_get_price', array( __CLASS__, 'get_free_product_price' ), 9999, 2 );
		add_filter( 'woocommerce_product_variation_get_price', array( __CLASS__, 'get_free_product_price' ), 9999, 2 );
		add_filter( 'woocommerce_product_get_sale_price', array( __CLASS__, 'get_free_product_price' ), 9999, 2 );
		add_filter( 'woocommerce_product_variation_get_sale_price', array( __CLASS__, 'get_free_product_price' ), 9999, 2 );
		add_filter( 'woocommerce_variation_prices_price', array( __CLASS__, 'get_free_product_price' ), 9999, 2 );
		add_filter( 'woocommerce_variation_prices_sale_price', array( __CLASS__, 'get_free_product_price' ), 9999, 2 );
		add_filter( 'woocommerce_get_variation_prices_hash', array( __CLASS__, 'get_variation_prices_hash' ), 9999, 2 );
		add_filter( 'woocommerce_get_children', array( __CLASS__, 'get_children' ), 10, 2 );
	}

	/**
	 * Add the price filter.
	 */
	public static function remove_price_filters() {
		remove_filter( 'woocommerce_product_get_price', array( __CLASS__, 'get_free_product_price' ), 9999, 2 );
		remove_filter( 'woocommerce_product_variation_get_price', array( __CLASS__, 'get_free_product_price' ), 9999, 2 );
		remove_filter( 'woocommerce_product_get_sale_price', array( __CLASS__, 'get_free_product_price' ), 9999, 2 );
		remove_filter( 'woocommerce_product_variation_get_sale_price', array( __CLASS__, 'get_free_product_price' ), 9999, 2 );
		remove_filter( 'woocommerce_variation_prices_price', array( __CLASS__, 'get_free_product_price' ), 9999, 2 );
		remove_filter( 'woocommerce_variation_prices_sale_price', array( __CLASS__, 'get_free_product_price' ), 9999, 2 );
		remove_filter( 'woocommerce_get_variation_prices_hash', array( __CLASS__, 'get_variation_prices_hash' ), 9999, 2 );
		remove_filter( 'woocommerce_get_children', array( __CLASS__, 'get_children' ), 10, 2 );
	}

	/**
	 * Add the bogof parameter to the URL
	 *
	 * @param string $form_action Form action link.
	 */
	public static function add_to_cart_form_action( $form_action ) {
		global $product;
		if ( WC_BOGOF_Cart::get_product_shop_free_quantity( $product ) > 0 ) {
			$form_action = add_query_arg( 'wc_bogo_refer', WC_BOGOF_Cart::get_hash(), $form_action );
		}
		return $form_action;
	}

	/**
	 * Output the "bogo_refer" field.
	 */
	public static function before_add_to_cart_button() {
		global $product;
		global $post;

		$product_id = is_callable( array( $product, 'get_id' ) ) ? $product->get_id() : $post->ID;

		if ( WC_BOGOF_Cart::get_product_shop_free_quantity( $product_id ) > 0 ) {
			echo '<input type="hidden" name="wc_bogo_refer" value="' . esc_attr( WC_BOGOF_Cart::get_hash() ) . '" />';
		}
	}

	/**
	 * Set the max purchase qty.
	 *
	 * @param int        $max_quantity Max purchase qty.
	 * @param WC_Product $product Product object.
	 * @return int
	 */
	public static function quantity_input_max( $max_quantity, $product ) {
		$max_free_qty = WC_BOGOF_Cart::get_product_shop_free_quantity( $product );
		if ( $max_free_qty > 0 && $max_free_qty > $max_quantity ) {
			$max_quantity = $max_free_qty;
		}
		return $max_free_qty;
	}

	/**
	 * Filter the max qty of the product variation.
	 *
	 * @param array      $data Variation data.
	 * @param WC_Product $product Parent product object.
	 * @return array
	 */
	public static function available_variation( $data, $product ) {
		$max_free_qty = WC_BOGOF_Cart::get_product_shop_free_quantity( $product );
		if ( isset( $data['max_qty'] ) && $data['max_qty'] > $max_free_qty ) {
			$data['max_qty'] = $max_free_qty;
		}
		return $data;
	}

	/**
	 * Return the zero price for free products.
	 *
	 * @param mixed      $price Product price.
	 * @param WC_Product $product Product instance.
	 */
	public static function get_free_product_price( $price, $product ) {
		self::remove_price_filters();
		if ( WC_BOGOF_Cart::get_product_shop_free_quantity( $product ) > 0 ) {
			if ( ! isset( $product->_bogof_discount ) || ! is_a( $product->_bogof_discount, 'WC_BOGOF_Cart_Item_Discount' ) ) {
				foreach ( wc_bogof_cart_rules() as $cart_rule ) {
					// Create a Item discount to calculate the price.
					if ( $cart_rule->is_shop_avilable_free_product( $product ) ) {
						$product->_bogof_discount = new WC_BOGOF_Cart_Item_Discount(
							array(
								'data'     => $product,
								'quantity' => 1,
							),
							array(
								$cart_rule->get_id() => 1,
							)
						);
						// Get the price of the first rule.
						break;
					}
				}
			}
			$price = $product->_bogof_discount->get_sale_price();
		}
		self::add_price_filters();
		return $price;
	}

	/**
	 * Returns unique cache key to store variation child prices.
	 *
	 * @param array      $price_hash Unique cache key.
	 * @param WC_Product $product Product instance.
	 * @return array
	 */
	public static function get_variation_prices_hash( $price_hash, $product ) {
		if ( WC_BOGOF_Cart::get_product_shop_free_quantity( $product ) > 0 ) {
			$price_hash   = is_array( $price_hash ) ? $price_hash : array( $price_hash );
			$price_hash[] = WC_BOGOF_Cart::get_hash();
		}
		return $price_hash;
	}

	/**
	 * Filter the variations for the choose your gift action.
	 *
	 * @param array $children Product variable children.
	 * @param array $product Product variable.
	 * @return array
	 */
	public static function get_children( $children, $product ) {
		self::remove_price_filters();

		if ( WC_BOGOF_Cart::get_product_shop_free_quantity( $product ) > 0 ) {
			// Filter the children.
			$children = array_filter( $children, array( 'WC_BOGOF_Cart', 'get_product_shop_free_quantity' ) );
			// Increment cache prefix to filter variations.
			WC_Cache_Helper::get_cache_prefix( 'product_' . $product->get_id(), true );

			// Add the product to the clear cache array.
			self::$parents[] = $product->get_id();
		}

		self::add_price_filters();

		return $children;
	}

	/**
	 * Clear the cache of variable products.
	 */
	public static function clear_cache() {
		foreach ( self::$parents as $product_id ) {
			// Increment cache prefix.
			WC_Cache_Helper::get_cache_prefix( 'product_' . $product_id, true );
		}
	}

	/**
	 * Add filters after free item added to the cart.
	 */
	public static function free_item_added_to_cart() {
		if ( wc_bogof_is_choose_your_gift_request() ) {
			add_filter( 'woocommerce_add_to_cart_redirect', array( __CLASS__, 'add_to_cart_redirect' ), 999, 2 );
		}
	}

	/**
	 * Redirects to the cart when there are no more free items.
	 *
	 * @param string $url Redirect URL.
	 * @param int    $product_id Product added to the cart.
	 * @return string
	 */
	public static function add_to_cart_redirect( $url, $product_id ) {
		if ( ! self::is_ajax() && self::get_refer() ) {
			if ( WC_BOGOF_Cart::get_shop_free_quantity() <= 0 ) {
				$url = wc_get_cart_url();
			} elseif ( ! WC_BOGOF_Cart::get_product_shop_free_quantity( $product_id ) ) {
				$url = self::get_link();
			}
		}
		return $url;
	}

	/**
	 * Add to cart fragments.
	 *
	 * @param array $fragments Fragments array.
	 */
	public static function add_to_cart_fragments( $fragments ) {
		// phpcs:disable WordPress.Security.NonceVerification
		$postdata   = isset( $_POST['wc_bogof_data'] ) ? wc_clean( wp_unslash( $_POST['wc_bogof_data'] ) ) : false;
		$product_id = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : false;
		$fragments  = is_array( $fragments ) ? $fragments : array();
		$data       = array(
			'is_choose_your_gift' => 'no',
		);

		// Redirect. Support for AJAX add to cart on single page.
		$data['cart_redirect']             = ( $postdata || self::get_refer() ) && WC_BOGOF_Cart::get_shop_free_quantity() <= 0 ? 'yes' : 'no';
		$data['choose_your_gift_redirect'] = self::get_refer() && ! WC_BOGOF_Cart::get_product_shop_free_quantity( $product_id ) ? 'yes' : 'no';

		if ( $postdata ) {

			$data['is_choose_your_gift'] = 'yes';

			$hash    = empty( $postdata['hash'] ) ? false : $postdata['hash'];
			$is_cart = empty( $postdata['is_cart'] ) ? false : wc_string_to_bool( $postdata['is_cart'] );

			// No redirect on cart page.
			$data['cart_redirect'] = $is_cart ? 'no' : $data['cart_redirect'];

			// Refresh choose your gift.
			if ( WC_BOGOF_Cart::get_shop_free_quantity() > 0 ) {

				// Add to cart message.
				if ( 'yes' !== $data['cart_redirect'] ) {
					$product_id = apply_filters( 'woocommerce_add_to_cart_product_id', $product_id );
					$quantity   = empty( $_POST['quantity'] ) ? 1 : wc_stock_amount( wc_clean( wp_unslash( $_POST['quantity'] ) ) );
					$message    = wc_add_to_cart_message( array( $product_id => $quantity ), true, ! $is_cart );

					if ( ! $is_cart ) {
						ob_start();
						wc_print_notice( $message, 'success' );
						$data['notice'] = ob_get_clean();
					}
				}

				// Refresh the choose your gift content.
				if ( ! $is_cart && $hash && WC_BOGOF_Cart::get_hash() !== $hash ) {
					$shortcode       = new WC_BOGOF_Choose_Gift_Shortcode( $postdata );
					$data['content'] = $shortcode->get_content();
				}
			}
		}
		// phpcs:enable

		$fragments['wc_choose_your_gift_data'] = $data;

		return $fragments;
	}

	/**
	 * Refresh "choose your gift" via AJAX.
	 */
	public static function update_choose_your_gift() {
		$data     = array();
		$postdata = wc_clean( wp_unslash( $_POST ) ); // phpcs:ignore WordPress.Security.NonceVerification
		$hash     = empty( $postdata['hash'] ) ? false : $postdata['hash'];

		// Refresh the choose your gift content.
		if ( WC_BOGOF_Cart::get_hash() !== $hash ) {
			$shortcode       = new WC_BOGOF_Choose_Gift_Shortcode( $postdata );
			$data['content'] = $shortcode->get_content();
		}

		wp_send_json(
			array(
				'wc_choose_your_gift_data' => $data,
			)
		);
	}

	/**
	 * Returns the choose your gift page URL.
	 *
	 * @return string
	 */
	private static function get_link() {
		$page_link = false;
		if ( 'after_cart' === get_option( 'wc_bogof_cyg_display_on', 'after_cart' ) ) {
			$page_id = wc_get_page_id( 'cart' );
			if ( $page_id && is_page( $page_id ) ) {
				$page_link = '#wc-choose-your-gift';
			} else {
				$page_link = wc_get_cart_url() . '#wc-choose-your-gift';
			}
		} else {
			$page_id = get_option( 'wc_bogof_cyg_page_id', 0 );
			if ( $page_id && is_page( $page_id ) ) {
				$page_link = '#wc-choose-your-gift';
			} elseif ( wc_bogof_has_choose_your_gift_shortcode( $page_id ) && 'publish' === get_post_status( $page_id ) ) {
				$page_link = get_permalink( $page_id );
				if ( $page_link ) {
					$page_link = add_query_arg( 'wc_bogo_refer', WC_BOGOF_Cart::get_hash(), $page_link ) . '#wc-choose-your-gift';
				}
			}
		}

		return $page_link;
	}

	/**
	 * Returns the choose a gift notice.
	 *
	 * @param int $qty Quantity at special price.
	 * @return string
	 */
	private static function get_default_choose_gift_message( $qty ) {
		$discounts = array();
		$message   = '';

		foreach ( wc_bogof_cart_rules() as $cart_rule ) {
			if ( 1 > $cart_rule->get_shop_free_quantity() ) {
				continue;
			}

			$discount = $cart_rule->get_rule()->get_discount();
			if ( ! in_array( $discount, $discounts, true ) ) {
				$discounts[] = $discount;
			}
		}

		if ( 1 === count( $discounts ) ) {
			if ( 100 === $discounts[0] ) {
				// translators: 1: free products qty.
				$message = sprintf( _n( 'You can now add %1$s product for free to the cart', 'You can now add %1$s products for free to the cart', $qty, 'wc-buy-one-get-one-free' ), $qty );
			} else {
				// translators: 1: free products qty, 2: percentage discount.
				$message = sprintf( _n( 'You can now add %1$s product with %2$s off', 'You can now add %1$s product with %2$s off', $qty, 'wc-buy-one-get-one-free' ), $qty, $discounts[0] . '%' );
			}
		} else {
			// translators: 1: free products qty.
			$message = sprintf( _n( 'You can now add %1$s products at a sale price', 'You can now add %1$s products at a sale price', $qty, 'wc-buy-one-get-one-free' ), $qty );
		}
		return apply_filters( 'wc_bogof_default_choose_gift_message', $message, $qty, $discounts );
	}

	/**
	 * Add a WooCommerce notice if there are avilable gifts.
	 */
	public static function choose_your_gift_notice() {
		if ( self::$notice_displayed ) {
			return;
		}

		$qty = WC_BOGOF_Cart::get_shop_free_quantity();
		if ( $qty <= 0 ) {
			return;
		}

		$page_link = self::get_link();

		if ( $page_link ) {

			$text = get_option( 'wc_bogof_cyg_notice', false );
			if ( empty( $text ) ) {
				$text = self::get_default_choose_gift_message( $qty );
			} else {
				$text = str_replace( '[qty]', $qty, $text );
			}

			$button_text = get_option( 'wc_bogof_cyg_notice_button_text', false );
			if ( empty( $button_text ) ) {
				$button_text = esc_html__( 'Choose your gift', 'wc-buy-one-get-one-free' );
			}

			$message = sprintf( ' %s <a href="%s" tabindex="1" class="button button-choose-your-gift">%s</a>', esc_html( $text ), esc_url( $page_link ), $button_text );

			echo '<div class="woocommerce-notices-wrapper woocommerce-choose-your-gift-notice-wrapper">';
			wc_print_notice( $message, 'success' );
			echo '</div>';

		} elseif ( current_user_can( 'manage_woocommerce' ) ) {
			// translators: HTML tags.
			wc_print_notice( sprintf( __( 'The "choose your gift" page has not set! Customers will not be able to add to the cart the free product. Go to the %1$ssettings page%2$s and set a %3$spublic page%4$s that contains the [wc_choose_your_gift] shortcode. ', 'wc-buy-one-get-one-free' ), '<a href="' . admin_url( 'admin.php?page=wc-settings&tab=products&section=buy-one-get-one-free' ) . '">', '</a>', '<strong>', '</strong>' ), 'error' );
		}

		self::$notice_displayed = true;
	}

	/**
	 * Displays the choose your gift shortcode after the cart.
	 */
	public static function choose_your_gift_after_cart() {
		if ( 'after_cart' === get_option( 'wc_bogof_cyg_display_on', 'after_cart' ) ) {
			$title = get_option( 'wc_bogof_cyg_title', false );
			$title = empty( $title ) ? __( 'Choose your gift', 'wc-buy-one-get-one-free' ) : $title;
			echo self::choose_your_gift( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				array(
					'title'      => $title,
					'no_results' => false,
				)
			);
		}
	}


	/**
	 * Register/queue frontend scripts.
	 */
	public static function load_scripts() {
		global $post;

		if ( ! did_action( 'before_woocommerce_init' ) ) {
			return;
		}

		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		$deps = array( 'jquery' );
		if ( 'yes' === get_option( 'woocommerce_enable_ajax_add_to_cart' ) ) {
			$deps[] = 'wc-add-to-cart';
		}
		if ( is_cart() ) {
			$deps[] = 'wc-cart';
		}

		wp_register_script( 'wc-bogof-choose-your-gift', plugins_url( 'assets/js/frontend/choose-your-gift' . $suffix . '.js', WC_BOGOF_PLUGIN_FILE ), $deps, WC_Buy_One_Get_One_Free::$version, true );
		wp_localize_script(
			'wc-bogof-choose-your-gift',
			'wc_bogof_choose_your_gift_params',
			array(
				'wc_ajax_url' => WC_AJAX::get_endpoint( 'bogof_update_choose_your_gift' ),
				'cart_url'    => apply_filters( 'woocommerce_add_to_cart_redirect', wc_get_cart_url(), null ),
			)
		);

		if ( self::get_refer() && wp_script_is( 'wc-single-product', 'enqueued' ) ) {
			// Single product.
			wp_register_script( 'wc-bogof-single-product', plugins_url( 'assets/js/frontend/single-product' . $suffix . '.js', WC_BOGOF_PLUGIN_FILE ), $deps, WC_Buy_One_Get_One_Free::$version, true );
			wp_localize_script(
				'wc-bogof-single-product',
				'wc_bogof_single_product_params',
				array(
					'cart_url'             => wc_get_cart_url(),
					'choose_your_gift_url' => self::get_link(),
				)
			);
			wp_enqueue_script( 'wc-bogof-single-product' );
		}
	}

	/**
	 * Sortcode callback. Lists free available products.
	 *
	 * @param array $atts Attributes.
	 * @return string
	 */
	public static function choose_your_gift( $atts ) {
		if ( is_admin() ) {
			return;
		}

		$content = '<div class="choose-your-gift-notice-wrapper"></div>';

		$shortcode = new WC_BOGOF_Choose_Gift_Shortcode( $atts );
		$content  .= $shortcode->get_content();

		wp_enqueue_script( 'wc-bogof-choose-your-gift' );

		return $content;
	}

}
