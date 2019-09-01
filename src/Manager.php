<?php

declare(strict_types=1);

namespace UniqueIdentityManager;

use League\Event\Emitter;
use League\Event\EmitterInterface;
use UniqueIdentityManager\Contracts\Storage;
use UniqueIdentityManager\Events\CustomerNewDeviceEvent;
use UniqueIdentityManager\Events\NewDeviceIdentityKeyEvent;
use UniqueIdentityManager\Events\UpdateCustomerIdentityKeyEvent;

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
     * @var EmitterInterface
     */
    private $emitter;

    public function __construct(
        Storage $storage,
        IdentityGenerator $identityGenerator = null,
        EmitterInterface $emitter = null
    ) {
        $this->storage = $storage;
        $this->identityGenerator = $identityGenerator ?? new IdentityGenerator();
        $this->emitter = $emitter ?? new Emitter();
    }

    public function identify(string $deviceUuid, ?string $customerUuid = null, array $customerAttributes = []): string
    {
        $identityKey = $this->getIdentityByCustomerUuid($customerUuid);

        if ($identityKey) {
            $this->emitter->emit(
                new CustomerNewDeviceEvent(
                    $deviceUuid,
                    $customerUuid,
                    $identityKey,
                    $customerAttributes
                )
            );

            return $identityKey;
        }

        $identityKey = $this->getIdentityByDeviceUuid($deviceUuid);

        if (!$identityKey) {
            $identityKey = $this->createDeviceIdentityKey($deviceUuid);

            $this->emitter->emit(
                new NewDeviceIdentityKeyEvent(
                    $identityKey,
                    $customerAttributes
                )
            );
        }

        if ($customerUuid) {
            $this->updateCustomerIdentityKey($customerUuid, $identityKey);

            $this->emitter->emit(
                new UpdateCustomerIdentityKeyEvent(
                    $customerUuid,
                    $identityKey,
                    $customerAttributes
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
