<?php

namespace Moe\Commerce\Services;

use Illuminate\Support\Facades\DB;
use Moe\Commerce\Events\OrderStatusChanged;
use Moe\Commerce\Events\RefundRequested;
use Moe\Commerce\Models\Order;
use Moe\Commerce\Models\Refund;

class OrderService
{
    /**
     * Get order by number.
     */
    public function getByNumber(string $orderNumber): ?Order
    {
        return Order::with('items.product', 'store', 'payments', 'refunds')
            ->where('order_number', $orderNumber)
            ->first();
    }

    /**
     * Cancel order.
     */
    public function cancel(Order $order, ?string $reason = null): void
    {
        if (! $order->canBeCancelled()) {
            throw new \Exception('Pesanan tidak dapat dibatalkan');
        }

        DB::transaction(function () use ($order, $reason) {
            $oldStatus = $order->status;

            // Restore stock
            foreach ($order->items as $item) {
                $item->product->inventory()->increment('quantity', $item->quantity);
            }

            $order->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
                'cancel_reason' => $reason,
            ]);

            event(new OrderStatusChanged($order, $oldStatus, 'cancelled'));
        });
    }

    /**
     * Update order status.
     */
    public function updateStatus(Order $order, string $status): void
    {
        $validTransitions = [
            'pending' => ['processing', 'cancelled'],
            'processing' => ['shipping', 'cancelled'],
            'shipping' => ['delivered'],
            'delivered' => ['completed'],
        ];

        $allowed = $validTransitions[$order->status] ?? [];

        if (! in_array($status, $allowed)) {
            throw new \Exception("Transisi status dari '{$order->status}' ke '{$status}' tidak valid");
        }

        $oldStatus = $order->status;
        $updateData = ['status' => $status];

        if ($status === 'delivered') {
            $updateData['delivered_at'] = now();
        } elseif ($status === 'completed') {
            $updateData['completed_at'] = now();
        } elseif ($status === 'cancelled') {
            $updateData['cancelled_at'] = now();
        }

        $order->update($updateData);

        event(new OrderStatusChanged($order, $oldStatus, $status));
    }

    /**
     * Request refund.
     */
    public function requestRefund(Order $order, float $amount, ?string $reason = null): Refund
    {
        if (! $order->canBeRefunded()) {
            throw new \Exception('Pesanan tidak dapat diajukan pengembalian');
        }

        $refund = Refund::create([
            'order_id' => $order->id,
            'amount' => $amount,
            'reason' => $reason,
            'status' => 'pending',
        ]);

        event(new RefundRequested($order, $refund));

        return $refund;
    }

    /**
     * Get user orders.
     */
    public function getUserOrders(int $userId, int $limit = 20): \Illuminate\Database\Eloquent\Collection
    {
        return Order::where('user_id', $userId)
            ->with('store', 'items.product')
            ->latest()
            ->limit($limit)
            ->get();
    }

    /**
     * Get store orders.
     */
    public function getStoreOrders(int $storeId, int $limit = 20): \Illuminate\Database\Eloquent\Collection
    {
        return Order::where('store_id', $storeId)
            ->with('user', 'items.product')
            ->latest()
            ->limit($limit)
            ->get();
    }
}
