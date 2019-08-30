<?php

namespace UniqueIdentityManager;

use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class IdentifyGenerator
{
    public function generate(): UuidInterface
    {
        return Uuid::uuid4();
    }
}
