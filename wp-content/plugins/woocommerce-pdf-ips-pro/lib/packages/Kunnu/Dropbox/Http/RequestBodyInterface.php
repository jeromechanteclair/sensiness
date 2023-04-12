<?php
namespace WPO\WC\PDF_Invoices_Pro\Vendor\Kunnu\Dropbox\Http;

/**
 * RequestBodyInterface
 */
interface RequestBodyInterface
{
    /**
     * Get the Body of the Request
     *
     * @return string|resource|WPO\WC\PDF_Invoices_Pro\Vendor\Psr\Http\Message\StreamInterface
     */
    public function getBody();
}
