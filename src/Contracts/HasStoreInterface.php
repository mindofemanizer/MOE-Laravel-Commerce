<?php

namespace Moe\Commerce\Contracts;

interface HasStoreInterface
{
    public function store();
    public function getStoreId(): ?int;
    public function isOwnerOfStore(int $storeId): bool;
}
