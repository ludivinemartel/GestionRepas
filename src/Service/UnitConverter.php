<?php

namespace App\Service;

class UnitConverter
{
    private static $conversionTable = [
        'tranche' => 28,
        'portion' => 25,
        'tasse' => 258,
        'verre' => 125,
        'bol' => 518,
        'cuillère à soupe' => 15,
        'cuillère à café' => 5,
        'g' => 1,
        'ml' => 1.3,
        'cl' => 10.3,
        'l' => 1030,
        'kg' => 1000,
        'piece' => 50,
    ];

    public function toGrams($quantity, $unit)
    {
        if (array_key_exists($unit, self::$conversionTable)) {
            return $quantity * self::$conversionTable[$unit];
        }

        throw new \Exception("Unité de mesure inconnue : $unit");
    }
}
