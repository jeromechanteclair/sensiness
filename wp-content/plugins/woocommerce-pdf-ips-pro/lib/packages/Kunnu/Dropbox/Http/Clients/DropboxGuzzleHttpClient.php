<?php

namespace WPO\WC\PDF_Invoices_Pro\Vendor\Kunnu\Dropbox\Http\Clients;

use WPO\WC\PDF_Invoices_Pro\Vendor\GuzzleHttp\Client;
use WPO\WC\PDF_Invoices_Pro\Vendor\GuzzleHttp\Psr7\Request;
use WPO\WC\PDF_Invoices_Pro\Vendor\Psr\Http\Message\StreamInterface;
use WPO\WC\PDF_Invoices_Pro\Vendor\Psr\Http\Message\ResponseInterface;
use WPO\WC\PDF_Invoices_Pro\Vendor\GuzzleHttp\Exception\RequestException;
use WPO\WC\PDF_Invoices_Pro\Vendor\Kunnu\Dropbox\Http\DropboxRawResponse;
use WPO\WC\PDF_Invoices_Pro\Vendor\GuzzleHttp\Exception\BadResponseException;
use WPO\WC\PDF_Invoices_Pro\Vendor\Kunnu\Dropbox\Exceptions\DropboxClientException;

/**
 * DropboxGuzzleHttpClient.
 */
class DropboxGuzzleHttpClient implements DropboxHttpClientInterface
{
    /**
     * WPO\WC\PDF_Invoices_Pro\Vendor\GuzzleHttp client.
     *
     * @var \WPO\WC\PDF_Invoices_Pro\Vendor\GuzzleHttp\Client
     */
    protected $client;

    /**
     * Create a new DropboxGuzzleHttpClient instance.
     *
     * @param Client $client WPO\WC\PDF_Invoices_Pro\Vendor\GuzzleHttp Client
     */
    public function __construct(Client $client = null)
    {
        //Set the client
        $this->client = $client ?: new Client();
    }

    /**
     * Send request to the server and fetch the raw response.
     *
     * @param  string $url     URL/Endpoint to send the request to
     * @param  string $method  Request Method
     * @param  string|resource|StreamInterface $body Request Body
     * @param  array  $headers Request Headers
     * @param  array  $options Additional Options
     *
     * @return \WPO\WC\PDF_Invoices_Pro\Vendor\Kunnu\Dropbox\Http\DropboxRawResponse Raw response from the server
     *
     * @throws \WPO\WC\PDF_Invoices_Pro\Vendor\Kunnu\Dropbox\Exceptions\DropboxClientException
     */
    public function send($url, $method, $body, $headers = [], $options = [])
    {
        //Create a new Request Object
        $request = new Request($method, $url, $headers, $body);

        try {
            //Send the Request
            $rawResponse = $this->client->send($request, $options);
        } catch (BadResponseException $e) {
            throw new DropboxClientException($e->getResponse()->getBody(), $e->getCode(), $e);
        } catch (RequestException $e) {
            $rawResponse = $e->getResponse();

            if (! $rawResponse instanceof ResponseInterface) {
                throw new DropboxClientException($e->getMessage(), $e->getCode());
            }
        }

        //Something went wrong
        if ($rawResponse->getStatusCode() >= 400) {
            throw new DropboxClientException($rawResponse->getBody());
        }

        if (array_key_exists('sink', $options)) {
            //Response Body is saved to a file
            $body = '';
        } else {
            //Get the Response Body
            $body = $this->getResponseBody($rawResponse);
        }

        $rawHeaders = $rawResponse->getHeaders();
        $httpStatusCode = $rawResponse->getStatusCode();

        //Create and return a DropboxRawResponse object
        return new DropboxRawResponse($rawHeaders, $body, $httpStatusCode);
    }

    /**
     * Get the Response Body.
     *
     * @param string|\WPO\WC\PDF_Invoices_Pro\Vendor\Psr\Http\Message\ResponseInterface $response Response object
     *
     * @return string
     */
    protected function getResponseBody($response)
    {
        //Response must be string
        $body = $response;

        if ($response instanceof ResponseInterface) {
            //Fetch the body
            $body = $response->getBody();
        }

        if ($body instanceof StreamInterface) {
            $body = $body->getContents();
        }

        return (string) $body;
    }
}
