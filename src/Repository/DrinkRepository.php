<?php

declare(strict_types=1);

namespace CoffeeShop\Repository;

use CoffeeShop\Entity\Drink;
use PDO;

/**
 * MySQL Drink Repository
 *
 * Implements drink data access using MySQL database.
 */
class DrinkRepository implements DrinkRepositoryInterface
{
    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? Database::getInstance();
    }

    /**
     * @inheritDoc
     */
    public function findAll(): array
    {
        $sql = 'SELECT * FROM drinks ORDER BY name ASC';
        $stmt = $this->db->query($sql);

        if ($stmt === false) {
            return [];
        }

        $drinks = [];
        while ($row = $stmt->fetch()) {
            $drinks[] = Drink::fromArray($row);
        }

        return $drinks;
    }

    /**
     * @inheritDoc
     */
    public function findById(int $id): ?Drink
    {
        $sql = 'SELECT * FROM drinks WHERE id = :id LIMIT 1';
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);

        $row = $stmt->fetch();

        if (!$row) {
            return null;
        }

        return Drink::fromArray($row);
    }

    /**
     * @inheritDoc
     */
    public function findBySlug(string $slug): ?Drink
    {
        $sql = 'SELECT * FROM drinks WHERE slug = :slug LIMIT 1';
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['slug' => $slug]);

        $row = $stmt->fetch();

        if (!$row) {
            return null;
        }

        return Drink::fromArray($row);
    }
}
