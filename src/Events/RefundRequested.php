<?php

declare(strict_types=1);

namespace Moe\Commerce\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Moe\Commerce\Models\Order;
use Moe\Commerce\Models\Refund;

class RefundRequested
{
    use Dispatchable;

    public function __construct(public Order $order, public Refund $refund) {}
}
