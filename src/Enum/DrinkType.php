<?php

declare(strict_types=1);

namespace CoffeeShop\Enum;

/**
 * Drink Type Enum
 *
 * Represents the category of drink (coffee or tea based).
 */
enum DrinkType: string
{
    case Coffee = 'coffee';
    case Tea = 'tea';

    /**
     * Get all valid type values as strings
     *
     * @return string[]
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Check if a string is a valid type
     */
    public static function isValid(string $value): bool
    {
        return in_array($value, self::values(), true);
    }
}

