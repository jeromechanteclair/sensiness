<?php
/*
	Plugin Name: MBE eShip
	Description: Mail Boxes Etc. Online MBE Plugin integration for main Ecommerce platforms.
	Version: 2.0.3
	Author: MBE Worldwide S.p.A.
	Author URI: https://www.mbeglobal.com/
	Text Domain: mail-boxes-etc
	Domain Path: /languages
*/

if ( ! defined( 'MBE_ESHIP_ID' ) ) {
	define( "MBE_ESHIP_ID", "mbe_eship" );
}

if ( ! defined( 'MBE_ESHIP_PLUGIN_DIR' ) ) {
	define( "MBE_ESHIP_PLUGIN_DIR", plugin_dir_path( __FILE__ ) );
}

if ( ! defined( 'MBE_ESHIP_PLUGIN_URL' ) ) {
	define( "MBE_ESHIP_PLUGIN_URL", plugin_dir_url( __FILE__ ) );
}

if ( ! defined( 'MBE_ESHIP_PLUGIN_LOG_DIR' ) ) {
	define( "MBE_ESHIP_PLUGIN_LOG_DIR", MBE_ESHIP_PLUGIN_DIR . 'log' );
}

if ( ! function_exists( 'get_plugin_data' ) ) {
	defined( 'ABSPATH' ) || exit;
	require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
}
$plugin_data = get_plugin_data( __FILE__, false, false );

/** Plugin version */
if ( ! defined( 'MBE_ESHIP_PLUGIN_VERSION' ) ) {
	define( "MBE_ESHIP_PLUGIN_VERSION", $plugin_data['Version'] );
}

/** Plugin Name */
if ( ! defined( 'MBE_ESHIP_PLUGIN_NAME' ) ) {
	define( "MBE_ESHIP_PLUGIN_NAME", $plugin_data['Name'] );
}

/** MBE Elink Database version */
if ( ! defined( 'MBE_ESHIP_DATABASE_VERSION_OPTION' ) ) {
	define( 'MBE_ESHIP_DATABASE_VERSION_OPTION', 'mbe_eship_db_version' );
}
if ( ! defined( 'MBE_ESHIP_DATABASE_VERSION' ) ) {
	define( 'MBE_ESHIP_DATABASE_VERSION', '1.0.0' );
}

if ( ! defined( 'MBE_E_LINK_DEBUG_LOG' ) ) {
	define( 'MBE_E_LINK_DEBUG_LOG', true );
}

if ( ! defined( 'MBE_UAP_WEIGHT_LIMIT_20_KG' ) ) {
	define( 'MBE_UAP_WEIGHT_LIMIT_20_KG', 20 );
}

if ( ! defined( 'MBE_UAP_LONGEST_LIMIT_97_CM' ) ) {
	define( 'MBE_UAP_LONGEST_LIMIT_97_CM', 97 );
}

if ( ! defined( 'MBE_UAP_TOTAL_SIZE_LIMIT_300_CM' ) ) {
	define( 'MBE_UAP_TOTAL_SIZE_LIMIT_300_CM', 300 );
}

if ( ! defined( 'MBE_UAP_SERVICE' ) ) {
	define( 'MBE_UAP_SERVICE', 'MDP' );
}

if ( ! defined( 'MBE_UAP_ALLOWED_COUNTRIES_LIST' ) ) {
	define( 'MBE_UAP_ALLOWED_COUNTRIES_LIST', array( 'IT', 'FR', 'GB', 'ES', 'DE', 'PL' ) );
}

require_once( MBE_ESHIP_PLUGIN_DIR . '/lib/Helper/Data.php' );
require_once( MBE_ESHIP_PLUGIN_DIR . '/lib/Helper/Logger.php' );
//require_once(MBE_E_LINK_PLUGIN_DIR . '/lib/Helper/Tracking.php');
require_once( MBE_ESHIP_PLUGIN_DIR . '/lib/Helper/Csv.php' );
require_once( MBE_ESHIP_PLUGIN_DIR . '/lib/Helper/Rates.php' );
require_once( MBE_ESHIP_PLUGIN_DIR . '/lib/Helper/UpsUap.php' );
require_once( MBE_ESHIP_PLUGIN_DIR . '/lib/Interfaces/CsvEntityModelInterface.php' );
require_once( MBE_ESHIP_PLUGIN_DIR . '/lib/Factories/CsvEntityFactory.php' );
require_once( MBE_ESHIP_PLUGIN_DIR . '/lib/Factories/CsvEditorEntityFactory.php' );
require_once( MBE_ESHIP_PLUGIN_DIR . '/lib/Model/CsvShipping.php' );
require_once( MBE_ESHIP_PLUGIN_DIR . '/lib/Model/CsvPackage.php' );
require_once( MBE_ESHIP_PLUGIN_DIR . '/lib/Model/CsvPackageProduct.php' );

require_once( MBE_ESHIP_PLUGIN_DIR . '/lib/Mbe/MbeWs.php' );
require_once( MBE_ESHIP_PLUGIN_DIR . '/lib/Mbe/MbeSoapClient.php' );
require_once( MBE_ESHIP_PLUGIN_DIR . '/lib/Model/Ws.php' );
require_once( MBE_ESHIP_PLUGIN_DIR . '/lib/Model/Carrier.php' );
require_once( MBE_ESHIP_PLUGIN_DIR . '/lib/Exceptions/MbeExceptions.php' );
require_once( MBE_ESHIP_PLUGIN_DIR . '/backward_compatibility/array_column.php' );

if ( ! class_exists( '\Dompdf\Dompdf' ) ) {
	require_once( MBE_ESHIP_PLUGIN_DIR . '/lib/dompdf/autoload.inc.php' );
}

require_once( MBE_ESHIP_PLUGIN_DIR . '/lib/vendor/autoload.php' );

function mbe_eship_is_old_elink_plugin_active() {
	$activePlugins = array_keys( get_plugins() );
	$oldPlugin     = array_filter( $activePlugins, function ( $item ) {
		return preg_match( '/mail-boxes-etc.php/', $item );
	} );

	return is_plugin_active( array_values( $oldPlugin )[0] );
}

/**
 * Plugin activation check
 */
function mbe_eship_activation_check() {
	if ( ! class_exists( 'SoapClient' ) ) {
		deactivate_plugins( basename( __FILE__ ) );
		wp_die( 'Sorry, but you cannot run this plugin, it requires the <a href="http://php.net/manual/en/class.soapclient.php">SOAP</a> support on your server/hosting to function.' );
	}
}

/**
 * Transfer the settings from the old plugin to the new ones
 * @return void
 */
