<?php

namespace WPO\WC\PDF_Invoices_Pro\Vendor\Psr\Http\Client;

use WPO\WC\PDF_Invoices_Pro\Vendor\Psr\Http\Message\RequestInterface;
use WPO\WC\PDF_Invoices_Pro\Vendor\Psr\Http\Message\ResponseInterface;

interface ClientInterface
{
    /**
     * Sends a PSR-7 request and returns a PSR-7 response.
     *
     * @param RequestInterface $request
     *
     * @return ResponseInterface
     *
     * @throws \WPO\WC\PDF_Invoices_Pro\Vendor\Psr\Http\Client\ClientExceptionInterface If an error happens while processing the request.
     */
    public function sendRequest(RequestInterface $request): ResponseInterface;
}
