<?php

declare(strict_types=1);

namespace CoffeeShop\Service;

use CoffeeShop\Entity\Order;
use CoffeeShop\Entity\OrderItem;
use CoffeeShop\Enum\DrinkSize;
use CoffeeShop\Enum\OrderStatus;
use CoffeeShop\Repository\OrderRepository;
use CoffeeShop\Repository\OrderRepositoryInterface;

/**
 * Order Service
 *
 * Business logic for order operations.
 * Handles validation, creation, and management of orders.
 */
class OrderService
{
    private OrderRepositoryInterface $orderRepository;
    private DrinkService $drinkService;

    public function __construct(
        ?OrderRepositoryInterface $orderRepository = null,
        ?DrinkService $drinkService = null,
    ) {
        $this->orderRepository = $orderRepository ?? new OrderRepository();
        $this->drinkService = $drinkService ?? new DrinkService();
    }

    /**
     * Get all orders with pagination
     *
     * @return array{orders: list<Order>, total: int, limit: int, offset: int}
     */
    public function getAllOrders(int $limit = 50, int $offset = 0): array
    {
        return [
            'orders' => $this->orderRepository->findAll($limit, $offset),
            'total' => $this->orderRepository->count(),
            'limit' => $limit,
            'offset' => $offset,
        ];
    }

    /**
     * Get a single order by ID
     */
    public function getOrderById(int $id): ?Order
    {
        return $this->orderRepository->findById($id);
    }

    /**
     * Create a new order
     *
     * @param string $customerName Customer's name
     * @param list<array{drink_id?: int, size?: string, quantity?: int, cup_text?: string|null}> $items Array of item data
     * @param string|null $notes Optional order notes
     * @return Order|ValidationResult Returns Order on success, ValidationResult on failure
     */
    public function createOrder(string $customerName, array $items, ?string $notes = null): Order|ValidationResult
    {
        // Validate customer name
        if (trim($customerName) === '') {
            return ValidationResult::failure('Customer name is required');
        }

        // Validate items
        if (empty($items)) {
            return ValidationResult::failure('Order must contain at least one item');
        }

        // Create order entity
        $order = new Order($customerName, OrderStatus::Pending, $notes);

        // Validate and add each item
        foreach ($items as $index => $itemData) {
            $result = $this->validateAndCreateItem($itemData, $index);

            if ($result instanceof ValidationResult) {
                return $result;
            }

            $order->addItem($result);
        }

        // Save and return
        return $this->orderRepository->save($order);
    }

    /**
     * Validate item data and create an OrderItem
     *
     * @param array{drink_id?: int, size?: string, quantity?: int, cup_text?: string|null} $data
     * @return OrderItem|ValidationResult
     */
    private function validateAndCreateItem(array $data, int $index): OrderItem|ValidationResult
    {
        // Validate required fields
        if (!isset($data['drink_id'])) {
            return ValidationResult::failure("Item $index: drink_id is required");
        }

        if (!isset($data['size'])) {
            return ValidationResult::failure("Item $index: size is required");
        }

        $drinkId = (int) $data['drink_id'];
        $sizeString = $data['size'];
        $quantity = (int) ($data['quantity'] ?? 1);
        $cupText = $data['cup_text'] ?? null;

        // Validate size value using enum
        $size = DrinkSize::tryFrom($sizeString);
        if ($size === null) {
            $validSizes = implode(', ', DrinkSize::values());
            return ValidationResult::failure("Item $index: Invalid size '$sizeString'. Valid sizes: $validSizes");
        }

        // Validate quantity
        if ($quantity < 1 || $quantity > 10) {
            return ValidationResult::failure("Item $index: Quantity must be between 1 and 10");
        }

        // Validate drink exists and size is allowed
        $sizeValidation = $this->drinkService->validateDrinkSize($drinkId, $sizeString);
        if (!$sizeValidation->isValid()) {
            return ValidationResult::failure("Item $index: " . $sizeValidation->getError());
        }

        // Get price
        $price = $this->drinkService->getDrinkPrice($drinkId, $sizeString);
        if ($price === null) {
            return ValidationResult::failure("Item $index: Unable to calculate price");
        }

        return new OrderItem(
            drinkId: $drinkId,
            size: $size,
            price: $price,
            quantity: $quantity,
            cupText: $cupText,
        );
    }

    /**
     * Update an existing order (status, notes, customer name)
     *
     * @param array<string, mixed> $data
     */
    public function updateOrder(int $id, array $data): Order|ValidationResult
    {
        $order = $this->orderRepository->findById($id);

        if ($order === null) {
            return ValidationResult::notFound("Order with ID $id not found");
        }

        // Update allowed fields
        if (isset($data['customer_name'])) {
            $customerName = trim($data['customer_name']);
            if ($customerName === '') {
                return ValidationResult::failure('Customer name cannot be empty');
            }
            $order->setCustomerName($customerName);
        }

        if (isset($data['status'])) {
            $status = OrderStatus::tryFrom($data['status']);
            if ($status === null) {
                $validStatuses = implode(', ', OrderStatus::values());
                return ValidationResult::failure("Invalid status. Valid statuses: $validStatuses");
            }
            $order->setStatus($status);
        }

        if (array_key_exists('notes', $data)) {
            $order->setNotes($data['notes']);
        }

        return $this->orderRepository->update($order);
    }

    /**
     * Delete an order
     */
    public function deleteOrder(int $id): bool
    {
        return $this->orderRepository->delete($id);
    }
}
