<?php

declare(strict_types=1);

namespace CoffeeShop\Service;

use CoffeeShop\Entity\Drink;
use CoffeeShop\Repository\DrinkRepository;
use CoffeeShop\Repository\DrinkRepositoryInterface;

/**
 * Drink Service
 * 
 * Business logic for drink operations.
 */
class DrinkService
{
    private DrinkRepositoryInterface $drinkRepository;

    public function __construct(?DrinkRepositoryInterface $drinkRepository = null)
    {
        $this->drinkRepository = $drinkRepository ?? new DrinkRepository();
    }

    /**
     * Get all available drinks
     * 
     * @return Drink[]
     */
    public function getAllDrinks(): array
    {
        return $this->drinkRepository->findAll();
    }

    /**
     * Get a drink by ID
     */
    public function getDrinkById(int $id): ?Drink
    {
        return $this->drinkRepository->findById($id);
    }

    /**
     * Validate that a size is allowed for a drink
     */
    public function validateDrinkSize(int $drinkId, string $size): ValidationResult
    {
        $drink = $this->drinkRepository->findById($drinkId);
        
        if ($drink === null) {
            return ValidationResult::failure("Drink with ID $drinkId not found");
        }
        
        if (!$drink->isSizeAllowed($size)) {
            $allowedSizes = implode(', ', $drink->getAllowedSizes());
            return ValidationResult::failure(
                "Size '$size' is not available for {$drink->getName()}. Allowed sizes: $allowedSizes"
            );
        }
        
        return ValidationResult::success();
    }

    /**
     * Get the price for a drink in a specific size
     */
    public function getDrinkPrice(int $drinkId, string $size): ?float
    {
        $drink = $this->drinkRepository->findById($drinkId);
        
        if ($drink === null || !$drink->isSizeAllowed($size)) {
            return null;
        }
        
        return $drink->getPriceForSize($size);
    }
}

