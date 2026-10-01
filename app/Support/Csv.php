<?php

namespace App\Support;

class Csv
{
    /**
     * Stop spreadsheet apps from treating a cell as a formula.
     */
    public static function safe(mixed $value): mixed
    {
        if (! is_string($value) || $value === '') {
            return $value;
        }

        if (preg_match('/^[=+\-@\t\r]/', $value) === 1) {
            return "'".$value;
        }

        return $value;
    }

    /**
     * @param  array<int, mixed>  $values
     * @return array<int, mixed>
     */
    public static function row(array $values): array
    {
        return array_map(self::safe(...), $values);
    }
}
