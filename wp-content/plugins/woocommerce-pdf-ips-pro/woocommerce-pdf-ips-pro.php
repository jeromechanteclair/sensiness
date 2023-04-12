<?php
/**
 * Plugin Name: WooCommerce PDF Invoices & Packing Slips Professional
 * Plugin URI: http://www.wpovernight.com
 * Description: Extended functionality for the WooCommerce PDF Invoices & Packing Slips plugin
 * Version: 2.7.3
 * Author: Ewout Fernhout
 * Author URI: http://www.wpovernight.com
 * License: GPLv2 or later
 * License URI: http://www.opensource.org/licenses/gpl-license.php
 * Text Domain: wpo_wcpdf_pro
 * WC requires at least: 2.3.0
 * WC tested up to: 5.0.0
 */

if ( !class_exists( 'WooCommerce_PDF_IPS_Pro' ) ) :

class WooCommerce_PDF_IPS_Pro {

	public $version = '2.7.3';
	public $plugin_basename;
	public $cloud_api = null;

	protected static $_instance = null;

	/**
	 * Main Plugin Instance
	 *
	 * Ensures only one instance of plugin is loaded or can be loaded.
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->plugin_basename = plugin_basename(__FILE__);

		$this->define( 'WPO_WCPDF_PRO_VERSION', $this->version );

		// load the localisation & classes
		add_action( 'plugins_loaded', array( $this, 'translations' ) );
		add_action( 'wpo_wcpdf_reload_attachment_translations', array( $this, 'translations' ) );
		add_action( 'plugins_loaded', array( $this, 'load_classes_early' ) );
		add_action( 'init', array( $this, 'load_classes' ) );

		// Load the updater
		add_action( 'init', array( $this, 'load_updater' ), 0 );

		// run lifecycle methods
		if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
			add_action( 'wp_loaded', array( $this, 'do_install' ) );
		}

		// Autoloader
		if ( version_compare( PHP_VERSION, '5.6', '>=' ) ) {
			require( plugin_dir_path( __FILE__).'lib/vendor/autoload.php' );
		}
	}

	/**
	 * Define constant if not already set
	 * @param  string $name
	 * @param  string|bool $value
	 */
	private function define( $name, $value ) {
		if ( ! defined( $name ) ) {
			define( $name, $value );
		}
	}

	/**
	 * Run the updater scripts from the WPO Sidekick
	 * @return void
	 */
	public function load_updater() {
		// Init updater data
		$item_name		= 'WooCommerce PDF Invoices & Packing Slips Professional';
		$file			= __FILE__;
		$license_slug	= 'wpo_wcpdf_pro_license';
		$version		= $this->version;
		$author			= 'Ewout Fernhout';

		// Check if sidekick is loaded
		if (class_exists('WPO_Updater')) {
			$this->updater = new WPO_Updater( $item_name, $file, $license_slug, $version, $author );
		}
	}

	/**
	 * Load the translation / textdomain files
	 * 
	 * Note: the first-loaded translation file overrides any following ones if the same translation is present
	 */
	public function translations() {
		if ( function_exists( 'determine_locale' ) ) { // WP5.0+
			$locale = determine_locale();
		} else {
			$locale = is_admin() && function_exists( 'get_user_locale' ) ? get_user_locale() : get_locale();
		}
		$locale = apply_filters( 'plugin_locale', $locale, 'woocommerce-pdf-invoices-packing-slips' );
		$dir    = trailingslashit( WP_LANG_DIR );

		/**
		 * Frontend/global Locale. Looks in:
		 *
		 * 		- WP_LANG_DIR/woocommerce-pdf-invoices-packing-slips/wpo_wcpdf_pro-LOCALE.mo
		 * 	 	- WP_LANG_DIR/plugins/wpo_wcpdf_pro-LOCALE.mo
		 * 	 	- woocommerce-pdf-invoices-packing-slips/languages/wpo_wcpdf_pro-LOCALE.mo (which if not found falls back to:)
		 * 	 	- WP_LANG_DIR/plugins/wpo_wcpdf_pro-LOCALE.mo
		 *
		 * WP_LANG_DIR defaults to wp-content/languages
		 */
		if ( current_filter() == 'wpo_wcpdf_reload_attachment_translations' ) {
			unload_textdomain( 'wpo_wcpdf_pro' );
			WC()->countries = new \WC_Countries();
		}
		load_textdomain( 'wpo_wcpdf_pro', $dir . 'woocommerce-pdf-ips-pro/wpo_wcpdf_pro-' . $locale . '.mo' );
		load_textdomain( 'wpo_wcpdf_pro', $dir . 'plugins/wpo_wcpdf_pro-' . $locale . '.mo' );
		load_plugin_textdomain( 'wpo_wcpdf_pro', false, dirname( plugin_basename(__FILE__) ) . '/languages' );
	}

