<?php

namespace WPO\WC\PDF_Invoices_Pro\Vendor\iio\libmergepdf\Driver;

use WPO\WC\PDF_Invoices_Pro\Vendor\iio\libmergepdf\Source\SourceInterface;

interface DriverInterface
{
    /**
     * Merge multiple sources
     */
    public function merge(SourceInterface ...$sources): string;
}
