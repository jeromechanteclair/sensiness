<?php
if ( ! class_exists( 'WFSPB_Data' ) ) {
	class WFSPB_Data {
		private $params;

		public function __construct() {
			/**
			 * WFSPB_FrontEnd_Data constructor.
			 * Init setting
			 */
			if ( ! $this->params ) {
				$this->get_params();
			}
		}

		/**
		 * Get Option
		 *
		 * @param $field_name
		 *
		 * @return bool|mixed|void
		 */
		public function get_option( $field_name ) {
			if ( ! $this->params ) {
				$this->get_params();
			}
			if ( isset( $this->params[ $field_name ] ) ) {

				return apply_filters( 'woocommerce_free_shipping_bar_get_option_' . $field_name, $this->params[ $field_name ] );
			} else {
				return false;
			}
		}

		// Get woocommerce free shipping zone from db
		public function check_woo_shipping_zone() {
			global $wpdb;
			$method_id   = apply_filters( 'wfspb_method_id', 'free_shipping' );
			$wfspb_query = $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}woocommerce_shipping_zone_methods WHERE method_id = %s AND is_enabled = %d", $method_id, 1 );
			$zone_data   = $wpdb->get_results( $wfspb_query, OBJECT );

			if ( empty( $zone_data ) ) {
				return false;
			} else {
				return true;
			}

		}

		// detect user's IP
		public function detect_ip( $country = null, $state = '', $postcode = '' ) {
			global $wpdb;
			if ( $country ) {
				$criteria   = array();
				$criteria[] = $wpdb->prepare( "( ( locations.location_type = 'country' AND locations.location_code = %s )", $country );

				if ( $state ) {
					$criteria[] = $wpdb->prepare( "OR ( locations.location_type = 'state' AND locations.location_code = %s )", $country . ':' . $state );
				}

				$criteria[] = 'OR ( locations.location_type IS NULL ) )';

				// Postcode range and wildcard matching.
				if ( $postcode ) {
					$postcode_locations = $wpdb->get_results( "SELECT zone_id, location_code FROM {$wpdb->prefix}woocommerce_shipping_zone_locations WHERE location_type = 'postcode';" );

					if ( $postcode_locations ) {
						$zone_ids_with_postcode_rules = array_map( 'absint', wp_list_pluck( $postcode_locations, 'zone_id' ) );
						$matches                      = wc_postcode_location_matcher( $postcode, $postcode_locations, 'zone_id', 'location_code', $country );
						$do_not_match                 = array_unique( array_diff( $zone_ids_with_postcode_rules, array_keys( $matches ) ) );

						if ( ! empty( $do_not_match ) ) {
							$criteria[] = 'AND zones.zone_id NOT IN (' . implode( ',', $do_not_match ) . ')';
						}
					}
				}

				$query = "SELECT zones.zone_id FROM {$wpdb->prefix}woocommerce_shipping_zones as zones
				INNER JOIN {$wpdb->prefix}woocommerce_shipping_zone_locations as locations ON zones.zone_id = locations.zone_id AND locations.location_type != 'postcode'
				WHERE " . implode( ' ', $criteria ) . " ORDER BY zone_order ASC LIMIT 1";

				$matching_zone_id = $wpdb->get_var( $query );

				if ( ! $matching_zone_id ) {
					$continent  = strtoupper( wc_clean( WC()->countries->get_continent_code_for_country( $country ) ) );
					$criteria[] = $wpdb->prepare( "OR ( locations.location_type = 'continent' AND locations.location_code = %s )", $continent );
					$query      = "SELECT zones.zone_id FROM {$wpdb->prefix}woocommerce_shipping_zones as zones
									INNER JOIN {$wpdb->prefix}woocommerce_shipping_zone_locations as locations ON zones.zone_id = locations.zone_id AND locations.location_type != 'postcode'
									WHERE " . implode( ' ', $criteria ) . " ORDER BY zone_order ASC LIMIT 1";

					$matching_zone_id = $wpdb->get_var( $query );
				}

				$shipping_methods = new  WC_Shipping_Zone( $matching_zone_id ? $matching_zone_id : 0 );
				$shipping_methods = $shipping_methods->get_shipping_methods();

				foreach ( $shipping_methods as $i => $shipping_method ) {
					if ( is_numeric( $i ) ) {
						if ( $shipping_method->id == apply_filters( 'wfspb_method_id', 'free_shipping' ) && $shipping_method->enabled == 'yes' ) {
							return array(
								'min_amount'       => $shipping_method->min_amount,
								'ignore_discounts' => $shipping_method->ignore_discounts
							);
						}
					}
				}
			} else {
				$ip = new WC_Geolocation();

				$decode_info = $ip->geolocate_ip();

				if ( isset( $decode_info['country'] ) ) {

					if ( ! wc()->customer ) {
						wc()->customer = new WC_Customer();
					}

					$shipping_country = wc()->customer->get_shipping_country();

					if ( $shipping_country !== $decode_info['country'] ) {
						wc()->customer->set_shipping_country( $decode_info['country'] );
						wc()->customer->save_data();
					}

					$get_country_code = $decode_info['country'];
					$state            = isset( $decode_info['state'] ) ? $decode_info['state'] : '';

					$criteria   = array();
					$criteria[] = $wpdb->prepare( "( ( location_type = 'country' AND location_code = %s )", $get_country_code );
					if ( $state ) {
						$criteria[] = $wpdb->prepare( "OR ( location_type = 'state' AND location_code = %s )", $get_country_code . ':' . $state );
					}

					$criteria[] = 'OR ( location_type IS NULL ) )';

					$query = "SELECT zones.zone_id FROM {$wpdb->prefix}woocommerce_shipping_zones as zones
								INNER JOIN {$wpdb->prefix}woocommerce_shipping_zone_locations as locations ON zones.zone_id = locations.zone_id AND location_type != 'postcode'
								WHERE " . implode( ' ', $criteria ) . " ORDER BY zone_order ASC LIMIT 1";

					$matching_zone_id = $wpdb->get_var( $query );

					if ( ! $matching_zone_id ) {
						$continent  = strtoupper( wc_clean( WC()->countries->get_continent_code_for_country( $get_country_code ) ) );
						$criteria[] = $wpdb->prepare( "OR ( location_type = 'continent' AND location_code = %s )", $continent );
						$query      = "SELECT zones.zone_id FROM {$wpdb->prefix}woocommerce_shipping_zones as zones
								INNER JOIN {$wpdb->prefix}woocommerce_shipping_zone_locations as locations ON zones.zone_id = locations.zone_id AND location_type != 'postcode'
								WHERE " . implode( ' ', $criteria ) . " ORDER BY zone_order ASC LIMIT 1";

						$matching_zone_id = $wpdb->get_var( $query );
					}

					$shipping_methods = new  WC_Shipping_Zone( $matching_zone_id ? $matching_zone_id : 0 );
					$shipping_methods = $shipping_methods->get_shipping_methods();

					foreach ( $shipping_methods as $i => $shipping_method ) {
						if ( is_numeric( $i ) ) {
							if ( $shipping_method->id == apply_filters( 'wfspb_method_id', 'free_shipping' ) && $shipping_method->enabled == 'yes' ) {
								return array(
									'min_amount'       => $shipping_method->min_amount,
									'ignore_discounts' => $shipping_method->ignore_discounts
								);
							}
						}
					}
				}
			}

			return array( 'min_amount' => '', 'ignore_discounts' => '' );

		}

		// get min amount cart with zone_id
		public function get_min_amount( $zone_id ) {
			$q_method_id  = apply_filters( 'wfspb_method_id', 'free_shipping' );
			$amount_index = apply_filters( 'wfspb_min_amount', 'min_amount' );
			global $wpdb;
			$wfspb_query = $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}woocommerce_shipping_zone_methods WHERE method_id = '{$q_method_id}' AND is_enabled = 1 AND zone_id=%d", $zone_id );
			$zone_data   = $wpdb->get_results( $wfspb_query, OBJECT );

			$r = array(
				'min_amount'       => '',
				'ignore_discounts' => ''
			);

			if ( ! empty( $zone_data ) ) {
				$first_zone            = $zone_data[0];
				$instance_id           = $first_zone->instance_id;
				$method_id             = $first_zone->method_id;
				$arr_method            = array( $method_id, $instance_id );
				$implode_method        = implode( "_", $arr_method );
				$free_option           = 'woocommerce_' . $implode_method . '_settings';
				$free_shipping_s       = get_option( $free_option );
				$r['min_amount']       = apply_filters( 'wmc_change_3rd_plugin_price', $free_shipping_s[ $amount_index ] );
				$r['ignore_discounts'] = $free_shipping_s['ignore_discounts'];
//				return array( 'min_amount' => $free_shipping_s[ $amount_index ], 'ignore_discounts' => $free_shipping_s['ignore_discounts'] );
			}

			return $r;
		}

		/**
		 * Get current shipping method of user with current zone
		 * @return WC_Shipping_Zone
		 */
		public function get_shipping_min_amount() {
			/*Get Shipping method*/
			global $wpdb;

			$country          = strtoupper( wc_clean( WC()->customer->country ) );
			$state            = strtoupper( wc_clean( WC()->customer->state ) );
			$continent        = strtoupper( wc_clean( WC()->countries->get_continent_code_for_country( $country ) ) );
			$postcode         = wc_normalize_postcode( WC()->customer->postcode );
			$cache_key        = WC_Cache_Helper::get_cache_prefix( 'shipping_zones' ) . 'wc_shipping_zone_' . md5( sprintf( '%s+%s+%s', $country, $state, $postcode ) );
			$matching_zone_id = wp_cache_get( $cache_key, 'shipping_zones' );

			if ( false === $matching_zone_id ) {

				// Work out criteria for our zone search
				$criteria   = array();
				$criteria[] = $wpdb->prepare( "( ( location_type = 'country' AND location_code = %s )", $country );
				$criteria[] = $wpdb->prepare( "OR ( location_type = 'state' AND location_code = %s )", $country . ':' . $state );
				$criteria[] = $wpdb->prepare( "OR ( location_type = 'continent' AND location_code = %s )", $continent );
				$criteria[] = "OR ( location_type IS NULL ) )";

				// Postcode range and wildcard matching
				$postcode_locations = $wpdb->get_results( "SELECT zone_id, location_code FROM {$wpdb->prefix}woocommerce_shipping_zone_locations WHERE location_type = 'postcode';" );

				if ( $postcode_locations ) {
					$zone_ids_with_postcode_rules = array_map( 'absint', wp_list_pluck( $postcode_locations, 'zone_id' ) );
					$matches                      = wc_postcode_location_matcher( $postcode, $postcode_locations, 'zone_id', 'location_code', $country );
					$do_not_match                 = array_unique( array_diff( $zone_ids_with_postcode_rules, array_keys( $matches ) ) );

					if ( ! empty( $do_not_match ) ) {
						$criteria[] = "AND zones.zone_id NOT IN (" . implode( ',', $do_not_match ) . ")";
					}
				}

				// Get matching zones
				$query = "SELECT zones.zone_id FROM {$wpdb->prefix}woocommerce_shipping_zones as zones
				INNER JOIN {$wpdb->prefix}woocommerce_shipping_zone_locations as locations ON zones.zone_id = locations.zone_id AND location_type != 'postcode'
				WHERE " . implode( ' ', $criteria ) . " ORDER BY zone_order ASC LIMIT 1";

				$matching_zone_id = $wpdb->get_var( $query );
			}

			$shipping_methods = new  WC_Shipping_Zone( $matching_zone_id ? $matching_zone_id : 0 );
			$shipping_methods = $shipping_methods->get_shipping_methods();
			foreach ( $shipping_methods as $i => $shipping_method ) {
				if ( is_numeric( $i ) ) {
					if ( $shipping_method->id == apply_filters( 'wfspb_method_id', 'free_shipping' ) ) {
						return array(
							'min_amount'       => $shipping_method->min_amount,
							'ignore_discounts' => $shipping_method->ignore_discounts
						);
					} else {
						continue;
					}
				}
			}

			return false;
		}

		public function get_free_shipping_min_amount() {
			$order_min_amount = $ignore_discounts = '';
			$detect_ip        = $this->get_option( 'detect-ip' );
			$default_zone     = $this->get_option( 'default-zone' );
			/*Compatible with SG Optimize Front-end*/
			if ( ! isset( WC()->session ) ) {
				return array( 'min_amount' => 0, 'ignore_discounts' => 0 );
			}
			$customer = WC()->session->get( 'customer' );
			$country  = isset( $customer['shipping_country'] ) ? $customer['shipping_country'] : '';
			$state    = isset( $customer['shipping_state'] ) ? $customer['shipping_state'] : '';
			$postcode = isset( $customer['shipping_postcode'] ) ? $customer['shipping_postcode'] : '';

			if ( $country ) {
				$detect_result    = $this->detect_ip( $country, $state, $postcode );
				$order_min_amount = $detect_result['min_amount'];
				$ignore_discounts = $detect_result['ignore_discounts'];
				if ( ! $order_min_amount && $default_zone && ! $detect_ip ) {
					$detect_result    = $this->get_min_amount( $default_zone );
					$order_min_amount = $detect_result['min_amount'];
					$ignore_discounts = $detect_result['ignore_discounts'];
					$order_min_amount = $this->toInt( $order_min_amount );
				}
			} elseif ( $detect_ip ) {
				$detect_result    = $this->detect_ip();
				$order_min_amount = $detect_result['min_amount'];
				$ignore_discounts = $detect_result['ignore_discounts'];
			} elseif ( $default_zone ) {
				$detect_result    = $this->get_min_amount( $default_zone );
				$order_min_amount = $detect_result['min_amount'];
				$ignore_discounts = $detect_result['ignore_discounts'];
				$order_min_amount = $this->toInt( $order_min_amount );
			} else {
				$detect_result    = $this->get_shipping_min_amount();
				$order_min_amount = $detect_result['min_amount'];
				$ignore_discounts = $detect_result['ignore_discounts'];
			}

			return array( 'min_amount' => $order_min_amount, 'ignore_discounts' => $ignore_discounts );
		}

		public function get_total( $ignore_discounts ) {
			if ( ! isset( WC()->cart ) ) {
				return 0;
			}
			$total = WC()->cart->get_displayed_subtotal();

			/*Compatible with YITH Together*/
			if ( class_exists( 'YITH_WFBT_Frontend' ) ) {
				$totals_var = WC()->cart->get_totals();
				$total      = $totals_var['total'] - $totals_var['shipping_total'] - $totals_var['shipping_tax'];
			}

			if ( WC()->cart->display_prices_including_tax() ) {
				$total = $total - WC()->cart->get_discount_tax();
			}

			if ( 'no' === $ignore_discounts ) {
				$total = $total - WC()->cart->get_discount_total();
			}
//			$virtual_total = 0;
//			$cart_contents = wc()->cart->get_cart_contents();
//			if ( ! empty( $cart_contents ) && is_array( $cart_contents ) ) {
//				foreach ( $cart_contents as $item ) {
//					$product = $item['data'];
//					if ( $product->is_virtual() ) {
//						$virtual_total += $item['line_subtotal'] + $item['line_subtotal_tax'];
//					}
//				}
//			}
//			$total -= $virtual_total;

			$exclude_shipping = $this->get_option( 'exclude-shipping-class' );

			if ( ! empty( $exclude_shipping ) ) {
				if ( wc()->cart->get_cart_contents_count() ) {
					$total_class = 0;
					foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
						if ( in_array( $cart_item['data']->get_shipping_class_id(), $exclude_shipping ) ) {
							$total_class += $cart_item['data']->get_price() * $cart_item['quantity'];
						}
					}
					$total -= $total_class;
				}
			}

			return round( $total, wc_get_price_decimals() );
		}

		public function get_message( $arg ) {
			$old_arg  = str_replace( '_', '-', $arg );
			$messages = $this->get_option( $old_arg );
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

			return $message ? $message : $this->get_option( $arg . '_' . $lang );
		}

		public function get_full_message( $order_min_amount, $total, $message = [] ) {
			if ( $order_min_amount === '' ) {
				$message = '';
			} else {

				$message_purchased = ! empty( $message['purchased_message'] ) ? $message['purchased_message'] : $this->get_message( 'message_purchased' );
				$announce_system   = ! empty( $message['announce_message'] ) ? $message['announce_message'] : $this->get_message( 'announce_system' );
				$message_success   = ! empty( $message['success_message'] ) ? $message['success_message'] : $this->get_message( 'message_success' );
				$message_error     = ! empty( $message['error_message'] ) ? $message['error_message'] : $this->get_message( 'message_error' );

				$announce_min_amount = '{min_amount}';

				$key = array(
					'{total_amounts}',
					'{cart_amount}',
					'{min_amount}',
					'{missing_amount}'
				);

				$key_msgerror = array(
					'{missing_amount}',
					'{shopping}'
				);

				$shopping_link_text = apply_filters( 'wfspb_filter_shopping_link_text', 'Shopping' );
				$checkout_link_text = apply_filters( 'wfspb_filter_checkout_link_text', 'Checkout' );
				$cart_link_text     = apply_filters( 'wfspb_filter_cart_link_text', 'Cart' );

				$shopping = '<a class="" href="' . get_permalink( get_option( 'woocommerce_shop_page_id' ) ) . '">' . esc_html__( $shopping_link_text, 'woocommerce-free-shipping-bar' ) . '</a>';
				$checkout = '<a class="vi-wcaio-sidebar-cart-bt-nav-checkout" href="' . wc_get_checkout_url() . '" title="' . esc_html__( 'Checkout', 'woocommerce-free-shipping-bar' ) . '">' . esc_html__( $checkout_link_text, 'woocommerce-free-shipping-bar' ) . '</a>';
				$cart_url = '<a href="' . wc_get_cart_url() . '" title="' . esc_html__( 'Cart', 'woocommerce-free-shipping-bar' ) . '">' . esc_html__( $cart_link_text, 'woocommerce-free-shipping-bar' ) . '</a>';

				$message_ss = str_replace( array( '{checkout_page}', '{cart_page}', '{shopping}' ), array(
					$checkout,
					$cart_url,
					$shopping
				), $message_success );

				$cart_amount = WC()->cart->cart_contents_count;

				if ( $order_min_amount === 0 ) {
					$message = $this->get_message( 'message_full_free_ship' );
				} else {
					$order_mins = '<b id="wfspb_min_order_amount">' . wc_price( $order_min_amount ) . '</b>';

					if ( is_checkout() ) {
						if ( $total < $order_min_amount ) {
							$missing_amount   = $order_min_amount - $total;
							$missing_amount_r = $missing_amount;
							if ( ! wc()->cart->display_prices_including_tax() ) {

								if ( 'incl' !== get_option( 'woocommerce_tax_display_shop' ) ) {

									if ( ! wc_prices_include_tax() ) {
										$missing_amount_r = $this->real_amount( $missing_amount );
									}

								}
							}

							$msgerror_replaced = array( wc_price( $missing_amount_r ), $shopping );
							$message           = str_replace( $key_msgerror, $msgerror_replaced, $message_error );
						} else {
							$message = $message_ss;
						}
					} else {
						if ( $total < $order_min_amount ) {

							$missing_amount = $order_min_amount - $total;

							$cart_amount1 = '<b id="current-quantity">' . $cart_amount . '</b>';

							$total_amount = '<b id="wfspb-current-amout">' . wc_price( $total ) . '</b>';

							if ( is_cart() ) {
								if ( wc()->cart->display_prices_including_tax() ) {
									$missing_amount1 = '<b id="wfspb_missing_amount">' . wc_price( $missing_amount ) . '</b>';
								} else {
									if ( 'incl' === get_option( 'woocommerce_tax_display_shop' ) ) {
										$missing_amount1 = '<b id="wfspb_missing_amount">' . wc_price( $missing_amount ) . '</b>';

									} else {
										if ( wc_prices_include_tax() ) {
											$missing_amount1 = '<b id="wfspb_missing_amount">' . wc_price( $missing_amount ) . '</b>';
										} else {
											$missing_amount_r = $this->real_amount( $missing_amount );
											$missing_amount1  = '<b id="wfspb_missing_amount">' . wc_price( $missing_amount_r ) . '</b>';
										}

									}
								}
							} else {
								if ( 'incl' === get_option( 'woocommerce_tax_display_shop' ) ) {
									if ( wc()->cart->display_prices_including_tax() ) {
										$missing_amount1 = '<b id="wfspb_missing_amount">' . wc_price( $missing_amount ) . '</b>';
									} else {
										$missing_amount_r = $this->get_price_including_tax( $missing_amount );
										$missing_amount1  = '<b id="wfspb_missing_amount">' . wc_price( $missing_amount_r ) . esc_html__( '(incl. tax)', 'woocommerce-free-shipping-bar' ) . '</b>';
									}

								} else {
									if ( wc_prices_include_tax() ) {
										$missing_amount1 = '<b id="wfspb_missing_amount">' . wc_price( $missing_amount ) . '</b>';
									} else {
										$missing_amount_r = $this->real_amount( $missing_amount );
										$missing_amount1  = '<b id="wfspb_missing_amount">' . wc_price( $missing_amount_r ) . '</b>';
									}

								}
							}

							$replaced = array( $total_amount, $cart_amount1, $order_mins, $missing_amount1 );
							$message  = str_replace( $key, $replaced, $message_purchased );
						} else {
							$message = $message_ss;
						}
					}

					if ( $total == 0 ) {
						$message = str_replace( $announce_min_amount, $order_mins, $announce_system );
					}
				}
			}

			$message = wp_kses_post( do_shortcode( wp_unslash( $message ) ) );

			return '<div id="wfspb-main-content">' . $message . '</div>';
		}

		//convert price to integer
		public function toInt( $str ) {
			return preg_replace( "/([^0-9\\.])/i", "", $str );
		}

		public function get_price_including_tax( $line_price, $round_mode = PHP_ROUND_HALF_UP ) {
			$return_price = $line_price;
			if ( ! wc_prices_include_tax() ) {
				$tax_rates = WC_Tax::get_rates( '' );
				$taxes     = WC_Tax::calc_tax( $line_price, $tax_rates, false );

				if ( 'yes' === get_option( 'woocommerce_tax_round_at_subtotal' ) ) {
					$taxes_total = array_sum( $taxes );
				} else {
					$taxes_total = array_sum( array_map( 'wc_round_tax_total', $taxes ) );
				}

				$return_price = round( $line_price + $taxes_total, wc_get_price_decimals(), $round_mode );
			} else {
				$tax_rates      = WC_Tax::get_rates( '' );
				$base_tax_rates = WC_Tax::get_base_tax_rates( '' );

				/**
				 * If the customer is excempt from VAT, remove the taxes here.
				 * Either remove the base or the user taxes depending on woocommerce_adjust_non_base_location_prices setting.
				 */
				if ( ! empty( WC()->customer ) && WC()->customer->get_is_vat_exempt() ) { // @codingStandardsIgnoreLine.
					$remove_taxes = apply_filters( 'woocommerce_adjust_non_base_location_prices', true ) ? WC_Tax::calc_tax( $line_price, $base_tax_rates, true ) : WC_Tax::calc_tax( $line_price, $tax_rates, true );

					if ( 'yes' === get_option( 'woocommerce_tax_round_at_subtotal' ) ) {
						$remove_taxes_total = array_sum( $remove_taxes );
					} else {
						$remove_taxes_total = array_sum( array_map( 'wc_round_tax_total', $remove_taxes ) );
					}

					$return_price = round( $line_price - $remove_taxes_total, wc_get_price_decimals(), $round_mode );

					/**
					 * The woocommerce_adjust_non_base_location_prices filter can stop base taxes being taken off when dealing with out of base locations.
					 * e.g. If a product costs 10 including tax, all users will pay 10 regardless of location and taxes.
					 * This feature is experimental @since 2.4.7 and may change in the future. Use at your risk.
					 */
				} elseif ( $tax_rates !== $base_tax_rates && apply_filters( 'woocommerce_adjust_non_base_location_prices', true ) ) {
					$base_taxes   = WC_Tax::calc_tax( $line_price, $base_tax_rates, true );
					$modded_taxes = WC_Tax::calc_tax( $line_price - array_sum( $base_taxes ), $tax_rates, false );

					if ( 'yes' === get_option( 'woocommerce_tax_round_at_subtotal' ) ) {
						$base_taxes_total   = array_sum( $base_taxes );
						$modded_taxes_total = array_sum( $modded_taxes );
					} else {
						$base_taxes_total   = array_sum( array_map( 'wc_round_tax_total', $base_taxes ) );
						$modded_taxes_total = array_sum( array_map( 'wc_round_tax_total', $modded_taxes ) );
					}

					$return_price = round( $line_price - $base_taxes_total + $modded_taxes_total, wc_get_price_decimals(), $round_mode );
				}
			}

			return $return_price;
		}

		public function real_amount( $price, $round_mode = PHP_ROUND_HALF_UP ) {
			return $price;
//			$applied_coupons = WC()->cart->get_applied_coupons();
//			if ( is_array( $applied_coupons ) && count( $applied_coupons ) ) {
//				foreach ( $applied_coupons as $coupon_code ) {
//					$coupon = new WC_Coupon( $coupon_code );
//					if ( $coupon ) {
//						switch ( $coupon->get_discount_type() ) {
//							case 'percent':
//								$coupon_amount = $coupon->get_amount();
//								if ( $coupon_amount ) {
//									$price = $price * ( 1 + $coupon_amount / 100 );
//									$price = round( $price, wc_get_price_decimals(), $round_mode );
//								}
//								break;
//							case 'fixed_cart':
//								break;
//							default:
//						}
//					}
//				}
//			}
//			if ( wc()->cart->display_prices_including_tax() ) {
//				$line_price     = $price;
//				$tax_rates      = WC_Tax::get_rates( '' );
//				$base_tax_rates = WC_Tax::get_base_tax_rates( '' );
//
//				/**
//				 * If the customer is excempt from VAT, remove the taxes here.
//				 * Either remove the base or the user taxes depending on woocommerce_adjust_non_base_location_prices setting.
//				 */
//				if ( ! empty( WC()->customer ) ) { // @codingStandardsIgnoreLine.
//					$remove_taxes = apply_filters( 'woocommerce_adjust_non_base_location_prices', true ) ? WC_Tax::calc_tax( $line_price, $base_tax_rates, true ) : WC_Tax::calc_tax( $line_price, $tax_rates, true );
//
//					if ( 'yes' === get_option( 'woocommerce_tax_round_at_subtotal' ) ) {
//						$remove_taxes_total = array_sum( $remove_taxes );
//					} else {
//						$remove_taxes_total = array_sum( array_map( 'wc_round_tax_total', $remove_taxes ) );
//					}
//
//					$price = $line_price - $remove_taxes_total;
//
//					/**
//					 * The woocommerce_adjust_non_base_location_prices filter can stop base taxes being taken off when dealing with out of base locations.
//					 * e.g. If a product costs 10 including tax, all users will pay 10 regardless of location and taxes.
//					 * This feature is experimental @since 2.4.7 and may change in the future. Use at your risk.
//					 */
//				}
//			}
//
//			return round( $price, wc_get_price_decimals(), $round_mode );
		}

		public function get_params() {
			$woocommerce_free_shipping_settings = get_option( 'wfspb-param', array() );
			$this->params                       = $woocommerce_free_shipping_settings;
			$args                               = array(
				/*General*/
				'enable'                 => 0,
				'default-zone'           => '',
				'detect-ip'              => 0,
				'detect-mobile'          => 0,
				'exclude-shipping-class' => [],

				/*Design*/
				'use_shortcode'          => 0,
				'bg-color'               => 'rgb(32, 98, 150)',
				'text-color'             => '#FFFFFF',
				'link-color'             => '77B508',
				'font'                   => 'PT Sans',
				'font-size'              => 16,
				'text-align'             => 'center',
				'enable-progress'        => 0,
				'bg-color-progress'      => '#C9CFD4',
				'bg-current-progress'    => '#0D47A1',
				'font-size-progress'     => '11',
				'progress_effect'        => 0,
				'position'               => 0,
				'show_at_order_bottom'   => 0,
				'position_checkout'      => 0,
				'position_cart'          => 0,
				'show_single_product'    => 0,
				'custom_css'             => '',
				'gift_icon'              => 0,
				'custom_icon'            => '',

				/*Message*/

				'announce_system_default'        => 'Free shipping for billing over {min_amount}',
				'message_purchased_default'      => 'You have purchased {total_amounts} of {min_amount}',
				'message_success_default'        => 'Congratulation! You have got free shipping. Go to {checkout_page}',
				'message_error_default'          => 'You are missing {missing_amount} to get Free Shipping. Continue {shopping}',
				'message_full_free_ship_default' => 'Free shipping for buying any products',

				/*Effect*/
				'initial-delay'                  => 0,
				'close-message'                  => 0,
				'time-to-disappear'              => 0,
				'set-time-disappear'             => 0,
				'show-giftbox'                   => 0,
				/*Assign*/
				'agn-homepage'                   => 0,
				'agn-cart'                       => 0,
				'agn-shop'                       => 0,
				'agn-checkout'                   => 0,
				'agn-single-product'             => 0,
				'agn-product-category'           => 0,
				'agn-product-tag'                => 0,
				'conditional-tags'               => '',
				'bar_in_mini_cart'               => '',
				/*Update*/
				'key'                            => 0,
				'cache_compa'                    => 0,
				'header_selector'                => ''
			);

			$this->params = apply_filters( 'woocommerce_free_shipping_bar_settings_args', wp_parse_args( $this->params, $args ) );

			return $this->params;
		}

		public function deprecated( $arg ) {
			$needles = array(
				'announce_system',
				'message_purchased',
				'message_success',
				'message_error',
				'message_full_free_ship'
			);
			foreach ( $needles as $needle ) {
				if ( strpos( $arg, $needle ) !== false ) {
					$len  = strlen( $needle );
					$code = substr( $arg, $len + 1 );

					return array( 'field' => str_replace( '_', '-', $needle ), 'lang' => $code );
				}
			}
		}

		public function enqueue_script_frontend() {

			$params = $this;

			if ( defined( WP_CACHE ) && WP_CACHE ) {
				wp_enqueue_script( 'woocommerce-free-shipping-bar-cache' );
			}

			wp_enqueue_script( 'woocommerce-free-shipping-bar' );
			wp_enqueue_style( 'woocommerce-free-shipping-bar' );

			$bg_color        = $params->get_option( 'bg-color' );
			$text_color      = $params->get_option( 'text-color' );
			$link_color      = $params->get_option( 'link-color' );
			$text_align      = $params->get_option( 'text-align' );
			$font            = $params->get_option( 'font' );
			$font_size       = $params->get_option( 'font-size' );
			$enable_progress = $params->get_option( 'enable-progress' );
			$style           = $params->get_option( 'style' );

			if ( $font == 'Default' ) {
				$font = '';
			}

			if ( ! empty( $font ) ) {
				$font = str_replace( '+', ' ', $font );
				wp_enqueue_style( 'google-font-' . strtolower( $font ), '//fonts.googleapis.com/css?family=' . $font . ':400,500,600,700' );
			}

//			switch ( $style ) {
//				case 2:
//					wp_enqueue_style( 'woocommerce-free-shipping-bar-style2' );
//					$css_style2 = "
//						#wfspb-top-bar #wfspb-progress::before, #wfspb-top-bar #wfspb-progress::after{
//							border-bottom-color: {$bg_color} !important;
//						}
//					";
//					wp_add_inline_style( 'woocommerce-free-shipping-bar-style2', $css_style2 );
//					break;
//				case 3:
//					wp_enqueue_style( 'woocommerce-free-shipping-bar-style3' );
//					break;
//				default :
//					wp_enqueue_style( 'woocommerce-free-shipping-bar-style' );
//					break;
//			}


			$custom_css = "
				#wfspb-top-bar .wfspb-lining-layer{
					background-color: {$bg_color} !important;
				}
				#wfspb-progress.wfsb-style-3{
					background-color: {$bg_color} !important;
				}
				#wfspb-top-bar{
					color: {$text_color} !important;
				} 
				#wfspb-top-bar{
					text-align: {$text_align} !important;
				}
				#wfspb-top-bar #wfspb-main-content{
					padding: 0 " . ( $font_size * 2 ) . "px;
					font-size: {$font_size}px !important;
					text-align: {$text_align} !important;
					color: {$text_color} !important;
				}
				#wfspb-top-bar #wfspb-main-content > a, #wfspb-top-bar #wfspb-main-content b span{
					color: {$link_color} !important;
				}
				div#wfspb-close{
				font-size: {$font_size}px !important;
				line-height: {$font_size}px !important;
				}
				";
			if ( $font ) {
				$custom_css .= "
				#wfspb-top-bar{
					font-family: {$font} !important;
				}";
			}

			if ( $enable_progress ) {
				$bg_progress         = $params->get_option( 'bg-color-progress' );
				$bg_current_progress = $params->get_option( 'bg-current-progress' );
				$progress_text_color = $params->get_option( 'progress-text-color' );
				$fontsize_progress   = $params->get_option( 'font-size-progress' );

				$free_shipping    = $this->get_free_shipping_min_amount();
				$order_min_amount = $free_shipping['min_amount'];
				$ignore_discounts = $free_shipping['ignore_discounts'];

//				if ( ! $order_min_amount ) {
//					return;
//				}

				$total = $this->get_total( $ignore_discounts );

				if ( $total >= $order_min_amount ) {
					$custom_css .= "
							#wfspb-current-progress{ width: 100%; }
						";
				} else {
					if ( $order_min_amount == 0 ) {
						$amount_total_pr = $total * 100;
					} else {
						$amount_total_pr = round( ( $total * 100 ) / $order_min_amount, 2 );
					}
					$custom_css .= "
						#wfspb-current-progress{
							width: {$amount_total_pr}%;
						}";
				}
				$custom_css .= "
					#wfspb-progress .wfspb-progress-background,.woocommerce-free-shipping-bar-order .woocommerce-free-shipping-bar-order-bar{
						background-color: {$bg_progress} !important;
					}
					#wfspb-current-progress,.woocommerce-free-shipping-bar-order .woocommerce-free-shipping-bar-order-bar .woocommerce-free-shipping-bar-order-bar-inner{
						background-color: {$bg_current_progress} !important;
					}
					#wfspb-top-bar > #wfspb-progress.wfsb-effect-2{
					outline-color:{$bg_current_progress} !important;
					}
					#wfspb-label{
						color: {$progress_text_color} !important;
						font-size: {$fontsize_progress}px !important;
					}
				";

				$custom_css .= $style == 2 ? "#wfspb-top-bar #wfspb-progress::before, #wfspb-top-bar #wfspb-progress::after{border-bottom-color: {$bg_color} !important;}" : '';
			}


			$css = stripslashes_from_strings_only( $params->get_option( 'custom_css' ) );

			wp_add_inline_style( 'woocommerce-free-shipping-bar', $custom_css . $css );
			// Localize the script with new data
			$translation_array = array(
				'ajax_url'          => admin_url( 'admin-ajax.php' ),
				'lang_code'         => function_exists( 'wpml_get_current_language' ) ? wpml_get_current_language() : 'default',
				'time_to_disappear' => boolval( $params->get_option( 'time-to-disappear' ) ),
				'displayTime'       => $params->get_option( 'set-time-disappear' ),
				'isCheckout'        => is_checkout(),
				'hash'              => 'wfspb_' . md5( get_current_blog_id() . '_' . get_site_url( get_current_blog_id(), '/' ) . get_template() ),
				'cacheCompa'        => $params->get_option( 'cache_compa' ),
				'headerSelector'    => $params->get_option( 'header_selector' ),
				'initialDelay'      => $params->get_option( 'initial-delay' )
			);
			wp_localize_script( 'woocommerce-free-shipping-bar', '_wfsb_params', $translation_array );
		}

	}
}
