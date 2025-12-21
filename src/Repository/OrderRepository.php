<?php

declare(strict_types=1);

namespace CoffeeShop\Repository;

use CoffeeShop\Entity\Order;
use CoffeeShop\Entity\OrderItem;
use PDO;

/**
 * MySQL Order Repository
 *
 * Implements order data access using MySQL database.
 */
class OrderRepository implements OrderRepositoryInterface
{
    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? Database::getInstance();
    }

    /**
     * @inheritDoc
     */
    public function findAll(int $limit = 50, int $offset = 0): array
    {
        $sql = 'SELECT * FROM orders ORDER BY created_at DESC LIMIT :limit OFFSET :offset';
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue('offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        $orders = [];
        while ($row = $stmt->fetch()) {
            $items = $this->findItemsByOrderId((int) $row['id']);
            $orders[] = Order::fromArray($row, $items);
        }

        return $orders;
    }

    /**
     * @inheritDoc
     */
    public function findById(int $id): ?Order
    {
        $sql = 'SELECT * FROM orders WHERE id = :id LIMIT 1';
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);

        $row = $stmt->fetch();

        if (!$row) {
            return null;
        }

        $items = $this->findItemsByOrderId($id);

        return Order::fromArray($row, $items);
    }

    /**
     * Find all items for an order
     *
     * @return OrderItem[]
     */
    private function findItemsByOrderId(int $orderId): array
    {
        $sql = 'SELECT oi.*, d.name as drink_name
                FROM order_items oi
                JOIN drinks d ON oi.drink_id = d.id
                WHERE oi.order_id = :order_id';
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['order_id' => $orderId]);

        $items = [];
        while ($row = $stmt->fetch()) {
            $items[] = OrderItem::fromArray($row);
        }

        return $items;
    }

    /**
     * @inheritDoc
     */
    public function save(Order $order): Order
    {
        $this->db->beginTransaction();

        try {
            // Insert order
            $sql = 'INSERT INTO orders (customer_name, status, notes) VALUES (:customer_name, :status, :notes)';
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'customer_name' => $order->getCustomerName(),
                'status' => $order->getStatus()->value,
                'notes' => $order->getNotes(),
            ]);

            $orderId = (int) $this->db->lastInsertId();
            $order->setId($orderId);

            // Insert items
            foreach ($order->getItems() as $item) {
                $this->saveItem($item, $orderId);
            }

            $this->db->commit();

            // Reload to get timestamps and hydrated data
            $savedOrder = $this->findById($orderId);
            if ($savedOrder === null) {
                throw new \RuntimeException("Failed to reload order after save");
            }
            return $savedOrder;

        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Save an order item
     */
    private function saveItem(OrderItem $item, int $orderId): void
    {
        $sql = 'INSERT INTO order_items (order_id, drink_id, size, quantity, cup_text, price)
                VALUES (:order_id, :drink_id, :size, :quantity, :cup_text, :price)';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'order_id' => $orderId,
            'drink_id' => $item->drinkId,
            'size' => $item->size->value,
            'quantity' => $item->quantity,
            'cup_text' => $item->cupText,
            'price' => $item->price,
        ]);
    }

    /**
     * @inheritDoc
     */
    public function update(Order $order): Order
    {
        $orderId = $order->getId();
        if ($orderId === null) {
            throw new \InvalidArgumentException("Cannot update order without ID");
        }

        $sql = 'UPDATE orders SET customer_name = :customer_name, status = :status, notes = :notes WHERE id = :id';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'id' => $orderId,
            'customer_name' => $order->getCustomerName(),
            'status' => $order->getStatus()->value,
            'notes' => $order->getNotes(),
        ]);

        $updatedOrder = $this->findById($orderId);
        if ($updatedOrder === null) {
            throw new \RuntimeException("Failed to reload order after update");
        }
        return $updatedOrder;
    }

    /**
     * @inheritDoc
     */
    public function delete(int $id): bool
    {
        $sql = 'DELETE FROM orders WHERE id = :id';
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);

        return $stmt->rowCount() > 0;
    }

    /**
     * @inheritDoc
     */
    public function count(): int
    {
        $sql = 'SELECT COUNT(*) FROM orders';
        $stmt = $this->db->query($sql);
        if ($stmt === false) {
            return 0;
        }
        return (int) $stmt->fetchColumn();
    }
}
