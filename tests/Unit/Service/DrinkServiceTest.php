<?php

declare(strict_types=1);

namespace CoffeeShop\Tests\Unit\Service;

use CoffeeShop\Entity\Drink;
use CoffeeShop\Repository\DrinkRepositoryInterface;
use CoffeeShop\Service\DrinkService;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

class DrinkServiceTest extends TestCase
{
    private DrinkService $service;
    private MockObject $mockRepository;

    protected function setUp(): void
    {
        $this->mockRepository = $this->createMock(DrinkRepositoryInterface::class);
        $this->service = new DrinkService($this->mockRepository);
    }

    public function testGetAllDrinksReturnsArrayOfDrinks(): void
    {
        $drinks = [
            $this->createDrink(1, 'Espresso', ['small']),
            $this->createDrink(2, 'Latte', ['small', 'medium']),
        ];

        $this->mockRepository
            ->expects($this->once())
            ->method('findAll')
            ->willReturn($drinks);

        $result = $this->service->getAllDrinks();

        $this->assertCount(2, $result);
        $this->assertInstanceOf(Drink::class, $result[0]);
    }

    public function testGetDrinkByIdReturnsNullWhenNotFound(): void
    {
        $this->mockRepository
            ->expects($this->once())
            ->method('findById')
            ->with(999)
            ->willReturn(null);

        $result = $this->service->getDrinkById(999);

        $this->assertNull($result);
    }

    public function testGetDrinkByIdReturnsDrinkWhenFound(): void
    {
        $drink = $this->createDrink(1, 'Espresso', ['small']);

        $this->mockRepository
            ->expects($this->once())
            ->method('findById')
            ->with(1)
            ->willReturn($drink);

        $result = $this->service->getDrinkById(1);

        $this->assertInstanceOf(Drink::class, $result);
        $this->assertEquals('Espresso', $result->getName());
    }

    public function testValidateDrinkSizeFailsForNonexistentDrink(): void
    {
        $this->mockRepository
            ->expects($this->once())
            ->method('findById')
            ->with(999)
            ->willReturn(null);

        $result = $this->service->validateDrinkSize(999, 'small');

        $this->assertFalse($result->isValid());
        $this->assertStringContainsString('not found', $result->getError());
    }

    public function testValidateDrinkSizeFailsForInvalidSize(): void
    {
        $drink = $this->createDrink(1, 'Espresso', ['small']);

        $this->mockRepository
            ->expects($this->once())
            ->method('findById')
            ->with(1)
            ->willReturn($drink);

        $result = $this->service->validateDrinkSize(1, 'large');

        $this->assertFalse($result->isValid());
        $this->assertStringContainsString('not available', $result->getError());
    }

    public function testValidateDrinkSizeSucceedsForValidSize(): void
    {
        $drink = $this->createDrink(1, 'Latte', ['small', 'medium']);

        $this->mockRepository
            ->expects($this->once())
            ->method('findById')
            ->with(1)
            ->willReturn($drink);

        $result = $this->service->validateDrinkSize(1, 'medium');

        $this->assertTrue($result->isValid());
        $this->assertNull($result->getError());
    }

    public function testGetDrinkPriceReturnsNullForInvalidDrink(): void
    {
        $this->mockRepository
            ->method('findById')
            ->with(999)
            ->willReturn(null);

        $result = $this->service->getDrinkPrice(999, 'small');

        $this->assertNull($result);
    }

    public function testGetDrinkPriceReturnsNullForInvalidSize(): void
    {
        $drink = $this->createDrink(1, 'Espresso', ['small']);

        $this->mockRepository
            ->method('findById')
            ->with(1)
            ->willReturn($drink);

        $result = $this->service->getDrinkPrice(1, 'large');

        $this->assertNull($result);
    }

    public function testGetDrinkPriceReturnsPriceForValidSizeSmall(): void
    {
        $drink = $this->createDrink(1, 'Espresso', ['small'], 2.50);

        $this->mockRepository
            ->method('findById')
            ->with(1)
            ->willReturn($drink);

        $result = $this->service->getDrinkPrice(1, 'small');

        $this->assertEquals(2.50, $result);
    }

    public function testGetDrinkPriceReturnsPriceForValidSizeMedium(): void
    {
        $drink = $this->createDrink(1, 'Latte', ['small', 'medium'], 3.00);

        $this->mockRepository
            ->method('findById')
            ->with(1)
            ->willReturn($drink);

        $result = $this->service->getDrinkPrice(1, 'medium');

        // Medium is 1.3x base price
        $this->assertEquals(3.90, $result);
    }

    /**
     * Helper to create a Drink entity for testing
     */
    private function createDrink(int $id, string $name, array $allowedSizes, float $basePrice = 2.50): Drink
    {
        return new Drink(
            name: $name,
            slug: strtolower($name),
            type: 'coffee',
            basePrice: $basePrice,
            hasMilk: false,
            allowedSizes: $allowedSizes,
            components: ['shot of coffee'],
            id: $id
        );
    }
}

