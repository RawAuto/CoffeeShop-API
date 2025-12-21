<?php

declare(strict_types=1);

namespace CoffeeShop\Tests\Unit\Service;

use CoffeeShop\Entity\Drink;
use CoffeeShop\Entity\Order;
use CoffeeShop\Entity\OrderItem;
use CoffeeShop\Enum\DrinkSize;
use CoffeeShop\Enum\DrinkType;
use CoffeeShop\Enum\OrderStatus;
use CoffeeShop\Repository\DrinkRepositoryInterface;
use CoffeeShop\Repository\OrderRepositoryInterface;
use CoffeeShop\Service\DrinkService;
use CoffeeShop\Service\OrderService;
use CoffeeShop\Service\ValidationResult;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

class OrderServiceTest extends TestCase
{
    private OrderService $service;
    private MockObject&OrderRepositoryInterface $mockOrderRepository;
    private MockObject&DrinkRepositoryInterface $mockDrinkRepository;

    protected function setUp(): void
    {
        $this->mockOrderRepository = $this->createMock(OrderRepositoryInterface::class);
        $this->mockDrinkRepository = $this->createMock(DrinkRepositoryInterface::class);

        $drinkService = new DrinkService($this->mockDrinkRepository);
        $this->service = new OrderService($this->mockOrderRepository, $drinkService);
    }

    public function testCreateOrderFailsWithEmptyCustomerName(): void
    {
        $result = $this->service->createOrder('', [['drink_id' => 1, 'size' => 'small']]);

        $this->assertInstanceOf(ValidationResult::class, $result);
        $this->assertStringContainsString('Customer name', $result->getError());
    }

    public function testCreateOrderFailsWithNoItems(): void
    {
        $result = $this->service->createOrder('John Doe', []);

        $this->assertInstanceOf(ValidationResult::class, $result);
        $this->assertStringContainsString('at least one item', $result->getError());
    }

    public function testCreateOrderFailsWithMissingDrinkId(): void
    {
        $result = $this->service->createOrder('John Doe', [
            ['size' => 'small'],
        ]);

        $this->assertInstanceOf(ValidationResult::class, $result);
        $this->assertStringContainsString('drink_id is required', $result->getError());
    }

    public function testCreateOrderFailsWithMissingSize(): void
    {
        $result = $this->service->createOrder('John Doe', [
            ['drink_id' => 1],
        ]);

        $this->assertInstanceOf(ValidationResult::class, $result);
        $this->assertStringContainsString('size is required', $result->getError());
    }

    public function testCreateOrderFailsWithInvalidSize(): void
    {
        $result = $this->service->createOrder('John Doe', [
            ['drink_id' => 1, 'size' => 'extra-large'],
        ]);

        $this->assertInstanceOf(ValidationResult::class, $result);
        $this->assertStringContainsString('Invalid size', $result->getError());
    }

    public function testCreateOrderFailsWithInvalidQuantity(): void
    {
        $this->mockDrinkRepository
            ->method('findById')
            ->willReturn($this->createDrink(1, 'Espresso', ['small']));

        $result = $this->service->createOrder('John Doe', [
            ['drink_id' => 1, 'size' => 'small', 'quantity' => 15],
        ]);

        $this->assertInstanceOf(ValidationResult::class, $result);
        $this->assertStringContainsString('Quantity must be between', $result->getError());
    }

    public function testCreateOrderFailsWithInvalidDrinkSizeCombination(): void
    {
        // Espresso only allows 'small'
        $this->mockDrinkRepository
            ->method('findById')
            ->with(1)
            ->willReturn($this->createDrink(1, 'Espresso', ['small']));

        $result = $this->service->createOrder('John Doe', [
            ['drink_id' => 1, 'size' => 'large'],
        ]);

        $this->assertInstanceOf(ValidationResult::class, $result);
        $this->assertStringContainsString('not available', $result->getError());
    }

    public function testCreateOrderSucceedsWithValidData(): void
    {
        $drink = $this->createDrink(1, 'Espresso', ['small'], 2.50);

        $this->mockDrinkRepository
            ->method('findById')
            ->with(1)
            ->willReturn($drink);

        $savedOrder = new Order(
            'John Doe',
            OrderStatus::Pending,
            null,
            [new OrderItem(1, DrinkSize::Small, 2.50, 1, null, 1, 1)],
            1,
        );

        $this->mockOrderRepository
            ->expects($this->once())
            ->method('save')
            ->willReturn($savedOrder);

        $result = $this->service->createOrder('John Doe', [
            ['drink_id' => 1, 'size' => 'small'],
        ]);

        $this->assertInstanceOf(Order::class, $result);
        $this->assertEquals('John Doe', $result->getCustomerName());
    }

    public function testUpdateOrderFailsForNonexistentOrder(): void
    {
        $this->mockOrderRepository
            ->method('findById')
            ->with(999)
            ->willReturn(null);

        $result = $this->service->updateOrder(999, ['status' => 'preparing']);

        $this->assertInstanceOf(ValidationResult::class, $result);
        $this->assertStringContainsString('not found', $result->getError());
        $this->assertTrue($result->isNotFound());
    }

    public function testUpdateOrderFailsWithInvalidStatus(): void
    {
        $order = new Order('John Doe', OrderStatus::Pending, null, [], 1);

        $this->mockOrderRepository
            ->method('findById')
            ->with(1)
            ->willReturn($order);

        $result = $this->service->updateOrder(1, ['status' => 'invalid-status']);

        $this->assertInstanceOf(ValidationResult::class, $result);
        $this->assertStringContainsString('Invalid status', $result->getError());
    }

    public function testUpdateOrderSucceedsWithValidStatus(): void
    {
        $order = new Order('John Doe', OrderStatus::Pending, null, [], 1);
        $updatedOrder = new Order('John Doe', OrderStatus::Preparing, null, [], 1);

        $this->mockOrderRepository
            ->method('findById')
            ->with(1)
            ->willReturn($order);

        $this->mockOrderRepository
            ->expects($this->once())
            ->method('update')
            ->willReturn($updatedOrder);

        $result = $this->service->updateOrder(1, ['status' => 'preparing']);

        $this->assertInstanceOf(Order::class, $result);
        $this->assertEquals(OrderStatus::Preparing, $result->getStatus());
    }

    public function testDeleteOrderReturnsFalseWhenNotFound(): void
    {
        $this->mockOrderRepository
            ->expects($this->once())
            ->method('delete')
            ->with(999)
            ->willReturn(false);

        $result = $this->service->deleteOrder(999);

        $this->assertFalse($result);
    }

    public function testDeleteOrderReturnsTrueWhenSuccessful(): void
    {
        $this->mockOrderRepository
            ->expects($this->once())
            ->method('delete')
            ->with(1)
            ->willReturn(true);

        $result = $this->service->deleteOrder(1);

        $this->assertTrue($result);
    }

    /**
     * Helper to create a Drink entity for testing
     *
     * @param string[] $allowedSizes
     */
    private function createDrink(int $id, string $name, array $allowedSizes, float $basePrice = 2.50): Drink
    {
        return new Drink(
            name: $name,
            slug: strtolower($name),
            type: DrinkType::Coffee,
            basePrice: $basePrice,
            hasMilk: false,
            allowedSizes: $allowedSizes,
            components: ['shot of coffee'],
            id: $id,
        );
    }
}
