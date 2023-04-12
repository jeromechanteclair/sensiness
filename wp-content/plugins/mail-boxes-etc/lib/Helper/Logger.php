<?php

class Mbe_Shipping_Helper_Logger
{
	public static $log = true;

	private $helper;
	private $pluginVersionMessage;

	public function __construct()
	{
		$this->helper = new Mbe_Shipping_Helper_Data();
		$this->pluginVersionMessage = MBE_ESHIP_PLUGIN_NAME.' version ' . MBE_ESHIP_PLUGIN_VERSION . ' :';
	}

	public function log($message, $force = false)
	{
		if ($this->helper->debug() || $force) {

			if (!file_exists(MBE_ESHIP_PLUGIN_LOG_DIR )) {
				mkdir(MBE_ESHIP_PLUGIN_LOG_DIR , 0755, true);
			}
			$row = date_format(new DateTime(), 'Y-m-d\TH:i:s\Z');
			$row .= " - ";
			$row .= $this->pluginVersionMessage . $message . "\n\r";
			file_put_contents($this->helper->getLogPluginPath(), $row, FILE_APPEND);
		}
	}


	public function logVar($var, $message = null)
	{
		if ($message) {
			$this->log($message);
		}
		$this->log(print_r($var, true));
	}
}