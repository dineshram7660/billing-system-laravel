<?php

namespace App\Support;

/**
 * Converts a whole-rupee amount to words using the Indian numbering system
 * (hundred / thousand / lakh / crore groups), matching the legacy
 * getIndianCurrency() used on printed invoices — see bill_print.php.
 */
class IndianCurrency
{
    private const array ONES = [
        '', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine', 'Ten',
        'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen', 'Eighteen', 'Nineteen',
    ];

    private const array TENS = [
        '', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety',
    ];

    public static function words(int $amount): string
    {
        if ($amount === 0) {
            return 'Zero Rupees Only';
        }

        $amount = abs($amount);
        $crore = intdiv($amount, 10000000);
        $amount %= 10000000;
        $lakh = intdiv($amount, 100000);
        $amount %= 100000;
        $thousand = intdiv($amount, 1000);
        $amount %= 1000;
        $hundred = intdiv($amount, 100);
        $rest = $amount % 100;

        $parts = [];

        if ($crore > 0) {
            $parts[] = self::twoDigits($crore).' Crore';
        }

        if ($lakh > 0) {
            $parts[] = self::twoDigits($lakh).' Lakh';
        }

        if ($thousand > 0) {
            $parts[] = self::twoDigits($thousand).' Thousand';
        }

        if ($hundred > 0) {
            $parts[] = self::ONES[$hundred].' Hundred';
        }

        if ($rest > 0) {
            $parts[] = self::twoDigits($rest);
        }

        return implode(' ', $parts).' Rupees Only';
    }

    private static function twoDigits(int $n): string
    {
        if ($n < 20) {
            return self::ONES[$n];
        }

        $tens = intdiv($n, 10);
        $ones = $n % 10;

        return trim(self::TENS[$tens].' '.self::ONES[$ones]);
    }
}
