<?php
/**
 * Condition Tag class.
 *
 * @since 3.0.0
 * @package WC_BOGOF
 */

defined( 'ABSPATH' ) || exit;

/**
 * WC_BOGOF_Condition_Tag Class
 */
class WC_BOGOF_Condition_Tag extends WC_BOGOF_Condition_Category {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->id    = 'product_tag';
		$this->title = __( 'Tag', 'wc-buy-one-get-one-free' );
	}
}
