<?php
/**
 * Class WC_BOGOF_Rule_Data_Store_CPT file.
 *
 * @package WC_BOGOF
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WC BOGOF Rule Data Store: Custom Post Type.
 */
class WC_BOGOF_Rule_Data_Store_CPT extends WC_Data_Store_WP implements WC_Object_Data_Store_Interface {

	/**
	 * Data stored in meta keys, but not considered "meta" for a rule.
	 *
	 * @var array
	 */
	protected $internal_meta_keys = array(
		'_enabled',
		'_type',
		'_applies_to',
		'_action',
		'_free_product_id',
		'_gift_products',
		'_individual',
		'_min_quantity',
		'_free_quantity',
		'_discount',
		'_cart_limit',
		'_allowed_user_roles',
		'_usage_limit_per_user',
		'_coupon_ids',
		'_minimum_amount',
		'_exclude_coupon_validation',
		'_start_date',
		'_end_date',
		'_exclude_other_rules',
		'_edit_lock',
		'_edit_last',
		'_wp_old_date',
		'_default_order',
	);

	/**
	 * Deprecated meta keys.
	 *
	 * @var array
	 */
	protected $deprecated_meta_keys = array(
		'_buy_product_ids',
		'_buy_category_ids',
		'_exclude_product_ids',
		'_buy_objects_ids',
		'_free_product_ids',
		'_free_category_ids',
	);

	/**
	 * Method to create a new BOGOF rule in the database.
	 *
	 * @param WC_BOGOF_rule $rule BOGOF rule object.
	 */
	public function create( &$rule ) {
		$rule->set_date_created( current_time( 'timestamp', true ) );

		$rule_id = wp_insert_post(
			array(
				'post_type'     => 'shop_bogof_rule',
				'post_status'   => $rule->get_enabled() ? 'publish' : 'wc-bogof-disabled',
				'post_author'   => get_current_user_id(),
				'post_title'    => $rule->get_title( 'edit' ),
				'post_content'  => '',
				'post_date'     => gmdate( 'Y-m-d H:i:s', $rule->get_date_created()->getOffsetTimestamp() ),
				'post_date_gmt' => gmdate( 'Y-m-d H:i:s', $rule->get_date_created()->getTimestamp() ),
			),
			true
		);

		if ( $rule_id ) {
			$rule->set_id( $rule_id );
			$this->update_post_meta( $rule );
			$rule->save_meta_data();
			$rule->apply_changes();
		}
	}

	/**
	 * Updates a rule in the database.
	 *
	 * @param WC_BOGOF_rule $rule BOGOF rule object.
	 */
	public function update( &$rule ) {
		if ( ! $rule->get_date_created() ) {
			$rule->set_date_created( current_time( 'timestamp', true ) );
			$rule->set_date_modified( current_time( 'timestamp', true ) );
		}

		$rule->save_meta_data();

		$changes     = $rule->get_changes();
		$post_status = $rule->get_enabled() ? 'publish' : 'wc-bogof-disabled';

		if ( array_intersect( array( 'title', 'date_created', 'date_modified', 'enabled' ), array_keys( $changes ) ) || $post_status !== $rule->get_post_status() ) {

			$post_data = array(
				'post_status'       => $post_status,
				'post_title'        => $rule->get_title( 'edit' ),
				'post_date'         => gmdate( 'Y-m-d H:i:s', $rule->get_date_created( 'edit' )->getOffsetTimestamp() ),
				'post_date_gmt'     => gmdate( 'Y-m-d H:i:s', $rule->get_date_created( 'edit' )->getTimestamp() ),
				'post_modified'     => isset( $changes['date_modified'] ) ? gmdate( 'Y-m-d H:i:s', $rule->get_date_modified( 'edit' )->getOffsetTimestamp() ) : current_time( 'mysql' ),
				'post_modified_gmt' => isset( $changes['date_modified'] ) ? gmdate( 'Y-m-d H:i:s', $rule->get_date_modified( 'edit' )->getTimestamp() ) : current_time( 'mysql', 1 ),
			);

			/**
			 * When updating this object, to prevent infinite loops, use $wpdb
			 * to update data, since wp_update_post spawns more calls to the
			 * save_post action.
			 *
			 * This ensures hooks are fired by either WP itself (admin screen save),
			 * or an update purely from CRUD.
			 */
			if ( doing_action( 'save_post' ) ) {
				$GLOBALS['wpdb']->update( $GLOBALS['wpdb']->posts, $post_data, array( 'ID' => $rule->get_id() ) );
				clean_post_cache( $rule->get_id() );
			} else {
				wp_update_post( array_merge( array( 'ID' => $rule->get_id() ), $post_data ) );
			}
			$rule->read_meta_data( true ); // Refresh internal meta data, in case things were hooked into `save_post` or another WP hook.
		}
		$this->update_post_meta( $rule );
		$rule->apply_changes();
	}

