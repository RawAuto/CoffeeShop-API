<?php

declare(strict_types=1);

namespace CoffeeShop\Entity;

use CoffeeShop\Enum\DrinkSize;
use CoffeeShop\Enum\DrinkType;
use DateTimeImmutable;

/**
 * Drink Entity
 *
 * Represents a drink type available in the coffee shop.
 * Contains business rules about allowed sizes and components.
 */
readonly class Drink
{
    /**
     * @param list<string> $allowedSizes
     * @param list<string> $components
     */
    public function __construct(
        public string $name,
        public string $slug,
        public DrinkType $type,
        public float $basePrice,
        public bool $hasMilk,
        public array $allowedSizes,
        public array $components,
        public ?int $id = null,
        public ?DateTimeImmutable $createdAt = null,
        public ?DateTimeImmutable $updatedAt = null,
    ) {}

    /**
     * Create a Drink entity from a database row
     *
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            slug: $data['slug'],
            type: DrinkType::from($data['type']),
            basePrice: (float) $data['base_price'],
            hasMilk: (bool) $data['has_milk'],
            allowedSizes: json_decode($data['allowed_sizes'], true) ?? [],
            components: json_decode($data['components'], true) ?? [],
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
            'name' => $this->name,
            'slug' => $this->slug,
            'type' => $this->type->value,
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
        $drinkSize = DrinkSize::tryFrom($size);

        if ($drinkSize === null) {
            return $this->basePrice;
        }

        return round($this->basePrice * $drinkSize->getPriceMultiplier(), 2);
    }
}
