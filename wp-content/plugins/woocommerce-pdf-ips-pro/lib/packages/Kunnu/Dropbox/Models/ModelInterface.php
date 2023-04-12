<?php
namespace WPO\WC\PDF_Invoices_Pro\Vendor\Kunnu\Dropbox\Models;

interface ModelInterface
{

    /**
     * Get the Model data
     *
     * @return array
     */
    public function getData();

    /**
     * Get Data Property
     *
     * @param  string $property
     *
     * @return mixed
     */
    public function getDataProperty($property);
}
