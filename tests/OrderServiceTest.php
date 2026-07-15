<?php

namespace Moe\Commerce\Tests;

use Moe\Commerce\Models\Order;
use Moe\Commerce\Models\Product;
use Moe\Commerce\Models\Store;
use Moe\Commerce\Services\OrderService;

class OrderServiceTest extends TestCase
{
    private OrderService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new OrderService();
    }

    public function test_can_create_order()
    {
        $order = Order::create([
            'order_number' => 'ORD-TEST-001',
            'user_id' => 1,
            'store_id' => 1,
            'status' => 'pending',
            'payment_method' => 'transfer',
            'subtotal' => 50000,
            'shipping_cost' => 10000,
            'total' => 60000,
        ]);

        $this->assertInstanceOf(Order::class, $order);
        $this->assertEquals('pending', $order->status);
    }

    public function test_can_cancel_order()
    {
        $order = Order::create([
            'order_number' => 'ORD-TEST-002',
            'user_id' => 1,
            'store_id' => 1,
            'status' => 'pending',
            'payment_method' => 'transfer',
            'subtotal' => 50000,
            'total' => 50000,
        ]);

        $this->assertTrue($order->canBeCancelled());

        $this->service->cancel($order, 'Test cancel');
        $this->assertEquals('cancelled', $order->fresh()->status);
    }

    public function test_cannot_cancel_completed_order()
    {
        $this->expectException(\Exception::class);

        $order = Order::create([
            'order_number' => 'ORD-TEST-003',
            'user_id' => 1,
            'store_id' => 1,
            'status' => 'completed',
            'payment_method' => 'transfer',
            'subtotal' => 50000,
            'total' => 50000,
            'completed_at' => now(),
        ]);

        $this->service->cancel($order);
    }
}
