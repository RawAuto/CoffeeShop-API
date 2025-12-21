<?php

declare(strict_types=1);

namespace CoffeeShop\Entity;

use CoffeeShop\Enum\DrinkSize;
use DateTimeImmutable;

/**
 * Order Item Entity
 *
 * Represents a single drink item within an order.
 */
readonly class OrderItem
{
    public function __construct(
        public int $drinkId,
        public DrinkSize $size,
        public float $price,
        public int $quantity = 1,
        public ?string $cupText = null,
        public ?int $orderId = null,
        public ?int $id = null,
        public ?DateTimeImmutable $createdAt = null,
        public ?string $drinkName = null,
    ) {}

    /**
     * Create an OrderItem entity from a database row
     *
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            drinkId: (int) $data['drink_id'],
            size: DrinkSize::from($data['size']),
            price: (float) $data['price'],
            quantity: (int) ($data['quantity'] ?? 1),
            cupText: $data['cup_text'] ?? null,
            orderId: isset($data['order_id']) ? (int) $data['order_id'] : null,
            id: isset($data['id']) ? (int) $data['id'] : null,
            createdAt: isset($data['created_at']) ? new DateTimeImmutable($data['created_at']) : null,
            drinkName: $data['drink_name'] ?? null,
        );
    }

    /**
     * Convert to array for JSON serialization
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $data = [
            'id' => $this->id,
            'drink_id' => $this->drinkId,
            'size' => $this->size->value,
            'quantity' => $this->quantity,
            'cup_text' => $this->cupText,
            'price' => $this->price,
            'subtotal' => round($this->price * $this->quantity, 2),
        ];

        if ($this->drinkName !== null) {
            $data['drink_name'] = $this->drinkName;
        }

        return $data;
    }

    /**
     * Check if size string is valid
     */
    public static function isValidSize(string $size): bool
    {
        return DrinkSize::isValid($size);
    }
}
