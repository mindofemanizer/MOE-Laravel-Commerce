<?php

declare(strict_types=1);

namespace Moe\Commerce\Services;

use Illuminate\Support\Facades\DB;
use Moe\Commerce\Models\Invoice;
use Moe\Commerce\Models\Order;

class InvoiceService
{
    /**
     * Generate invoice from order.
     */
    public function generateFromOrder(Order $order, string $type = Invoice::TYPE_INVOICE): Invoice
    {
        return DB::transaction(function () use ($order, $type) {
            return Invoice::create([
                'invoice_number' => Invoice::generateInvoiceNumber($type),
                'order_id' => $order->id,
                'user_id' => $order->user_id,
                'store_id' => $order->store_id,
                'type' => $type,
                'status' => Invoice::STATUS_UNPAID,
                'subtotal' => $order->subtotal,
                'discount' => $order->discount,
                'shipping_cost' => $order->shipping_cost,
                'platform_fee' => $order->platform_fee ?? 0,
                'tax' => $order->tax ?? 0,
                'total' => $order->total,
                'due_date' => now()->addDays(config('commerce.invoice.due_days', 14)),
                'metadata' => [
                    'order_number' => $order->order_number,
                    'payment_method' => $order->payment_method,
                    'items_count' => $order->items->count(),
                    'customer_name' => $order->user->name,
                    'customer_email' => $order->user->email,
                ],
            ]);
        });
    }

    /**
     * Create credit note (for refunds).
     */
    public function createCreditNote(Order $order, float $amount, ?string $reason = null): Invoice
    {
        return DB::transaction(function () use ($order, $amount, $reason) {
            return Invoice::create([
                'invoice_number' => Invoice::generateInvoiceNumber(Invoice::TYPE_CREDIT_NOTE),
                'order_id' => $order->id,
                'user_id' => $order->user_id,
                'store_id' => $order->store_id,
                'type' => Invoice::TYPE_CREDIT_NOTE,
                'status' => Invoice::STATUS_REFUNDED,
                'subtotal' => 0,
                'discount' => 0,
                'shipping_cost' => 0,
                'platform_fee' => 0,
                'tax' => 0,
                'total' => -$amount,
                'due_date' => null,
                'notes' => $reason,
                'metadata' => [
                    'original_order' => $order->order_number,
                    'refund_amount' => $amount,
                    'reason' => $reason,
                ],
            ]);
        });
    }

    /**
     * Get invoice by number.
     */
    public function getByNumber(string $invoiceNumber): ?Invoice
    {
        return Invoice::with('order.items', 'user', 'store')
            ->where('invoice_number', $invoiceNumber)
            ->first();
    }

    /**
     * Get invoices for a user.
     */
    public function getUserInvoices(int $userId, int $limit = 20): \Illuminate\Database\Eloquent\Collection
    {
        return Invoice::where('user_id', $userId)
            ->with('order', 'store')
            ->latest()
            ->limit($limit)
            ->get();
    }

    /**
     * Mark invoice as paid.
     */
    public function markAsPaid(Invoice $invoice, ?string $reference = null): void
    {
        $invoice->markAsPaid($reference);
    }
}
