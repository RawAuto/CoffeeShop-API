<?php

declare(strict_types=1);

namespace CoffeeShop\Entity;

/**
 * Order Item Entity
 * 
 * Represents a single drink item within an order.
 */
class OrderItem
{
    public const SIZE_SMALL = 'small';
    public const SIZE_MEDIUM = 'medium';
    public const SIZE_LARGE = 'large';

    public const VALID_SIZES = [
        self::SIZE_SMALL,
        self::SIZE_MEDIUM,
        self::SIZE_LARGE,
    ];

    private ?int $id;
    private ?int $orderId;
    private int $drinkId;
    private string $size;
    private int $quantity;
    private ?string $cupText;
    private float $price;
    private ?\DateTimeImmutable $createdAt;

    // Hydrated drink data (optional, loaded from join)
    private ?string $drinkName = null;

    public function __construct(
        int $drinkId,
        string $size,
        float $price,
        int $quantity = 1,
        ?string $cupText = null,
        ?int $orderId = null,
        ?int $id = null,
        ?\DateTimeImmutable $createdAt = null
    ) {
        $this->id = $id;
        $this->orderId = $orderId;
        $this->drinkId = $drinkId;
        $this->size = $size;
        $this->quantity = $quantity;
        $this->cupText = $cupText;
        $this->price = $price;
        $this->createdAt = $createdAt;
    }

    /**
     * Create an OrderItem entity from a database row
     */
    public static function fromArray(array $data): self
    {
        $item = new self(
            drinkId: (int)$data['drink_id'],
            size: $data['size'],
            price: (float)$data['price'],
            quantity: (int)($data['quantity'] ?? 1),
            cupText: $data['cup_text'] ?? null,
            orderId: isset($data['order_id']) ? (int)$data['order_id'] : null,
            id: isset($data['id']) ? (int)$data['id'] : null,
            createdAt: isset($data['created_at']) ? new \DateTimeImmutable($data['created_at']) : null
        );

        // Hydrate drink name if available from join
        if (isset($data['drink_name'])) {
            $item->drinkName = $data['drink_name'];
        }

        return $item;
    }

    /**
     * Convert to array for JSON serialization
     */
    public function toArray(): array
    {
        $data = [
            'id' => $this->id,
            'drink_id' => $this->drinkId,
            'size' => $this->size,
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
     * Check if size is valid
     */
    public static function isValidSize(string $size): bool
    {
        return in_array($size, self::VALID_SIZES, true);
    }

    // Getters
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOrderId(): ?int
    {
        return $this->orderId;
    }

    public function setOrderId(int $orderId): void
    {
        $this->orderId = $orderId;
    }

    public function getDrinkId(): int
    {
        return $this->drinkId;
    }

    public function getSize(): string
    {
        return $this->size;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function getCupText(): ?string
    {
        return $this->cupText;
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    public function getDrinkName(): ?string
    {
        return $this->drinkName;
    }

    public function setDrinkName(string $name): void
    {
        $this->drinkName = $name;
    }
}