	/**
	 * Deletes a rule from the database.
	 *
	 * @param WC_BOGOF_rule $rule BOGOF rule object.
	 * @param array         $args Array of args to pass to the delete method.
	 */
	public function delete( &$rule, $args = array() ) {
		$args = wp_parse_args(
			$args,
			array(
				'force_delete' => false,
			)
		);

		$id = $rule->get_id();

		if ( ! $id ) {
			return;
		}

		if ( $args['force_delete'] ) {
			wp_delete_post( $id );
			$rule->set_id( 0 );
		} else {
			wp_trash_post( $id );
		}
	}

	/**
	 * Method to read a rule.
	 *
	 * @param WC_BOGOF_rule $rule BOGOF rule object.
	 *
	 * @throws Exception If invalid rule.
	 */
	public function read( &$rule ) {
		$rule->set_defaults();
		$post_object = get_post( $rule->get_id() );

		if ( ! $rule->get_id() || ! $post_object || 'shop_bogof_rule' !== $post_object->post_type ) {
			throw new Exception( __( 'Invalid BOGO rule.', 'wc-buy-one-get-one-free' ) );
		}
		$rule->set_props(
			array(
				'post_status'   => $post_object->post_status,
				'title'         => $post_object->post_title,
				'date_created'  => 0 < $post_object->post_date_gmt ? wc_string_to_timestamp( $post_object->post_date_gmt ) : null,
				'date_modified' => 0 < $post_object->post_modified_gmt ? wc_string_to_timestamp( $post_object->post_modified_gmt ) : null,
			)
		);
		$this->read_properties( $rule );
		$rule->read_meta_data();
		$rule->set_object_read( true );
	}

	/**
	 * Reads rule properties from meta data
	 *
	 * @param WC_BOGOF_rule $rule BOGOF rule object.
	 */
	protected function read_properties( &$rule ) {
		$post_meta_values = get_post_meta( $rule->get_id() );
		$set_props        = array();
		$internal_meta    = array_merge(
			$this->deprecated_meta_keys,
			array( '_title', '_date_created', '_date_modified', '_default_order', '_applies_to', '_gift_products', '_edit_lock', '_edit_last', '_wp_old_date' )
		);

		foreach ( $post_meta_values as $meta_key => $meta_value ) {
			if ( in_array( $meta_key, $internal_meta, true ) ) {
				continue;
			}
			$prop  = substr( $meta_key, 1 );
			$value = isset( $meta_value[0] ) ? $meta_value[0] : null;

			$set_props[ $prop ] = maybe_unserialize( $value ); // get_post_meta only unserializes single values.
		}
		$this->read_legacy_props( $set_props, $post_meta_values );

		$rule->set_props( $set_props );
	}

	/**
	 * Read legacy gift action.
	 *
	 * @param array $set_props Array of properties.
	 * @param array $post_meta_values Post meta value array.
	 */
	protected function read_legacy_props( &$set_props, $post_meta_values ) {
		$set_props['applies_to']    = $this->read_legacy_applies_to( $post_meta_values );
		$set_props['gift_products'] = $this->read_legacy_gift_products_prop( $post_meta_values );
	}

	/**
	 * Reads legacy applies to property.
	 *
	 * @param array $post_meta_values Post meta value array.
	 */
	protected function read_legacy_applies_to( $post_meta_values ) {
		$applies_to = ! isset( $post_meta_values['_applies_to'][0] ) ? array() : maybe_unserialize( $post_meta_values['_applies_to'][0] );

		if ( ! is_array( $applies_to ) && is_string( $applies_to ) ) {
			// Legacy.
			$conditions = array();

			$buy_product_ids     = isset( $post_meta_values['_buy_product_ids'][0] ) ? maybe_unserialize( $post_meta_values['_buy_product_ids'][0] ) : array();
			$buy_category_ids    = isset( $post_meta_values['_buy_category_ids'][0] ) ? maybe_unserialize( $post_meta_values['_buy_category_ids'][0] ) : array();
			$exclude_product_ids = isset( $post_meta_values['_exclude_product_ids'][0] ) ? maybe_unserialize( $post_meta_values['_exclude_product_ids'][0] ) : array();

			$buy_product_ids     = is_array( $buy_product_ids ) ? $buy_product_ids : array();
			$buy_category_ids    = is_array( $buy_category_ids ) ? $buy_category_ids : array();
			$exclude_product_ids = is_array( $exclude_product_ids ) ? $exclude_product_ids : array();

			$condition = array(
				'type'     => 'product' === $applies_to ? 'product' : 'product_cat',
				'modifier' => 'in',
				'value'    => 'product' === $applies_to ? $buy_product_ids : $buy_category_ids,
			);

			if ( 'product_cat' === $condition['type'] && in_array( 'all', $condition['value'], true ) ) {
				$condition['type'] = 'all_products';
			}

			// Add main condition.
			$conditions[] = $condition;

			// Exclude condition.
			if ( ! empty( $exclude_product_ids ) ) {
				$conditions[] = array(
					'type'     => 'product',
					'modifier' => 'not-in',
					'value'    => $exclude_product_ids,
				);
			}

			$applies_to = array( $conditions );
		}
		return $applies_to;
	}

