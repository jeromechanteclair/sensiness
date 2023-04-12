<?php
namespace WPO\WC\PDF_Invoices_Pro\Vendor\Kunnu\Dropbox\Http\Clients;

/**
 * DropboxHttpClientInterface
 */
interface DropboxHttpClientInterface
{
    /**
     * Send request to the server and fetch the raw response
     *
     * @param  string $url     URL/Endpoint to send the request to
     * @param  string $method  Request Method
     * @param  string|resource|\WPO\WC\PDF_Invoices_Pro\Vendor\Psr\Http\Message\StreamInterface|null $body Request Body
     * @param  array  $headers Request Headers
     * @param  array  $options Additional Options
     *
     * @return \WPO\WC\PDF_Invoices_Pro\Vendor\Kunnu\Dropbox\Http\DropboxRawResponse Raw response from the server
     *
     * @throws \WPO\WC\PDF_Invoices_Pro\Vendor\Kunnu\Dropbox\Exceptions\DropboxClientException
     */
    public function send($url, $method, $body, $headers = [], $options = []);
}
