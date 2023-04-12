<?php
namespace WPO\WC\PDF_Invoices_Pro\Cloud\Dropbox;

use WPO\WC\PDF_Invoices_Pro\Cloud\Cloud_API;
use WPO\WC\PDF_Invoices_Pro\Vendor\Kunnu\Dropbox\Dropbox;
use WPO\WC\PDF_Invoices_Pro\Vendor\Kunnu\Dropbox\DropboxApp;
use WPO\WC\PDF_Invoices_Pro\Vendor\Kunnu\Dropbox\DropboxFile;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( !class_exists( '\\WPO\\WC\\PDF_Invoices_Pro\\Cloud\\Dropbox\\Dropbox_API' ) ) :

/**
 * Dropbox API Class
 * 
 * @class       \WPO\WC\PDF_Invoices_Pro\Cloud\Dropbox\Dropbox_API
 * @version     1.0
 * @category    Class
 * @author      Alexandre Faustino
 */

class Dropbox_API extends Cloud_API {

	private $slug;
	private $name;
	private $access_token;
	private $key;
	private $secret;
	private $callback_url;
	private $service;

	/**
	 * Construct
	 * 
	 * @return	void
	 */
	public function __construct()
	{
		// Parent constructor
		parent::__construct();

		// Check if we are dealing with this service API
		if( parent::$service_slug != 'dropbox' ) return;

		// set this service slug, name and token
		$this->slug = parent::$service_slug;
		$this->name = parent::$service_name;
		$this->access_token = parent::$service_access_token;

		// Destination folders
		$this->destination_folders = $this->destination_folders();
		// Define key and secret
		$this->access_type = isset( parent::$cloud_storage_settings['access_type'] ) ? parent::$cloud_storage_settings['access_type'] : 'app_folder';
		foreach( $this->destination_folders as $folder_slug => $folder_keys ) {
			if( $this->access_type == $folder_slug ) {
				$this->key		= $folder_keys['key'];
				$this->secret	= $folder_keys['secret'];
			}
		}

		$this->callback_url	= ''; // can't use dynamic callback urls

		// Configure Dropbox Application
		$this->app = $this->configure_app( $this->access_token );
		$this->service = $this->configure_service( $this->app );

		// Authorization message
		if ( empty($this->access_token) && $this->enabled ) {
			add_action( 'wpo_wcpdf_before_settings_page', array( $this, 'api_auth_message' ), 10, 2 );
		}

		if ( !empty($_REQUEST['wpo_wcpdf_'.$this->slug.'_code']) ) {
			$this->finish_auth();
		}

		if ( isset($_REQUEST['wpo_wcpdf_'.$this->slug.'_success']) ) {
			add_action( 'wpo_wcpdf_before_settings_page', array( $this, 'auth_success' ), 10, 2 );
		}

		if ( isset($_REQUEST['wpo_wcpdf_'.$this->slug.'_fail']) ) {
			add_action( 'wpo_wcpdf_before_settings_page', array( $this, 'auth_fail' ), 10, 2 );
		}

	}

	/**
	 * Defines the Dropbox destination folders and the key/secret pairs
	 * 
	 * @return	array
	 */
	private function destination_folders()
	{
		return array(
			'app_folder' 	=> array(
				'key'		=> 'p40abi3fysjr6o9',
				'secret'	=> '6abfjn0ddlal3oc'
			),
			'root_folder'	=> array(
				'key'		=> 'wtra5psb2pszzqb',
				'secret'	=> 'ne8j2qo1rtefekr'
			)
		);
	}

	/**
	 * Configures Dropbox API app
	 * 
	 * @return	object
	 */
	private function configure_app( $access_token )
	{
		if ( empty($access_token) ) {
			return new DropboxApp($this->key, $this->secret);
		} else {
			return new DropboxApp($this->key, $this->secret, $access_token);
		}
	}

	/**
	 * Configures Dropbox API service
	 * 
	 * @return	object
	 */
	private function configure_service( $app )
	{
		return new Dropbox($app);
	}

	/**
	 * Saves the access token from Dropbox in the API settings
	 * 
	 * @return	void
	 */
	public function set_access_token( $access_token )
	{
		$service_api_settings = $this->service_api_settings;
		$service_api_settings['access_token'] = $access_token;
		if ( !empty($access_token) ) {
			$service_api_settings['account_info'] = $this->get_account_info( $access_token );
		} else {
			unset($service_api_settings['account_info']);
		}
		update_option( $this->service_api_settings_option, $service_api_settings );
		return;
	}

