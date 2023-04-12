<?php
namespace WPO\WC\PDF_Invoices_Pro\Cloud;

use WC_Logger;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( !class_exists( '\\WPO\\WC\\PDF_Invoices_Pro\\Cloud\\Cloud_API' ) ) :

/**
 * Cloud API abstract
 * 
 * @class       \WPO\WC\PDF_Invoices_Pro\Cloud\Cloud_API
 * @version     1.0
 * @category    Class
 * @author      Alexandre Faustino
 */

abstract class Cloud_API {

	public static $cloud_storage_settings;
	public $enabled;
	public static $service_slug = '';
	public static $service_name = '';
	public static $service_logo = '';
	public $service_api_settings;
	public $service_api_settings_option;
	public static $service_access_token;

	/**
	 * Construct
	 *
	 * @return	void
	 */
	public function __construct()
	{
		self::$cloud_storage_settings = get_option( 'wpo_wcpdf_cloud_storage_settings' );
		$this->enabled = self::is_enabled();
		$this::$service_slug = self::service_enabled();

		if( isset(self::$cloud_storage_settings['cloud_service']) && !empty(self::$cloud_storage_settings['cloud_service']) ) {
			$this->service_api_settings = get_option( 'wpo_wcpdf_'.self::$cloud_storage_settings['cloud_service'].'_api_settings' );
			$this->service_api_settings_option = 'wpo_wcpdf_'.self::$cloud_storage_settings['cloud_service'].'_api_settings';
		}
		foreach( self::available_cloud_services() as $cloud_service ) {
			if( $this::$service_slug == $cloud_service['slug'] ) {
				self::$service_name = $cloud_service['name'];
				self::$service_logo = $cloud_service['logo'];
			}
		}

		// prevent WPML from crashing when activated due to a conflict with tightenco/collect
		if ( isset($_GET['action']) && $_GET['action'] == 'activate' && isset($_GET['plugin']) && strpos($_GET['plugin'], 'sitepress.php') !== false ) {
			return;
		}

		// Get the access token
		$this::$service_access_token = $this->get_access_token();

		// Check if the API is enabled
		if( $this->enabled == true ) {
			return;
		}
	}

	/**
	 * Get list of available cloud services
	 *
	 * @return  array
	 */
	public static function available_cloud_services()
	{
		return array(
			0 => array(
				'slug'		=> 'dropbox',
				'name'		=> __( 'Dropbox' , 'wpo_wcpdf_pro' ),
				'logo'		=> WPO_WCPDF_Pro()->plugin_url().'/images/dropbox-logo.jpg',
				'active'	=> true
			),
			1 => array(
				'slug'		=> 'gdrive',
				'name'		=> __( 'Google Drive' , 'wpo_wcpdf_pro' ),
				'logo'		=> WPO_WCPDF_Pro()->plugin_url().'/images/gdrive-logo.jpg',
				'active'	=> false
			),
			2 => array(
				'slug'		=> 'onedrive',
				'name'		=> __( 'OneDrive' , 'wpo_wcpdf_pro' ),
				'logo'		=> WPO_WCPDF_Pro()->plugin_url().'/images/onedrive-logo.jpg',
				'active'	=> false
			),
		);
	}

