<?php

namespace Moe\Commerce\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Moe\Commerce\Events\OrderPlaced;
use Moe\Commerce\Models\Order;
use Moe\Commerce\Models\OrderItem;
use Moe\Commerce\Models\Product;

class CheckoutService
{
    /**
     * Process checkout and create orders.
     */
    public function process(array $items, array $address, array $shipping, array $payment): array
    {
        return DB::transaction(function () use ($items, $address, $shipping, $payment) {
            // Group items by store for multi-store support
            $grouped = $this->groupItemsByStore($items);
            $orders = [];

            foreach ($grouped as $storeId => $storeItems) {
                $order = $this->createOrder($storeId, $storeItems, $address, $shipping[$storeId] ?? $shipping, $payment);
                $orders[] = $order;
            }

            return $orders;
        });
    }

    /**
     * Validate stock for all items.
     */
    public function validateStock(array $items): bool
    {
        foreach ($items as $item) {
            $product = Product::with('inventory')->find($item['product_id']);

            if (! $product || ! $product->isAvailable()) {
                return false;
            }

            if ($product->getStock() < $item['quantity']) {
                return false;
            }
        }

        return true;
    }

    /**
     * Calculate total for all items.
     */
    public function calculateTotal(array $items, array $shipping): float
    {
        $subtotal = collect($items)->sum(function ($item) {
            $product = Product::find($item['product_id']);
            return $product ? $product->retail_price * $item['quantity'] : 0;
        });

        $shippingCost = collect($shipping)->sum('cost');

        return $subtotal + $shippingCost;
    }

    /**
     * Group items by store.
     */
    protected function groupItemsByStore(array $items): array
    {
        $grouped = [];

        foreach ($items as $item) {
            $product = Product::find($item['product_id']);
            $storeId = $product->store_id ?? 0;
            $grouped[$storeId][] = $item;
        }

        return $grouped;
    }

    /**
     * Create order for a single store.
     */
    protected function createOrder(int $storeId, array $items, array $address, array $shipping, array $payment): Order
    {
        $subtotal = 0;
        $orderItems = [];

        foreach ($items as $item) {
            $product = Product::findOrFail($item['product_id']);
            $itemSubtotal = $product->retail_price * $item['quantity'];
            $subtotal += $itemSubtotal;

            $orderItems[] = [
                'product_id' => $product->id,
                'product_name' => $product->name,
                'quantity' => $item['quantity'],
                'unit' => $product->unit ?? 'pcs',
                'unit_price' => $product->retail_price,
                'subtotal' => $itemSubtotal,
                'discount' => 0,
            ];

            // Decrement stock
            $product->inventory()->decrement('quantity', $item['quantity']);
        }

        $shippingCost = $shipping['cost'] ?? 0;
        $total = $subtotal + $shippingCost;

        $order = Order::create([
            'order_number' => Order::generateOrderNumber(),
            'user_id' => Auth::id(),
            'store_id' => $storeId,
            'status' => 'pending',
            'payment_method' => $payment['method'] ?? 'bank_transfer',
            'payment_status' => 'unpaid',
            'subtotal' => $subtotal,
            'discount' => 0,
            'shipping_cost' => $shippingCost,
            'platform_fee' => 0,
            'total' => $total,
            'notes' => $payment['notes'] ?? null,
            'shipping_address_snapshot' => $address,
            'shipping_courier' => $shipping['courier'] ?? null,
            'shipping_service' => $shipping['service'] ?? null,
            'shipping_etd' => $shipping['etd'] ?? null,
        ]);

        foreach ($orderItems as $orderItem) {
            $order->items()->create($orderItem);
        }

        event(new OrderPlaced($order));

        return $order;
    }
}