	/**
	 * Read legacy gift action.
	 *
	 * @param array $post_meta_values Post meta value array.
	 * @return array
	 */
	protected function read_legacy_gift_products_prop( $post_meta_values ) {
		$gift_products = isset( $post_meta_values['_gift_products'][0] ) ? maybe_unserialize( $post_meta_values['_gift_products'][0] ) : array();
		$action        = isset( $post_meta_values['_action'][0] ) ? $post_meta_values['_action'][0] : false;

		if ( in_array( $action, array( 'choose_from_category', 'choose_from_products' ), true ) ) {
			$free_product_ids  = isset( $post_meta_values['_free_product_ids'][0] ) ? maybe_unserialize( $post_meta_values['_free_product_ids'][0] ) : array();
			$free_category_ids = isset( $post_meta_values['_free_category_ids'][0] ) ? maybe_unserialize( $post_meta_values['_free_category_ids'][0] ) : array();

			$free_product_ids  = is_array( $free_product_ids ) ? $free_product_ids : array();
			$free_category_ids = is_array( $free_category_ids ) ? $free_category_ids : array();

			$condition = array(
				'type'     => 'choose_from_products' === $action ? 'product' : 'product_cat',
				'modifier' => 'in',
				'value'    => 'choose_from_products' === $action ? $free_product_ids : $free_category_ids,
			);

			if ( 'product_cat' === $condition['type'] && in_array( 'all', $condition['value'], true ) ) {
				$condition['type'] = 'all_products';
			}

			$gift_products = array(
				array( $condition ),
			);
		}
		return $gift_products;
	}

	/**
	 * Helper method that updates all the post meta for a rule based on it's settings in the WC_BOGOF_rule class.
	 *
	 * @param WC_BOGOF_rule $rule BOGOF rule object.
	 */
	protected function update_post_meta( &$rule ) {
		$updated_props     = array();
		$meta_key_to_props = array();

		foreach ( $this->internal_meta_keys as $meta_key ) {
			if ( in_array( $meta_key, array( '_title', '_date_created', '_date_modified', '_default_order', '_edit_lock', '_edit_last', '_wp_old_date' ), true ) ) {
				continue;
			}
			$meta_key_to_props[ $meta_key ] = substr( $meta_key, 1 );
		}

		$props_to_update = $this->get_props_to_update( $rule, $meta_key_to_props );

		foreach ( $props_to_update as $meta_key => $prop ) {
			$value = $rule->{"get_$prop"}( 'edit' );
			$value = is_string( $value ) ? wp_slash( $value ) : $value;
			switch ( $prop ) {
				case 'enabled':
				case 'individual':
				case 'exclude_other_rules':
				case 'exclude_coupon_validation':
					$value = wc_bool_to_string( $value );
					break;
				case 'start_date':
				case 'end_date':
					$value = $value ? $value->getTimestamp() : '';
					break;
			}

			$updated = update_post_meta( $rule->get_id(), $meta_key, $value );

			if ( $updated ) {
				$updated_props[] = $prop;
			}
		}

		$this->validate_updated_properties( $rule, $updated_props );
		$this->update_default_order( $rule, $updated_props );
		$this->clear_caches( $rule->get_id() );
	}

	/**
	 * Validate updated properties after updating meta data.
	 *
	 * @param WC_BOGOF_rule $rule Rule object.
	 * @param array         $updated_props Update properties.
	 */
	protected function validate_updated_properties( &$rule, $updated_props ) {
		if ( wc_bogof_in_array_intersect( array( 'min_quantity', 'free_quantity' ), $updated_props ) && 'cheapest_free' === $rule->get_type() ) {
			if ( $rule->get_free_quantity() && $rule->get_min_quantity() && $rule->get_free_quantity() >= $rule->get_min_quantity() ) {
				$free_quantity = $rule->get_min_quantity() - 1 > 0 ? $rule->get_min_quantity() - 1 : 0;
				update_post_meta( $rule->get_id(), '_free_quantity', $free_quantity );
			}
		}
	}