	/**
	 * Load the main plugin classes and functions
	 */
	public function includes() {
		// compatibility classes
		include_once( $this->plugin_path().'/includes/compatibility/abstract-wc-data-compatibility.php' );
		include_once( $this->plugin_path().'/includes/compatibility/class-wc-date-compatibility.php' );
		include_once( $this->plugin_path().'/includes/compatibility/class-wc-core-compatibility.php' );
		include_once( $this->plugin_path().'/includes/compatibility/class-wc-order-compatibility.php' );
		include_once( $this->plugin_path().'/includes/compatibility/class-wc-product-compatibility.php' );
		include_once( $this->plugin_path().'/includes/compatibility/wc-datetime-functions-compatibility.php' );

		// Plugin classes
		$this->functions = include_once( $this->plugin_path().'/includes/wcpdf-pro-functions.php' );
		$this->settings = include_once( $this->plugin_path().'/includes/wcpdf-pro-settings.php' );
		$this->writepanels = include_once( $this->plugin_path().'/includes/wcpdf-pro-writepanels.php' );
		
		// Backwards compatibility with self
		include_once( $this->plugin_path().'/includes/legacy/wcpdf-pro-legacy.php' );

		// multilingual plugins
		if ( class_exists('SitePress') || class_exists('Polylang') ) {
			$this->multilingual = include_once( $this->plugin_path().'/includes/wcpdf-pro-multilingual.php' );
		}
		// Bulk export
		if ( version_compare( WOOCOMMERCE_VERSION, '3.0', '>=' ) ) {
			$this->bulk_export	= include_once( $this->plugin_path().'/includes/wcpdf-pro-bulk-export.php' );
		}

		if ( version_compare( PHP_VERSION, '7.2.5', '<' ) ) {
			add_action( 'admin_notices', array ( $this, 'required_php_version_cloud_storage' ) );
		} else {
			// Abstract Cloud API class
			$this->cloud_api = include_once( $this->plugin_path().'/includes/cloud/abstract-wcpdf-cloud-api.php' );
			$cloud_services_enabled = include_once( $this->plugin_path().'/includes/cloud/cloud-services-enabled.php' );
			foreach( $cloud_services_enabled::$services_enabled as $service_slug ) {
				if( $service_slug == 'dropbox' ) {
					// Dropbox API
					$this->dropbox_api = include_once( $this->plugin_path().'/includes/cloud/dropbox/dropbox-api.php' );
				}
				if( $service_slug == 'gdrive' ) {
					// Gdrive API
					$this->gdrive_api = include_once( $this->plugin_path().'/includes/cloud/gdrive/gdrive-api.php' );
				}
			}
			// Cloud Storage class
			$this->cloud_storage = include_once( $this->plugin_path().'/includes/wcpdf-pro-cloud-storage.php' );
		}
	}
	

	/**
	 * Instantiate classes when woocommerce is activated
	 */
	public function load_classes() {
		if ( $this->check_plugin_requirements() === false ) {
			return;
		}

		// all systems ready - GO!
		$this->includes();
	}

	/**
	 * Instantiate classes when woocommerce is activated
	 */
	public function load_classes_early() {
		if ( $this->check_plugin_requirements() === false ) {
			return;
		}

		// all systems ready - GO!
		$this->emails = include_once( $this->plugin_path().'/includes/wcpdf-pro-emails.php' );
	}

	public function check_plugin_requirements() {
		if ( $this->is_woocommerce_activated() === false ) {
			add_action( 'admin_notices', array ( $this, 'need_woocommerce' ) );
			return false;
		}

		if ( version_compare( PHP_VERSION, '5.6', '<' ) ) {
			add_action( 'admin_notices', array ( $this, 'required_php_version' ) );
			return false;
		}

		if ( $this->is_base_plugin_activated() === false ) {
			add_action( 'admin_notices', array ( $this, 'base_plugin_requirement' ) );
			return false;
		}

		return true;
	}

