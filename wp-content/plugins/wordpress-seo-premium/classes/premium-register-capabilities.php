<?php
/**
 * WPSEO Premium plugin file.
 *
 * @package WPSEO\Admin\Capabilities
 */

/**
 * Capabilities registration class.
 */
class WPSEO_Premium_Register_Capabilities implements WPSEO_WordPress_Integration {

	/**
	 * Registers the hooks.
	 *
	 * @return void
	 */
	public function register_hooks() {
		add_action( 'wpseo_register_capabilities_premium', [ $this, 'register' ] );
	}

	/**
	 * Registers the capabilities.
	 *
	 * @return void
	 */
	public function register() {
		$manager = WPSEO_Capability_Manager_Factory::get( 'premium' );

		$manager->register( 'wpseo_manage_redirects', [ 'administrator', 'editor', 'wpseo_editor', 'wpseo_manager' ] );
	}
}
