<?php

declare(strict_types=1);

namespace UniqueIdentityManager;

use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class IdentityGenerator
{
    public function generate(): UuidInterface
    {
        return Uuid::uuid1();
    }
}