function mbe_eship_update_new_settings_check() {
	global $wpdb;
	$logger = new Mbe_Shipping_Helper_Logger();

	$schemaFlagOption          = MBE_ESHIP_ID . '_' . 'need_new_settings';
	$oldOption                 = get_option( Mbe_Shipping_Helper_Data::MBE_ELINK_SETTINGS );
	$mbeNeedUpdate             = ! ( get_option( $schemaFlagOption ) === 'no' ) && ! empty( $oldOption );
	$csv_rates_model           = new Mbe_Shipping_Model_Csv_Shipping();
	$csv_package_model         = new Mbe_Shipping_Model_Csv_Package();
	$csv_package_product_model = new Mbe_Shipping_Model_Csv_Package_Product();

	$tableExist = $csv_rates_model->tableExists() && $csv_package_model->tableExists() && $csv_package_product_model->tableExists();

	if ( '2.0.0' === MBE_ESHIP_PLUGIN_VERSION && $mbeNeedUpdate && $tableExist ) {

		$logger->log( 'Migrating from MBE e-Link --- Starting', true );
//		try {

		$logger->log( 'Migrating from MBE e-Link --- Options', true );
		foreach ( $oldOption as $key => $value ) {
			update_option( MBE_ESHIP_ID . '_' . $key, $value );
		}

		$logger->log( 'Migrating from MBE e-Link --- Login', true );
		$wsUser = $oldOption[ Mbe_Shipping_Helper_Data::XML_PATH_WS_USERNAME ];
		$wsPwd  = $oldOption[ Mbe_Shipping_Helper_Data::XML_PATH_WS_PASSWORD ];

		// Set advanced login mode
		if ( ! empty( $wsUser ) && ! empty( $wsPwd ) ) {
			update_option( MBE_ESHIP_ID . '_' . Mbe_Shipping_Helper_Data::XML_PATH_LOGIN_MODE, Mbe_Shipping_Helper_Data::MBE_LOGIN_MODE_ADVANCED );
			update_option( MBE_ESHIP_ID . '_' . Mbe_Shipping_Helper_Data::XML_PATH_LOGIN_LINK_ADV, true );
		}

		$logger->log( 'Migrating from MBE e-Link --- Configuration mode', true );
		$csvMode = $oldOption[ Mbe_Shipping_Helper_Data::XML_PATH_SHIPMENTS_CSV_MODE ];
		if ( $csvMode <> Mbe_Shipping_Helper_Data::MBE_CSV_MODE_DISABLED ) {
			update_option( MBE_ESHIP_ID . '_' . Mbe_Shipping_Helper_Data::XML_PATH_COURIER_CONFIG_MODE, Mbe_Shipping_Helper_Data::MBE_COURIER_MODE_CSV );
		} else if ( $oldOption['mbe_enable_custom_mapping'] === 'yes' ) {
			update_option( MBE_ESHIP_ID . '_' . Mbe_Shipping_Helper_Data::XML_PATH_COURIER_CONFIG_MODE, Mbe_Shipping_Helper_Data::MBE_COURIER_MODE_MAPPING );
		} else {
			update_option( MBE_ESHIP_ID . '_' . Mbe_Shipping_Helper_Data::XML_PATH_COURIER_CONFIG_MODE, Mbe_Shipping_Helper_Data::MBE_COURIER_MODE_SERVICES );
		}

		// Migrate CSV tables
		$logger->log( 'Migrating from MBE e-Link --- Csv Tables', true );
		$csvTables = [
			$csv_rates_model->getTableName()           => 'mbeshippingrate',
			$csv_package_model->getTableName()         => 'mbe_shipping_standard_packages',
			$csv_package_product_model->getTableName() => 'mbe_shipping_standard_package_product',
		];

		foreach ( $csvTables as $key => $value ) {
			$query = "INSERT INTO " . $key . " SELECT * FROM " . $wpdb->prefix . $value;
			$wpdb->query( $query );
		}

		update_option( $schemaFlagOption, 'no' );

		$logger->log( 'Migrating from MBE e-Link --- Complete', true );
//		} catch ( Exception $e ) {
		//
//		}
	} else if (empty($oldOption)) {
        $logger->log('Migrating from MBE e-Link --- Old options are missing ');
    }
}

function mbe_eship_uninstall_db_options() {
	global $wpdb;
	$csv_rates_model           = new Mbe_Shipping_Model_Csv_Shipping();
	$csv_package_model         = new Mbe_Shipping_Model_Csv_Package();
	$csv_package_product_model = new Mbe_Shipping_Model_Csv_Package_Product();

	// Delete all the tables
	$tables = [
		$csv_rates_model->getTableName(),
		$csv_package_model->getTableName(),
		$csv_package_product_model->getTableName(),
	];

	// Remove "old plugin" tables too if any
	array_push( $tables,
		$wpdb->prefix . 'mbe_shipping_standard_package_product',
		$wpdb->prefix . 'mbe_shipping_standard_packages',
		$wpdb->prefix . 'mbeshippingrate' );

	foreach ( $tables as $table ) {
		$wpdb->query( "DROP TABLE IF EXISTS $table" );
	}

	// Delete all the options
	$wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE '" . MBE_ESHIP_ID . "\_%'" );

	// Delete the "old plugin" db version option, since we are removing the tables
	delete_option( 'mbe_elink_db_version' );

}

function mbe_eship_install_db() {

	$helper         = new Mbe_Shipping_Helper_Data();
	$currentVersion = get_option( MBE_ESHIP_DATABASE_VERSION_OPTION ) ?: '0';

	if ( version_compare( $currentVersion, MBE_ESHIP_DATABASE_VERSION, '<' ) ) {
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		$dbDelta = dbDelta( get_mbe_elink_db_schema() );

		// ADD conditions to verify if the database has been correctly updated accordingly to the last schema changes
		// If only a new table is added , $updateCondition can be kept as "true" but mbe_elink_db_tables_exist() must be updated
		$updateCondition = true;

		if ( mbe_elink_db_tables_exist() && $updateCondition ) {
			update_option( MBE_ESHIP_DATABASE_VERSION_OPTION, MBE_ESHIP_DATABASE_VERSION );
		}
	}
}

function mbe_elink_db_tables_exist() {
	$csv_rates_model           = new Mbe_Shipping_Model_Csv_Shipping();
	$csv_package_model         = new Mbe_Shipping_Model_Csv_Package();
	$csv_package_product_model = new Mbe_Shipping_Model_Csv_Package_Product();

	return $csv_rates_model->tableExists()
	       && $csv_package_model->tableExists()
	       && $csv_package_product_model->tableExists();
}

