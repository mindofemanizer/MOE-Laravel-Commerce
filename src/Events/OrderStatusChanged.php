<?php

declare(strict_types=1);

namespace Moe\Commerce\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Moe\Commerce\Models\Order;

class OrderStatusChanged
{
    use Dispatchable;

    public function __construct(
        public Order $order,
        public string $oldStatus,
        public string $newStatus,
    ) {}
}

