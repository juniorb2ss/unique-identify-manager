<?php

declare(strict_types=1);

namespace UniqueIdentityManager\Contracts;

interface StorageInterface
{
    public function exists(string $key): bool;

    public function get(string $key): ?string;

    public function set(string $key, string $value): void;
}
