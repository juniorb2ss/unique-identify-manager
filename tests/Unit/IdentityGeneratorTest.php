<?php

declare(strict_types=1);

namespace UniqueIdentityManager\Tests\Unit;

use Ramsey\Uuid\UuidInterface;
use UniqueIdentityManager\IdentityGenerator;
use UniqueIdentityManager\Tests\TestCase;

class IdentityGeneratorTest extends TestCase
{
    public function testGeneratingNewIdentityKey(): void
    {
        $identityGenerator = new IdentityGenerator();
        $actual = $identityGenerator->generate();

        $this->assertInstanceOf(UuidInterface::class, $actual);
    }
}
