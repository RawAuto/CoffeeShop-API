<?php

declare(strict_types=1);

namespace CoffeeShop\Entity;

/**
 * Order Entity
 * 
 * Represents a customer order containing one or more drinks.
 */
class Order
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_PREPARING = 'preparing';
    public const STATUS_READY = 'ready';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';

    public const VALID_STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_PREPARING,
        self::STATUS_READY,
        self::STATUS_COMPLETED,
        self::STATUS_CANCELLED,
    ];

    private ?int $id;
    private string $customerName;
    private string $status;
    private ?string $notes;
    /** @var OrderItem[] */
    private array $items;
    private ?\DateTimeImmutable $createdAt;
    private ?\DateTimeImmutable $updatedAt;

    public function __construct(
        string $customerName,
        string $status = self::STATUS_PENDING,
        ?string $notes = null,
        array $items = [],
        ?int $id = null,
        ?\DateTimeImmutable $createdAt = null,
        ?\DateTimeImmutable $updatedAt = null
    ) {
        $this->id = $id;
        $this->customerName = $customerName;
        $this->status = $status;
        $this->notes = $notes;
        $this->items = $items;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }

    /**
     * Create an Order entity from a database row
     */
    public static function fromArray(array $data, array $items = []): self
    {
        return new self(
            customerName: $data['customer_name'],
            status: $data['status'] ?? self::STATUS_PENDING,
            notes: $data['notes'] ?? null,
            items: $items,
            id: isset($data['id']) ? (int)$data['id'] : null,
            createdAt: isset($data['created_at']) ? new \DateTimeImmutable($data['created_at']) : null,
            updatedAt: isset($data['updated_at']) ? new \DateTimeImmutable($data['updated_at']) : null
        );
    }

    /**
     * Convert to array for JSON serialization
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'customer_name' => $this->customerName,
            'status' => $this->status,
            'notes' => $this->notes,
            'items' => array_map(fn(OrderItem $item) => $item->toArray(), $this->items),
            'total' => $this->getTotal(),
            'created_at' => $this->createdAt?->format('c'),
            'updated_at' => $this->updatedAt?->format('c'),
        ];
    }

    /**
     * Calculate total order price
     */
    public function getTotal(): float
    {
        $total = 0.0;
        foreach ($this->items as $item) {
            $total += $item->getPrice() * $item->getQuantity();
        }
        return round($total, 2);
    }

    /**
     * Add an item to the order
     */
    public function addItem(OrderItem $item): void
    {
        $this->items[] = $item;
    }

    /**
     * Check if status is valid
     */
    public static function isValidStatus(string $status): bool
    {
        return in_array($status, self::VALID_STATUSES, true);
    }

    // Getters and Setters
    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getCustomerName(): string
    {
        return $this->customerName;
    }

    public function setCustomerName(string $customerName): void
    {
        $this->customerName = $customerName;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): void
    {
        if (!self::isValidStatus($status)) {
            throw new \InvalidArgumentException("Invalid status: $status");
        }
        $this->status = $status;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): void
    {
        $this->notes = $notes;
    }

    /**
     * @return OrderItem[]
     */
    public function getItems(): array
    {
        return $this->items;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }
}

