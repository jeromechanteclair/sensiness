<?php
namespace WPO\WC\PDF_Invoices_Pro\Vendor\Kunnu\Dropbox\Models;

use DateTime;

class CopyReference extends BaseModel
{

    /**
     * The expiration date of the copy reference
     *
     * @var DateTime
     */
    protected $expires;

    /**
     * The copy reference
     *
     * @var string
     */
    protected $reference;

    /**
     * File or Folder Metadata
     *
     * @var \WPO\WC\PDF_Invoices_Pro\Vendor\Kunnu\Dropbox\Models\FileMetadata|\WPO\WC\PDF_Invoices_Pro\Vendor\Kunnu\Dropbox\Models\FolderMetadata
     */
    protected $metadata;


    /**
     * Create a new CopyReference instance
     *
     * @param array $data
     */
    public function __construct(array $data)
    {
        parent::__construct($data);
        $this->expires = new DateTime($this->getDataProperty('expires'));
        $this->reference = $this->getDataProperty('copy_reference');
        $this->setMetadata();
    }

    /**
     * Set Metadata
     */
    protected function setMetadata()
    {
        $metadata = $this->getDataProperty('metadata');
        if (is_array($metadata)) {
            $this->metadata = ModelFactory::make($metadata);
        }
    }

    /**
     * Get the expiration date of the copy reference
     *
     * @return DateTime
     */
    public function getExpirationDate()
    {
        return $this->expires;
    }

    /**
     * The metadata WPO_WCPDF_IPS_PRO_for the file/folder
     *
     * @return \WPO\WC\PDF_Invoices_Pro\Vendor\Kunnu\Dropbox\Models\FileMetadata|\WPO\WC\PDF_Invoices_Pro\Vendor\Kunnu\Dropbox\Models\FolderMetadata
     */
    public function getMetadata()
    {
        return $this->metadata;
    }

    /**
     * Get the copy reference
     *
     * @return string
     */
    public function getReference()
    {
        return $this->reference;
    }
}
