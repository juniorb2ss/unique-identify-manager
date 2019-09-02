<?php

declare(strict_types=1);

namespace UniqueIdentityManager\Tests;

use League\Event\Emitter;
use League\Event\EmitterInterface;
use PHPUnit\Framework\TestCase as BaseTestCase;
use UniqueIdentityManager\EventHandler;

abstract class TestCase extends BaseTestCase
{
    /**
     * @var EmitterInterface
     */
    protected $emitter;

    /**
     * @var FakeListener
     */
    protected $listener;

    /**
     * @var EventHandler
     */
    protected $eventHandler;

    protected function setUp(): void
    {
        $this->emitter = new Emitter();
        $this->listener = new FakeListener();

        $this->emitter->useListenerProvider(new FakeListenerProvider($this->listener));

        $this->eventHandler = new EventHandler($this->emitter);

        parent::setUp();
    }
}
