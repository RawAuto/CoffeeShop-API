<?php

declare(strict_types=1);

namespace CoffeeShop\Repository;

use CoffeeShop\Entity\Order;

/**
 * Order Repository Interface
 * 
 * Defines the contract for order data access.
 */
interface OrderRepositoryInterface
{
    /**
     * Find all orders
     * 
     * @param int $limit Maximum number of orders to return
     * @param int $offset Offset for pagination
     * @return Order[]
     */
    public function findAll(int $limit = 50, int $offset = 0): array;

    /**
     * Find an order by ID (with items)
     */
    public function findById(int $id): ?Order;

    /**
     * Save a new order (with items)
     */
    public function save(Order $order): Order;

    /**
     * Update an existing order
     */
    public function update(Order $order): Order;

    /**
     * Delete an order
     */
    public function delete(int $id): bool;

    /**
     * Count total orders
     */
    public function count(): int;
}

