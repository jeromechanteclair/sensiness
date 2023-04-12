<?php
/**
 * WooCommerce Buy One Get One Free Meta Boxes
 *
 * @package WC_BOGOF
 */

defined( 'ABSPATH' ) || exit;

/**
 * WC_BOGOF_Admin_Meta_Boxes Class
 */
class WC_BOGOF_Admin_Meta_Boxes {

	/**
	 * Is meta boxes saved once?
	 *
	 * @var boolean
	 */
	private static $saved_meta_boxes = false;

	/**
	 * Retruns an array of metaboxes. Format 'id' => 'title'.
	 *
	 * @var array
	 */
	private static function metaboxes() {
		return array(
			'settings' => __( 'Promotion settings', 'wc-buy-one-get-one-free' ),
		);
	}

	/**
	 * Init hooks
	 */
	public static function init() {
		add_action( 'add_meta_boxes', array( __CLASS__, 'add_meta_boxes' ), 30 );
		add_action( 'save_post_shop_bogof_rule', array( __CLASS__, 'save' ), 10, 2 );
		add_action( 'wp_ajax_wc_bogof_validate_save', array( __CLASS__, 'ajax_validate_save' ) );
		add_action( 'post_submitbox_misc_actions', array( __CLASS__, 'post_misc_actions' ) );
	}

	/**
	 * Add WC Meta boxes.
	 */
	public static function add_meta_boxes() {
		foreach ( self::metaboxes() as $id => $title ) {
			add_meta_box( 'wc_bogo_field_group-' . $id, $title, array( __CLASS__, 'output' ), 'shop_bogof_rule', 'normal', 'high' );
			// Add the wc-bogo-postbox CSS class.
			add_filter( 'postbox_classes_shop_bogof_rule_wc_bogo_field_group-' . $id, array( __CLASS__, 'metabox_classes' ) );
		}
	}

	/**
	 * Add the postbox CSS class to the metabox wrapper.
	 *
	 * @param array $classes Array of css clasess.
	 * @return array
	 */
	public static function metabox_classes( $classes ) {
		$classes[] = 'wc-bogo-postbox';
		return $classes;
	}

	/**
	 * Output the metabox.
	 *
	 * @param WP_Post $post WP_Post instance.
	 * @param array   $box Metabox data.
	 */
	public static function output( $post, $box ) {
		$rule     = new WC_BOGOF_Rule( $post->ID );
		$box_id   = str_replace( 'wc_bogo_field_group-', '', $box['id'] );
		$filename = dirname( __FILE__ ) . '/metabox-fields/wc-bogof-metabox-fields-' . $box_id . '.php';
		$fields   = file_exists( $filename ) ? ( include $filename ) : apply_filters( "wc_bogof_metabox_{$box_id}_fields", array(), $rule );

		if ( ! is_array( $fields ) || ! count( $fields ) ) {
			return;
		}

		echo '<div class="wc-bogo-fields -left -loading">';

		self::placeholder_content();

		foreach ( $fields as $field ) {
			self::output_metabox_field( $field );
		}

		do_action( "wc_bogof_after_metabox_{$box_id}_fields", $rule );

		echo '</div>';
	}

	/**
	 * Metabox placeholder
	 *
	 * @see https://codepen.io/yunusekim/pen/XaBoNZ
	 */
	private static function placeholder_content() {
		for ( $i = 1; $i < 10; $i++ ) {
			$field = array(
				'id'   => "placeholder_item_{$i}",
				'type' => 'placeholder',
			);
			self::output_metabox_field( $field );
		}
	}

	/**
	 * Output a BOGO metabox field.
	 *
	 * @param array $field Field data.
	 */
	public static function output_metabox_field( $field ) {
		$field = wp_parse_args(
			$field,
			array(
				'id'          => '',
				'label'       => '',
				'description' => '',
				'type'        => 'text',
				'conditions'  => false,
				'fields'      => array(),
			)
		);

		if ( 'nonce' === $field['type'] ) {
			wp_nonce_field( $field['value'], $field['id'] );
		} else {
			include dirname( __FILE__ ) . '/views/html-metabox-field.php';
		}
	}

