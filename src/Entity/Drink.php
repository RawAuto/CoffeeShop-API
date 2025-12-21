<?php

declare(strict_types=1);

namespace CoffeeShop\Entity;

/**
 * Drink Entity
 * 
 * Represents a drink type available in the coffee shop.
 * Contains business rules about allowed sizes and components.
 */
class Drink
{
    private ?int $id;
    private string $name;
    private string $slug;
    private string $type; // 'coffee' or 'tea'
    private float $basePrice;
    private bool $hasMilk;
    private array $allowedSizes;
    private array $components;
    private ?\DateTimeImmutable $createdAt;
    private ?\DateTimeImmutable $updatedAt;

    public function __construct(
        string $name,
        string $slug,
        string $type,
        float $basePrice,
        bool $hasMilk,
        array $allowedSizes,
        array $components,
        ?int $id = null,
        ?\DateTimeImmutable $createdAt = null,
        ?\DateTimeImmutable $updatedAt = null
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->slug = $slug;
        $this->type = $type;
        $this->basePrice = $basePrice;
        $this->hasMilk = $hasMilk;
        $this->allowedSizes = $allowedSizes;
        $this->components = $components;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }

    /**
     * Create a Drink entity from a database row
     */
    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            slug: $data['slug'],
            type: $data['type'],
            basePrice: (float)$data['base_price'],
            hasMilk: (bool)$data['has_milk'],
            allowedSizes: json_decode($data['allowed_sizes'], true) ?? [],
            components: json_decode($data['components'], true) ?? [],
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
            'name' => $this->name,
            'slug' => $this->slug,
            'type' => $this->type,
            'base_price' => $this->basePrice,
            'has_milk' => $this->hasMilk,
            'allowed_sizes' => $this->allowedSizes,
            'components' => $this->components,
        ];
    }

    /**
     * Check if a given size is allowed for this drink
     */
    public function isSizeAllowed(string $size): bool
    {
        return in_array($size, $this->allowedSizes, true);
    }

    /**
     * Calculate price for a given size
     */
    public function getPriceForSize(string $size): float
    {
        $multipliers = [
            'small' => 1.0,
            'medium' => 1.3,
            'large' => 1.6,
        ];

        $multiplier = $multipliers[$size] ?? 1.0;
        return round($this->basePrice * $multiplier, 2);
    }

    // Getters
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getBasePrice(): float
    {
        return $this->basePrice;
    }

    public function hasMilk(): bool
    {
        return $this->hasMilk;
    }

    public function getAllowedSizes(): array
    {
        return $this->allowedSizes;
    }

    public function getComponents(): array
    {
        return $this->components;
    }
}