	/**
	 * Check if base plugin is activated and 2.0+
	 */
	public function is_base_plugin_activated() {
		if (class_exists('WooCommerce_PDF_Invoices') && version_compare( WooCommerce_PDF_Invoices::$version, '2.0-beta-2', '<' ) ) {
			return false;
		} elseif ( function_exists('WPO_WCPDF') && version_compare( WPO_WCPDF()->version, '2.0-beta-2', '>=' ) ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Check if woocommerce is activated
	 */
	public function is_woocommerce_activated() {
		$blog_plugins = get_option( 'active_plugins', array() );
		$site_plugins = get_site_option( 'active_sitewide_plugins', array() );

		if ( in_array( 'woocommerce/woocommerce.php', $blog_plugins ) || isset( $site_plugins['woocommerce/woocommerce.php'] ) ) {
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * WooCommerce not active notice.
	 *
	 * @return string Fallack notice.
	 */
	 
	public function need_woocommerce() {
		$error = sprintf( __( 'WooCommerce PDF Invoices & Packing Slips Professional requires %sWooCommerce%s to be installed & activated!' , 'wpo_wcpdf_pro' ), '<a href="https://wordpress.org/plugins/woocommerce/">', '</a>' );

		$message = '<div class="error"><p>' . $error . '</p></div>';
	
		echo $message;
	}

	/**
	 * PHP version requirement notice
	 */
	
	public function required_php_version() {
		$error = __( 'WooCommerce PDF Invoices & Packing Slips Professional requires PHP 5.6 or higher.', 'wpo_wcpdf_pro' );
		$how_to_update = __( 'How to update your PHP version', 'wpo_wcpdf_pro' );
		$message = sprintf('<div class="error"><p>%s</p><p><a href="%s">%s</a></p></div>', $error, 'http://docs.wpovernight.com/general/how-to-update-your-php-version/', $how_to_update);
	
		echo $message;
	}

	/**
	 * PHP version requirement notice for Cloud Storage
	 */
	
	public function required_php_version_cloud_storage() {
		if( ! get_option('wpo_wcpdf_pro_hide_cloud_storage_notice') ) {
			$error = __( 'WooCommerce PDF Invoices & Packing Slips Professional <strong>Cloud Storage</strong> feature requires <strong>PHP 7.2.5 or higher</strong>.', 'wpo_wcpdf_pro' );
			$how_to_update = __( 'How to update your PHP version', 'wpo_wcpdf_pro' );
			ob_start();
			?>
			<div class="error">
				<?php printf('<p>%s</p><p><a class="button" href="%s" target="_blank">%s</a></p>', $error, 'http://docs.wpovernight.com/general/how-to-update-your-php-version/', $how_to_update); ?>
				<p><a href="<?php echo esc_url( add_query_arg( 'wpo_wcpdf_pro_hide_cloud_storage_notice', 'true' ) ); ?>"><?php _e( 'Hide this message', 'wpo_wcpdf_pro' ); ?></a></p>
			</div>
			<?php
			echo ob_get_clean();
		}

		// save option to hide cloud storage notice
		if ( isset( $_GET['wpo_wcpdf_pro_hide_cloud_storage_notice'] ) ) {
			update_option( 'wpo_wcpdf_pro_hide_cloud_storage_notice', true );
			wp_redirect( 'admin.php?page=wpo_wcpdf_options_page' );
			exit;
		}
	}

	/**
	 * Base Plugin not active or 2.0+ notice.
	 *
	 * @return string Fallack notice.
	 */
	 
	public function base_plugin_requirement() {
		$error = sprintf( __( 'WooCommerce PDF Invoices & Packing Slips Professional requires at least version 2.0 of WooCommerce PDF Invoices & Packing Slips - get it %shere%s!' , 'wpo_wcpdf_pro' ), '<a href="https://wordpress.org/plugins/woocommerce-pdf-invoices-packing-slips/">', '</a>' );

		$message = '<div class="error"><p>' . $error . '</p></div>';
	
		echo $message;
	}

	/** Lifecycle methods *******************************************************
	 * Because register_activation_hook only runs when the plugin is manually
	 * activated by the user, we're checking the current version against the
	 * version stored in the database
	****************************************************************************/

	/**
	 * Handles version checking
	 */
	public function do_install() {
		// only install when base plugin is and up to date
		if ( !$this->is_base_plugin_activated() || version_compare( PHP_VERSION, '5.6', '<' ) ) {
			return;
		}

		$version_setting = 'wpo_wcpdf_pro_version';
		$installed_version = get_option( $version_setting );
		// 1.5.2 and older used wpo_wcpdf_ips_version!
		if ( $installed_version === false ) {
			$installed_version = get_option( 'wpo_wcpdf_ips_version' );
			delete_option( 'wpo_wcpdf_ips_version' );
		}

		// installed version lower than plugin version?
		if ( version_compare( $installed_version, $this->version, '<' ) ) {

			if ( ! $installed_version ) {
				$this->install();
			} else {
				$this->upgrade( $installed_version );
			}

			// new version number
			update_option( $version_setting, $this->version );
		}
	}


	/**
	 * Plugin install method. Perform any installation tasks here
	 */
	protected function install() {
		// set default settings
		$settings_defaults = array(
			'wpo_wcpdf_documents_settings_credit-note' => array(
				'enabled'					=> 1,
			),
		);
		foreach ($settings_defaults as $option => $defaults) {
			update_option( $option, $defaults );
		}

		// set customizer defaults for credit note & proforma
		if ( function_exists('WPO_WCPDF_Templates') && $customizer_settings = get_option('wpo_wcpdf_editor_settings') ) {
			// mark as unsaved to allow overriding
			unset($customizer_settings['settings_saved']);
			update_option( 'wpo_wcpdf_editor_settings', $customizer_settings );

			// error_log('templates');
			$documents = array( 'proforma', 'credit-note' );
			foreach ($documents as $document) {
				// echo "1<pre>";var_dump($customizer_settings);echo "</pre>";
				if ( empty($customizer_settings["fields_{$document}_totals"]) && empty($customizer_settings["fields_{$document}_columns"]) ) {
					$customizer_settings["fields_{$document}_totals"] = apply_filters( 'wpo_wcpdf_template_editor_defaults', array(), $document, 'totals' );
					$customizer_settings["fields_{$document}_columns"] = apply_filters( 'wpo_wcpdf_template_editor_defaults', array(), $document, 'columns' );
				}
			}
			$customizer_settings['settings_saved'] = 1;
			update_option( 'wpo_wcpdf_editor_settings', $customizer_settings );
		}
	}

	/**
	 * Plugin upgrade method.  Perform any required upgrades here
	 *
	 * @param string $installed_version the currently installed ('old') version
	 */
	protected function upgrade( $installed_version ) {
		// 1.4.0 - set default for new settings
		if ( version_compare( $installed_version, '1.4.0', '<' ) ) {
			$settings_key = 'wpo_wcpdf_pro_settings';
			$current_settings = get_option( $settings_key );
			$new_defaults = array(
				'enable_proforma'	=> 1,
			);
			
			$new_settings = array_merge($current_settings, $new_defaults);

			update_option( $settings_key, $new_settings );
		}

		// 2.0-dev update: reorganize settings
		if ( version_compare( $installed_version, '2.0-dev', '<' ) ) {
			$old_settings = array(
				'wpo_wcpdf_pro_settings'		=> get_option( 'wpo_wcpdf_pro_settings' ),
				'wpo_wcpdf_template_settings'	=> get_option( 'wpo_wcpdf_template_settings' ),
			);

			// combine number formatting in array
			$documents = array( 'proforma', 'credit_note' );
			foreach ($documents as $document) {
				$old_settings['wpo_wcpdf_pro_settings']["{$document}_number_formatting"] = array();
				$format_option_keys = array('padding','suffix','prefix');
				foreach ($format_option_keys as $format_option_key) {
					if (isset($old_settings['wpo_wcpdf_pro_settings']["{$document}_number_formatting_{$format_option_key}"])) {
						$old_settings['wpo_wcpdf_pro_settings']["{$document}_number_formatting"][$format_option_key] = $old_settings['wpo_wcpdf_pro_settings']["{$document}_number_formatting_{$format_option_key}"];
					}
				}
			}

			// convert abbreviated email_ids
			$email_settings = array( 'pro_attach_static', 'pro_attach_credit-note', 'pro_attach_proforma', 'pro_attach_packing-slip' );
			foreach ($email_settings as $email_setting_key) {
				if ( !isset( $old_settings['wpo_wcpdf_pro_settings'][$email_setting_key] ) ) {
					continue;
				}
				foreach ($old_settings['wpo_wcpdf_pro_settings'][$email_setting_key] as $email_id => $value) {
					if ($email_id == 'completed' || $email_id == 'processing') {
						$old_settings['wpo_wcpdf_pro_settings'][$email_setting_key]["customer_{$email_id}_order"] = $value;
						unset($old_settings['wpo_wcpdf_pro_settings'][$email_setting_key][$email_id]);
					}
				}
			}

			// convert old single static file to array
			if ( isset( $old_settings['wpo_wcpdf_pro_settings']['static_file'] ) && isset( $old_settings['wpo_wcpdf_pro_settings']['static_file']['id'] ) ) {
				$old_settings['wpo_wcpdf_pro_settings']['static_file'] = array( $old_settings['wpo_wcpdf_pro_settings']['static_file'] );
			}

			// map new settings to old
			$settings_map = array(
				'wpo_wcpdf_settings_pro' => array(
					'static_file'						=> array( 'wpo_wcpdf_pro_settings' => 'static_file' ),
					'static_file_attach_to_email_ids'	=> array( 'wpo_wcpdf_pro_settings' => 'pro_attach_static' ),
					'billing_address'					=> array( 'wpo_wcpdf_pro_settings' => 'billing_address' ),
					'shipping_address'					=> array( 'wpo_wcpdf_pro_settings' => 'shipping_address' ),
					'remove_whitespace'					=> array( 'wpo_wcpdf_pro_settings' => 'remove_whitespace' ),
					'placeholders_allow_line_breaks'	=> array( 'wpo_wcpdf_pro_settings' => 'placeholders_allow_line_breaks' ),
				),
				'wpo_wcpdf_documents_settings_packing-slip' => array(
					'attach_to_email_ids'				=> array( 'wpo_wcpdf_pro_settings' => 'pro_attach_packing-slip' ),
					'subtract_refunded_qty'				=> array( 'wpo_wcpdf_pro_settings' => 'subtract_refunded_qty' ),
					'hide_virtual_products'				=> array( 'wpo_wcpdf_pro_settings' => 'hide_virtual_products' ),
				),
				'wpo_wcpdf_documents_settings_credit-note' => array(
					'attach_to_email_ids'				=> array( 'wpo_wcpdf_pro_settings' => 'pro_attach_credit-note' ),
					'subtract_refunded_qty'				=> array( 'wpo_wcpdf_pro_settings' => 'subtract_refunded_qty' ),
					'display_shipping_address'			=> array( 'wpo_wcpdf_template_settings' => 'invoice_shipping_address' ),
					'display_email'						=> array( 'wpo_wcpdf_template_settings' => 'invoice_email' ),
					'display_phone'						=> array( 'wpo_wcpdf_template_settings' => 'invoice_phone' ),
					'display_date'						=> array( 'wpo_wcpdf_pro_settings' => 'credit_note_date' ),
					'original_invoice_number'			=> array( 'wpo_wcpdf_pro_settings' => 'credit_note_original_invoice_number' ),
					'number_sequence'					=> array( 'wpo_wcpdf_pro_settings' => 'credit_note_number' ),
					'number_format'						=> array( 'wpo_wcpdf_pro_settings' => 'credit_note_number_formatting' ),
					'positive_prices'					=> array( 'wpo_wcpdf_pro_settings' => 'positive_credit_note' ),
					'reset_number_yearly'				=> array( 'wpo_wcpdf_template_settings' => 'yearly_reset_invoice_number' ),
				),
				'wpo_wcpdf_documents_settings_proforma' => array(
					'enabled'							=> array( 'wpo_wcpdf_pro_settings' => 'enable_proforma' ),
					'attach_to_email_ids'				=> array( 'wpo_wcpdf_pro_settings' => 'pro_attach_proforma' ),
					'display_shipping_address'			=> array( 'wpo_wcpdf_template_settings' => 'invoice_shipping_address' ),
					'display_email'						=> array( 'wpo_wcpdf_template_settings' => 'invoice_email' ),
					'display_phone'						=> array( 'wpo_wcpdf_template_settings' => 'invoice_phone' ),
					'display_date'						=> array( 'wpo_wcpdf_pro_settings' => 'proforma_date' ),
					'number_sequence'					=> array( 'wpo_wcpdf_pro_settings' => 'proforma_number' ),
					'number_format'						=> array( 'wpo_wcpdf_pro_settings' => 'proforma_number_formatting' ),
					'reset_number_yearly'				=> array( 'wpo_wcpdf_template_settings' => 'yearly_reset_invoice_number' ),
				),
			);

			// walk through map
			foreach ($settings_map as $new_option => $new_settings_keys) {
				${$new_option} = array();
				foreach ($new_settings_keys as $new_key => $old_setting ) {
					$old_key = reset($old_setting);
					$old_option = key($old_setting);
					if (!empty($old_settings[$old_option][$old_key])) {
						${$new_option}[$new_key] = $old_settings[$old_option][$old_key];
					}
				}

				// auto enable credit note
				if ( $new_option == 'wpo_wcpdf_documents_settings_credit-note' ) {
					${$new_option}['enabled'] = 1;
				}

				// auto enable number display
				$enabled = array( 'wpo_wcpdf_documents_settings_proforma', 'wpo_wcpdf_documents_settings_credit-note' );
				if ( in_array( $new_option, $enabled ) ) {
					${$new_option}['display_number'] = 1;
					// echo '<pre>';var_dump(${$new_option});echo '</pre>';die();
				}

				// merge with existing settings
				${$new_option."_old"} = get_option( $new_option, ${$new_option} ); // second argument loads new as default in case the settings did not exist yet
				// echo '<pre>';var_dump(${$new_option."_old"});echo '</pre>';die();
				${$new_option} = ${$new_option} + ${$new_option."_old"}; // duplicate options take new options as default

				// store new option values
				update_option( $new_option, ${$new_option} );
			}

			// copy next numbers to separate options
			$number_map = array(
				'wpo_wcpdf_next_proforma_number'		=> array( 'wpo_wcpdf_pro_settings' => 'next_proforma_number' ),
				'wpo_wcpdf_next_credit_note_number'		=> array( 'wpo_wcpdf_pro_settings' => 'next_credit_note_number' ),
			);
			foreach ($number_map as $number_option => $old_setting) {
				$old_key = reset($old_setting);
				$old_option = key($old_setting);
				if (!empty($old_settings[$old_option][$old_key])) {
					${$number_option} = $old_settings[$old_option][$old_key];
					// store new option values
					update_option( $number_option, ${$number_option} );
				}
			}

			// copy settings fields translations
			$translations = get_option( 'wpo_wcpdf_translations' );
			if ( $translations !== false ) {
				$general_settings = get_option( 'wpo_wcpdf_settings_general' );
				foreach ($translations as $setting => $translations) {
					// settings are stored by HTML form name as key, i.e. wpo_wcpdf_template_settings[shop_name]
					preg_match('/^(.*?)\[(.*?)\]/s',$setting,$matches);
					if ( !empty($matches) && count($matches) == 3 ) {
						$option = $matches[1];
						$option_key = $matches[2];
						if (isset($general_settings[$option_key])) {
							$general_settings[$option_key] = $translations + $general_settings[$option_key];
						} else {
							$general_settings[$option_key] = $translations;
						}
					}
				}
				update_option( 'wpo_wcpdf_settings_general', $general_settings );
			}
			
		}

		// 2.0-beta-2 update: copy next numbers to separate store & convert sequence options
		if ( version_compare( $installed_version, '2.0-beta-2', '<' ) ) {
			// load number store class (just in case)
			include_once( WPO_WCPDF()->plugin_path() . '/includes/documents/class-wcpdf-sequential-number-store.php' );

			// copy next numbers to number store tables
			$number_map = array(
				'proforma_number'		=> 'wpo_wcpdf_next_proforma_number',
				'credit_note_number'	=> 'wpo_wcpdf_next_credit_note_number',
			);
			foreach ($number_map as $store_name => $old_option) {
				$next_number = get_option( $old_option );
				if (!empty($next_number)) {
					$number_store = new \WPO\WC\PDF_Invoices\Documents\Sequential_Number_Store( $store_name );
					$number_store->set_next( (int) $next_number );
				}
				delete_option( $old_option ); // clean up after ourselves
			}

			// convert sequence setting
			// main => invoice_number
			// separate => {$document_slug}_number
			$document_stores = array(
				'wpo_wcpdf_documents_settings_credit-note' => 'credit_note_number',
				'wpo_wcpdf_documents_settings_proforma' => 'proforma_number',
			);
			foreach ($document_stores as $document_option => $number_store_name) {
				$settings = get_option( $document_option, array() );
				if (isset($settings['number_sequence'])) {
					if ($settings['number_sequence'] == 'main' || $settings['number_sequence'] == 'invoice_number' ) { // invoice_number in case this was manually triggered
						$settings['number_sequence'] = 'invoice_number';
					} else { // separate
						$settings['number_sequence'] = $number_store_name;
					}
					update_option( $document_option, $settings );
				}
			}
		}

		// 2.7.0 update: replace and delete legacy Dropbox options
		if ( version_compare( $installed_version, '2.7.0', '<' ) ) {
			// Dropbox legacy settings porting
			if( !empty($legacy_settings = get_option('wpo_wcpdf_dropbox_settings')) ) {
				// update legacy data
				$legacy_settings['cloud_service'] = 'dropbox';
				if( $legacy_settings['access_type'] == 'dropbox' ) {
					$legacy_settings['access_type'] = 'root_folder';
				}

				// check if the legacy Dropbox API settings exist
				if( !empty($legacy_api_settings = get_option('wpo_wcpdf_dropbox_api_v2')) ) {
					// create the new api settings and delete the legacy ones
					update_option('wpo_wcpdf_dropbox_api_settings', $legacy_api_settings);
					delete_option('wpo_wcpdf_dropbox_api_v2');
				}

				// create the new settings and delete the legacy ones
				update_option('wpo_wcpdf_cloud_storage_settings', $legacy_settings);
				delete_option('wpo_wcpdf_dropbox_settings');
			}
		}
	}

	/**
	 * Get the plugin url.
	 * @return string
	 */
	public function plugin_url() {
		return untrailingslashit( plugins_url( '/', __FILE__ ) );
	}

	/**
	 * Get the plugin path.
	 * @return string
	 */
	public function plugin_path() {
		return untrailingslashit( plugin_dir_path( __FILE__ ) );
	}

	/**
	 * Return false.
	 * @return bool
	 */
	public function return_false() {
		return false;
	}

	/**
	 * Return true.
	 * @return bool
	 */
	public function return_true() {
		return true;
	}

} // class WooCommerce_PDF_IPS_Pro

endif; // class_exists

/**
 * Returns the main instance of the plugin to prevent the need to use globals.
 *
 * @since  2.0
 * @return WooCommerce_PDF_IPS_Pro
 */
function WPO_WCPDF_Pro() {
	return WooCommerce_PDF_IPS_Pro::instance();
}

// Load Professional
WPO_WCPDF_Pro();

/**
 * WPOvernight updater admin notice
 */
if ( ! class_exists( 'WPO_Updater' ) && ! function_exists( 'wpo_updater_notice' ) ) {

	if ( ! empty( $_GET['hide_wpo_updater_notice'] ) ) {
		update_option( 'wpo_updater_notice', 'hide' );
	}

	/**
	 * Display a notice if the "WP Overnight Sidekick" plugin hasn't been installed.
	 * @return void
	 */
	function wpo_updater_notice() {
		$wpo_updater_notice = get_option( 'wpo_updater_notice' );

		$blog_plugins = get_option( 'active_plugins', array() );
		$site_plugins = get_site_option( 'active_sitewide_plugins', array() );
		$plugin = 'wpovernight-sidekick/wpovernight-sidekick.php';

		if ( in_array( $plugin, $blog_plugins ) || isset( $site_plugins[$plugin] ) || $wpo_updater_notice == 'hide' ) {
			return;
		}

		echo '<div class="updated fade"><p>Install the <strong>WP Overnight Sidekick</strong> plugin to receive updates for your WP Overnight plugins - check your order confirmation email for more information. <a href="'.add_query_arg( 'hide_wpo_updater_notice', 'true' ).'">Hide this notice</a></p></div>' . "\n";
	}

	add_action( 'admin_notices', 'wpo_updater_notice' );
}
