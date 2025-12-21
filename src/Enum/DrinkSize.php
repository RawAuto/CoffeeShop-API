<?php

declare(strict_types=1);

namespace CoffeeShop\Enum;

/**
 * Drink Size Enum
 *
 * Represents the available sizes for drinks.
 * Each size has an associated price multiplier.
 */
enum DrinkSize: string
{
    case Small = 'small';
    case Medium = 'medium';
    case Large = 'large';

    /**
     * Get the price multiplier for this size
     */
    public function getPriceMultiplier(): float
    {
        return match ($this) {
            self::Small => 1.0,
            self::Medium => 1.3,
            self::Large => 1.6,
        };
    }

    /**
     * Get all valid size values as strings
     *
     * @return string[]
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Check if a string is a valid size
     */
    public static function isValid(string $value): bool
    {
        return in_array($value, self::values(), true);
    }
}

