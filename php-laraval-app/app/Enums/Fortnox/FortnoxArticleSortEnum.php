<?php

namespace App\Enums\Fortnox;

use ArchTech\Enums\InvokableCases;
use ArchTech\Enums\Values;

/**
 * Enum: articlenumber, quantityinstock, reservedquantity, stockvalue
 */
enum FortnoxArticleSortEnum: string
{
    use InvokableCases;
    use Values;

    case ArticleNumber = 'articlenumber';
    case QuantityInStock = 'quantityinstock';
    case ReservedQuantity = 'reservedquantity';
    case StockValue = 'stockvalue';
}
