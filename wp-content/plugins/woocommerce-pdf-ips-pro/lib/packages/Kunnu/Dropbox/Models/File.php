<?php
namespace WPO\WC\PDF_Invoices_Pro\Vendor\Kunnu\Dropbox\Models;

use WPO\WC\PDF_Invoices_Pro\Vendor\Kunnu\Dropbox\DropboxFile;

class File extends BaseModel
{

    /**
     * The file contents
     *
     * @var string|DropboxFile
     */
    protected $contents;

    /**
     * File Metadata
     *
     * @var \WPO\WC\PDF_Invoices_Pro\Vendor\Kunnu\Dropbox\Models\FileMetadata
     */
    protected $metadata;


    /**
     * Create a new File instance
     *
     * @param array  $data
     * @param string|DropboxFile $contents
     */
    public function __construct(array $data, $contents)
    {
        parent::__construct($data);
        $this->contents = $contents;
        $this->metadata = new FileMetadata($data);
    }

    /**
     * The metadata WPO_WCPDF_IPS_PRO_for the file
     *
     * @return \WPO\WC\PDF_Invoices_Pro\Vendor\Kunnu\Dropbox\Models\FileMetadata
     */
    public function getMetadata()
    {
        return $this->metadata;
    }

    /**
     * Get the file contents
     *
     * @return string
     */
    public function getContents()
    {
        if ($this->contents instanceof DropboxFile) {
            return $this->contents->getContents();
        }
        return $this->contents;
    }
}