function get_mbe_elink_db_schema() {
	global $wpdb;

	$helper                    = new Mbe_Shipping_Helper_Data();
	$csv_package_model         = new Mbe_Shipping_Model_Csv_Package();
	$csv_package_product_model = new Mbe_Shipping_Model_Csv_Package_Product();

	$standard_package_table         = $csv_package_model->getTableName();
	$standard_package_product_table = $csv_package_product_model->getTableName();

	$rates_table     = $helper->getShipmentCsvTable();
	$charset_collate = $wpdb->get_charset_collate();

	return "
    CREATE TABLE $standard_package_table (
        max_weight decimal(12,4) default 0 not null,
        length decimal(12,4) default 0 not null,
        width decimal(12,4) default 0 not null,
        height decimal(12,4) default 0 not null,
        package_label varchar(255) not null,
        package_code varchar(55) not null,
        id int(10) unsigned auto_increment,
        primary key  (id),
        unique key wp_mbe_shipping_standard_packages_package_code_uindex  (package_code)
    ) $charset_collate;
    CREATE TABLE $standard_package_product_table (
        custom_package tinyint(1) null ,
        single_parcel tinyint(1) null ,
        product_sku varchar(64) not null ,
        package_code varchar(50) not null ,
        id int(10) unsigned auto_increment ,
        primary key  (id),
        unique key MBE_PKG_PROD_PACKAGE_PRODUCT_UNIQUE  (package_code, product_sku),
        unique key MBE_PKG_PROD_PRODUCT_SKU  (product_sku),
        key MBE_PKG_PROD_PACKAGE_CODE  (package_code)
    ) $charset_collate;
	CREATE TABLE $rates_table (
	    id_mbeshippingrate int(10) unsigned NOT NULL AUTO_INCREMENT,
        country varchar(4) NOT NULL DEFAULT '',
        region varchar(10) NOT NULL DEFAULT '',
        city varchar(30) NOT NULL DEFAULT '',
        zip varchar(10) NOT NULL DEFAULT '',
        zip_to varchar(10) NOT NULL DEFAULT '',
        weight_from decimal(12,4) NOT NULL DEFAULT 0,
        weight_to decimal(12,4) NOT NULL DEFAULT 0,
        price decimal(12,4) DEFAULT 0,
        delivery_type varchar(255) DEFAULT '',
        PRIMARY KEY  (id_mbeshippingrate)
    ) $charset_collate;
    ";
}

function mbe_eship_update_db_check() {
	$action = $_REQUEST['action'] ?? '';
	if ( ! empty( $action ) && $action !== 'deactivate' ) {
		mbe_eship_install_db();
	}
}

register_uninstall_hook( __FILE__, 'mbe_eship_uninstall_db_options' );
register_activation_hook( __FILE__, 'mbe_eship_activation_check' );
register_activation_hook( __FILE__, 'mbe_eship_install_db' );

add_action( 'plugins_loaded', 'mbe_eship_update_db_check', 9 );
add_action( 'plugins_loaded', 'mbe_eship_update_new_settings_check', 10 );

