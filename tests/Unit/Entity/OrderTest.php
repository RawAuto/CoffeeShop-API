<?php

declare(strict_types=1);

namespace CoffeeShop\Tests\Unit\Entity;

use CoffeeShop\Entity\Order;
use CoffeeShop\Entity\OrderItem;
use CoffeeShop\Enum\DrinkSize;
use CoffeeShop\Enum\OrderStatus;
use PHPUnit\Framework\TestCase;

class OrderTest extends TestCase
{
    public function testIsValidStatusReturnsTrueForValidStatuses(): void
    {
        $this->assertTrue(Order::isValidStatus('pending'));
        $this->assertTrue(Order::isValidStatus('preparing'));
        $this->assertTrue(Order::isValidStatus('ready'));
        $this->assertTrue(Order::isValidStatus('completed'));
        $this->assertTrue(Order::isValidStatus('cancelled'));
    }

    public function testIsValidStatusReturnsFalseForInvalidStatus(): void
    {
        $this->assertFalse(Order::isValidStatus('invalid'));
        $this->assertFalse(Order::isValidStatus(''));
        $this->assertFalse(Order::isValidStatus('PENDING')); // Case-sensitive
    }

    public function testGetTotalReturnsZeroForEmptyOrder(): void
    {
        $order = new Order('John Doe');

        $this->assertEquals(0.0, $order->getTotal());
    }

    public function testGetTotalCalculatesCorrectSum(): void
    {
        $order = new Order('John Doe');
        $order->addItem(new OrderItem(1, DrinkSize::Small, 2.50, 2)); // 5.00
        $order->addItem(new OrderItem(2, DrinkSize::Medium, 3.00, 1)); // 3.00

        $this->assertEquals(8.00, $order->getTotal());
    }

    public function testSetStatusThrowsExceptionForInvalidStatus(): void
    {
        $order = new Order('John Doe');

        $this->expectException(\InvalidArgumentException::class);
        $order->setStatus('invalid');
    }

    public function testSetStatusWorksForValidStatus(): void
    {
        $order = new Order('John Doe');

        $order->setStatus(OrderStatus::Preparing);

        $this->assertEquals(OrderStatus::Preparing, $order->getStatus());
    }

    public function testSetStatusWorksWithStringValue(): void
    {
        $order = new Order('John Doe');

        $order->setStatus('preparing');

        $this->assertEquals(OrderStatus::Preparing, $order->getStatus());
    }

    public function testFromArrayCreatesValidEntity(): void
    {
        $data = [
            'id' => 1,
            'customer_name' => 'Jane Doe',
            'status' => 'pending',
            'notes' => 'Extra hot',
            'created_at' => '2024-01-01 12:00:00',
            'updated_at' => '2024-01-01 12:00:00',
        ];

        $order = Order::fromArray($data);

        $this->assertEquals(1, $order->getId());
        $this->assertEquals('Jane Doe', $order->getCustomerName());
        $this->assertEquals(OrderStatus::Pending, $order->getStatus());
        $this->assertEquals('pending', $order->getStatusValue());
        $this->assertEquals('Extra hot', $order->getNotes());
    }

    public function testToArrayReturnsExpectedStructure(): void
    {
        $order = new Order('John Doe', OrderStatus::Pending, 'Test notes', [], 1);

        $array = $order->toArray();

        $this->assertArrayHasKey('id', $array);
        $this->assertArrayHasKey('customer_name', $array);
        $this->assertArrayHasKey('status', $array);
        $this->assertArrayHasKey('notes', $array);
        $this->assertArrayHasKey('items', $array);
        $this->assertArrayHasKey('total', $array);
        $this->assertArrayHasKey('created_at', $array);
        $this->assertArrayHasKey('updated_at', $array);
        $this->assertEquals('pending', $array['status']); // Enum value is serialized
    }

    public function testAddItemIncreasesItemCount(): void
    {
        $order = new Order('John Doe');

        $this->assertCount(0, $order->getItems());

        $order->addItem(new OrderItem(1, DrinkSize::Small, 2.50));

        $this->assertCount(1, $order->getItems());
    }
}
