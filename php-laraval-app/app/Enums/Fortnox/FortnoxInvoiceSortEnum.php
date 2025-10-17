<?php

namespace App\Enums\Fortnox;

use ArchTech\Enums\InvokableCases;
use ArchTech\Enums\Values;

/**
 * Enum: customername, customernumber, documentnumber, invoicedate, ocr, total
 */
enum FortnoxInvoiceSortEnum: string
{
    use InvokableCases;
    use Values;

    case CustomerName = 'customername';
    case CustomerNumber = 'customernumber';
    case DocumentNumber = 'documentnumber';
    case InvoiceDate = 'invoicedate';
    case OCR = 'ocr';
    case Total = 'total';
}
