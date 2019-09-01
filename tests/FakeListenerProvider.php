<?php

declare(strict_types=1);

namespace UniqueIdentityManager\Tests;

use League\Event\ListenerAcceptorInterface;
use League\Event\ListenerInterface;
use League\Event\ListenerProviderInterface;
use UniqueIdentityManager\Events\CustomerNewDeviceEvent;
use UniqueIdentityManager\Events\NewDeviceIdentityKeyEvent;
use UniqueIdentityManager\Events\UpdateCustomerIdentityKeyEvent;

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
        $listenerAcceptor->addListener(CustomerNewDeviceEvent::EVENT_NAME, $this->listener);
        $listenerAcceptor->addListener(NewDeviceIdentityKeyEvent::EVENT_NAME, $this->listener);
        $listenerAcceptor->addListener(UpdateCustomerIdentityKeyEvent::EVENT_NAME, $this->listener);
    }
}
