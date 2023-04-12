<?php
/**
 * Plugin Name: WooCommerce Free Shipping Bar Premium
 * Plugin URI: https://villatheme.com/extensions/woocommerce-free-shipping-bar/
 * Description: Display the total amounts of customer to reach minimum order amount Free Shipping system.
 * Version: 1.1.13
 * Author: VillaTheme
 * Author URI: https://villatheme.com
 * Text Domain: woocommerce-free-shipping-bar
 * Copyright 2017-2022 VillaTheme.com. All rights reserved.
 * Requires at least: 5.0
 * Tested up to: 5.9
 * WC requires at least: 4.0
 * WC tested up to: 6.3.0
 * Requires PHP: 7.0
 */

define( 'WFSPB_VERSION', '1.1.13' );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WFSPB_Shipping' ) ) {
	include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

	if ( is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
		define( 'WFSPB_SHIPPING_DIR', WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . 'woocommerce-free-shipping-bar' . DIRECTORY_SEPARATOR );
		define( 'WFSPB_SHIPPING_LANGUAGES_DIR', WFSPB_SHIPPING_DIR . 'languages' . DIRECTORY_SEPARATOR );

		$wfspb_plugin_url = plugins_url( 'woocommerce-free-shipping-bar' );

		define( 'WFSPB_SHIPPING_CSS', $wfspb_plugin_url . '/assets/css/' );
		define( 'WFSPB_SHIPPING_JS', $wfspb_plugin_url . '/assets/js/' );
		define( 'WFSPB_SHIPPING_IMAGES', $wfspb_plugin_url . '/assets/images/' );

	} else {
		return;
	}

	/**
	 * Class WFSPB_Shipping
	 */
	class WFSPB_Shipping {
		private $settings;

		public function __construct() {
			register_activation_hook( __FILE__, array( $this, 'activate' ) );
			register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );

			if ( ! is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
				return;
			}

			if ( is_file( WFSPB_SHIPPING_DIR . "admin-system.php" ) ) {
				require_once WFSPB_SHIPPING_DIR . "includes/data.php";
				$this->settings = new WFSPB_Data();
			}

			if ( is_file( WFSPB_SHIPPING_DIR . "wfspb-front-end.php" ) ) {
				require_once WFSPB_SHIPPING_DIR . "wfspb-front-end.php";
			}

			if ( is_file( WFSPB_SHIPPING_DIR . "shortcode.php" ) ) {
				require_once WFSPB_SHIPPING_DIR . "shortcode.php";
			}

			if ( is_file( WFSPB_SHIPPING_DIR . "admin-system.php" ) ) {
				require_once WFSPB_SHIPPING_DIR . "admin-system.php";
			}

			if ( is_file( WFSPB_SHIPPING_DIR . "includes/mobile_detect.php" ) ) {
				require_once WFSPB_SHIPPING_DIR . "includes/mobile_detect.php";
			}
			if ( is_file( WFSPB_SHIPPING_DIR . "includes/update.php" ) ) {
				require_once WFSPB_SHIPPING_DIR . "includes/update.php";
			}
			if ( is_file( WFSPB_SHIPPING_DIR . "includes/check_update.php" ) ) {
				require_once WFSPB_SHIPPING_DIR . "includes/check_update.php";
			}
			if ( is_file( WFSPB_SHIPPING_DIR . "includes/support.php" ) ) {
				require_once WFSPB_SHIPPING_DIR . "includes/support.php";
			}
			if ( is_file( WFSPB_SHIPPING_DIR . "includes/image-field.php" ) ) {
				require_once WFSPB_SHIPPING_DIR . "includes/image-field.php";
			}

			add_action( 'init', array( $this, 'init' ) );

			add_filter( 'plugin_action_links_woocommerce-free-shipping-bar/woocommerce-free-shipping-bar.php', array(
				$this,
				'settings_link'
			), 9 );
			add_action( 'admin_notices', array( $this, 'notification' ) );
			add_action( 'admin_menu', array( $this, 'save_data' ), 1 );
			add_action( 'admin_menu', array( $this, 'create_options_page' ), 10 );
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_script' ) );

			//inline script ajax
			add_action( 'wp_ajax_wfspb_added_to_cart', array( $this, 'get_data_atc' ) );
			add_action( 'wp_ajax_nopriv_wfspb_added_to_cart', array( $this, 'get_data_atc' ) );

			add_action( 'wp_ajax_wfspb_get_min_amount', array( $this, 'get_min_amount_updated_cart_totals' ) );
			add_action( 'wp_ajax_nopriv_wfspb_get_min_amount', array( $this, 'get_min_amount_updated_cart_totals' ) );

			add_filter( 'woocommerce_add_to_cart_fragments', array( $this, 'get_refresh_fragment' ) );

			/*Check update*/
			add_action( 'admin_menu', array( $this, 'admin_init' ), 20 );
		}

		/**
		 * Function init when run plugin+
		 */
		function init() {
			/*Register post type*/
			load_plugin_textdomain( 'woocommerce-free-shipping-bar' );
			$this->load_plugin_textdomain();
		}

		/**
		 * load Language translate
		 */
		public function load_plugin_textdomain() {
			$locale = apply_filters( 'plugin_locale', get_locale(), 'woocommerce-free-shipping-bar' );
			// Admin Locale
			if ( is_admin() ) {
				load_textdomain( 'woocommerce-free-shipping-bar', WFSPB_SHIPPING_LANGUAGES_DIR . "woocommerce-free-shipping-bar-$locale.mo" );
			}

			// Global + Frontend Locale
			load_textdomain( 'woocommerce-free-shipping-bar', WFSPB_SHIPPING_LANGUAGES_DIR . "woocommerce-free-shipping-bar-$locale.mo" );
			load_plugin_textdomain( 'woocommerce-free-shipping-bar', false, WFSPB_SHIPPING_LANGUAGES_DIR );
		}

		// Notification when activate plugin
		public function notification() {
			if ( ! is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
				deactivate_plugins( 'woocommerce-free-shipping-bar/woocommerce-free-shipping-bar.php' );
				unset( $_GET['activate'] ); ?>

                <div id="message" class="error">
                    <p><?php esc_html_e( 'Please install WooCommerce and active to use Woocommerce Free Shipping Bar plugin !', 'woocommerce-free-shipping-bar' ); ?></p>
                </div>

				<?php
			}
		}

		//	When activate plugin
		public function activate() {
			global $wp_version;
			if ( version_compare( $wp_version, '2.9', '<' ) ) {
				deactivate_plugins( plugin_basename( __FILE__ ) );
				flush_rewrite_rules();
				wp_die( "This plugin requires WordPress version 2.9 or higher." );

			}
			if ( ! get_option( 'wfspb-param', 0 ) ) {
				update_option( 'wfspb-param', unserialize( 'a:23:{s:12:"default-zone";s:1:"6";s:15:"ipfind_auth_key";s:0:"";s:13:"detect-mobile";s:1:"1";s:8:"bg-color";s:16:"rgb(32, 98, 150)";s:10:"text-color";s:7:"#FFFFFF";s:10:"link-color";s:7:"#77B508";s:4:"font";s:7:"PT Sans";s:9:"font-size";s:2:"16";s:10:"text-align";s:6:"center";s:17:"bg-color-progress";s:7:"#C9CFD4";s:19:"bg-current-progress";s:7:"#0D47A1";s:19:"progress-text-color";s:7:"#FFFFFF";s:18:"font-size-progress";s:2:"11";s:5:"style";s:1:"1";s:8:"position";s:1:"0";s:15:"announce-system";s:43:"Free shipping for billing over {min_amount}";s:17:"message-purchased";s:50:"You have purchased {total_amounts} of {min_amount}";s:15:"message-success";s:65:"Congratulation! You have got free shipping. Go to {checkout_page}";s:13:"message-error";s:74:"You are missing {missing_amount} to get Free Shipping. Continue {shopping}";s:13:"initial-delay";s:1:"5";s:13:"close-message";s:1:"1";s:18:"set-time-disappear";s:1:"5";s:16:"conditional-tags";s:0:"";}' ) );
			}
		}

		//	When deactivate plugin
		public function deactivate() {
			flush_rewrite_rules();
		}

		// link setting page on install plugin
		public function settings_link( $links ) {
			$settings_link = '<a href="admin.php?page=woocommerce_free_ship" title="' . esc_html__( 'Settings', 'woocommerce-free-shipping-bar' ) . '">' . esc_html__( 'Settings', 'woocommerce-free-shipping-bar' ) . '</a>';
			array_unshift( $links, $settings_link );

			return $links;
		}

		//	enqueue script
		public function admin_enqueue_script() {
			$page = isset( $_REQUEST['page'] ) ? $_REQUEST['page'] : '';
			if ( $page == 'woocommerce_free_ship' ) {
				global $wp_scripts;
				$scripts = $wp_scripts->registered;
				//			print_r($scripts);
				foreach ( $scripts as $k => $script ) {
					preg_match( '/^\/wp-/i', $script->src, $result );
					if ( count( array_filter( $result ) ) < 1 && $script->handle != 'query-monitor' ) {
						wp_dequeue_script( $script->handle );
					}
				}
				wp_enqueue_style( 'woocommerce-free-shipping-bar-menu', WFSPB_SHIPPING_CSS . 'menu.min.css' );
				wp_enqueue_style( 'woocommerce-free-shipping-bar-button', WFSPB_SHIPPING_CSS . 'button.min.css' );
				wp_enqueue_style( 'woocommerce-free-shipping-bar-segment', WFSPB_SHIPPING_CSS . 'segment.min.css' );
				wp_enqueue_style( 'woocommerce-free-shipping-bar-tab', WFSPB_SHIPPING_CSS . 'tab.css' );
				wp_enqueue_style( 'woocommerce-free-shipping-bar-form', WFSPB_SHIPPING_CSS . 'form.min.css' );
				wp_enqueue_style( 'woocommerce-free-shipping-bar-dropdown', WFSPB_SHIPPING_CSS . 'dropdown.min.css' );
				wp_enqueue_style( 'woocommerce-free-shipping-bar-checkbox', WFSPB_SHIPPING_CSS . 'checkbox.min.css' );
				wp_enqueue_style( 'woocommerce-free-shipping-bar-icon', WFSPB_SHIPPING_CSS . 'icon.min.css' );
				wp_enqueue_style( 'woocommerce-free-shipping-font-select', WFSPB_SHIPPING_CSS . 'fontselect.css' );
				wp_enqueue_style( 'woocommerce-free-shipping-font-transition', WFSPB_SHIPPING_CSS . 'transition.min.css' );
				wp_enqueue_style( 'woocommerce-free-shipping-bar-style', WFSPB_SHIPPING_CSS . 'woocommerce-free-shipping-bar-admin-style.css', array(), WFSPB_VERSION );

				//wp_enqueue_script( 'jquery' );
				wp_enqueue_media();
				wp_enqueue_script( 'woocommerce-free-shipping-bar-dependsOn', WFSPB_SHIPPING_JS . 'dependsOn-1.0.2.min.js', array( 'jquery' ) );
				wp_enqueue_script( 'woocommerce-free-shipping-bar-tab', WFSPB_SHIPPING_JS . 'tab.js', array( 'jquery' ) );
				wp_enqueue_script( 'woocommerce-free-shipping-bar-form', WFSPB_SHIPPING_JS . 'form.js', array( 'jquery' ) );
				wp_enqueue_script( 'woocommerce-free-shipping-bar-dropdown', WFSPB_SHIPPING_JS . 'dropdown.js', array( 'jquery' ) );
				wp_enqueue_script( 'woocommerce-free-shipping-bar-checkbox', WFSPB_SHIPPING_JS . 'checkbox.js', array( 'jquery' ) );
				wp_enqueue_script( 'woocommerce-free-shipping-bar-font-select', WFSPB_SHIPPING_JS . 'jquery.fontselect.min.js', array( 'jquery' ) );
				wp_enqueue_script( 'woocommerce-free-shipping-bar-transition', WFSPB_SHIPPING_JS . 'transition.min.js', array( 'jquery' ) );
				wp_enqueue_script( 'woocommerce-free-shipping-bar-jqColorPicker', WFSPB_SHIPPING_JS . 'jqColorPicker.min.js', array( 'jquery' ) );
				wp_enqueue_script( 'woocommerce-free-shipping-bar-address', WFSPB_SHIPPING_JS . 'jquery.address-1.6.min.js', array( 'jquery' ) );
				wp_enqueue_script( 'woocommerce-free-shipping-bar-media-field', WFSPB_SHIPPING_JS . 'media-field.js', array( 'jquery' ) );
				wp_enqueue_script( 'woocommerce-free-shipping-bar-admin', WFSPB_SHIPPING_JS . 'woocommerce-free-shipping-bar-admin.js', array( 'jquery' ), WFSPB_VERSION );

				//inline style Style tab
				$bg_color            = $this->get_field( 'bg-color' );
				$text_color          = $this->get_field( 'text-color' );
				$link_color          = $this->get_field( 'link-color' );
				$text_align          = $this->get_field( 'text-align' );
				$font                = $this->get_field( 'font' );
				$font_size           = $this->get_field( 'font-size' );
				$font_family         = str_replace( '+', ' ', $font );
				$enable_progress     = $this->get_field( 'enable-progress' );
				$bg_progress         = $this->get_field( 'bg-color-progress' );
				$bg_current_progress = $this->get_field( 'bg-current-progress' );
				$progress_text_color = $this->get_field( 'progress-text-color' );
				$fontsize_progress   = $this->get_field( 'font-size-progress' );

				$custom_css = "
					#wfspb-top-bar{
						background-color: {$bg_color};
						color: {$text_color};
						font-family: {$font_family};
					} 
					#wfspb-top-bar #wfspb-main-content{
						font-size: {$font_size}px;
						text-align: {$text_align};
					}
					div#wfspb-close{
						font-size: {$font_size}px;
						line-height: {$font_size}px;
					}
					#wfspb-top-bar #wfspb-main-content > a{
						color: {$link_color};
					}";

				if ( $enable_progress ) {
					$custom_css .= "
					#wfspb-progress{
						background-color: {$bg_progress};
						display: block !important;
					}
					#wfspb-current-progress{
						background-color: {$bg_current_progress};
					}
					#wfspb-label{
						color: {$progress_text_color};
						font-size: {$fontsize_progress}px;
					}
				";
				}

				wp_add_inline_style( 'woocommerce-free-shipping-bar-style', $custom_css );
			}
		}

		public static function set_field( $field, $multi = false ) {
			if ( $field ) {
				if ( $multi ) {
					return 'wfspb-param[' . $field . '][]';
				} else {
					return 'wfspb-param[' . $field . ']';
				}

			} else {
				return '';
			}
		}

		public static function get_field( $field, $default = '' ) {
			$params = get_option( 'wfspb-param', array() );
			if ( isset( $params[ $field ] ) && $params[ $field ] ) {
				return $params[ $field ];
			} else {
				return $default;
			}
		}

		public function get_message_field( $field ) {
			$params  = $this->settings->get_params();
			$find    = $this->settings->deprecated( $field );
			$message = '';
			if ( isset( $params[ $field ] ) ) {
				$message = $params[ $field ];
			}
			if ( isset( $params[ $find['field'] ][ $find['lang'] ] ) ) {
				$message = $params[ $find['field'] ][ $find['lang'] ];
			}

			return stripslashes( $message );
		}

		/**
		 * Check update
		 */
		public function admin_init() {
			$key = self::get_field( 'key' );
			if ( class_exists( 'VillaTheme_Plugin_Check_Update' ) ) {
				new VillaTheme_Plugin_Check_Update (
					WFSPB_VERSION,                    // current version
					'https://villatheme.com/wp-json/downloads/v3',  // update path
					'woocommerce-free-shipping-bar/woocommerce-free-shipping-bar.php',                  // plugin file slug
					'woocommerce-free-shipping-bar', '7339', $key
				);
				$setting_url = admin_url( 'admin.php?page=woocommerce_free_ship' );

				new VillaTheme_Plugin_Updater( 'woocommerce-free-shipping-bar/woocommerce-free-shipping-bar.php', 'woocommerce-free-shipping-bar', $setting_url );
			}

			if ( class_exists( 'VillaTheme_Support_Pro' ) ) {
				new VillaTheme_Support_Pro(
					array(
						'support'   => 'https://villatheme.com/supports/forum/plugins/woocommerce-free-shipping-bar/',
						'docs'      => 'https://docs.villatheme.com/?item=woocommerce-free-shipping-bar',
						'review'    => 'https://codecanyon.net/downloads',
						'css'       => WFSPB_SHIPPING_CSS,
						'image'     => WFSPB_SHIPPING_IMAGES,
						'slug'      => 'woocommerce-free-shipping-bar',
						'menu_slug' => 'woocommerce_free_ship',
						'version'   => WFSPB_VERSION,
					)
				);
			}
		}

		// save data on admin setting options page
		public function save_data() {

			if ( ! current_user_can( 'manage_options' ) ) {
				return false;
			}
			if ( ! isset( $_POST['_woofreeshipbar_nonce'] ) || ! wp_verify_nonce( $_POST['_woofreeshipbar_nonce'], 'woofreeshipbar_action_nonce' ) ) {
				return false;
			}

			$langs = function_exists( 'icl_get_languages' ) ?
				icl_get_languages( 'skip_missing=0&orderby=code' ) :
				array( 'default' => array( 'native_name' => '', 'country_flag_url' => '' ) );

			$html_fields = [
				'announce_system_',
				'message_purchased_',
				'message_success_',
				'message_error_',
				'message_full_free_ship_',
			];

			$save_text = [];
			foreach ( $html_fields as $field ) {
				foreach ( $langs as $code => $lang_data ) {
					$save_text[ $field . $code ] = isset( $_POST['wfspb-param'][ $field . $code ] ) ? wp_kses_post( $_POST['wfspb-param'][ $field . $code ] ) : '';
				}
			}

			$data                     = wc_clean( $_POST['wfspb-param'] );
			$data                     = wp_parse_args( $save_text, $data );
			$data['conditional-tags'] = stripslashes_deep( $data['conditional-tags'] );
			if ( isset( $data['check_key'] ) ) {
				unset( $data['check_key'] );
				delete_transient( '_site_transient_update_plugins' );
				delete_transient( 'villatheme_item_7339' );
				delete_option( 'woocommerce-free-shipping-bar_messages' );
			}

			update_option( 'wfspb-param', $data );
		}


		// admin setting options page
		public function setting_page_woo_free_shipping_bar() {
			$this->settings->get_params();
			?>
            <div class="wrap">
                <h1><?php echo esc_html__( 'WooCommerce Free Shipping Bar', 'woocommerce-free-shipping-bar' ); ?></h1>
                <div class="woocommerce-free-shipping-bar">
					<?php if ( $this->settings->check_woo_shipping_zone() == false ) { ?>
                        <div id="message" class="notice error">
                            <p>
								<?php
								$link  = admin_url( 'admin.php?page=wc-settings&tab=shipping' );
								$mess0 = esc_html__( 'WooCommerce Shipping settings', 'woocommerce-free-shipping-bar' );
								$mess1 = esc_html__( 'Not exists a free shipping zone. Please go to', 'woocommerce-free-shipping-bar' );
								$mess2 = esc_html__( 'and then Add New a Shipping Zone with Free Shipping method (or Enable Free Shipping method) for your location.', 'woocommerce-free-shipping-bar' );
								printf( "%s <a href='%s' target='_blank'>%s</a> %s", $mess1, esc_url( $link ), $mess0, $mess2 );
								?>
                            </p>
                        </div>
					<?php } ?>
                    <form class="vi-ui form" method="post" action="">
						<?php
						wp_nonce_field( 'woofreeshipbar_action_nonce', '_woofreeshipbar_nonce' );
						settings_fields( 'woocommerce-free-shipping-bar' );
						do_settings_sections( 'woocommerce-free-shipping-bar' );

						?>

                        <div class="vi-ui top attached tabular menu">
                            <div class="item active" data-tab="general">
                                <i class="large setting icon"></i><?php esc_html_e( 'General', 'woocommerce-free-shipping-bar' ) ?>
                            </div>
                            <div class="item" data-tab="design">
                                <i class="large tags icon"></i><?php esc_html_e( 'Design', 'woocommerce-free-shipping-bar' ) ?>
                            </div>
                            <div class="item" data-tab="message">
                                <i class="large announcement icon"></i><?php esc_html_e( 'Message', 'woocommerce-free-shipping-bar' ) ?>
                            </div>
                            <div class="item" data-tab="effect">
                                <i class="large crop icon"></i><?php esc_html_e( 'Effect', 'woocommerce-free-shipping-bar' ) ?>
                            </div>
                            <div class="item" data-tab="assign">
                                <i class="large columns icon"></i><?php esc_html_e( 'Assign', 'woocommerce-free-shipping-bar' ) ?>
                            </div>
                            <div class="item" data-tab="update">
                                <i class="large cloud download icon"></i><?php esc_html_e( 'Update', 'woocommerce-free-shipping-bar' ) ?>
                            </div>
                        </div>
                        <div class="vi-ui wfspb-container tab attached bottom segment active" data-tab="general">
                            <table class="optiontable form-table">
                                <tr valign="top">
                                    <th scope="row"><?php esc_html_e( 'Enable', 'woocommerce-free-shipping-bar' ) ?></th>
                                    <td>
                                        <div class="vi-ui toggle checkbox checked">
                                            <input type="checkbox"
                                                   name="<?php echo self::set_field( 'enable' ); ?>" <?php checked( self::get_field( 'enable' ), 1 ); ?>
                                                   value="1">
                                            <label for="<?php echo self::set_field( 'enable' ); ?>"><?php esc_html_e( 'Enable', 'woocommerce-free-shipping-bar' ) ?></label>
                                        </div>
                                    </td>
                                </tr>
                                <tr valign="top">
                                    <th scope="row"><?php esc_html_e( 'Free Shipping Zone', 'woocommerce-free-shipping-bar' ) ?></th>
                                    <td>
                                        <select class="vi-ui fluid dropdown required"
                                                name="<?php echo self::set_field( 'default-zone' ); ?>"
                                                value="<?php echo htmlentities( self::get_field( 'default-zone' ) ); ?>">
											<?php echo $this->get_default_shipping_zone(); ?>
                                        </select>
                                        <p class="description"><?php esc_html_e( 'Please select zone default what you set Free Shiping method.', 'woocommerce-free-shipping-bar' ) ?>
                                            (*)require</p>
                                    </td>
                                </tr>
                                <tr valign="top">
                                    <th scope="row"><?php esc_html_e( 'Detect IP', 'woocommerce-free-shipping-bar' ) ?></th>
                                    <td>
                                        <div class="vi-ui toggle checkbox">
                                            <input type="checkbox"
                                                   name="<?php echo self::set_field( 'detect-ip' ); ?>" <?php checked( self::get_field( 'detect-ip', 0 ), 1 ); ?>
                                                   value="1">
                                            <label for="<?php echo self::set_field( 'detect-ip' ); ?>"><?php esc_html_e( 'Enable', 'woocommerce-free-shipping-bar' ) ?></label>
                                            <p class="description"><?php esc_html_e( 'If you enable to Detect IP then the user is accessing to your site will be automatically apply to Free Shipping zone with their IP. Note: their ip are contained in Free Shipping zone (Don\'t apply with STATE)', 'woocommerce-free-shipping-bar' ) ?></p>
                                        </div>
                                    </td>
                                </tr>
                                <tr valign="top">
                                    <th scope="row"><?php esc_html_e( 'Mobile', 'woocommerce-free-shipping-bar' ) ?></th>
                                    <td>
                                        <div class="vi-ui toggle checkbox">
                                            <input type="checkbox"
                                                   name="<?php echo self::set_field( 'detect-mobile' ); ?>" <?php checked( self::get_field( 'detect-mobile', 0 ), 1 ); ?>
                                                   value="1">
                                            <label for="<?php echo self::set_field( 'detect-mobile' ); ?>"><?php esc_html_e( 'Enable', 'woocommerce-free-shipping-bar' ) ?></label>
                                        </div>
                                        <p class="description"><?php esc_html_e( 'Enable on mobile and tablet.', 'woocommerce-free-shipping-bar' ) ?></p>

                                    </td>
                                </tr>
                                <tr valign="top">
                                    <th scope="row"><?php esc_html_e( 'Exclude shipping class', 'woocommerce-free-shipping-bar' ) ?></th>
                                    <td>
										<?php
										$wc_shipping = WC_Shipping::instance();
										$classes     = $wc_shipping->get_shipping_classes();
										$saved_ids   = self::get_field( 'exclude-shipping-class', [] );
										?>
                                        <select class="wfsb-exclude-shipping-class vi-ui dropdown" multiple
                                                name="<?php echo self::set_field( 'exclude-shipping-class', true ); ?>">
                                            <option value=""><?php esc_html_e( 'Shipping classes', 'woocommerce-free-shipping-bar' ); ?></option>
											<?php
											if ( ! empty( $classes ) && is_array( $classes ) ) {
												foreach ( $classes as $term ) {
													$selected = in_array( $term->term_id, $saved_ids ) ? 'selected' : '';
													printf( '<option value="%s" %s>%s</option>', esc_attr( $term->term_id ), esc_attr( $selected ), esc_html( $term->name ) );
												}
											}
											?>
                                        </select>
                                        <p class="description"><?php esc_html_e( 'Select shipping class to exclude when calculate subtotal', 'woocommerce-free-shipping-bar' ) ?></p>

                                    </td>
                                </tr>

                            </table>
                        </div>
                        <div class="vi-ui wfspb-container tab attached bottom segment" data-tab="design">
                            <table class="optiontable form-table">
                                <tr valign="top">
                                    <th scope="row"><?php esc_html_e( 'Small Progres Bar', 'woocommerce-free-shipping-bar' ) ?></th>
                                    <td>
                                        <div class="vi-ui toggle checkbox">
                                            <input type="checkbox"
                                                   name="<?php echo self::set_field( 'show_at_order_bottom' ); ?>" <?php checked( self::get_field( 'show_at_order_bottom', 0 ), 1 ); ?>
                                                   value="1">
                                            <label for="<?php echo self::set_field( 'show_at_order_bottom' ); ?>"><?php esc_html_e( 'Enable', 'woocommerce-free-shipping-bar' ) ?></label>
                                        </div>
                                        <p class="description"><?php esc_html_e( 'Show progress bar at bottom Cart page, Checkout page', 'woocommerce-free-shipping-bar' ) ?></p>
                                    </td>
                                </tr>
                                <tr valign="top" class="wfsb-small-bar">
                                    <th scope="row"><?php esc_html_e( 'Position on Cart page', 'woocommerce-free-shipping-bar' ) ?></th>
                                    <td>
                                        <select class="vi-ui fluid dropdown"
                                                name="<?php echo self::set_field( 'position_cart' ) ?>">
                                            <option value="0" <?php selected( self::get_field( 'position_cart' ), 0 ) ?>>
												<?php esc_html_e( 'Above Process Button', 'woocommerce-free-shipping-bar' ) ?>
                                            </option>
                                            <option value="1" <?php selected( self::get_field( 'position_cart' ), 1 ) ?>>
												<?php esc_html_e( 'Below Process Button', 'woocommerce-free-shipping-bar' ) ?>
                                            </option>
                                            <option value="2" <?php selected( self::get_field( 'position_cart' ), 2 ) ?>>
												<?php esc_html_e( 'Before Cart Table', 'woocommerce-free-shipping-bar' ) ?>
                                            </option>
                                        </select>
                                    </td>
                                </tr>
                                <tr valign="top" class="wfsb-small-bar">
                                    <th scope="row"><?php esc_html_e( 'Position on Checkout page', 'woocommerce-free-shipping-bar' ) ?></th>
                                    <td>
                                        <select class="vi-ui fluid dropdown"
                                                name="<?php echo self::set_field( 'position_checkout' ) ?>">
                                            <option value="0" <?php selected( self::get_field( 'position_checkout' ), 0 ) ?>><?php esc_html_e( 'Above Payment Methods', 'woocommerce-free-shipping-bar' ) ?></option>
                                            <option value="1" <?php selected( self::get_field( 'position_checkout' ), 1 ) ?>><?php esc_html_e( 'Below Process Button', 'woocommerce-free-shipping-bar' ) ?></option>
                                        </select>
                                    </td>
                                </tr>
                                <tr valign="top" class="wfsb-small-bar">
                                    <th scope="row"><?php esc_html_e( 'Show single product page', 'woocommerce-free-shipping-bar' ) ?></th>
                                    <td>
                                        <div class="vi-ui toggle checkbox">
                                            <input type="checkbox"
                                                   name="<?php echo self::set_field( 'show_single_product' ); ?>" <?php checked( self::get_field( 'show_single_product', 0 ), 1 ); ?>
                                                   value="1">
                                            <label for="<?php echo self::set_field( 'show_single_product' ); ?>"><?php esc_html_e( 'Enable', 'woocommerce-free-shipping-bar' ) ?></label>
                                        </div>
                                        <p class="description"><?php esc_html_e( 'Show progress bar below add to cart button.', 'woocommerce-free-shipping-bar' ) ?></p>
                                    </td>
                                </tr>
                                <tr valign="top" >
                                    <th scope="row"><?php esc_html_e( 'Show in mini cart', 'woocommerce-free-shipping-bar' ) ?></th>
                                    <td>
                                        <div class="vi-ui toggle checkbox">
                                            <input type="checkbox"
                                                   name="<?php echo self::set_field( 'bar_in_mini_cart' ); ?>" <?php checked( self::get_field( 'bar_in_mini_cart', 0 ), 1 ); ?>
                                                   value="1">
                                            <label for="<?php echo self::set_field( 'bar_in_mini_cart' ); ?>"><?php esc_html_e( 'Enable', 'woocommerce-free-shipping-bar' ) ?></label>
                                        </div>
                                        <p class="description"><?php esc_html_e( 'Show progress bar in mini cart.', 'woocommerce-free-shipping-bar' ) ?></p>
                                    </td>
                                </tr>
                                <tr valign="top" class="wfsb-bar-in-mini-cart">
                                    <th scope="row"><?php esc_html_e( 'Position on mini cart', 'woocommerce-free-shipping-bar' ) ?></th>
                                    <td>
                                        <select class="vi-ui fluid dropdown"
                                                name="<?php echo self::set_field( 'position_mini_cart' ) ?>">
                                            <option value="0" <?php selected( self::get_field( 'position_mini_cart' ), 0 ) ?>><?php esc_html_e( 'At the bottom on mini cart', 'woocommerce-free-shipping-bar' ) ?></option>
                                            <option value="1" <?php selected( self::get_field( 'position_mini_cart' ), 1 ) ?>><?php esc_html_e( 'At the top on mini cart', 'woocommerce-free-shipping-bar' ) ?></option>
                                        </select>
                                    </td>
                                </tr>
                                <tr valign="top">
                                    <th scope="row"><?php esc_html_e( 'Background Color', 'woocommerce-free-shipping-bar' ) ?></th>
                                    <td>
                                        <input type="text" name="<?php echo self::set_field( 'bg-color' ); ?>"
                                               value="<?php echo htmlentities( self::get_field( 'bg-color', 'rgb(32, 98, 150)' ) ); ?>">
                                    </td>
                                </tr>
                                <tr valign="top">
                                    <th scope="row"><?php esc_html_e( 'Text Color', 'woocommerce-free-shipping-bar' ) ?></th>
                                    <td>
                                        <input type="text" name="<?php echo self::set_field( 'text-color' ); ?>"
                                               value="<?php echo htmlentities( self::get_field( 'text-color', '#FFFFFF' ) ); ?>">
                                    </td>
                                </tr>
                                <tr valign="top">
                                    <th scope="row"><?php esc_html_e( 'Link Color', 'woocommerce-free-shipping-bar' ) ?></th>
                                    <td>
                                        <input type="text" name="<?php echo self::set_field( 'link-color' ); ?>"
                                               value="<?php echo htmlentities( self::get_field( 'link-color', '#77B508' ) ); ?>">
                                    </td>
                                </tr>
                                <tr valign="top">
                                    <th scope="row"><?php esc_html_e( 'Font-Family', 'woocommerce-free-shipping-bar' ) ?></th>
                                    <td>
                                        <input id="wfspb-font" type="text"
                                               name="<?php echo self::set_field( 'font' ); ?>"
                                               value="<?php echo htmlentities( self::get_field( 'font', 'PT Sans' ) ); ?>">
                                    </td>
                                </tr>
                                <tr valign="top">
                                    <th scope="row"><?php esc_html_e( 'Font-Size', 'woocommerce-free-shipping-bar' ) ?></th>
                                    <td>
                                        <select class="vi-ui fluid dropdown select-fontsize"
                                                name="<?php echo self::set_field( 'font-size' ); ?>">

											<?php for ( $i = 10; $i <= 40; $i ++ ) { ?>
                                                <option value="<?php echo $i; ?>" <?php selected( self::get_field( 'font-size', 16 ), $i ); ?> > <?php echo $i . 'px'; ?></option>
											<?php } ?>
                                        </select>
                                    </td>
                                </tr>
                                <tr valign="top">
                                    <th scope="row"><?php esc_html_e( 'Text Align', 'woocommerce-free-shipping-bar' ) ?></th>
                                    <td>
                                        <select class="vi-ui fluid dropdown select-textalign"
                                                name="<?php echo self::set_field( 'text-align' ) ?>">
                                            <option value="left" <?php selected( self::get_field( 'text-align' ), 'left' ) ?>><?php esc_html_e( 'Left', 'woocommerce-free-shipping-bar' ) ?></option>
                                            <option value="center" <?php selected( self::get_field( 'text-align', 'center' ), 'center' ) ?>><?php esc_html_e( 'Center', 'woocommerce-free-shipping-bar' ) ?></option>
                                        </select>
                                    </td>
                                </tr>
                                <tr valign="top">
                                    <th scope="row"><?php esc_html_e( 'Enable Progress', 'woocommerce-free-shipping-bar' ) ?></th>
                                    <td>
                                        <div class="vi-ui toggle checkbox wfspb-enable-progress">
                                            <input type="checkbox"
                                                   name="<?php echo self::set_field( 'enable-progress' ); ?>" <?php checked( self::get_field( 'enable-progress' ), 1 ); ?>
                                                   value="1">
                                            <label for="<?php echo self::set_field( 'enable-progress' ); ?>"><?php esc_html_e( 'Enable', 'woocommerce-free-shipping-bar' ) ?></label>
                                        </div>
                                    </td>
                                </tr>

                                <tr valign="top" class="wfspb-progress-percent">
                                    <th scope="row"><?php esc_html_e( 'Progress Background Color', 'woocommerce-free-shipping-bar' ) ?></th>
                                    <td>
                                        <input type="text" name="<?php echo self::set_field( 'bg-color-progress' ); ?>"
                                               value="<?php echo htmlentities( self::get_field( 'bg-color-progress', '#C9CFD4' ) ); ?>">
                                    </td>
                                </tr>
                                <tr valign="top" class="wfspb-progress-percent">
                                    <th scope="row"><?php esc_html_e( 'Current Progress Background Color', 'woocommerce-free-shipping-bar' ) ?></th>
                                    <td>
                                        <input type="text"
                                               name="<?php echo self::set_field( 'bg-current-progress' ); ?>"
                                               value="<?php echo htmlentities( self::get_field( 'bg-current-progress', '#0D47A1' ) ); ?>">
                                    </td>
                                </tr>
                                <tr valign="top" class="wfspb-progress-percent">
                                    <th scope="row"><?php esc_html_e( 'Progress Text Color', 'woocommerce-free-shipping-bar' ) ?></th>
                                    <td>
                                        <input type="text"
                                               name="<?php echo self::set_field( 'progress-text-color' ); ?>"
                                               value="<?php echo htmlentities( self::get_field( 'progress-text-color', '#FFFFFF' ) ); ?>">
                                    </td>
                                </tr>
                                <tr valign="top" class="wfspb-progress-percent">
                                    <th scope="row"><?php esc_html_e( 'Font-Size Progress Bar', 'woocommerce-free-shipping-bar' ) ?></th>
                                    <td>
                                        <select class="vi-ui fluid dropdown select-fontsize-progress"
                                                name="<?php echo self::set_field( 'font-size-progress' ); ?>">
											<?php for ( $i = 10; $i <= 20; $i ++ ) { ?>
                                                <option value="<?php echo $i; ?>" <?php selected( self::get_field( 'font-size-progress', 11 ), $i ); ?> > <?php echo $i . 'px'; ?></option>
											<?php } ?>
                                        </select>
                                    </td>
                                </tr>
                                <tr valign="top" class="wfspb-progress-percent">
                                    <th scope="row"><?php esc_html_e( 'Progress Bar Effect', 'woocommerce-free-shipping-bar' ) ?></th>
                                    <td>
                                        <select class="vi-ui fluid dropdown select-progress-effect"
                                                name="<?php echo self::set_field( 'progress_effect' ); ?>">

                                            <option value="0" <?php selected( self::get_field( 'progress_effect', 0 ), 0 ); ?>><?php esc_html_e( 'Plain', 'woocommerce-free-shipping-bar' ) ?></option>
                                            <option value="1" <?php selected( self::get_field( 'progress_effect' ), 1 ); ?>><?php esc_html_e( 'Loading', 'woocommerce-free-shipping-bar' ) ?></option>
                                            <option value="2" <?php selected( self::get_field( 'progress_effect' ), 2 ); ?>><?php esc_html_e( 'Border', 'woocommerce-free-shipping-bar' ) ?></option>

                                        </select>
                                    </td>
                                </tr>
                                <tr valign="top">
                                    <th scope="row"><?php esc_html_e( 'Style', 'woocommerce-free-shipping-bar' ) ?></th>
                                    <td>
                                        <div class="vi-ui form">
                                            <div class="three fields">
                                                <div class="field">
                                                    <img src="<?php echo WFSPB_SHIPPING_IMAGES ?>progress-style1.png"
                                                         class="vi-ui centered medium image middle aligned"/>
                                                    <div class="vi-ui toggle checkbox checked center aligned segment">
                                                        <input type="radio"
                                                               name="<?php echo self::set_field( 'style' ); ?>" <?php checked( self::get_field( 'style', 1 ), 1 ); ?>
                                                               value="1">
                                                        <label for="<?php echo self::set_field( 'style' ); ?>"><?php esc_html_e( 'Style 1', 'woocommerce-free-shipping-bar' ) ?></label>
                                                    </div>
                                                </div>
                                                <div class="field">
                                                    <img src="<?php echo WFSPB_SHIPPING_IMAGES ?>progress-style2.png"
                                                         class="vi-ui centered medium image middle aligned"/>
                                                    <div class="vi-ui toggle checkbox checked center aligned segment">
                                                        <input type="radio"
                                                               name="<?php echo self::set_field( 'style' ); ?>" <?php checked( self::get_field( 'style' ), 2 ); ?>
                                                               value="2">
                                                        <label for="<?php echo self::set_field( 'style' ); ?>"><?php esc_html_e( 'Style 2', 'woocommerce-free-shipping-bar' ) ?></label>
                                                    </div>
                                                </div>
                                                <div class="field">
                                                    <img src="<?php echo WFSPB_SHIPPING_IMAGES ?>progress-style3.png"
                                                         class="vi-ui centered medium image middle aligned"/>
                                                    <div class="vi-ui toggle checkbox checked center aligned segment">
                                                        <input type="radio"
                                                               name="<?php echo self::set_field( 'style' ); ?>" <?php checked( self::get_field( 'style' ), 3 ); ?>
                                                               value="3">
                                                        <label for="<?php echo self::set_field( 'style' ); ?>"><?php esc_html_e( 'Style 3', 'woocommerce-free-shipping-bar' ) ?></label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <tr valign="top">
                                    <th scope="row"><?php esc_html_e( 'Position', 'woocommerce-free-shipping-bar' ) ?></th>
                                    <td>
                                        <div class="vi-ui form">
                                            <div class="three fields">
                                                <div class="field">
                                                    <img src="<?php echo WFSPB_SHIPPING_IMAGES ?>position-top.png"
                                                         class="vi-ui centered large image middle aligned "/>
                                                    <div class="vi-ui toggle checkbox checked center aligned segment">
                                                        <input type="radio"
                                                               name="<?php echo self::set_field( 'position' ); ?>" <?php checked( self::get_field( 'position', 0 ), 0 ); ?>
                                                               value="0">
                                                        <label for="<?php echo self::set_field( 'position' ); ?>"><?php esc_html_e( 'Top', 'woocommerce-free-shipping-bar' ) ?></label>
                                                    </div>
                                                </div>
                                                <div class="field">
                                                    <img src="<?php echo WFSPB_SHIPPING_IMAGES ?>position-bottom.png"
                                                         class="vi-ui centered large image middle aligned "/>
                                                    <div class="vi-ui toggle checkbox center aligned segment">
                                                        <input type="radio"
                                                               name="<?php echo self::set_field( 'position' ); ?>" <?php checked( self::get_field( 'position' ), 1 ); ?>
                                                               value="1">
                                                        <label for="<?php echo self::set_field( 'position' ); ?>"><?php esc_html_e( 'Bottom', 'woocommerce-free-shipping-bar' ) ?></label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <tr valign="top">
                                    <th scope="row"><?php esc_html_e( 'Gift Icon', 'woocommerce-free-shipping-bar' ) ?></th>
                                    <td>
                                        <select class="vi-ui fluid dropdown"
                                                name="<?php echo self::set_field( 'gift_icon' ); ?>">

                                            <option value="0" <?php selected( self::get_field( 'gift_icon', 0 ), 0 ); ?>><?php esc_html_e( 'Truck Icon', 'woocommerce-free-shipping-bar' ) ?></option>
                                            <option value="1" <?php selected( self::get_field( 'gift_icon' ), 1 ); ?>><?php esc_html_e( 'Custom Image', 'woocommerce-free-shipping-bar' ) ?></option>

                                        </select>
                                    </td>
                                </tr>
                                <tr valign="top" class="wfspb-gift-box-option">
                                    <th scope="row"><?php esc_html_e( 'Custom Icon', 'woocommerce-free-shipping-bar' ) ?></th>
                                    <td> <?php $image = new VillaTheme_Image_Field( self::set_field( 'custom_icon' ), '', self::get_field( 'custom_icon' ) );
										echo $image->get_field(); ?>
                                        <p class="description"><?php esc_html_e( 'Image dimension should be 147 x 71(px).', 'woocommerce-free-shipping-bar' ) ?></p>
                                    </td>
                                </tr>
                                <tr valign="top">
                                    <th scope="row"><?php esc_html_e( 'Custom CSS', 'woocommerce-free-shipping-bar' ) ?></th>
                                    <td>
                                        <textarea
                                                name="<?php echo self::set_field( 'custom_css' ); ?>"><?php echo stripslashes_from_strings_only( self::get_field( 'custom_css' ) ) ?></textarea>
                                    </td>
                                </tr>
                            </table>
							<?php
							if ( self::get_field( 'position' ) == 0 ) {
								$class_pos = 'top_bar';
							} else {
								$class_pos = 'bottom_bar';
							}

							if ( self::get_field( 'enable-progress' ) == 0 ) {
								$class_progress = 'disable_progress_bar';
							} else {
								$class_progress = 'enable_progress_bar';
							}
							?>
                            <div id="wfspb-top-bar" class="customized <?php echo esc_attr( $class_pos ) ?>">
                                <div id="wfspb-main-content"><?php echo esc_html__( 'You have purchased $100 of $120. Continue', 'woocommerce-free-shipping-bar' ) ?>
                                    <a href="#"><?php echo esc_html__( 'Shopping', 'woocommerce-free-shipping-bar' ) ?></a>
                                </div>
                                <div class="" id="wfspb-close"></div>
                                <div id="wfspb-progress" class="<?php echo esc_attr( $class_progress ) ?>"
                                     style="display: none">
                                    <div id="wfspb-current-progress">
                                        <div id="wfspb-label">25%</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="vi-ui wfspb-container tab attached bottom segment" data-tab="message">
							<?php $langs = function_exists( 'icl_get_languages' ) ? icl_get_languages( 'skip_missing=0&orderby=code' ) :
								array( 'default' => array( 'native_name' => '', 'country_flag_url' => '' ) );
							?>

                            <table class="optiontable form-table">
                                <tr valign="top">
                                    <th scope="row"><?php esc_html_e( 'Announce System', 'woocommerce-free-shipping-bar' ) ?></th>
                                    <td class="wfspb-notice-sample">
										<?php
										foreach ( $langs as $lang_code => $lang_opttion ) {
											?>
                                            <label>
                                                <img class="wfspb-flag"
                                                     src="<?php echo esc_url( $lang_opttion['country_flag_url'] ); ?>"> <?php echo esc_html( $lang_opttion['native_name'] ) ?>
                                            </label>
                                            <textarea rows="2"
                                                      name="<?php echo( self::set_field( 'announce_system_' . $lang_code ) ); ?>"
                                            ><?php echo trim( self::get_message_field( 'announce_system_' . $lang_code ) ); ?></textarea>
											<?php
										} ?>
                                        <ul class="description" style="list-style: none">
                                            <li>
                                                <span>{min_amount}</span>
                                                - <?php esc_html_e( 'Minimum order amount Free Shipping', 'woocommerce-free-shipping-bar' ) ?>
                                            </li>
                                        </ul>
                                    </td>
                                </tr>
                                <tr valign="top">
                                    <th scope="row"><?php esc_html_e( 'Message Purchased', 'woocommerce-free-shipping-bar' ) ?></th>
                                    <td class="wfspb-notice-sample">
                                        <div class="field">
											<?php
											foreach ( $langs as $lang_code => $lang_opttion ) {
												?>
                                                <label><img class="wfspb-flag"
                                                            src="<?php echo esc_url( $lang_opttion['country_flag_url'] ); ?>"> <?php echo esc_html( $lang_opttion['native_name'] ) ?>
                                                </label>
                                                <textarea rows="2"
                                                          name="<?php echo( self::set_field( 'message_purchased_' . $lang_code ) ); ?>"
                                                ><?php echo trim( self::get_message_field( 'message_purchased_' . $lang_code ) ); ?></textarea>
												<?php
											} ?>
                                            <ul class="description" style="list-style: none">
                                                <li>
                                                    <span>{total_amounts}</span>
                                                    - <?php esc_html_e( 'The total amount of your purchases', 'woocommerce-free-shipping-bar' ) ?>
                                                </li>
                                                <li>
                                                    <span>{cart_amount}</span>
                                                    - <?php esc_html_e( 'Total quantity in cart.', 'woocommerce-free-shipping-bar' ) ?>
                                                </li>
                                                <li>
                                                    <span>{min_amount}</span>
                                                    - <?php esc_html_e( 'Minimum order amount Free Shipping', 'woocommerce-free-shipping-bar' ) ?>
                                                </li>
                                                <li>
                                                    <span>{missing_amount}</span>
                                                    - <?php esc_html_e( 'The outstanding amount of the free shipping program', 'woocommerce-free-shipping-bar' ) ?>
                                                </li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                                <tr valign="top">
                                    <th scope="row"><?php esc_html_e( 'Message Success', 'woocommerce-free-shipping-bar' ) ?></th>
                                    <td class="wfspb-notice-sample">
										<?php
										foreach ( $langs as $lang_code => $lang_opttion ) {
											?>
                                            <label><img class="wfspb-flag"
                                                        src="<?php echo esc_url( $lang_opttion['country_flag_url'] ); ?>"> <?php echo esc_html( $lang_opttion['native_name'] ) ?>
                                            </label>
                                            <textarea rows="2"
                                                      name="<?php echo( self::set_field( 'message_success_' . $lang_code ) ); ?>"
                                            ><?php echo trim( self::get_message_field( 'message_success_' . $lang_code ) ); ?></textarea>
											<?php
										} ?>
                                        <ul class="description" style="list-style: none">
                                            <li>
                                                <span>{checkout_page}</span>
                                                - <?php esc_html_e( 'Link to checkout page', 'woocommerce-free-shipping-bar' ) ?>
                                            </li>
                                            <li>
                                                <span>{cart_page}</span>
                                                - <?php esc_html_e( 'Link to cart page', 'woocommerce-free-shipping-bar' ) ?>
                                            </li>
                                            <li>
                                                <span>{shopping}</span>
                                                - <?php esc_html_e( 'Link to shop page', 'woocommerce-free-shipping-bar' ) ?>
                                            </li>

                                        </ul>
                                    </td>
                                </tr>
                                <tr valign="top">
                                    <th scope="row"><?php esc_html_e( 'Message Error', 'woocommerce-free-shipping-bar' ) ?></th>
                                    <td class="wfspb-notice-sample">
										<?php
										foreach ( $langs as $lang_code => $lang_opttion ) {
											?>
                                            <label><img class="wfspb-flag"
                                                        src="<?php echo esc_url( $lang_opttion['country_flag_url'] ); ?>"> <?php echo esc_html( $lang_opttion['native_name'] ) ?>
                                            </label>
                                            <textarea rows="2"
                                                      name="<?php echo( self::set_field( 'message_error_' . $lang_code ) ); ?>"
                                            ><?php echo trim( self::get_message_field( 'message_error_' . $lang_code ) ); ?></textarea>
											<?php
										} ?>
                                        <ul class="description" style="list-style: none">
                                            <li>
                                                <span>{missing_amount}</span>
                                                - <?php esc_html_e( 'The outstanding amount of the free shipping program', 'woocommerce-free-shipping-bar' ) ?>
                                            </li>
                                            <li>
                                                <span>{shopping}</span>
                                                - <?php esc_html_e( 'Link to shop page', 'woocommerce-free-shipping-bar' ) ?>
                                            </li>
                                        </ul>
                                    </td>
                                </tr>
                                <tr valign="top">
                                    <th scope="row"><?php esc_html_e( 'Message Full Free Shipping', 'woocommerce-free-shipping-bar' ) ?></th>
                                    <td class="wfspb-notice-sample">
										<?php
										foreach ( $langs as $lang_code => $lang_opttion ) {
											?>
                                            <label>
                                                <img class="wfspb-flag"
                                                     src="<?php echo esc_url( $lang_opttion['country_flag_url'] ); ?>"> <?php echo esc_html( $lang_opttion['native_name'] ) ?>
                                            </label>
                                            <textarea rows="2"
                                                      name="<?php echo( self::set_field( 'message_full_free_ship_' . $lang_code ) ); ?>"><?php
												echo trim( self::get_message_field( 'message_full_free_ship_' . $lang_code ) );
												?></textarea>
											<?php
										} ?>
                                        <p class="description">
											<?php esc_html_e( 'This message is used when min amount is zero', 'woocommerce-free-shipping-bar' ) ?>
                                        </p>
                                    </td>
                                </tr>

                                <tr valign="top">
                                    <th scope="row"><?php esc_html_e( 'Compatible with cache plugin', 'woocommerce-free-shipping-bar' ) ?></th>
                                    <td>
                                        <div class="vi-ui toggle checkbox">
                                            <input type="checkbox"
                                                   name="<?php echo self::set_field( 'cache_compa' ); ?>" <?php checked( self::get_field( 'cache_compa', 0 ), 1 ); ?>
                                                   value="1">
                                            <label for="<?php echo self::set_field( 'cache_compa' ); ?>"><?php esc_html_e( 'Enable', 'woocommerce-free-shipping-bar' ) ?></label>
                                        </div>
                                        <p class="description">
											<?php esc_html_e( 'Enable this option if your message is cached by cache plugin', 'woocommerce-free-shipping-bar' ) ?>
                                        </p>
                                    </td>
                                </tr>

                                <tr valign="top">
                                    <th scope="row"><?php esc_html_e( 'Header selector', 'woocommerce-free-shipping-bar' ) ?></th>
                                    <td>
                                        <input type="text" name="<?php echo self::set_field( 'header_selector' ) ?>"
                                               value="<?php echo self::get_field( 'header_selector' ) ?>"
                                               placeholder="<?php echo '#header.fixed' ?>"/>
                                        <p class="description">
											<?php esc_html_e( 'Add CSS selector to make free shipping bar working with the header bar', 'woocommerce-free-shipping-bar' ) ?>
                                        </p>
                                    </td>
                                </tr>

                            </table>
                        </div>
                        <div class="vi-ui wfspb-container tab attached bottom segment" data-tab="effect">
                            <table class="optiontable form-table">
                                <tr valign="top">
                                    <th scope="row"><?php esc_html_e( 'Initial delay', 'woocommerce-free-shipping-bar' ) ?></th>
                                    <td>
                                        <div class="vi-ui form">
                                            <div class="inline fields">
                                                <input type="number"
                                                       name="<?php echo self::set_field( 'initial-delay' ) ?>"
                                                       value="<?php echo intval( self::get_field( 'initial-delay' ) ) ?>"
                                                       min="0"/>
                                                <label><?php esc_html_e( 'seconds', 'woocommerce-free-shipping-bar' ) ?></label>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <tr valign="top">
                                    <th scope="row"><?php esc_html_e( 'Close message', 'woocommerce-free-shipping-bar' ) ?></th>
                                    <td>
                                        <div class="vi-ui toggle checkbox checked">
                                            <input type="hidden"
                                                   name="<?php echo self::set_field( 'close-message' ); ?>" value="0"/>
                                            <input type="checkbox"
                                                   name="<?php echo self::set_field( 'close-message' ); ?>" <?php checked( self::get_field( 'close-message' ), 1 ); ?>
                                                   value="1"/>
                                            <label><?php esc_html_e( 'Enable', 'woocommerce-free-shipping-bar' ) ?></label>
                                            <p class="description"><?php esc_html_e( '(Enable or Disable to allow close message)', 'woocommerce-free-shipping-bar' ) ?></p>
                                        </div>
                                    </td>
                                </tr>
                                <tr valign="top">
                                    <th scope="row"><?php esc_html_e( 'Time to disappear', 'woocommerce-free-shipping-bar' ) ?></th>
                                    <td>
                                        <div class="vi-ui toggle checkbox">
                                            <input type="checkbox"
                                                   name="<?php echo self::set_field( 'time-to-disappear' ); ?>" <?php checked( self::get_field( 'time-to-disappear' ), 1 ); ?>
                                                   value="1"/>
                                            <label><?php esc_html_e( 'Enable', 'woocommerce-free-shipping-bar' ) ?></label>
                                            <p class="description"><?php esc_html_e( '(Enable or Disable to allow time to disappear)', 'woocommerce-free-shipping-bar' ) ?></p>
                                        </div>
                                    </td>
                                </tr>
                                <tr valign="top" class="wfspb-sub-settime">
                                    <th scope="row"><?php esc_html_e( 'Set time to disappear', 'woocommerce-free-shipping-bar' ) ?></th>
                                    <td>
                                        <div class="inline fields">
                                            <input type="number" min="0"
                                                   name="<?php echo self::set_field( 'set-time-disappear' ); ?>"
                                                   value="<?php echo self::get_field( 'set-time-disappear', 5 ) ?>">
                                            <label><?php esc_html_e( 'seconds', 'woocommerce-free-shipping-bar' ) ?></label>
                                        </div>
                                    </td>
                                </tr>

                                <tr valign="top">
                                    <th scope="row"><?php esc_html_e( 'Show gift box', 'woocommerce-free-shipping-bar' ) ?></th>
                                    <td>
                                        <div class="vi-ui toggle checkbox checked">
                                            <input type="hidden" name="<?php echo self::set_field( 'show-giftbox' ); ?>"
                                                   value="0"/>
                                            <input type="checkbox"
                                                   name="<?php echo self::set_field( 'show-giftbox' ); ?>" <?php checked( self::get_field( 'show-giftbox' ), 1 ); ?>
                                                   value="1"/>
                                            <label><?php esc_html_e( 'Enable', 'woocommerce-free-shipping-bar' ) ?></label>
                                            <p class="description"><?php esc_html_e( '(Display gift box when customer add product to cart)', 'woocommerce-free-shipping-bar' ) ?></p>
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="vi-ui wfspb-container tab attached bottom segment" data-tab="assign">
                            <table class="optiontable form-table">
                                <tr valign="top">
                                    <th scope="row"><?php esc_html_e( 'Assign pages', 'woocommerce-free-shipping-bar' ) ?></th>
                                    <td>
                                        <div class="field">
                                            <div class="vi-ui checkbox home_page">
                                                <input type="checkbox"
                                                       name="<?php echo self::set_field( 'agn-homepage' ) ?>" <?php checked( self::get_field( 'agn-homepage' ), 1 ); ?>
                                                       value="1">
                                                <label><?php esc_html_e( 'Home page', 'woocommerce-free-shipping-bar' ) ?></label>
                                            </div>
                                        </div>
                                        <div class="field">
                                            <div class="vi-ui checkbox cart_page">
                                                <input type="checkbox"
                                                       name="<?php echo self::set_field( 'agn-cart' ) ?>" <?php checked( self::get_field( 'agn-cart' ), 2 ); ?>
                                                       value="2">
                                                <label><?php esc_html_e( 'Cart', 'woocommerce-free-shipping-bar' ) ?></label>
                                            </div>
                                        </div>
                                        <div class="field">
                                            <div class="vi-ui checkbox shop_page">
                                                <input type="checkbox"
                                                       name="<?php echo self::set_field( 'agn-shop' ) ?>" <?php checked( self::get_field( 'agn-shop' ), 3 ); ?>
                                                       value="3">
                                                <label><?php esc_html_e( 'Shop', 'woocommerce-free-shipping-bar' ) ?></label>
                                            </div>
                                        </div>
                                        <div class="field">
                                            <div class="vi-ui checkbox checkout_page">
                                                <input type="checkbox"
                                                       name="<?php echo self::set_field( 'agn-checkout' ) ?>" <?php checked( self::get_field( 'agn-checkout' ), 4 ); ?>
                                                       value="4">
                                                <label><?php esc_html_e( 'Checkout', 'woocommerce-free-shipping-bar' ) ?></label>
                                            </div>
                                        </div>
                                        <div class="field">
                                            <div class="vi-ui checkbox single_product_page">
                                                <input type="checkbox"
                                                       name="<?php echo self::set_field( 'agn-single-product' ) ?>"
                                                       value="5" <?php checked( self::get_field( 'agn-single-product' ), 5 ); ?> >
                                                <label><?php esc_html_e( 'Single Product', 'woocommerce-free-shipping-bar' ) ?></label>
                                            </div>
                                        </div>
                                        <div class="field">
                                            <div class="vi-ui checkbox product_category">
                                                <input type="checkbox"
                                                       name="<?php echo self::set_field( 'agn-product-category' ) ?>" <?php checked( self::get_field( 'agn-product-category' ), 6 ); ?>
                                                       value="6">
                                                <label><?php esc_html_e( 'Product Category', 'woocommerce-free-shipping-bar' ) ?></label>
                                            </div>
                                        </div>
                                        <div class="field">
                                            <div class="vi-ui checkbox product_tag checked">
                                                <input type="checkbox"
                                                       name="<?php echo self::set_field( 'agn-product-tag' ) ?>" <?php checked( self::get_field( 'agn-product-tag' ), 7 ); ?>
                                                       value="7">
                                                <label><?php esc_html_e( 'Product Tag', 'woocommerce-free-shipping-bar' ) ?></label>
                                            </div>
                                        </div>
                                        <p class="description">
											<?php esc_html_e( 'Checked to', 'woocommerce-free-shipping-bar' );
											echo '<span class="wfspb-note"> ' . esc_html__( 'hide', 'woocommerce-free-shipping-bar' ) . ' </span>';
											esc_html_e( 'bar on this page', 'woocommerce-free-shipping-bar' ); ?>
                                        </p>
                                    </td>
                                </tr>
                                <tr valign="top">
                                    <th scope="row"><?php esc_html_e( 'Conditional tags', 'woocommerce-free-shipping-bar' ) ?></th>
                                    <td>
                                        <input type="text" name="<?php echo self::set_field( 'conditional-tags' ) ?>"
                                               value="<?php echo self::get_field( 'conditional-tags' ) ?>"/>
                                        <p class="description"><?php esc_html_e( 'Lets you control on which pages disappear using WP\'s conditional tags.', 'woocommerce-free-shipping-bar' ) ?></p>
                                    </td>
                                </tr>
                                <tr valign="top">
                                    <th scope="row"><?php esc_html_e( 'Shortcode', 'woocommerce-free-shipping-bar' ) ?></th>
                                    <td>
                                        <p class="">[woo_free_shipping_bar hide_processing_bar='' error_message=''
                                            success_message='' announce_message='' purchased_message='']</p>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="vi-ui wfspb-container tab attached bottom segment" data-tab="update">
                            <table class="optiontable form-table">
                                <tr valign="top">
                                    <th scope="row"><?php esc_html_e( 'Auto Update Key', 'woocommerce-free-shipping-bar' ) ?></th>
                                    <td>
                                        <div class="fields">
                                            <div class="ten wide field">
                                                <input type="text" name="<?php echo self::set_field( 'key' ) ?>"
                                                       id="auto-update-key"
                                                       class="villatheme-autoupdate-key-field"
                                                       value="<?php echo self::get_field( 'key' ) ?>">
                                            </div>
                                            <div class="six wide field">
                                        <span class="vi-ui button green villatheme-get-key-button"
                                              data-href="https://api.envato.com/authorization?response_type=code&client_id=villatheme-download-keys-6wzzaeue&redirect_uri=https://villatheme.com/update-key"
                                              data-id="19536343"><?php echo esc_html__( 'Get Key', 'woocommerce-free-shipping-bar' ) ?></span>
                                            </div>
                                        </div>
										<?php do_action( 'woocommerce-free-shipping-bar_key' ) ?>
                                        <p class="description"><?php echo __( 'Please fill your key what you get from <a target="_blank" href="https://villatheme.com/my-download">Villatheme</a>. You can automatically update WooCommerce Free Shipping Bar plugin. See guide <a target="_blank" href="https://villatheme.com/knowledge-base/how-to-use-auto-update-feature/">here</a>', 'woocommerce-free-shipping-bar' ) ?></p>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <p>
                            <button class="vi-ui primary button wfsb-submit">
                                <i class="send icon"></i>
								<?php echo esc_html__( 'Save', 'woocommerce-free-shipping-bar' ) ?>
                            </button>
                            <button class="vi-ui button wfsb-submit"
                                    name="<?php echo self::set_field( 'check_key' ) ?>">
                                <i class="send icon"></i>
								<?php echo esc_html__( 'Save and Check Key', 'woocommerce-free-shipping-bar' ) ?>
                            </button>
                        </p>
                    </form>
                </div>
				<?php do_action( 'villatheme_support_woocommerce-free-shipping-bar' ); ?>
            </div>
			<?php
		}

		// Create menu for plugin
		public function create_options_page() {
			add_menu_page(
				__( 'WooCommerce Free Shipping Bar', 'woocommerce-free-shipping-bar' ),
				__( 'WC F-Shipping Bar', 'woocommerce-free-shipping-bar' ),
				'manage_options',
				'woocommerce_free_ship',
				array( $this, 'setting_page_woo_free_shipping_bar' ),
				'dashicons-backup',
				2
			);
		}

		public function get_refresh_fragment( $fragment ) {
			$notice = $this->shipping_bar_notice();
			if ( $notice ) {
				$fragment['wfspb'] = $notice;
			}

			return $fragment;
		}

		public function shipping_bar_notice( $lang_current ='' ) {

			$free_shipping    = $this->settings->get_free_shipping_min_amount();
			$min_amount       = $free_shipping['min_amount'];
			$ignore_discounts = $free_shipping['ignore_discounts'];

			if ( $min_amount === '' ) {
				return ( array( 'no_free_shipping' => 1 ) );
			}

			if ( $min_amount == 0 ) {
			    if( !empty( $lang_current ) ){
				    $lang_code = $lang_current;
                }else{
				    $lang_code = function_exists( 'wpml_get_current_language' ) ? wpml_get_current_language() : 'default';
			    }
				$message   = $this->get_message_field( 'message_full_free_ship_' . $lang_code );
				$arr_data  = array( 'message_bar' => wp_unslash($message) );
			} else {
				$total = $this->settings->get_total( $ignore_discounts );

				$cart_amount = WC()->cart->get_cart_contents_count();
				$key         = array(
					'{total_amounts}',
					'{cart_amount}',
					'{min_amount}',
					'{missing_amount}'
				);

				$missing_amount = $min_amount - $total;
				$amount1        = '<b id="current_amout">' . wc_price( $total ) . '</b>';
				$cart_amount1   = '<b id="current_amout">' . esc_html( $cart_amount ) . '</b>';
				$min_amount1    = '<b id="wfspb_min_order_amount">' . wc_price( $min_amount ) . '</b>';

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
								$missing_amount_r = $this->settings->real_amount( $missing_amount );
								$missing_amount1  = '<b id="wfspb_missing_amount">' . wc_price( $missing_amount_r ) . '</b>';
							}

						}
					}
				} else {
					if ( 'incl' === get_option( 'woocommerce_tax_display_shop' ) ) {
						if ( wc()->cart->display_prices_including_tax() ) {
							$missing_amount1 = '<b id="wfspb_missing_amount">' . wc_price( $missing_amount ) . '</b>';
						} else {
							$missing_amount_r = $this->settings->get_price_including_tax( $missing_amount );
							$missing_amount1  = '<b id="wfspb_missing_amount">' . wc_price( $missing_amount_r ) . esc_html__( '(incl. tax)', 'woocommerce-free-shipping-bar' ) . '</b>';
						}

					} else {
						if ( wc_prices_include_tax() ) {
							$missing_amount1 = '<b id="wfspb_missing_amount">' . wc_price( $missing_amount ) . '</b>';
						} else {
							$missing_amount_r = $this->settings->real_amount( $missing_amount );
							$missing_amount1  = '<b id="wfspb_missing_amount">' . wc_price( $missing_amount_r ) . '</b>';
						}

					}
				}

				$replaced = array(
					$amount1,
					$cart_amount1,
					$min_amount1,
					$missing_amount1
				);

				$min_percent    = $this->settings->toInt( $min_amount );
				$amount_percent = $this->settings->toInt( $total );
				if ( $amount_percent >= $min_percent ) {
					$amount_total_pr = 100;
				} else {
					if ( $min_percent == 0 ) {
						$amount_total_pr = $amount_percent * 100;
					} else {
						$amount_total_pr = round( ( $amount_percent * 100 ) / $min_percent, 2 );
					}
				}

				if( !empty( $lang_current ) ){
					$lang_code = $lang_current;
				}else{
					$lang_code = function_exists( 'wpml_get_current_language' ) ? wpml_get_current_language() : 'default';
				}

				$message_success   = $this->get_message_field( 'message_success_' . $lang_code );
				$message_purchased = $this->get_message_field( 'message_purchased_' . $lang_code );
				$message_announce  = $this->get_message_field( 'announce_system_' . $lang_code );

				if ( $amount_percent == 0 ) {
					$message = str_replace( $key, $replaced, $message_announce );
				} elseif ( $amount_percent < $min_percent ) {
					$message = str_replace( $key, $replaced, $message_purchased );
				} else {
					$shopping = '<a class="" href="' . get_permalink( get_option( 'woocommerce_shop_page_id' ) ) . '">' . __( 'Shopping', 'woocommerce-free-shipping-bar' ) . '</a>';
					$checkout = '<a class="vi-wcaio-sidebar-cart-bt-nav-checkout" href="' . wc_get_checkout_url() . '" title="' . esc_html__( 'Checkout', 'woocommerce-free-shipping-bar' ) . '">' . esc_html__( 'Checkout', 'woocommerce-free-shipping-bar' ) . '</a>';
					$cart_url = '<a href="' . wc_get_cart_url() . '" title="' . esc_html__( 'Cart', 'woocommerce-free-shipping-bar' ) . '">' . esc_html__( 'Cart', 'woocommerce-free-shipping-bar' ) . '</a>';
					$message  = str_replace( array( '{checkout_page}', '{cart_page}', '{shopping}' ), array(
						$checkout,
						$cart_url,
						$shopping
					),
						'<div id="wfspb-main-content">' . wp_unslash($message_success) . '</div>' );
				}

				$front_end = new WFSPB_FrontEnd();

				$small_bar     = $front_end->get_small_bar_ajax_html();
				$shortcode_bar = $front_end->get_small_sc_bar_ajax_html();

				$arr_data = array(
					'lang_code'     => $lang_code,
					'total_percent' => $amount_total_pr,
					'message_bar'   => wp_kses_post( $message ),
					'small_bar'     => $small_bar,
					'shortcode_bar' => $shortcode_bar
				);
			}

			return $arr_data;
		}


		/**
		 * Get total amount woocommerce when added to cart
		 */
		public function get_data_atc() {
			$lang_current = isset( $_POST['lang_code'] ) ? sanitize_text_field( $_POST['lang_code'] ) : '';
			$arr_data = $this->shipping_bar_notice($lang_current);
			wp_send_json( $arr_data );
			die();
		}

