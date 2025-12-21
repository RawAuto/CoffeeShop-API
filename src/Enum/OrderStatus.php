<?php

declare(strict_types=1);

namespace CoffeeShop\Enum;

/**
 * Order Status Enum
 *
 * Represents the possible states of a customer order.
 */
enum OrderStatus: string
{
    case Pending = 'pending';
    case Preparing = 'preparing';
    case Ready = 'ready';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    /**
     * Get all valid status values as strings
     *
     * @return string[]
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Check if a string is a valid status
     */
    public static function isValid(string $value): bool
    {
        return in_array($value, self::values(), true);
    }

    /**
     * Try to create from string, returns null if invalid
     */
    public static function tryFromString(string $value): ?self
    {
        return self::tryFrom($value);
    }
}

