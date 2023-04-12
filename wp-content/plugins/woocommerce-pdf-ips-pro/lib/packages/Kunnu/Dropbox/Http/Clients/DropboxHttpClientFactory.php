<?php
namespace WPO\WC\PDF_Invoices_Pro\Vendor\Kunnu\Dropbox\Http\Clients;

use InvalidArgumentException;
use WPO\WC\PDF_Invoices_Pro\Vendor\GuzzleHttp\Client as Guzzle;

/**
 * DropboxHttpClientFactory
 */
class DropboxHttpClientFactory
{
    /**
     * Make HTTP Client
     *
     * @param  \WPO\WC\PDF_Invoices_Pro\Vendor\Kunnu\Dropbox\Http\Clients\DropboxHttpClientInterface|\WPO\WC\PDF_Invoices_Pro\Vendor\GuzzleHttp\Client|null $handler
     *
     * @return \WPO\WC\PDF_Invoices_Pro\Vendor\Kunnu\Dropbox\Http\Clients\DropboxHttpClientInterface
     */
    public static function make($handler)
    {
        //No handler specified
        if (!$handler) {
            return new DropboxGuzzleHttpClient();
        }

        //Custom Implementation, maybe.
        if ($handler instanceof DropboxHttpClientInterface) {
            return $handler;
        }

        //Handler is a custom configured Guzzle Client
        if ($handler instanceof Guzzle) {
            return new DropboxGuzzleHttpClient($handler);
        }

        //Invalid handler
        throw new InvalidArgumentException('The http client handler must be an instance of WPO\WC\PDF_Invoices_Pro\Vendor\GuzzleHttp\Client or an instance of WPO\WC\PDF_Invoices_Pro\Vendor\Kunnu\Dropbox\Http\Clients\DropboxHttpClientInterface.');
    }
}