	/**
	 * Update _default_order meta key.
	 *
	 * @param WC_BOGOF_rule $rule Rule object.
	 * @param array         $updated_props Update properties.
	 */
	protected function update_default_order( &$rule, $updated_props ) {
		if ( count( array_intersect( array( 'type', 'exclude_other_rules', 'minimum_amount' ), $updated_props ) ) ) {
			update_post_meta( $rule->get_id(), '_default_order', $rule->get_priority() );
		}
	}

	/**
	 * Clear any caches.
	 *
	 * @param int $rule_id Rule ID.
	 */
	protected function clear_caches( $rule_id ) {
		if ( version_compare( WC_VERSION, '3.9', '>=' ) ) {
			WC_Cache_Helper::invalidate_cache_group( 'bogof_rule_' . $rule_id );
		} else {
			WC_Cache_Helper::incr_cache_prefix( 'bogof_rule_' . $rule_id );
		}
		WC_Cache_Helper::get_transient_version( 'bogof_rules', true );
	}

	/**
	 * Returns the coupon codes of a rule.
	 *
	 * @param WC_BOGOF_rule $rule Rule object.
	 * @return array
	 */
	public function get_coupon_codes( &$rule ) {
		$cache_key = WC_Cache_Helper::get_cache_prefix( 'bogof_rule_' . $rule->get_id() ) . $rule->get_id() . '_coupon_codes';
		$codes     = wp_cache_get( $cache_key, 'wc_bogof' );

		if ( false !== $codes && is_array( $codes ) ) {
			return $codes;
		}

		$codes = array();
		$ids   = $rule->get_coupon_ids();
		if ( ! empty( $ids ) ) {
			$posts = get_posts(
				array(
					'post_type'      => 'shop_coupon',
					'post_status'    => 'publish',
					'posts_per_page' => -1,
					'post__in'       => $ids,
				)
			);

			foreach ( $posts as $post ) {
				$codes[] = function_exists( 'wc_format_coupon_code' ) ? wc_format_coupon_code( $post->post_title ) : $post->post_title;
			}
		}

		wp_cache_set( $cache_key, $codes, 'wc_bogof' );

		return $codes;
	}

	/**
	 * Returns all enabled rules.
	 *
	 * @return array
	 */
	public function get_rules() {
		$rules     = array();
		$cache_key = 'wc_bogof_rules_' . WC_Cache_Helper::get_transient_version( 'bogof_rules' );
		$ids       = wp_cache_get( $cache_key, 'wc_bogof' );

		if ( ! $ids || ! is_array( $ids ) ) {

			do_action( 'wc_bogof_before_get_all_rules' );

			$ids = get_posts(
				array(
					'post_type'      => 'shop_bogof_rule',
					'posts_per_page' => -1,
					'post_status'    => 'publish',
					'fields'         => 'ids',
					'meta_query'     => array(
						array(
							'key'   => '_enabled',
							'value' => 'yes',
						),
						array(
							'key'     => '_min_quantity',
							'value'   => '0',
							'compare' => '>',
						),
					),
				)
			);

			wp_cache_set( $cache_key, $ids, 'wc_bogof' );
		}

		foreach ( $ids as $rule_id ) {
			$rules[ $rule_id ] = new WC_BOGOF_Rule( $rule_id );
		}
		return $rules;
	}

	/**
	 * Increase usage count for current rule.
	 *
	 * @param WC_BOGOF_rule $rule Rule object.
	 * @param WC_Order      $order Order object.
	 */
	public function increase_usage_count( $rule, $order ) {
		$order->add_meta_data( '_wc_bogof_rule_id', $rule->get_id() );
		delete_transient( 'wc_bogof_uses_' . $rule->get_id() );
	}