//		 get shipping method (function of ajax)
		public function get_min_amount_updated_cart_totals() {
			// get value current total cart
			$detect_ip    = $this->get_field( 'detect-ip' );
			$default_zone = $this->get_field( 'default-zone' );
			$customer     = WC()->session->get( 'customer' );
			$country      = isset( $customer['shipping_country'] ) ? $customer['shipping_country'] : '';
			$state        = isset( $customer['shipping_state'] ) ? $customer['shipping_state'] : '';
			$postcode     = isset( $customer['shipping_postcode'] ) ? $customer['shipping_postcode'] : '';
			if ( $country ) {
				$min_amount = $this->settings->detect_ip( $country, $state, $postcode );
			} else if ( $detect_ip ) {
				$min_amount = $this->settings->toInt( $this->settings->detect_ip() );
			} elseif ( $default_zone ) {
				$min_amount = $this->settings->toInt( $this->settings->get_min_amount( $default_zone ) );
			} else {
				$min_amount = $this->settings->get_shipping_min_amount();
			}

			$total = WC()->cart->get_displayed_subtotal();

			if ( WC()->cart->display_prices_including_tax() ) {
				$total = round( $total - ( WC()->cart->get_discount_total() + WC()->cart->get_discount_tax() ), wc_get_price_decimals() );
			} else {
				$total = round( $total - WC()->cart->get_discount_total(), wc_get_price_decimals() );
			}
			if ( $min_amount > $total ) {
				echo wc_price( $min_amount );
			} else {
				echo wc_price( $min_amount );
			}

			die();
		}

		// get default shipping method
		public function get_default_shipping_zone() {

			$zones = array();

			// Rest of the World zone
			$zone                                                = new \WC_Shipping_Zone( 0 );
			$zones[ $zone->get_id() ]                            = $zone->get_data();
			$zones[ $zone->get_id() ]['formatted_zone_location'] = $zone->get_formatted_location();
			$zones[ $zone->get_id() ]['shipping_methods']        = $zone->get_shipping_methods();

			// Add user configured zones
			$zones         = array_merge( $zones, WC_Shipping_Zones::get_zones() );
			$save_selected = self::get_field( 'default-zone' );
			foreach ( $zones as $each_zone ) {
				$zone_name        = $each_zone['zone_name'];
				$shipping_methods = $each_zone['shipping_methods'];
				$zone_id          = isset( $each_zone['zone_id'] ) ? $each_zone['zone_id'] : $each_zone['id'];
				if ( is_array( $shipping_methods ) && count( $shipping_methods ) ) {
					foreach ( $shipping_methods as $free_shipping ) {
						if ( $free_shipping->id == 'free_shipping' ) {
							$selected = $save_selected == $zone_id ? 'selected' : '';
							echo "<option value='" . $zone_id . "' " . $selected . " >" . esc_html__( $zone_name ) . "</option>";
						} else {
							echo '';
						}
					}
				}
			}
		}
	}

	new WFSPB_Shipping();
}