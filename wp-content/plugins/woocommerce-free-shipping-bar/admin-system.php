<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

class WFSPB_Admin_System {
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'menu_page' ),999 );
	}

	public function page_callback() { ?>
		<div class="wrap">
			<h2><?php esc_html_e( 'System Status', 'woocommerce-free-shipping-bar' ) ?></h2>
			<table cellspacing="0" id="status" class="widefat">
				<tbody>
				<tr>
					<td data-export-label="<?php esc_html_e( 'PHP Time Limit', 'woocommerce-free-shipping-bar' ) ?>"><?php esc_html_e( 'PHP Time Limit', 'woocommerce-free-shipping-bar' ) ?></td>
					<td><?php echo ini_get( 'max_execution_time' ); ?></td>
				</tr>
				<tr>
					<td data-export-label="<?php esc_html_e( 'PHP Max Input Vars', 'woocommerce-free-shipping-bar' ) ?>"><?php esc_html_e( 'PHP Max Input Vars', 'woocommerce-free-shipping-bar' ) ?></td>

					<td><?php echo ini_get( 'max_input_vars' ); ?></td>
				</tr>
				<tr>
					<td data-export-label="<?php esc_html_e( 'Memory Limit', 'woocommerce-free-shipping-bar' ) ?>"><?php esc_html_e( 'Memory Limit', 'woocommerce-free-shipping-bar' ) ?></td>

					<td><?php echo ini_get( 'memory_limit' ); ?></td>
				</tr>
				</tbody>
			</table>
		</div>
	<?php }

	function menu_page() {
		add_submenu_page(
			'woocommerce_free_ship',
			esc_html__( 'System Status', 'woocommerce-free-shipping-bar' ),
			esc_html__( 'System Status', 'woocommerce-free-shipping-bar' ),
			'manage_options',
			'woo_free_ship_status',
			array( $this, 'page_callback' )
		);
	}
}

new WFSPB_Admin_System();