<?php

declare(strict_types=1);

namespace UniqueIdentityManager;

use League\Event\Emitter;
use UniqueIdentityManager\Contracts\Storage;
use UniqueIdentityManager\Events\CustomerNewDeviceEvent;
use UniqueIdentityManager\Events\NewDeviceIdentityKeyEvent;
use UniqueIdentityManager\Events\UpdateCustomerIdentityKey;

class Manager
{
    const DEVICE_KEY_IDENTIFICATION_NAME = 'device:%s';
    const CUSTOMER_KEY_IDENTIFICATION_NAME = 'customer:%s';

    /**
     * @var Storage
     */
    private $storage;

    /**
     * @var IdentityGenerator
     */
    private $identityGenerator;

    /**
     * @var Emitter
     */
    private $emitter;

    public function __construct(
        Storage $storage,
        IdentityGenerator $identityGenerator,
        Emitter $emitter = null
    ) {
        $this->storage = $storage;
        $this->identityGenerator = $identityGenerator;
        $this->emitter = $emitter ?? new Emitter();
    }

    public function identify(string $deviceUuid, ?string $customerUuid = null): string
    {
        $identityKey = $this->getIdentityByCustomerUuid($customerUuid);

        if ($identityKey) {
            $this->emitter->emit(
                new CustomerNewDeviceEvent(
                    $deviceUuid,
                    $customerUuid,
                    $identityKey
                )
            );

            return $identityKey;
        }

        $identityKey = $this->getIdentityByDeviceUuid($deviceUuid);

        if (!$identityKey) {
            $identityKey = $this->createDeviceIdentityKey($deviceUuid);

            $this->emitter->emit(
                new NewDeviceIdentityKeyEvent(
                    $identityKey
                )
            );
        }

        if ($customerUuid) {
            $this->updateCustomerIdentityKey($customerUuid, $identityKey);

            $this->emitter->emit(
                new UpdateCustomerIdentityKey(
                    $customerUuid,
                    $identityKey
                )
            );
        }

        return $identityKey;
    }

    private function getIdentityByCustomerUuid(?string $customerUuid): ?string
    {
        return $this
            ->storage
            ->get(
                sprintf(
                    self::CUSTOMER_KEY_IDENTIFICATION_NAME,
                    $customerUuid
                )
            );
    }

    private function getIdentityByDeviceUuid(string $deviceUuid): ?string
    {
        return $this
            ->storage
            ->get(
                sprintf(
                    self::DEVICE_KEY_IDENTIFICATION_NAME,
                    $deviceUuid
                )
            );
    }

    private function createDeviceIdentityKey(string $deviceUuid): string
    {
        $identityKey = (string) $this->identityGenerator->generate();

        $this
            ->storage
            ->set(
                sprintf(
                    self::DEVICE_KEY_IDENTIFICATION_NAME,
                    $deviceUuid
                ),
                $identityKey
            );

        return $identityKey;
    }

    private function updateCustomerIdentityKey(string $customerUuid, string $identityKey): void
    {
        $this
            ->storage
            ->set(
                sprintf(
                    self::CUSTOMER_KEY_IDENTIFICATION_NAME,
                    $customerUuid
                ),
                $identityKey
            );
    }
}
