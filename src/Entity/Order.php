<?php

declare(strict_types=1);

namespace CoffeeShop\Entity;

use CoffeeShop\Enum\OrderStatus;
use DateTimeImmutable;

/**
 * Order Entity
 *
 * Represents a customer order containing one or more drinks.
 */
class Order
{
    private ?int $id;
    private string $customerName;
    private OrderStatus $status;
    private ?string $notes;
    /** @var list<OrderItem> */
    private array $items;
    private ?DateTimeImmutable $createdAt;
    private ?DateTimeImmutable $updatedAt;

    /**
     * @param list<OrderItem> $items
     */
    public function __construct(
        string $customerName,
        OrderStatus $status = OrderStatus::Pending,
        ?string $notes = null,
        array $items = [],
        ?int $id = null,
        ?DateTimeImmutable $createdAt = null,
        ?DateTimeImmutable $updatedAt = null,
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
     *
     * @param array<string, mixed> $data
     * @param list<OrderItem> $items
     */
    public static function fromArray(array $data, array $items = []): self
    {
        return new self(
            customerName: $data['customer_name'],
            status: OrderStatus::tryFrom($data['status'] ?? '') ?? OrderStatus::Pending,
            notes: $data['notes'] ?? null,
            items: $items,
            id: isset($data['id']) ? (int) $data['id'] : null,
            createdAt: isset($data['created_at']) ? new DateTimeImmutable($data['created_at']) : null,
            updatedAt: isset($data['updated_at']) ? new DateTimeImmutable($data['updated_at']) : null,
        );
    }

    /**
     * Convert to array for JSON serialization
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'customer_name' => $this->customerName,
            'status' => $this->status->value,
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
            $total += $item->price * $item->quantity;
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
     * Check if status string is valid
     */
    public static function isValidStatus(string $status): bool
    {
        return OrderStatus::isValid($status);
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

    public function getStatus(): OrderStatus
    {
        return $this->status;
    }

    public function getStatusValue(): string
    {
        return $this->status->value;
    }

    public function setStatus(OrderStatus|string $status): void
    {
        if (is_string($status)) {
            $enumStatus = OrderStatus::tryFrom($status);
            if ($enumStatus === null) {
                throw new \InvalidArgumentException("Invalid status: $status");
            }
            $this->status = $enumStatus;
        } else {
            $this->status = $status;
        }
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
     * @return list<OrderItem>
     */
    public function getItems(): array
    {
        return $this->items;
    }

    public function getCreatedAt(): ?DateTimeImmutable
    {
        return $this->createdAt;
    }
}
