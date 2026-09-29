<?php

namespace App\Support;

class PhoneNumberNormalizer
{
    /**
     * Normalize an Indonesian phone number to canonical digits-only format '628...'.
     */
    public static function normalize(?string $phone): ?string
    {
        if ($phone === null) {
            return null;
        }

        $trimmed = trim($phone);
        if ($trimmed === '') {
            return null;
        }

        // Remove formatting characters except plus
        $cleaned = preg_replace('/[^\d+]/', '', $trimmed);

        if (str_starts_with($cleaned, '+62')) {
            $cleaned = '62'.substr($cleaned, 3);
        } elseif (str_starts_with($cleaned, '+')) {
            $cleaned = substr($cleaned, 1);
        }

        // Keep digits only
        $digits = preg_replace('/\D/', '', $cleaned);

        if (str_starts_with($digits, '08')) {
            $digits = '628'.substr($digits, 2);
        } elseif (str_starts_with($digits, '8')) {
            $digits = '62'.$digits;
        }

        return $digits !== '' ? $digits : null;
    }
}