	/**
	 * Returns the number of times a user used a rule.
	 *
	 * @since 3.2.0 Returns the total usages if $used_by param is null.
	 *
	 * @param array             $used_by Array of user IDs (ID and|or emails).
	 * @param WC_BOGOF_rule|int $rule Rule object or Rule ID.
	 * @return int
	 */
	public function get_usage_count( $used_by, $rule ) {
		$rule_id   = is_callable( array( $rule, 'get_id' ) ) ? $rule->get_id() : absint( $rule );
		$cache_key = 'wc_bogof_uses_' . $rule_id;
		$data      = get_transient( $cache_key );
		$get_total = is_null( $used_by );
		$distinct  = apply_filters( 'wc_bogof_usage_count_distinct', true ) ? 'DISTINCT' : '';

		if ( $get_total ) {
			// Get the total usages.
			$used_by_hash = 'total';
		} else {
			$used_by      = is_array( $used_by ) ? array_filter( array_unique( array_map( 'strtolower', ( is_array( $used_by ) ? $used_by : array( $used_by ) ) ) ) ) : array();
			$used_by_s    = "'" . implode( "','", array_map( 'sanitize_text_field', $used_by ) ) . "'";
			$used_by_hash = md5( $used_by_s . $distinct );
		}

		if ( ! is_array( $data ) ) {
			$data = array();
		}

		if ( ! isset( $data[ $used_by_hash ] ) || ! is_numeric( $data[ $used_by_hash ] ) ) {

			global $wpdb;

			$query = array(
				'select' => "COUNT( {$distinct} posts.ID )",
				'from'   => array(
					"FROM {$wpdb->posts} posts",
					"INNER JOIN {$wpdb->postmeta} postmeta ON postmeta.post_id= posts.ID AND postmeta.meta_key = '_wc_bogof_rule_id' AND postmeta.meta_value = %d",
				),
				'where'  => array(
					"posts.post_status IN ( 'wc-completed', 'wc-processing', 'wc-on-hold' )",
				),
			);

			if ( ! $get_total ) {
				$query['from'][]  = "LEFT JOIN {$wpdb->postmeta} billing_email ON billing_email.post_id = posts.ID and billing_email.meta_key = '_billing_email'";
				$query['from'][]  = "LEFT JOIN {$wpdb->postmeta} customer_user ON customer_user.post_id = posts.ID and customer_user.meta_key = '_customer_user'";
				$query['where'][] = '( billing_email.meta_value IN (' . $used_by_s . ') OR customer_user.meta_value IN (' . $used_by_s . ') )';
			}

			$sql = 'SELECT ' . $query['select'] . ' ' . implode( ' ', $query['from'] ) . ' WHERE ' . implode( ' AND ', $query['where'] );

			$count = $wpdb->get_var( $wpdb->prepare( $sql, $rule_id ) ); //phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

			$data[ $used_by_hash ] = $count;

			set_transient( $cache_key, $data, 30 * DAY_IN_SECONDS );
		}

		return absint( $data[ $used_by_hash ] );
	}

	/*
	|--------------------------------------------------------------------------
	| Deprecated
	|--------------------------------------------------------------------------
	*/

	/**
	 * Returns the number of times a user used a rule.
	 *
	 * @deprecated 3.0
	 * @param string        $used_by Either user ID or billing email.
	 * @param WC_BOGOF_rule $rule Rule object.
	 */
	public function get_used_by_count( $used_by, $rule ) {
		wc_deprecated_function( 'WC_BOGOF_Rule_Data_Store_CPT::get_used_by_count', '3.0.0', 'WC_BOGOF_Admin_Meta_Boxes::get_usage_count' );
		return $this->get_usage_count( $used_by, $rule );
	}

	/**
	 * Is a buy product?
	 *
	 * @deprecated 3.0
	 * @param int           $product_id Product ID.
	 * @param WC_BOGOF_rule $rule Rule object.
	 * @return bool
	 */
	public function is_buy_product( $product_id, $rule ) {
		wc_deprecated_function( 'WC_BOGOF_Rule_Data_Store_CPT::is_buy_product', '3.0', 'WC_BOGOF_Rule::is_buy_product' );
		return $rule->is_buy_product( $product_id );
	}

	/**
	 * Is a free product?
	 *
	 * @deprecated 3.0
	 * @param int           $product_id Product ID.
	 * @param WC_BOGOF_rule $rule Rule object.
	 * @return bool
	 */
	public function is_free_product( $product_id, $rule ) {
		wc_deprecated_function( 'WC_BOGOF_Rule_Data_Store_CPT::is_free_product', '3.0', 'WC_BOGOF_Rule::is_free_product' );
		return $rule->is_free_product( $product_id );
	}

	/**
	 * Get a lists of rules by a product ID.
	 *
	 * @deprecated 3.0
	 * @param int $product_id Product ID.
	 * @return array
	 */
	public function get_rules_by_product( $product_id ) {
		wc_deprecated_function( 'WC_BOGOF_Rule_Data_Store_CPT::get_rules_by_product', '3.0' );
		return array();
	}
}