	/**
	 * Gets the Dropbox user account informations (name and email)
	 * 
	 * @return	string|void
	 */
	public function get_account_info( $access_token )
	{
		try {
			$app = $this->configure_app( $access_token );
			$service = $this->configure_service( $app );
			$account = $service->getCurrentAccount();

			$name = $account->getDisplayName();
			$email = $account->getEmail();
			
			return "{$name} [{$email}]";
		} catch ( \Exception $e ) {
			self::log( 'error', "fetching {$this->slug} account info failed" );
		}
	}

	/**
	 * Gets the authorization helper
	 * 
	 * @return	object
	 */
	public function auth_helper()
	{
		return $this->service->getAuthHelper();
	}

	/**
	 * Generates the Dropbox authorization request URL
	 * 
	 * @return	string
	 */
	public function auth_url()
	{
		return remove_query_arg( 'redirect_uri', $this->auth_helper()->getAuthUrl($this->callback_url) );
	}

	/**
	 * Generates the token from the access code provided by the authorization request
	 * 
	 * @return	string
	 */
	public function auth_get_access_token( $code )
	{
		$accessToken = $this->auth_helper()->getAccessToken($code, null, null);
		return $accessToken->getToken();
	}

	/**
	 * Finishes the authorization process by saving the token on the Dropbox API settings
	 * 
	 * @return	resource
	 */
	public function finish_auth()
	{
		$code = sanitize_text_field( $_REQUEST['wpo_wcpdf_'.$this->slug.'_code'] );

		self::log( 'notice', "{$this->slug} authentication code entered: {$code}" );

		// Fetch the AccessToken
		try {
			// get token
			$access_token = $this->auth_get_access_token( $code );

			// save token to settings
			$this->set_access_token( $access_token );
			self::log( 'info', "{$this->slug} access token successfully created from code: {$code}" );

			// redirect back to where we came from
			if (!empty($_REQUEST['wpo_wcpdf_'.$this->slug.'_return_url'])) {
				$url = $_REQUEST['wpo_wcpdf_'.$this->slug.'_return_url'];
			} else {
				$url = admin_url();
			}

			$url = add_query_arg( 'wpo_wcpdf_'.$this->slug.'_success', $access_token, $url);
			wp_redirect( $url );

		} catch ( \Exception $e ) {
			self::log( 'error', "{$this->slug} failed to create access token: ".$e->getMessage() );
			$url = add_query_arg( [ 'wpo_wcpdf_'.$this->slug.'_fail', 'true' ], remove_query_arg( 'wpo_wcpdf_'.$this->slug.'_code' ) );
			wp_redirect( $url );
		} catch ( \TypeError $e ) {
			self::log( 'error', "{$this->slug} failed to create access token: ".$e->getMessage() );
			$url = add_query_arg( [ 'wpo_wcpdf_'.$this->slug.'_fail', 'true' ], remove_query_arg( 'wpo_wcpdf_'.$this->slug.'_code' ) );
			wp_redirect( $url );
		} catch ( \Error $e ) {
			self::log( 'error', "{$this->slug} failed to create access token: ".$e->getMessage() );
			$url = add_query_arg( [ 'wpo_wcpdf_'.$this->slug.'_fail', 'true' ], remove_query_arg( 'wpo_wcpdf_'.$this->slug.'_code' ) );
			wp_redirect( $url );
		}
	}

	/**
	 * Dropbox upload process
	 * 
	 * @return	array
	 */
	public function upload( $file = null, $folder = '/' )
	{	
		if ( empty($file) ) {
			return false;
		}

		$destination_folder = $folder;
		$service_name = $this->name;

		try {
			$dropboxFile = new DropboxFile( $file );
			$filename = basename($file);
			$uploaded_file = $this->service->simpleUpload($dropboxFile, "{$destination_folder}{$filename}", ['mode' => 'overwrite'] );

			self::log( 'info', "successfully uploaded {$filename} to {$service_name}" );
			return array( 'success' => $uploaded_file );
		}
		catch (\Exception $e) {
			$error_response = $e->getMessage();
			$error_message = "trying to upload to {$service_name}: " . $error_response;
			self::log( 'error', $error_message );
			
			// check for JSON
			if ( is_string( $error_response ) && $decoded_response = $this->maybe_json_decode( $error_response ) ) {
				if (isset($decoded_response['error'])) {
					$error = $decoded_response['error'];
					$unlink_on = array( 'invalid_access_token' );
					if ( in_array( $error['.tag'], $unlink_on ) ) {
						$this->set_access_token('');
					}
				}
			}

			return array( 'error' => $error_message );
		}
	}

	/**
	 * Displays the authorization notice for Dropbox service
	 * 
	 * @return	resource
	 */
	public function api_auth_message( $active_tab, $active_section )
	{
		if( $active_tab == 'cloud_storage' ) {
			return $this->auth_message( $this->auth_url() );
		}
	}

}

endif; // class_exists

return new Dropbox_API();