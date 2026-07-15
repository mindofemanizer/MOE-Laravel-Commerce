<?php

namespace Moe\Commerce\Contracts;

interface OrderInterface
{
    public function getOrderNumber(): string;
    public function getStatus(): string;
    public function getTotal(): float;
    public function canBeCancelled(): bool;
    public function canBeRefunded(): bool;
}
