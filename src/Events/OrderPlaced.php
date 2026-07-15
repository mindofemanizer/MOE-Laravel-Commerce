<?php

declare(strict_types=1);

namespace Moe\Commerce\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Moe\Commerce\Contracts\OrderInterface;

class OrderPlaced
{
    use Dispatchable;

    public function __construct(public OrderInterface $order) {}
}

