<?php

declare(strict_types=1);

namespace Moe\Commerce\Contracts;

interface SellableInterface
{
    public function isAvailable(): bool;
    public function getStock(): int;
    public function getPrice(): float;
    public function getMinimumOrder(): int;
    public function getStoreId(): ?int;
}
