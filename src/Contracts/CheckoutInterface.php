<?php

declare(strict_types=1);

namespace Moe\Commerce\Contracts;

interface CheckoutInterface
{
    public function process(array $items, array $address, array $shipping, array $payment): array;

    public function validateStock(array $items): bool;

    public function calculateTotal(array $items, array $shipping): float;
}
