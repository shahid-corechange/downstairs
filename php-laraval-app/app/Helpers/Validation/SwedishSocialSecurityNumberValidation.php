<?php

namespace App\Helpers\Validation;

class SwedishSocialSecurityNumberValidation
{
    /**
     * Validate Swedish social security number
     */
    public static function validate($value): bool
    {
        if (! $value) {
            return false;
        }

        // Remove any non-digit characters
        $ssn = preg_replace('/\D/', '', $value);

        // Check the length of the SSN
        if (strlen($ssn) !== 12) {
            return false;
        }

        // Validate the date part of the SSN
        $birthdate = substr($ssn, 0, 8);
        if (! self::validateDate($birthdate)) {
            return false;
        }

        // Validate the checksum using the Luhn algorithm
        return self::validateLuhn(substr($ssn, 2));
    }

    /**
     * Validate the birthdate part of the SSN.
     */
    private static function validateDate(string $birthdate): bool
    {
        $year = substr($birthdate, 0, 4);
        $month = substr($birthdate, 4, 2);
        $day = substr($birthdate, 6, 2);

        return checkdate($month, $day, $year);
    }

    /**
     * Validate the SSN using the Luhn algorithm.
     */
    private static function validateLuhn(string $number): bool
    {
        $sum = 0;
        $length = strlen($number);

        // Start from the rightmost digit
        for ($i = 0; $i < $length; $i++) {
            $digit = (int) $number[$length - $i - 1];

            // Double every second digit
            if ($i % 2 === 1) {
                $digit *= 2;
                if ($digit > 9) {
                    $digit -= 9;
                }
            }

            $sum += $digit;
        }

        // If the total modulo 10 is 0, the number is valid
        return ($sum % 10) === 0;
    }
}