	/**
	 * Checks if the API is enabled
	 *
	 * @return  bool
	 */
	public static function is_enabled()
	{
		if( !empty(self::$cloud_storage_settings) && isset(self::$cloud_storage_settings['enabled']) ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Get the current service from the cloud storage settings
	 *
	 * @return  string|bool
	 */
	public static function service_enabled()
	{
		if( !empty(self::$cloud_storage_settings) && isset(self::$cloud_storage_settings['cloud_service']) ) {
			return self::$cloud_storage_settings['cloud_service'];
		} else {
			return false;
		}
	}

	/**
	 * Get Access token from the cloud storage settings
	 * 
	 * @return	string|bool string when available, false when not set
	 */
	public function get_access_token()
	{
		// return token if it's saved in the settings
		if (!empty($this->service_api_settings['access_token'])) {
			return $this->service_api_settings['access_token'];
		} else {
			return false;
		}
	}

	/**
	 * Shows cloud service authorization notice
	 * 
	 * @return	void
	 */
	public function auth_message( $authUrl ) {
		$formUrl = admin_url( '?wcdal_authorize' );
		$returnUrl = ((!empty($_SERVER["HTTPS"])) ? "https" : "http") . "://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
		?>
		<div class="notice notice-warning wcpdf-pro-cloud-storage-notice inline">
			<p><img class="logo" src="<?= self::$service_logo; ?>" alt="<?= self::$service_name; ?>"></p>
			<p><strong><?php printf( __( 'Authorize %s cloud service!', 'wpo_wcpdf_pro' ), self::$service_name ); ?></strong></p>
			<p><?php printf( __( 'Visit %s via %sthis link%s to get an access code and enter this below:' , 'wpo_wcpdf_pro' ), self::$service_name, '<a href="'.$authUrl.'" target="_blank">', '</a>' ); ?></p>
			<form action="<?php echo $formUrl; ?>">
				<input type="hidden" id="wpo_wcpdf_<?= self::$service_slug; ?>_return_url" name="wpo_wcpdf_<?= self::$service_slug; ?>_return_url" value="<?php echo $returnUrl; ?>">
				<input type="text" id="wpo_wcpdf_<?= self::$service_slug; ?>_code" name="wpo_wcpdf_<?= self::$service_slug; ?>_code" size="50"/>
				<?php submit_button( __( 'Authorize', 'wpo_wcpdf_pro' ) ); ?>
			</form>
		</div>
		<?php
	}

	/**
	 * Shows authorization succeed notice
	 * 
	 * @return	void
	 */
	public function auth_success( $active_tab, $active_section ) {
		if( $active_tab == 'cloud_storage' ) {
			$token = isset($_REQUEST['wpo_wcpdf_'.self::$service_slug.'_success']) ? $_REQUEST['wpo_wcpdf_'.self::$service_slug.'_success'] : '';
			?>
			<div class="notice notice-success inline">
				<?php printf('<p>'.__( '%s connection established! Access token: %s', 'wpo_wcpdf_pro' ).'</p>', self::$service_name, $token); ?>
			</div>
			<?php
		}
	}

	/**
	 * Shows authorization fail notice
	 * 
	 * @return	void
	 */
	public function auth_fail( $active_tab, $active_section ) {
		if( $active_tab == 'cloud_storage' ) {
			$view_log_link = '<a href="'.esc_url_raw( admin_url( 'admin.php?page=wc-status&tab=logs' ) ).'" target="_blank">'.__( 'View logs', 'wpo_wcpdf_pro' ).'</a>';
			$message = sprintf( __( '%s authentication failed. Please try again or check the logs for details: %s', 'wpo_wcpdf_pro' ), self::$service_name, $view_log_link );
			
			print_r('<div class="notice notice-error inline"><p>%s</p></div>', $message);
		}
	}

	/**
	 * Write logs enabled in cloud storage settings
	 * 
	 * @return	void
	 */
	public static function log( $level, $message ) {
		$general_settings = self::$cloud_storage_settings;
		$cloud_service_slug = isset($general_settings['cloud_service']) ? $general_settings['cloud_service'] : null;
		if( isset($general_settings['api_log']) ) {
			if( class_exists('WC_Logger') ) {
				$wc_logger = new WC_Logger();
				$context = array( 'source' => 'wpo-wcpdf-'.$cloud_service_slug );
				$wc_logger->log( $level, $message, $context);
			} else {
				$current_date_time = date("Y-m-d H:i:s");
				$message = $current_date_time.' '.$message."\n";

				file_put_contents( plugin_dir_path(__FILE__) . '/wpo_wcpdf_'.$cloud_service_slug.'_log.txt', $message, FILE_APPEND);
			}
		}
	}

	/**
	 * Validates a JSON string
	 * 
	 * @return	array|bool
	 */
	public function maybe_json_decode( $string ) {
		$decoded = json_decode( $string, true );
		if ( json_last_error() == JSON_ERROR_NONE ) {
			return $decoded;
		} else {
			return false;
		}
	}

}

endif; // class_exists
