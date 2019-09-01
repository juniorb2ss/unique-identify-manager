<?php

declare(strict_types=1);

namespace UniqueIdentityManager\Tests;

use League\Event\AbstractListener;
use League\Event\EventInterface;

class FakeListener extends AbstractListener
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
}