	/**
	 * Output the input control.
	 *
	 * @param array $field Field data.
	 */
	private static function output_input( $field ) {
		$field         = wp_parse_args(
			$field,
			array(
				'type'              => 'text',
				'options'           => array(),
				'custom_attributes' => array(),
				'id'                => '',
				'name'              => false,
				'value'             => '',
				'fields'            => array(),
				'message'           => '',
			)
		);
		$field['name'] = empty( $field['name'] ) ? $field['id'] : $field['name'];

		$filename = dirname( __FILE__ ) . '/views/fields/html-field-' . $field['type'] . '.php';
		if ( file_exists( $filename ) ) {
			include $filename;
		} else {
			// Output a default input text.
			$field['custom_attributes']['type']  = $field['type'];
			$field['custom_attributes']['id']    = $field['id'];
			$field['custom_attributes']['name']  = empty( $field['name'] ) ? $field['id'] : $field['name'];
			$field['custom_attributes']['value'] = $field['value'];
			printf( '<input %s />', wc_implode_html_attributes( $field['custom_attributes'] ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			if ( ! empty( $field['message'] ) ) {
				printf( '<p class="description">%s</p>', esc_html( $field['message'] ) );
			}
		}
	}

	/**
	 * Output enhanced select options.
	 *
	 * @param array $options Options in array.
	 * @param array $values Values selected.
	 */
	private static function output_options( $options, $values ) {
		if ( ! is_array( $options ) ) {
			return;
		}

		$values = is_array( $values ) ? $values : array( $values );

		foreach ( $options as $key => $option_value ) {
			if ( is_array( $option_value ) ) {
				echo '<optgroup label="' . esc_attr( $key ) . '">';
				self::output_options( $option_value, $values );
				echo '</optgroup>';
			} else {
				echo '<option value="' . esc_attr( $key ) . '" ' . selected( in_array( $key, $values ), true, false ) . '>' . esc_html( $option_value ) . '</option>'; // phpcs:ignore WordPress.PHP.StrictInArray
			}
		}
	}


	/**
	 * Check if we're saving, save the post data.
	 *
	 * @param int    $post_id Post ID.
	 * @param object $post Post object.
	 */
	public static function save( $post_id, $post ) {
		if (
			self::$saved_meta_boxes
			|| empty( $_POST['woocommerce_meta_nonce'] ) // phpcs:ignore WordPress.Security.NonceVerification
			|| empty( $post_id ) || empty( $post ) // $post_id and $post are required.
			|| ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || is_int( wp_is_post_revision( $post ) ) || is_int( wp_is_post_autosave( $post ) ) // Dont' save meta boxes for revisions or autosaves.
			|| ! current_user_can( 'edit_post', $post_id ) // Check user has permission to edit.
		) {
			return;
		}

		// We need this save event to run once to avoid potential endless loops. This would have been perfect:
		// remove_action( current_filter(), __METHOD__ );
		// But cannot be used due to https://github.com/woocommerce/woocommerce/issues/6485
		// When that is patched in core we can use the above.
		self::$saved_meta_boxes = true;

		$post_id  = absint( $post_id );
		$postdata = self::get_post_data();

		// Check the post being saved == the $post_id to prevent triggering this call for other save_post events.
		if ( empty( $postdata['post_ID'] ) || absint( $postdata['post_ID'] ) !== $post_id ) {
			return;
		}

		self::save_bogof_rule( $post_id, $postdata );
	}

	/**
	 * Returns the $_POST array after applying the sanitize functions.
	 *
	 * @since 3.3.2
	 * @return array
	 */
	private static function get_post_data() {
		if ( empty( $_POST['woocommerce_meta_nonce'] ) || ! check_admin_referer( 'woocommerce_save_data', 'woocommerce_meta_nonce' ) ) {
			return array();
		}

		$postdata = array();

		foreach ( wp_unslash( $_POST ) as $key => $value ) {

			/**
			 * Attributes slugs could contain special chars that the sanitize functions remove.
			 * Sanitize '_applies_to' and '_gift_products' fields in __CLASS__::read_product_filters.
			 *
			 * @see Zendesk ticket #728 (Woo  Request Number #264479)
			 */
			$postdata[ $key ] = in_array( $key, array( '_applies_to', '_gift_products' ), true ) ? $value : wc_clean( $value );
		}

		return $postdata;
	}

	/**
	 * Save the BOGOF rule
	 *
	 * @param int   $post_id Post ID.
	 * @param array $postdata Data of the _POST array sanitized.
	 */
	private static function save_bogof_rule( $post_id, $postdata ) {
		$rule   = new WC_BOGOF_Rule( $post_id );
		$errors = self::set_props( $rule, $postdata );

		if ( is_wp_error( $errors ) ) {
			foreach ( $errors->get_error_codes() as $code ) {
				WC_Admin_Meta_Boxes::add_error( $errors->get_error_message( $code ) );
			}
			// Do no save.
			return;
		}

		/**
		 * Set props before save.
		 */
		do_action( 'wc_bogof_admin_process_rule_object', $rule, $postdata );

		$rule->save();

		/**
		 * Object saved.
		 */
		do_action( 'wc_bogof_admin_rule_saved', $rule->get_id() );
	}

	/**
	 * Set the properties of the object and validate it.
	 *
	 * @param WC_BOGOF_Rule $rule The rule object.
	 * @param array         $postdata Data of the _POST array sanitized.
	 * @return WP_Error
	 */
	private static function set_props( &$rule, $postdata ) {
		// Handle dates.
		$start_date = '';
		$end_date   = '';

		// Force date from to beginning of day.
		if ( isset( $postdata['_start_date'], $postdata['schedule_switch'] ) && 'yes' === $postdata['schedule_switch'] ) {
			$start_date = wc_clean( wp_unslash( $postdata['_start_date'] ) );

			if ( ! empty( $start_date ) ) {
				$start_date = gmdate( 'Y-m-d 00:00:00', strtotime( $start_date ) );
			}
		}

		// Force date to to the end of the day.
		if ( isset( $postdata['_end_date'], $postdata['schedule_switch'] ) && 'yes' === $postdata['schedule_switch'] ) {
			$end_date = wc_clean( wp_unslash( $postdata['_end_date'] ) );

			if ( ! empty( $end_date ) ) {
				$end_date = gmdate( 'Y-m-d 23:59:59', strtotime( $end_date ) );
			}
		}

		// Applies to.
		if ( ! empty( $postdata['_applies_to'] ) ) {
			$postdata['_applies_to'] = self::read_product_filters( $postdata['_applies_to'] );
		}

		// Gift products.
		if ( ! empty( $postdata['_gift_products'] ) ) {
			$postdata['_gift_products'] = self::read_product_filters( $postdata['_gift_products'] );
		}

		$rule->set_props(
			array(
				'enabled'                   => isset( $postdata['_enabled'] ) && 'yes' === $postdata['_enabled'],
				'type'                      => $postdata['_type'],
				'applies_to'                => empty( $postdata['_applies_to'] ) ? array() : $postdata['_applies_to'],
				'min_quantity'              => $postdata['_min_quantity'],
				'action'                    => $postdata['_action'],
				'free_product_id'           => isset( $postdata['_free_product_id'] ) ? $postdata['_free_product_id'] : array(),
				'gift_products'             => empty( $postdata['_gift_products'] ) ? array() : $postdata['_gift_products'],
				'individual'                => isset( $postdata['_individual'] ) && 'yes' === $postdata['_individual'],
				'free_quantity'             => $postdata['_free_quantity'],
				'discount'                  => $postdata['_discount'],
				'cart_limit'                => $postdata['_cart_limit'],
				'usage_limit_per_user'      => $postdata['_usage_limit_per_user'],
				'coupon_ids'                => isset( $postdata['_coupon_ids'], $postdata['coupon_switch'] ) && 'yes' === $postdata['coupon_switch'] ? $postdata['_coupon_ids'] : array(),
				'allowed_user_roles'        => isset( $postdata['_allowed_user_roles'], $postdata['available_for'] ) && 'roles' === $postdata['available_for'] ? $postdata['_allowed_user_roles'] : array(),
				'minimum_amount'            => isset( $postdata['_minimum_amount'] ) ? wc_format_decimal( $postdata['_minimum_amount'] ) : '',
				'start_date'                => $start_date,
				'end_date'                  => $end_date,
				'exclude_other_rules'       => 'yes' === $postdata['_exclude_other_rules'],
				'exclude_coupon_validation' => 'yes' === $postdata['_exclude_coupon_validation'],
			)
		);

		return $rule->validate_props();
	}

	/**
	 * Read the product filters from post data array and return an array of conditions.
	 *
	 * @param array $postdata Filter data array.
	 */
	private static function read_product_filters( $postdata ) {
		if ( empty( $postdata['type'] ) || ! is_array( $postdata['type'] ) ) {
			return array();
		}

		$filters = array();

		foreach ( $postdata['type'] as $index => $condition_id ) {
			$condition = WC_BOGOF_Conditions::get_condition( $condition_id );

			if ( ! $condition ) {
				continue;
			}

			$data = array(
				'type'     => $condition_id,
				'modifier' => '',
				'value'    => '',
			);

			foreach ( array( 'modifier', 'value' ) as $prop ) {
				if ( ! empty( $postdata[ $condition_id ][ $prop ][ $index ] ) ) {
					$data[ $prop ] = $postdata[ $condition_id ][ $prop ][ $index ];
				}
			}

			$data = $condition->sanitize( $data );

			if ( ! $condition->is_empty( $data ) ) {
				$filters[] = $data;
			}
		}

		return array( array_unique( $filters, SORT_REGULAR ) );
	}

	/**
	 * Validate the form via AJAX.
	 */
	public static function ajax_validate_save() {
		$postdata = self::get_post_data();
		if ( empty( $postdata ) ) {
			wp_die( -1 );
		}

		$data = array();
		$rule = new WC_BOGOF_Rule();

		$errors = self::set_props( $rule, $postdata );

		if ( is_wp_error( $errors ) ) {
			foreach ( $errors->get_error_codes() as $code ) {
				$data[] = array(
					'message' => $errors->get_error_message( $code ),
					'field'   => $errors->get_error_data( $code ),
				);
			}
		}
		wp_send_json( $data );
	}

	/**
	 * Displays the usage count of the promotion.
	 *
	 * @param WP_Post $post WP_Post object for the current post.
	 */
	public static function post_misc_actions( $post ) {
		$rule = wc_bogof_get_rule( $post->ID );
		if ( $rule ) {
			echo '<div class="misc-pub-section misc-pub-bogof-usage-count">' . esc_html__( 'Usage count', 'wc-buy-one-get-one-free' ) . ':&nbsp;<strong>' . esc_html( $rule->get_usage_count() ) . '</strong></div>';
		}
	}
}
