<?php
namespace WPO\WC\PDF_Invoices_Pro\Vendor\Kunnu\Dropbox\Http;

use WPO\WC\PDF_Invoices_Pro\Vendor\Kunnu\Dropbox\DropboxFile;

/**
 * RequestBodyStream
 */
class RequestBodyStream implements RequestBodyInterface
{

    /**
     * File to be sent with the Request
     *
     * @var \WPO\WC\PDF_Invoices_Pro\Vendor\Kunnu\Dropbox\DropboxFile
     */
    protected $file;

    /**
     * Create a new RequestBodyStream instance
     *
     * @param \WPO\WC\PDF_Invoices_Pro\Vendor\Kunnu\Dropbox\DropboxFile $file
     */
    public function __construct(DropboxFile $file)
    {
        $this->file = $file;
    }

    /**
     * Get the Body of the Request
     *
     * @return string
     */
    public function getBody()
    {
        return $this->file->getContents();
    }
}
