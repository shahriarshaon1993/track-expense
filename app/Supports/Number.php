<?php

namespace App\Supports;

class Number
{
    public static function short($number): string
    {
        $units = ['', 'K', 'M', 'B', 'T'];

        $power = $number > 0 ? floor(log($number, 1000)) : 0;

        return number_format($number / pow(1000, $power), 1) . $units[$power];
    }
}
