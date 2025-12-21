<?php

declare(strict_types=1);

namespace CoffeeShop\Tests\Unit\Entity;

use CoffeeShop\Entity\Drink;
use CoffeeShop\Enum\DrinkType;
use PHPUnit\Framework\TestCase;

class DrinkTest extends TestCase
{
    public function testIsSizeAllowedReturnsTrueForAllowedSize(): void
    {
        $drink = $this->createDrink(['small', 'medium']);

        $this->assertTrue($drink->isSizeAllowed('small'));
        $this->assertTrue($drink->isSizeAllowed('medium'));
    }

    public function testIsSizeAllowedReturnsFalseForDisallowedSize(): void
    {
        $drink = $this->createDrink(['small']);

        $this->assertFalse($drink->isSizeAllowed('medium'));
        $this->assertFalse($drink->isSizeAllowed('large'));
    }

    public function testGetPriceForSizeReturnsBasePriceForSmall(): void
    {
        $drink = $this->createDrink(['small'], 2.50);

        $this->assertEquals(2.50, $drink->getPriceForSize('small'));
    }

    public function testGetPriceForSizeReturnsMultipliedPriceForMedium(): void
    {
        $drink = $this->createDrink(['medium'], 2.50);

        // Medium is 1.3x base price
        $this->assertEquals(3.25, $drink->getPriceForSize('medium'));
    }

    public function testGetPriceForSizeReturnsMultipliedPriceForLarge(): void
    {
        $drink = $this->createDrink(['large'], 2.50);

        // Large is 1.6x base price
        $this->assertEquals(4.00, $drink->getPriceForSize('large'));
    }

    public function testFromArrayCreatesValidEntity(): void
    {
        $data = [
            'id' => 1,
            'name' => 'Espresso',
            'slug' => 'espresso',
            'type' => 'coffee',
            'base_price' => '2.50',
            'has_milk' => 0,
            'allowed_sizes' => '["small"]',
            'components' => '["shot of coffee"]',
            'created_at' => '2024-01-01 00:00:00',
            'updated_at' => '2024-01-01 00:00:00',
        ];

        $drink = Drink::fromArray($data);

        $this->assertEquals(1, $drink->id);
        $this->assertEquals('Espresso', $drink->name);
        $this->assertEquals('espresso', $drink->slug);
        $this->assertEquals(DrinkType::Coffee, $drink->type);
        $this->assertEquals(2.50, $drink->basePrice);
        $this->assertFalse($drink->hasMilk);
        $this->assertEquals(['small'], $drink->allowedSizes);
        $this->assertEquals(['shot of coffee'], $drink->components);
    }

    public function testToArrayReturnsExpectedStructure(): void
    {
        $drink = $this->createDrink(['small', 'medium'], 3.50);

        $array = $drink->toArray();

        $this->assertArrayHasKey('id', $array);
        $this->assertArrayHasKey('name', $array);
        $this->assertArrayHasKey('slug', $array);
        $this->assertArrayHasKey('type', $array);
        $this->assertArrayHasKey('base_price', $array);
        $this->assertArrayHasKey('has_milk', $array);
        $this->assertArrayHasKey('allowed_sizes', $array);
        $this->assertArrayHasKey('components', $array);
        $this->assertEquals('coffee', $array['type']); // Enum value is serialized
    }

    /**
     * @param string[] $allowedSizes
     */
    private function createDrink(array $allowedSizes, float $basePrice = 2.50): Drink
    {
        return new Drink(
            name: 'Test Drink',
            slug: 'test-drink',
            type: DrinkType::Coffee,
            basePrice: $basePrice,
            hasMilk: false,
            allowedSizes: $allowedSizes,
            components: ['shot of coffee'],
            id: 1,
        );
    }
}
