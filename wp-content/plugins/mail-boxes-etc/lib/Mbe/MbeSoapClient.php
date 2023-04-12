<?php

class MbeSoapClient extends SoapClient {

	public $isOnlinembe = false;
	private $helper;

	public function __construct( $wsdl, array $options = null, $username = null, $password = null, $wsdlcache = true)
	{
		$this->helper = new Mbe_Shipping_Helper_Data();
		if ($this->helper->isOnlineMBE()) {
			$options['cache_wsdl'] = WSDL_CACHE_NONE;
			parent::__construct( $wsdl, $options );
		}
		else
		{
			$opts = array(
				'ssl' => array(
					'verify_peer' => false,
					'verify_peer_name' => false,
					'allow_self_signed' => true
				),
				'http' => array(
					'protocol_version' => 1.0
				) 
			);
			$context = stream_context_create($opts);

			$soapClientOptions = array(
				'trace' => 1,
				'stream_context' => $context,
				'login' => $username,
				'password' => $password,
				'location' =>  preg_replace('/(\/e-link\.wsdl)$/i', '', $wsdl),
				'cache_wsdl' => $wsdlcache?WSDL_CACHE_MEMORY:WSDL_CACHE_NONE,
			);

			parent::__construct( $wsdl, $soapClientOptions );
		}
	}
	#[\ReturnTypeWillChange]
	public function __soapCall($function_name, $arguments, $options = null, $input_headers = null, &$output_headers = null) {
		if (!$this->helper->isOnlineMBE()) {
			$arguments[0]->RequestContainer->Credentials->Username = '';
			$arguments[0]->RequestContainer->Credentials->Passphrase = '';
		}
		return parent::__soapCall( $function_name, $arguments, $options, $input_headers, $output_headers );
	}


}