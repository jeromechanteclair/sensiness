<?php
namespace WPO\WC\PDF_Invoices_Pro\Vendor\GuzzleHttp\Exception;

use WPO\WC\PDF_Invoices_Pro\Vendor\Psr\Http\Message\RequestInterface;

/**
 * Exception thrown when a connection cannot be established.
 *
 * Note that no response is present WPO_WCPDF_IPS_PRO_for a ConnectException
 */
class ConnectException extends RequestException
{
    public function __construct(
        $message,
        RequestInterface $request,
        \Exception $previous = null,
        array $handlerContext = []
    ) {
        parent::__construct($message, $request, null, $previous, $handlerContext);
    }

    /**
     * @return null
     */
    public function getResponse()
    {
        return null;
    }

    /**
     * @return bool
     */
    public function hasResponse()
    {
        return false;
    }
}
