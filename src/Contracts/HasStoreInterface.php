<?php

declare(strict_types=1);

namespace Moe\Commerce\Contracts;

interface HasStoreInterface
{
    public function store();

    public function getStoreId(): ?int;

    public function isOwnerOfStore(int $storeId): bool;
}