/**
 * Check if WooCommerce is active
 */
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
	if ( ! function_exists( 'mbe_e_link_get_settings_url' ) ) {
		function mbe_e_link_get_settings_url() {
			return version_compare( WC()->version, '2.1', '>=' ) ? "wc-settings" : "woocommerce_settings";
		}
	}

	if ( ! function_exists( 'mbe_e_link_plugin_override' ) ) {
		add_action( 'plugins_loaded', 'mbe_e_link_plugin_override' );
		function mbe_e_link_plugin_override() {
			if ( ! function_exists( 'WC' ) ) {
				function WC() {
					return $GLOBALS['woocommerce'];
				}
			}
		}
	}

	if ( ! class_exists( 'mbe_e_link_wooCommerce_shipping_setup' ) ) {
		class mbe_e_link_wooCommerce_shipping_setup {
			protected $helper;

			public function __construct() {
				add_filter( 'woocommerce_get_settings_pages', array( $this, 'load_custom_settings_tab' ) );

				// Add settings link in the plugin list
				add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array(
					$this,
					'plugin_action_links'
				) );
				// Add settings tab and page in the WooCommerce => Settings => Shipping
				add_filter( 'woocommerce_shipping_methods', array( $this, 'wf_mbe_wooCommerce_shipping_methods' ) );
				add_action( 'woocommerce_shipping_init', array( $this, 'wf_mbe_wooCommerce_shipping_init' ) );

				// Action for downloading log files
				add_action( 'admin_post_mbe_download_log_files', array( $this, 'mbe_download_log_files' ) );
				add_action( 'admin_post_mbe_download_standard_package_file', array(
					$this,
					'mbe_download_standard_package_file'
				), 10, 0 );
				add_action( 'admin_post_mbe_download_shipping_file', array(
					$this,
					'mbe_download_shipping_file'
				), 10, 0 );
				add_filter( 'query_vars', array( $this, 'mbe_add_query_vars' ) );

				// Action for deleting log files
				add_action( 'admin_post_mbe_delete_log_files', array( $this, 'mbe_delete_log_files' ) );

				// Action for generating API KEY
				add_action( 'admin_post_mbe_generate_api_key', array( $this, 'mbe_generate_api_key' ) );

				//Action for new settings
				add_action( 'admin_post_mbe_sign_in', array( $this, 'mbe_sign_in' ) );
				add_action( 'admin_post_mbe_goto_advanced_login', array( $this, 'mbe_goto_advanced_login' ) );
				add_action( 'admin_post_mbe_reset_login', array( $this, 'mbe_reset_login' ) );

				// Add menu
				add_action( 'admin_menu', array( $this, 'add_mbe_tab' ) );

				// Load translations
				add_action( 'plugins_loaded', array( $this, 'wan_load_wf_shipping_mbe' ) );

				// Add UAP Field to Admin Order details view
				add_action( 'woocommerce_admin_order_data_after_billing_address', array(
					$this,
					'wf_mbe_wooCommerce_show_uap_meta_field'
				), 10, 1 );

				// Add box in the order detail
				add_action( 'init', array( $this, 'wf_mbe_wooCommerce_shipping_init_box' ) );

				$this->helper = new Mbe_Shipping_Helper_Data();
				if ( $this->helper->isEnabled() ) {
					add_action( 'closure_event', array( $this, 'automatic_closure' ) );
					add_action( 'woocommerce_settings_saved', array( $this, 'add_cron_job' ) );
					add_action( 'woocommerce_admin_updated', array( $this, 'mbe_error_notice' ) );

					//Add admin notice
					add_action( 'admin_notices', array( $this, 'mbe_shipping_admin_notices' ) );

					// Add label "Free" to Frontend if the price is 0
					add_filter( 'woocommerce_cart_shipping_method_full_label', array(
						$this,
						'wf_mbe_wooCommerce_set_free_label'
					), 10, 2 );

					// Creation shipping automatically action
					add_filter( 'woocommerce_order_status_processing', array( $this, 'mbe_update_tracking' ), 10, 2 );

					// Creation shipping manually action
					add_action( 'woocommerce_order_actions', array( $this, 'add_order_meta_box_actions' ) );
					add_action( 'woocommerce_order_action_mbe_shipping_creation', array(
						$this,
						'process_order_meta_box_actions'
					) );

					// Add frontend tracking
					add_action( 'woocommerce_order_details_after_order_table', array(
						$this,
						'woocommerce_order_details_tracking'
					) );

					// Add frontend UAP list
					add_action( 'woocommerce_after_shipping_rate', array(
						$this,
						'wf_mbe_wooCommerce_shipping_uap_list'
					) );

					// Add UAP Location ID field to the checkout fields list
					add_filter( 'woocommerce_checkout_fields', array(
						$this,
						'wf_mbe_wooCommerce_selected_uap_locationID'
					) );

					// Add UAP address field validation
					add_action( 'woocommerce_checkout_process', array(
						$this,
						'wf_mbe_wooCommerce_shipping_uap_validation'
					) );

					// Set UAP address as order's shipping address
					add_filter( 'woocommerce_checkout_posted_data', array(
						$this,
						'wf_mbe_wooCommerce_shipping_uap_address'
					) );

					// Set UAP shippiment metafield value
					add_action( 'woocommerce_checkout_update_order_meta', array(
						$this,
						'wf_mbe_wooCommerce_shipping_uap_meta_field'
					) );

					// Set Custom Mapping metafiled value
					add_action( 'woocommerce_checkout_update_order_meta', array(
						$this,
						'wf_mbe_wooCommerce_shipping_custom_mapping_meta_field'
					) );

					// Add track ID to transational emails
					add_action( 'woocommerce_email_order_details', array(
						$this,
						'mbe_woocommerce_email_track_id'
					), 20, 4 );
				}
			}

			function load_custom_settings_tab( $settings ) {
				$settings[] = include __DIR__ . '/includes/class-mbe-settings-page.php';

				return $settings;
			}

			function mbe_error_notice() {
				require_once( ABSPATH . 'wp-admin/includes/screen.php' );
				if ( is_admin() ) {
					if ( ! isset( get_current_screen()->id ) || get_current_screen()->id !== 'woocommerce_page_wc-settings' ) {
						return;
					} //only show on 'order' pages
					echo '<div class="notice notice-error"><p>Warning - If you modify template files this could cause problems with your website.</p></div>';
				}
			}

			/**
			 * Plugin cron job
			 */
			function automatic_closure() {
				$logger = new Mbe_Shipping_Helper_Logger();
				$logger->log( 'Cron automatic_closure' );
				include_once 'includes/cron.php';
			}

			function remove_cron_job() {
				wp_clear_scheduled_hook( 'closure_event' );
			}

			public function add_cron_job() {
//                $this->>helper = new Mbe_Shipping_Helper_Data();
				$time = $this->helper->getShipmentsClosureTime();
				if ( $this->helper->isEnabled() && $this->helper->isClosureAutomatically() ) {
					$cron_jobs = get_option( 'cron' );
					$array     = array_column( $cron_jobs, 'closure_event' );
					if ( ! empty( $array ) ) {
						$index         = array_search( array( 'closure_event' => $array[0] ), $cron_jobs );
						$scheduledTime = date( 'H:i:s', $index );
						if ( $scheduledTime != $time ) {
							wp_clear_scheduled_hook( 'closure_event' );
							wp_schedule_event( strtotime( "today $time" ), 'daily', 'closure_event' );
						}
					} else {
						wp_schedule_event( strtotime( "today $time" ), 'daily', 'closure_event' );
					}
				} else {
					wp_clear_scheduled_hook( 'closure_event' );
				}
			}

			/**
			 * Add settings link in the plugin list
			 */

			public function plugin_action_links( $links ) {
				$plugin_links = array(
					'<a href="' . admin_url( 'admin.php?page=' . mbe_e_link_get_settings_url() . '&tab=' . MBE_ESHIP_ID ) . '">' . __( 'Settings', 'mail-boxes-etc' ) . '</a>',
				);

				return array_merge( $plugin_links, $links );
			}

			/**
			 * Add settings tab and page in the WooCommerce => Settings => Shipping
			 */

			public function wf_mbe_wooCommerce_shipping_methods( $methods ) {
				$methods[] = 'mbe_shipping_method';

				return $methods;
			}

			public function wf_mbe_wooCommerce_shipping_init() {
				include_once( 'includes/class-mbe-shipping.php' );
			}

			/**
			 * Add box in the order detail
			 */

			public function wf_mbe_wooCommerce_shipping_init_box() {
				include_once( 'includes/class-mbe-tracking-admin.php' );
			}

			/**
			 * Add menu tab in WooCommerce
			 */

			function add_mbe_tab() {
				add_submenu_page( 'woocommerce', __( 'MBE Shipments List', 'mail-boxes-etc' ), __( 'MBE Shipments List', 'mail-boxes-etc' ), 'manage_woocommerce', 'woocommerce_mbe_tabs', array(
					$this,
					'add_order_list'
				) );

				add_submenu_page( null, __( 'MBE CSV Editor', 'mail-boxes-etc' ), __( 'MBE CSV Editor', 'mail-boxes-etc' ), 'manage_woocommerce', 'woocommerce_mbe_csv_tabs', array(
					$this,
					'add_csv_list'
				) );

				add_submenu_page( null, __( 'Add new', 'mail-boxes-etc' ), __( 'Add new', 'mail-boxes-etc' ), 'activate_plugins', MBE_ESHIP_ID . '_csv_edit_form', array(
					$this,
					'csv_form_page_handler'
				) );
			}

			/**
			 * Add order list in MBE tab
			 */

			function add_order_list() {
				require_once 'includes/class-mbe-order.php';
				$orders = new Mbe_E_Link_Order_List_Table();

				$orders->prepare_items(); ?>

                <div class="wrap">
                    <h2><?php echo __( 'MBE Shipments List', 'mail-boxes-etc' ) ?></h2>

                    <form id="certificates-filter" method="get">

                        <input type="hidden" name="page"
                               value="<?php echo ( ! empty( $_REQUEST['page'] ) ) ? esc_attr( wp_unslash( $_REQUEST['page'] ) ) : ''; ?>"
                        />
						<?php $orders->display() ?>
                    </form>
                </div>
				<?php
			}

			function add_csv_list() {
				$csvType    = $_REQUEST['csv'] ?: null;
				$csvFactory = new Mbe_Csv_Editor_Model_Factory();
				$csv        = $csvFactory->create( $csvType );
				$message    = '';
				if ( ! empty( $csv ) ) {
					$title = $csv->get_title( false );
					$csv->prepare_items();
					if ( 'delete' === $csv->current_action() ) {
						$message = '<div class="updated below-h2" id="message"><p>' . sprintf( __( 'Items deleted: %d', 'mail-boxes-etc' ), count( (array) $_REQUEST['id'] ) ) . '</p></div>';
					}
					?>

                    <div class="wrap">
                        <h2><?php echo 'MBE ' . $title . ' ' . __( 'Editor' ) ?>
                            <a class="add-new-h2"
                               href="<?php echo get_admin_url( get_current_blog_id(), 'admin.php?page=' . mbe_e_link_get_settings_url() . '&tab=' . MBE_ESHIP_ID . '&section=mbe_packages' ); ?>">
								<?php _e( 'Back to settings', 'mail-boxes-etc' ) ?>
                            </a>
                        </h2>
						<?php echo $message; ?>

                        <form id="certificates-filter" method="get">
							<?php //Fields to be sent with request and bulk actions ?>
                            <input type="hidden" name="page"
                                   value="<?php echo ( ! empty( $_REQUEST['page'] ) ) ? esc_attr( wp_unslash( $_REQUEST['page'] ) ) : ''; ?>"
                            />
                            <input type="hidden" name="csv"
                                   value="<?php echo $csvType ?>"
                            />
							<?php $csv->display() ?>
                        </form>
                    </div>
					<?php
				} else {
					?>
                    <div class="wrap">
                        <h2><?php echo __( 'Missing or wrong csv type', 'mail-boxes-etc' ) ?>
                            <a class="add-new-h2"
                               href="<?php echo get_admin_url( get_current_blog_id(), 'admin.php?page=' . mbe_e_link_get_settings_url() . '&tab=' . MBE_ESHIP_ID . '&section=mbe_packages' ); ?>">
								<?php _e( 'Back to settings', 'mail-boxes-etc' ) ?>
                            </a>
                        </h2>
                    </div>
					<?php
				}
			}

			/**
			 * Creation shipping manually action
			 */

			function add_order_meta_box_actions( $actions ) {
				if ( is_array( $actions ) ) {
//                    $this->>helper = new Mbe_Shipping_Helper_Data();
					if ( $_GET['post'] ) {
						$order = new WC_Order( (int) $_GET['post'] );
						if ( $this->helper->isMbeShipping( $order ) ) {
							if ( ! $this->helper->isCreationAutomatically() && ! $this->helper->hasTracking() ) {
								$actions['mbe_shipping_creation'] = __( 'Create MBE shipping', 'mail-boxes-etc' );
							}
						}
					}
				}

				return $actions;
			}

			function process_order_meta_box_actions( $order ) {
//                $this->>helper = new Mbe_Shipping_Helper_Data();
				if ( $this->helper->isMbeShipping( $order ) ) {
					include_once 'includes/class-mbe-tracking-factory.php';

					$orderId = $this->helper->getOrderId( $order );
					mbe_tracking_factory::create( $orderId );
				}
			}


			public function mbe_update_tracking( $order_id ) {

//                $this->>helper = new Mbe_Shipping_Helper_Data();
				$order = new WC_Order( $order_id );
				if ( $this->helper->isEnabled() && $this->helper->isMbeShipping( $order ) && $this->helper->isCreationAutomatically() && empty( $this->helper->getTrackings( $order_id ) ) ) {
					include_once 'includes/class-mbe-tracking-factory.php';
					mbe_tracking_factory::create( $order_id );
				}
			}

			/**
			 * Set Free Label to Carriers
			 */

			public function wf_mbe_wooCommerce_set_free_label( $full_label, $method ) {
				if ( (float) $method->cost == 0.0 ) {
					return $full_label . ': ' . __( "Free", 'mail-boxes-etc' );
				} else {
					return $full_label;
				}
			}

			public function wan_load_wf_shipping_mbe() {
				load_plugin_textdomain( 'mail-boxes-etc', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
			}

			/*
			 * Add front tracking
			 */

			public function woocommerce_order_details_tracking( $order ) {
//                $this->>helper = new Mbe_Shipping_Helper_Data();
				$orderId      = $this->helper->getOrderId( $order );
				$trackings    = $this->helper->getTrackings( $orderId );
				$tracking_url = get_post_meta( $orderId, 'woocommerce_mbe_tracking_url', true );
				$trackingName = $this->tracking_name = get_post_meta( $orderId, 'woocommerce_mbe_tracking_name', true );
				if ( $this->helper->isMbeShipping( $order ) && ! empty( $trackings ) ) {
					echo "<h2>" . __( 'Mail Boxes ETC Tracking', 'mail-boxes-etc' ) . "</h2>";
					echo '
					<table class="shop_table tracking_details">
						<thead>
							<tr>
								<th class="service-name">' . __( 'Service', 'mail-boxes-etc' ) . '</th>
								<th class="tracking-link">' . __( 'Tracking', 'mail-boxes-etc' ) . '</th>
							</tr>
						</thead>
						<tbody>';
					foreach ( $trackings as $track ) {
						echo '<tr class="order_item">
								<td class="tracking-name">' . $trackingName . '</td>
								<td class="product-total"><a target="_blank" href="' . $tracking_url . $track . '">' . $track . '</a></td>
							  </tr>';
					}
					echo '</tbody></table>';
				}
			}

			public function wf_mbe_wooCommerce_shipping_uap_list( $method ) {

				$mbeServiceSelected = (
					strpos( WC()->session->get( 'chosen_shipping_methods' )[0], MBE_UAP_SERVICE ) !== false
					&& $method->id === WC()->session->get( 'chosen_shipping_methods' )[0]
				);
				if ( $mbeServiceSelected && is_checkout() ) {
					$uapList = [];
					try {
						$uapList = Mbe_Shipping_Helper_Ups_Uap::getUapList( array(
							'AddressLine1'       => WC()->customer->get_shipping_address_1(),
							'PostcodePrimaryLow' => WC()->customer->get_shipping_postcode(),
							'PoliticalDivision2' => WC()->customer->get_shipping_city(),
							'PoliticalDivision1' => WC()->customer->get_shipping_state(),
							'CountryCode'        => WC()->customer->get_shipping_country(),
							'language'           => 'IT',
							'MaximumListSize'    => '20',
							'SearchRadius'       => '20',
//                            'RequestOption' => Mbe_Shipping_Helper_Ups_Uap::MBE_UPS_OPTION_UPS_ACCESS_POINT_LOCATIONS
						) );
					} catch ( Exception $e ) {
						$logger = new Mbe_Shipping_Helper_Logger();
						$logger->log( $e->getMessage() );
					}
					$uaps = [];
					foreach ( $uapList as $item ) {
						$uaps[ json_encode( $item ) ] = $item['Distance'] . ' // ' . $item['ConsigneeName'] . ' // ' . $item['AddressLine'] . ' // ' . $item['PoliticalDivision2'] . ' // ' . $item['StandardHoursOfOperation'];
					}
					echo '<div style="margin-top:1em;">';
					woocommerce_form_field( 'uap-list', array(
							'label'       => __( 'Set Shipping address to UAP', 'mail-boxes-etc' ),
							'label_class' => array( 'uap-list-label' ),
							'type'        => 'select',
							'required'    => true,
							'class'       => array(),
							'placeholder' => __( 'Select a UAP', 'mail-boxes-etc' ),
							'options'     => [ null => '' ] + $uaps,
						)
					);
					echo '</div>';

					echo '<script type="text/javascript">
                        jQuery(document).ready(function(){
                          if (typeof manageUaplist === "undefined") {
                              var manageUaplist = function () {
//                                  console.log(Date.now() + \' ready uaplist\')
                                  let uaplist = jQuery(\'#uap-list\');
                                  uaplist.selectWoo({allowClear:true})
                              }
                          }
//                          console.log(Date.now() + \' ready\')
                          manageUaplist()
                        })
                        </script>';
				}
			}

			function wf_mbe_wooCommerce_shipping_uap_validation() {
				if ( isset( $_POST['uap-list'] ) && empty( $_POST['uap-list'] ) ) {
					wc_add_notice( __( 'Set Shipping address to UAP', 'mail-boxes-etc' ), 'error' );
				}
			}

			function wf_mbe_wooCommerce_selected_uap_locationID( $fields ) {
				$fields['shipping']['shipping_uap_publicaccespointid'] = array(
					'required' => false,
					'clear'    => true,
					'type'     => 'hidden',
					'value'    => null,
				);

				return $fields;
			}

			function wf_mbe_wooCommerce_show_uap_meta_field( $order ) {
//		        $this->>helper = new Mbe_Shipping_Helper_Data();
				$order_meta_uap = get_post_meta( $this->helper->getOrderId( $order ), 'woocommerce_mbe_uap_shipment', true );
//		        if((bool)$this->helper->getOption('mbe_ship_to_UAP') && !empty($order_meta_uap)) {
				if ( ! empty( $order_meta_uap ) ) {
					echo '<p><strong>' . __( 'UAP' ) . ':</strong> ' . __( $order_meta_uap, 'mail-boxes-etc' ) . '</p>';
				}
			}

			function wf_mbe_wooCommerce_shipping_uap_meta_field( $order_id ) {
//		        if((bool)$this->helper->getOption('mbe_ship_to_UAP') && $this->helper->isMbeShipping(wc_get_order($order_id))) {
				if ( $this->helper->isMbeShipping( wc_get_order( $order_id ) ) ) {
					if ( ! empty( $_POST['shipping_uap_publicaccespointid'] ) ) {
						update_post_meta( $order_id, 'woocommerce_mbe_uap_shipment', 'Yes' );
						update_post_meta( $order_id, 'woocommerce_mbe_uap_shipment_publicaccespointId', $_POST['shipping_uap_publicaccespointid'] );
					} else {
						update_post_meta( $order_id, 'woocommerce_mbe_uap_shipment', 'No' );
					}
				}
			}

			function wf_mbe_wooCommerce_shipping_custom_mapping_meta_field( $order_id ) {
				if ( $this->helper->isMbeShippingCustomMapping( $this->helper->getShippingMethod( wc_get_order( $order_id ) ) ) ) {
					update_post_meta( $order_id, woocommerce_mbe_tracking_admin::SHIPMENT_SOURCE_TRACKING_CUSTOM_MAPPING, 'yes' );
				}
			}

			function wf_mbe_wooCommerce_shipping_uap_address( $data ) {
				// convert $data['shipping_method'] to array to avoid issues with empty (virtual products) or string values
				$shippingMethod = is_array( $data['shipping_method'] ) ? $data['shipping_method'] : [ $data['shipping_method'] ];
				$serviceOK      = preg_grep( '/^' . MBE_UAP_SERVICE . '/', $shippingMethod ) !== false;
				if ( ! empty( $_POST['uap-list'] ) && $serviceOK ) {
					$address = json_decode( stripslashes( $_POST['uap-list'] ) );
					// Set the UAP address as shipping address
					$_POST['shipping_uap_publicaccespointid'] = sanitize_text_field( $address->PublicAccesPointID );
//                        $data['shipping_uap_publicaccespointid']  = sanitize_text_field($address->PublicAccesPointID);
					$data['shipping_company']   = sanitize_text_field( $address->ConsigneeName );
					$data['shipping_address_1'] = sanitize_text_field( $address->AddressLine );
					$data['shipping_postcode']  = sanitize_text_field( $address->PostcodePrimaryLow );
					$data['shipping_city']      = sanitize_text_field( $address->PoliticalDivision2 );
					$data['shipping_country']   = sanitize_text_field( $address->CountryCode );
					$data['shipping_state']     = array_search( ucfirst( strtolower( sanitize_text_field( $address->PoliticalDivision1 ) ) ), WC()->countries->get_states( sanitize_text_field( $address->CountryCode ) ) );
				}

//				}
				return $data;
			}

			function mbe_woocommerce_email_track_id( $order, $sent_to_admin, $plain_text, $email ) {
//			    $this->>helper = new Mbe_Shipping_Helper_Data();
				//new_order, customer_on_hold_order, customer_processing_order
				$mailArray   = [ 'customer_invoice', 'customer_completed_order' ];
				$trackingUrl = get_post_meta( $order->get_id(), woocommerce_mbe_tracking_admin::SHIPMENT_SOURCE_TRACKING_URL, true );
				$trackId     = $this->helper->getTrackingsString( $order->get_id() );
				if ( ! empty( $trackId ) && in_array( $email->id, $mailArray ) && $this->helper->getTrackingSetting() ) {
					$htmlouput = '
                        <table id="track_id" cellspacing="0" cellpadding="0" border="0" style="width: 100%; vertical-align: top; margin-bottom: 40px; padding: 0;">
                            <tbody>
                                <tr>
                                    <td valign="top" width="40%" style="text-align: left; font-family: \'Helvetica Neue\', Helvetica, Roboto, Arial, sans-serif; border: 0; padding: 0;">
                                        <h2 style="color: #96588a; display: block; font-family: &quot;Helvetica Neue&quot;, Helvetica, Roboto, Arial, sans-serif; font-size: 18px; font-weight: bold; line-height: 130%; margin: 0 0 18px; text-align: left;"> ' . __( woocommerce_mbe_tracking_admin::TRACKING_TITLE_DISPLAY, 'mail-boxes-etc' ) . ' </h2>        
                                    </td>
                                </tr>
                                <tr>
                                    <td valign="top" width="40%" style="text-align: left; font-family: \'Helvetica Neue\', Helvetica, Roboto, Arial, sans-serif; border: 0; padding: 0;">
                                        <strong>' . __( 'Tracking id: ', 'mail-boxes-etc' ) . '</strong>
                                    </td>
                                    <td valign="top" width="40%" style="text-align: left; font-family: \'Helvetica Neue\', Helvetica, Roboto, Arial, sans-serif; border: 0; padding: 0;">
                                        <a href="' . esc_url_raw( $trackingUrl . $trackId ) . '">
                                            ' . sanitize_text_field( $trackId ) . '
                                        </a>
                                    </td>
                                </tr>
                            </tbody>
                         </table>';
					echo $htmlouput;
				}
			}

			function mbe_add_query_vars( $vars ) {
				$vars[] = "mbe_filetype";

				return $vars;
			}

			public function mbe_download_file( $filePath, $fileName = '', $fileType = 'text/csv', $deleteAfter = false ) {
				$fileNamefromPath = [];
				if ( $fileName === '' && preg_match( '/(?P<filename>[\w\-. ]+)$/', $filePath, $fileNamefromPath ) ) {
					$fileName = $fileNamefromPath['filename'] ?? 'mbe_download_file.csv';
				}
				try {
					if ( is_file( $filePath ) ) {
						header( 'Content-Description: File Transfer' );
						header( 'Content-Type: ' . $fileType );
						header( "Content-Disposition: attachment; filename=" . $fileName );
						header( 'Content-Transfer-Encoding: binary' );
						header( 'Expires: 0' );
						header( 'Cache-Control: must-revalidate, post-check=0, pre-check=0' );
						header( 'Pragma: public' );
						header( 'Content-Length: ' . filesize( $filePath ) );
						ob_clean();
						flush();
						readfile( $filePath );
						if ( $deleteAfter ) {
							wp_delete_file( $filePath );
						}
						exit;
					}
					error_log( __( 'MBE Download file - files not found' ) );
				} catch ( \Exception $e ) {
					error_log( __( 'MBE Download file - Unexpected error' ) . ' - ' . $e->getMessage() );
				}
				exit;
			}

			public function mbe_download_standard_package_file() {
				$fileType = sanitize_text_field( $_GET['mbe_filetype'] );

				switch ( $fileType ) {
					case 'package':
						$this->mbe_download_file( $this->helper->getCurrentCsvPackagesDir() );
						break;
					case 'package-template':
						$this->mbe_download_file( $this->helper->getCsvTemplatePackagesDir() );
						break;
					case 'package-product':
						$this->mbe_download_file( $this->helper->getCurrentCsvPackagesProductDir() );
						break;
					case 'package-product-template':
						$this->mbe_download_file( $this->helper->getCsvTemplatePackagesProductDir() );
						break;
					default:
						break;
				}
			}

			public function mbe_download_shipping_file() {
				$fileType = sanitize_text_field( $_GET['mbe_filetype'] );

				switch ( $fileType ) {
					case 'shipping':
						$this->mbe_download_file( $this->helper->getShipmentsCsvFileDir() );
						break;
					case 'shipping-template':
						$this->mbe_download_file( $this->helper->getShipmentsCsvTemplateFileDir() );
						break;
					default:
						break;
				}
			}

			public function mbe_download_log_files() {
				$zipfilepath    = MBE_ESHIP_PLUGIN_LOG_DIR . DIRECTORY_SEPARATOR . 'log.zip';
				$wslogfilepath  = $this->helper->getLogWsPath();
				$pluginfilepath = $this->helper->getLogPluginPath();
				$logZip         = new \ZipArchive();
				if ( $logZip->open( $zipfilepath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE ) === true ) {
					$logZip->addFile( $wslogfilepath, substr( $wslogfilepath, strrpos( $wslogfilepath, '/' ) + 1 ) );
					$logZip->addFile( $pluginfilepath, substr( $pluginfilepath, strrpos( $pluginfilepath, '/' ) + 1 ) );
					if ( $logZip->count() > 0 && $logZip->close() ) {
						$this->mbe_download_file( $zipfilepath, 'mbe_log.zip', 'application/zip', true );
					}
				}
				$logZip = null;
				error_log( __( 'MBE Download log - files not found' ) );
				exit;
			}


			public function mbe_delete_log_files() {
				try {
					$wslogfilepath  = $this->helper->getLogWsPath();
					$pluginfilepath = $this->helper->getLogPluginPath();
					if ( file_exists( $wslogfilepath ) ) {
						wp_delete_file( $wslogfilepath );
					}
					if ( file_exists( $pluginfilepath ) ) {
						wp_delete_file( $pluginfilepath );
					}
				} catch ( \Exception $e ) {
					error_log( __( 'MBE Delete log - Unexpected error' ) . ' - ' . $e->getMessage() );
				}
				wp_redirect( wp_get_referer() );
			}


			public function mbe_goto_advanced_login() {
				$this->helper->setLoginMode( false );
				$this->helper->setOption( Mbe_Shipping_Helper_Data::XML_PATH_LOGIN_LINK_ADV, true );
				wp_redirect( wp_get_referer() );
			}

			public function mbe_sign_in() {
				$mbeUser    = $_GET[ MBE_ESHIP_ID . '_' . Mbe_Shipping_Helper_Data::XML_PATH_MBE_USERNAME ];
				$mbePwd     = $_GET[ MBE_ESHIP_ID . '_' . Mbe_Shipping_Helper_Data::XML_PATH_MBE_PASSWORD ];
				$mbeCountry = $_GET[ MBE_ESHIP_ID . '_' . Mbe_Shipping_Helper_Data::XML_PATH_COUNTRY ];
				$this->helper->setOption( Mbe_Shipping_Helper_Data::XML_PATH_LOGIN_LINK_ADV, false );

				if ( ! empty( $mbePwd ) && ! empty( $mbeUser ) ) {
					// Set the option in case it wasn't saved before
					$this->helper->setOption( Mbe_Shipping_Helper_Data::XML_PATH_MBE_USERNAME, $mbeUser );
					$this->helper->setOption( Mbe_Shipping_Helper_Data::XML_PATH_MBE_PASSWORD, $mbePwd );
					$this->helper->setOption( Mbe_Shipping_Helper_Data::XML_PATH_COUNTRY, $mbeCountry );
					$this->helper->setWsUrl( $mbeCountry );

					if ( $this->mbe_generate_api_key() ) {
						// set advanced
						$this->helper->setLoginMode( false );
						update_option( 'mbe_shipping_admin_messages', [
							'message' => urlencode( __( 'Logged in', 'mail-boxes-etc' ) ),
							'status'  => urlencode( 'success' )
						] );
					} else {
						update_option( 'mbe_shipping_admin_messages', [
							'message' => urlencode( __( 'Error Logging in. Please enable debug and check the log files for more details', 'mail-boxes-etc' ) ),
							'status'  => urlencode( 'error' )
						] );
					}
				} else {
					update_option( 'mbe_shipping_admin_messages', [
						'message' => urlencode( __( 'Missing MBE Online Login Information', 'mail-boxes-etc' ) ),
						'status'  => urlencode( 'error' )
					] );
				}

				wp_redirect( wp_get_referer() );
			}

			public function mbe_reset_login() {
				$this->helper->setLoginMode();
				$this->helper->setOption( Mbe_Shipping_Helper_Data::XML_PATH_WS_USERNAME, '' );
				$this->helper->setOption( Mbe_Shipping_Helper_Data::XML_PATH_WS_PASSWORD, '' );
				$this->helper->setOption( Mbe_Shipping_Helper_Data::XML_PATH_WS_URL, '' );
				wp_redirect( wp_get_referer() );
			}

			public function mbe_generate_api_key() {
				$logger = new Mbe_Shipping_Helper_Logger();
				$logger->log( 'MBE Generate API KEY - Start' );
				$status = 'success';
				try {
					$ws       = new MbeWs( true );
					$response = $ws->generateApiKey( $this->helper->getMbeUsername(), $this->helper->getMbePassword() );
					if ( ! empty( $response ) ) {
						$this->helper->setWsUsername( $response->apiKey );
						$this->helper->setWsPassword( $response->apiSecret );
						$logger->log( 'MBE Generate API KEY - Done' );
						$message = __( 'eShip credentials generated correctly', 'mail-boxes-etc' );
					} else {
						$logger->log( 'MBE Generate API KEY - Empty response' );
						$message = __( 'eShip credentials not generated - Empty response', 'mail-boxes-etc' );
						$status  = 'error';
					}

				} catch ( \Exception $e ) {
					$logger->log( __( 'MBE Generate API KEY' ) . ' - ' . $e->getMessage() );
					$message = __( 'eShip credentials not generated. Check the log files for more details', 'mail-boxes-etc' );
					$status  = 'error';
				}
				update_option( 'mbe_shipping_admin_messages', [
					'message' => urlencode( $message ),
					'status'  => urlencode( $status )
				] );

				if ( $status === 'success' ) {
					return true;
				} else {
					return false;
				}
//	            wp_redirect(wp_get_referer());

			}

			public function mbe_shipping_admin_notices() {
				$apiKeyMessage = maybe_unserialize( get_option( 'mbe_shipping_admin_messages' ) );
				if ( ! empty( $apiKeyMessage ) ) {
					$messageStatus = urldecode( $apiKeyMessage['status'] );
					$messageText   = urldecode( $apiKeyMessage['message'] );
					if ( $messageStatus === 'error' ) {
						WC_Admin_Settings::add_error( $messageText );
					} else {
						WC_Admin_Settings::add_message( $messageText );
					}
				}
				delete_option( 'mbe_shipping_admin_messages' );
			}

			function csv_form_page_handler() {
				global $wpdb;

				$csvType    = $_REQUEST['csv'] ?: null;
				$csvFactory = new Mbe_Csv_Editor_Model_Factory();
				$csv        = $csvFactory->create( $csvType );
				if ( ! empty( $csv ) ) {
					$message = '';
					$notice  = '';

					// default $item data to be used for new records
					$default = $csv->get_defaults();

					$table_name = $csv->get_tablename();

					// check post back and correct nonce
					if ( isset( $_REQUEST['nonce'] ) && wp_verify_nonce( $_REQUEST['nonce'], basename( __FILE__ ) ) ) {
						// combine default and request params
						$item = shortcode_atts( $default, $_REQUEST );
						// data validation
						$item_valid = $csv->validate_row( $item );
						try {
							if ( $item_valid === true ) {
								if ( $item['id'] == 0 ) {
									$result     = $wpdb->insert( $table_name, $item );
									$item['id'] = $wpdb->insert_id;
									if ( $result !== false ) {
										$message = __( 'Item successfully saved', 'mail-boxes-etc' );
									} else {
										$notice = __( 'There was an error while saving the item', 'mail-boxes-etc' ) . ': ' . $wpdb->last_error;
									}
								} else {
									$result = $wpdb->update( $table_name, $item, array( 'id' => $item['id'] ) );
									if ( $result !== false ) {
										$message = __( 'Item successfully updated', 'mail-boxes-etc' );
									} else {
										$notice = __( 'There was an error while updating the item', 'mail-boxes-etc' ) . ': ' . $wpdb->last_error;
									}
								}
							} else {
								$notice = $item_valid;
							}
						} catch ( Exception $e ) {
							$notice = $e->getMessage();
						}
					} else {
						$item = $default;
						if ( isset( $_REQUEST['id'] ) ) {
							$item = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_name WHERE id = %d", $_REQUEST['id'] ), ARRAY_A );
							if ( ! $item ) {
								$item = $default;
//								$notice = __( 'Item not found', 'mail-boxes-etc' );
							}
						}
					}
					// custom meta box is a method of $csv class
					add_meta_box( MBE_ESHIP_ID . '_csv_form_meta_box', $csv->get_title(),
						array( $csv, 'form_meta_box_handler' ),
						'csv-' . $csvType, 'normal', 'default' );
					?>

                    <div class="wrap">
                        <div class="icon32 icon32-posts-post" id="icon-edit"><br></div>
                        <h2><?php echo __( 'Edit' ) . ' ' . $csv->get_title( false ) ?>
                            <a class="add-new-h2"
                               href="<?php echo get_admin_url( get_current_blog_id(), 'admin.php?page=woocommerce_mbe_csv_tabs&csv=' . $csvType ); ?>">
								<?php _e( 'Back to list', 'mail-boxes-etc' ) ?>
                            </a>
                        </h2>

						<?php if ( ! empty( $notice ) ): ?>
                            <div id="notice" class="error"><p><?php echo $notice ?></p></div>
						<?php endif; ?>
						<?php if ( ! empty( $message ) ): ?>
                            <div id="message" class="updated"><p><?php echo $message ?></p></div>
						<?php endif; ?>

                        <form id="form" method="POST">
                            <input type="hidden" name="nonce"
                                   value="<?php echo wp_create_nonce( basename( __FILE__ ) ) ?>"/>
							<?php /* storing id to check if we need to add or update the item */ ?>
                            <input type="hidden" name="id" value="<?php echo $item['id'] ?>"/>

                            <div class="metabox-holder" id="poststuff">
                                <div id="post-body">
                                    <div id="post-body-content">
										<?php /* render meta box */ ?>
										<?php do_meta_boxes( 'csv-' . $csvType, 'normal', $item ); ?>
                                        <input type="submit" value="<?php _e( 'Save', 'mail-boxes-etc' ) ?>" id="submit"
                                               class="button-primary" name="submit">
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
					<?php
				} else {
					?>
                    <div class="wrap">
                        <h2><?php echo __( 'Missing or wrong csv type', 'mail-boxes-etc' ) ?>
                            <a class="add-new-h2"
                               href="<?php echo get_admin_url( get_current_blog_id(), 'admin.php?page=' . mbe_e_link_get_settings_url() . '&tab=' . MBE_ESHIP_ID . '&section=mbe_packages' ); ?>">
								<?php _e( 'Back to settings', 'mail-boxes-etc' ) ?>
                            </a>
                        </h2>
                    </div>
					<?php
				}
			}
		}
	}
	new mbe_e_link_wooCommerce_shipping_setup();

}