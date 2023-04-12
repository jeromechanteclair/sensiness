<?php

namespace WPO\WC\PDF_Invoices_Pro\Vendor\iio\libmergepdf;

interface PagesInterface
{
    /**
     * @return int[]
     */
    public function getPageNumbers(): array;
}
