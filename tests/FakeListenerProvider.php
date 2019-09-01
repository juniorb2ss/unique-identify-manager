<?php

declare(strict_types=1);

namespace UniqueIdentityManager\Tests;

use League\Event\ListenerAcceptorInterface;
use League\Event\ListenerInterface;
use League\Event\ListenerProviderInterface;

class FakeListenerProvider implements ListenerProviderInterface
{
    /**
     * @var ListenerInterface
     */
    protected $listener;

    public function __construct(ListenerInterface $listener)
    {
        $this->listener = $listener;
    }

    public function provideListeners(ListenerAcceptorInterface $listenerAcceptor): void
    {
        $listenerAcceptor->addListener('*', $this->listener);
    }
}
