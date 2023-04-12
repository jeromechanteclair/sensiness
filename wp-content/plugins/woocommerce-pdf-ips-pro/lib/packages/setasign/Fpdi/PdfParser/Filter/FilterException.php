<?php

/**
 * This file is part of FPDI
 *
 * @package   WPO\WC\PDF_Invoices_Pro\Vendor\setasign\Fpdi
 * @copyright Copyright (c) 2020 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @license   http://opensource.org/licenses/mit-license The MIT License
 */

namespace WPO\WC\PDF_Invoices_Pro\Vendor\setasign\Fpdi\PdfParser\Filter;

use WPO\WC\PDF_Invoices_Pro\Vendor\setasign\Fpdi\PdfParser\PdfParserException;

/**
 * Exception for filters
 */
class FilterException extends PdfParserException
{
    const UNSUPPORTED_FILTER = 0x0201;

    const NOT_IMPLEMENTED = 0x0202;
}
