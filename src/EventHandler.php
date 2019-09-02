<?php

declare(strict_types=1);

namespace UniqueIdentityManager;

use League\Event\EmitterInterface;
use UniqueIdentityManager\Events\CustomerNewDeviceEvent;
use UniqueIdentityManager\Events\NewDeviceIdentityKeyEvent;
use UniqueIdentityManager\Events\UpdateCustomerIdentityKeyEvent;

class EventHandler
{
    /**
     * @var EmitterInterface
     */
    protected $emitter;

    public function __construct(EmitterInterface $emitter)
    {
        $this->emitter = $emitter;
    }

    public function newDevice(string $deviceUuid, string $identityKey, array $customAttributes = []): void
    {
        $this->emitter->emit(
            new NewDeviceIdentityKeyEvent(
                $deviceUuid,
                $identityKey,
                $customAttributes
            )
        );
    }

    public function customerNewIdentityKey(
        string $customerUuid,
        string $identityKey,
        array $customAttributes = []
    ): void {
        $this->emitter->emit(
            new UpdateCustomerIdentityKeyEvent(
                $customerUuid,
                $identityKey,
                $customAttributes
            )
        );
    }

    public function customerNewDevice(
        string $deviceUuid,
        string $customerUuid,
        string $identityKey,
        array $customAttributes = []
    ): void {
        $this->emitter->emit(
            new CustomerNewDeviceEvent(
                $deviceUuid,
                $customerUuid,
                $identityKey,
                $customAttributes
            )
        );
    }
}
