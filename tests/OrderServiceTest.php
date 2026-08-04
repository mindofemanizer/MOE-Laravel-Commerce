<?php

use Moe\Commerce\Models\Order;
use Moe\Commerce\Services\OrderService;

beforeEach(function () {
    $this->service = new OrderService();
});

it('can create order', function () {
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

    expect($order)->toBeInstanceOf(Order::class);
    expect($order->status)->toEqual('pending');
});

it('can cancel order', function () {
    $order = Order::create([
        'order_number' => 'ORD-TEST-002',
        'user_id' => 1,
        'store_id' => 1,
        'status' => 'pending',
        'payment_method' => 'transfer',
        'subtotal' => 50000,
        'total' => 50000,
    ]);

    expect($order->canBeCancelled())->toBeTrue();

    $this->service->cancel($order, 'Test cancel');
    expect($order->fresh()->status)->toEqual('cancelled');
});

it('cannot cancel completed order', function () {
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

    expect(fn () => $this->service->cancel($order))->toThrow(\Exception::class);
});
