<?php

namespace App\Http\Traits;

use App\Enums\Fortnox\FortnoxInvoiceTypeEnum;
use App\Enums\Fortnox\FortnoxTypeEnum;
use App\Enums\Fortnox\FortnoxVatEnum;
use App\Models\User;

trait FortnoxCustomerTrait
{
    private static function convertToFortnox(User $user): array
    {
        $customer['type'] = FortnoxTypeEnum::Private->value;
        $customer['invoice_type'] = FortnoxInvoiceTypeEnum::Email->value;
        $customer['VATType'] = FortnoxVatEnum::Sevat->value;
        $customer['payment_terms'] = config('fortnox.terms_of_payment');
        $customer['note'] = null;

        return [
            'Customer' => [
                'CustomerNumber' => $user['fortnox_id'],
                'Name' => $user['fullname'],
                'Email' => $user['email'],
                'Type' => strtoupper($customer['type']),
                'OrganisationNumber' => $customer['identity_number'],
                'Address1' => $customer['address'],
                'ZipCode' => $customer['zip'],
                'City' => $customer['city'],
                'Phone1' => $customer['cellphone'],
                'Phone2' => $customer['phone'],
                'OurReference' => 'Klubbrickan',
                'EmailInvoice' => $customer['email'],
                'EmailOrder' => $customer['email'],
                'DefaultDeliveryTypes' => [
                    'Invoice' => $customer['invoice_type'],
                    'Order' => $customer['invoice_type'],
                ],
                'VATType' => $customer['VATType'],
                'TermsOfPayment' => $customer['payment_terms'],
                'Comments' => $customer['note'],
                'ShowPriceVATIncluded' => strtoupper($customer['type']) == 'PRIVATE' ? true : false,
            ],
        ];
    }

    private static function convertToLaravel(): array
    {
        $customer = self::$fortnoxCustomer['Customer'];
        $customer['type'] = 'PRIVATE'; // Enum: "PRIVATE" "COMPANY"
        $customer['invoice_type'] = 'EMAIL'; // Enum: "PRINT" "EMAIL" "PRINTSERVICE"
        $customer['VATType'] = 'SEVAT'; // Enum: "SEVAT" "SEREVERSEDVAT" "EUREVERSEDVAT" "EUVAT" "EXPORT"
        $customer['payment_terms'] = 30;
        $customer['note'] = null;

        return [
            'Customer' => [
                'CustomerNumber' => $customer['fortnox_id'],
                'Name' => $customer['fullname'],
                'Email' => $customer['email'],
                'Type' => strtoupper($customer['type']),
                'OrganisationNumber' => $customer['identity_number'],
                'Address1' => $customer['address'],
                'ZipCode' => $customer['zip'],
                'City' => $customer['city'],
                'Phone1' => $customer['cellphone'],
                'Phone2' => $customer['phone'],
                'OurReference' => 'Klubbrickan',
                'EmailInvoice' => $customer['email'],
                'EmailOrder' => $customer['email'],
                'DefaultDeliveryTypes' => [
                    'Invoice' => $customer['invoice_type'],
                    'Order' => $customer['invoice_type'],
                ],
                'VATType' => $customer['VATType'],
                'TermsOfPayment' => $customer['payment_terms'],
                'Comments' => $customer['note'],
                'ShowPriceVATIncluded' => strtoupper($customer['type']) == 'PRIVATE' ? true : false,
            ],
        ];
    }
}
