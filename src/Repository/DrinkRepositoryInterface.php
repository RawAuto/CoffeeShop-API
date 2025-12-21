<?php

declare(strict_types=1);

namespace CoffeeShop\Repository;

use CoffeeShop\Entity\Drink;

/**
 * Drink Repository Interface
 * 
 * Defines the contract for drink data access.
 * Implementations can use MySQL, in-memory storage, etc.
 */
interface DrinkRepositoryInterface
{
    /**
     * Find all drinks
     * 
     * @return Drink[]
     */
    public function findAll(): array;

    /**
     * Find a drink by ID
     */
    public function findById(int $id): ?Drink;

    /**
     * Find a drink by slug
     */
    public function findBySlug(string $slug): ?Drink;
}

