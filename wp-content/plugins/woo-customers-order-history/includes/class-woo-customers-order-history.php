<?php

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Woo_Customers_Order_History
 * @subpackage Woo_Customers_Order_History/includes
 * @author     javmah <jaedmah@gmail.com>
 */
class Woo_Customers_Order_History {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'WOO_CUSTOMERS_ORDER_HISTORY_VERSION' ) ) {
			$this->version = WOO_CUSTOMERS_ORDER_HISTORY_VERSION;
		} else {
			$this->version = '5.2.1';
		}
		$this->plugin_name = 'woo-customers-order-history';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Woo_Customers_Order_History_Loader. Orchestrates the hooks of the plugin.
	 * - Woo_Customers_Order_History_i18n. Defines internationalization functionality.
	 * - Woo_Customers_Order_History_Admin. Defines all hooks for the admin area.
	 * - Woo_Customers_Order_History_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {
		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-woo-customers-order-history-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-woo-customers-order-history-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-woo-customers-order-history-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-woo-customers-order-history-public.php';

		$this->loader = new Woo_Customers_Order_History_Loader();
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Woo_Customers_Order_History_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {
		$plugin_i18n = new Woo_Customers_Order_History_i18n();
		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );
	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {
		$plugin_admin = new Woo_Customers_Order_History_Admin( $this->get_plugin_name(), $this->get_version() );
		# Hooks
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
		# Menus
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'wcchpo_plugin_options_create_menu' );
		# Admin Notice
		$this->loader->add_action( 'admin_notices', $plugin_admin, 'wcchpo_admin_notice' );
		# Adding a Column On The Order Page
		$this->loader->add_action( 'manage_edit-shop_order_columns', $plugin_admin, 'wcchpo_Order_table_Previous_Orders_columns',20 );
		$this->loader->add_action( 'manage_posts_custom_column', $plugin_admin, 'wcchpo_Order_table_Previous_Orders_columns_content_row',20,2);
		# Adding New Column On Users Table
		$this->loader->add_action( 'manage_users_columns', $plugin_admin, 'wcchpo_User_table_order_history_column' );
		$this->loader->add_action( 'manage_users_custom_column', $plugin_admin, 'wcchpo_User_table_order_history_column_rows', 10,3 );
		# Sortable
		$this->loader->add_action( 'manage_users_sortable_columns', $plugin_admin, 'wcchpo_User_table_order_history_column_add_sortable' );
		$this->loader->add_action( 'pre_get_users', $plugin_admin, 'wcchpo_User_table_order_history_column_add_sortable_query' );
		# call register settings function
		$this->loader->add_action( 'admin_init', $plugin_admin, 'register_wcchpo_plugin_settings' );
		# Adding a Meta Box On order edit Page showing Previous Orders on Number  in Circle
		$this->loader->add_action( 'add_meta_boxes', $plugin_admin, 'adding_custom_meta_boxes_wcchpo' );
		# Admin Footer 
		$this->loader->add_action( 'admin_footer', $plugin_admin, 'wcchpo_admin_footer' );
		# Admin Ajax 
		$this->loader->add_action( 'wp_ajax_wcchpo_ajax', $plugin_admin, 'wcchpo_ajax' );
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Woo_Customers_Order_History_Public( $this->get_plugin_name(), $this->get_version() );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Woo_Customers_Order_History_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}
}
