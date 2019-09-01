<?php

declare(strict_types=1);

namespace UniqueIdentityManager\Tests;

use League\Event\EventInterface;
use League\Event\ListenerInterface;

class FakeListener implements ListenerInterface
{
    /**
     * @var array
     */
    protected $events = [];

    /**
     * Handle an event.
     */
    public function handle(EventInterface $event): void
    {
        $this->events[] = $event;
    }

    public function getEvents(): array
    {
        return $this->events;
    }

    /**
     * Check whether the listener is the given parameter.
     *
     *
     * @return bool
     */
    public function isListener($listener)
    {
        // TODO: Implement isListener() method.
    }
}
