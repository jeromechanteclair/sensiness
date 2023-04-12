<?php
namespace WPO\WC\PDF_Invoices_Pro\Vendor\GuzzleHttp\Handler;

use WPO\WC\PDF_Invoices_Pro\Vendor\GuzzleHttp\RequestOptions;
use WPO\WC\PDF_Invoices_Pro\Vendor\Psr\Http\Message\RequestInterface;

/**
 * Provides basic proxies WPO_WCPDF_IPS_PRO_for handlers.
 */
class Proxy
{
    /**
     * Sends synchronous requests to a specific handler while sending all other
     * requests to another handler.
     *
     * @param callable $default Handler used WPO_WCPDF_IPS_PRO_for normal responses
     * @param callable $sync    Handler used WPO_WCPDF_IPS_PRO_for synchronous responses.
     *
     * @return callable Returns the composed handler.
     */
    public static function wrapSync(
        callable $default,
        callable $sync
    ) {
        return function (RequestInterface $request, array $options) use ($default, $sync) {
            return empty($options[RequestOptions::SYNCHRONOUS])
                ? $default($request, $options)
                : $sync($request, $options);
        };
    }

    /**
     * Sends streaming requests to a streaming compatible handler while sending
     * all other requests to a default handler.
     *
     * This, WPO_WCPDF_IPS_PRO_for example, could be useful WPO_WCPDF_IPS_PRO_for taking advantage of the
     * performance benefits of curl while still supporting true streaming
     * through the StreamHandler.
     *
     * @param callable $default   Handler used WPO_WCPDF_IPS_PRO_for non-streaming responses
     * @param callable $streaming Handler used WPO_WCPDF_IPS_PRO_for streaming responses
     *
     * @return callable Returns the composed handler.
     */
    public static function wrapStreaming(
        callable $default,
        callable $streaming
    ) {
        return function (RequestInterface $request, array $options) use ($default, $streaming) {
            return empty($options['stream'])
                ? $default($request, $options)
                : $streaming($request, $options);
        };
    }
}
