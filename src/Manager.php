<?php

declare(strict_types=1);

namespace UniqueIdentityManager;

use UniqueIdentityManager\Contracts\StorageInterface;

class Manager
{
    const DEVICE_KEY_IDENTIFICATION_NAME = 'device:%s';
    const CUSTOMER_KEY_IDENTIFICATION_NAME = 'customer:%s';

    /**
     * @var StorageInterface
     */
    private $storage;

    /**
     * @var IdentityGenerator
     */
    private $identityGenerator;

    /**
     * @var EventHandler
     */
    private $eventHandler;

    public function __construct(
        StorageInterface $storage,
        EventHandler $eventHandler,
        IdentityGenerator $identityGenerator = null
    ) {
        $this->storage = $storage;
        $this->eventHandler = $eventHandler;
        $this->identityGenerator = $identityGenerator ?? new IdentityGenerator();
    }

    public function identify(string $deviceUuid, ?string $customerUuid = null, array $customAttributes = []): string
    {
        $identityKey = $this->getIdentityByCustomerUuid($customerUuid);

        if ($identityKey) {
            $this->eventHandler->customerNewDevice(
                $deviceUuid,
                $customerUuid,
                $identityKey,
                $customAttributes
            );

            return $identityKey;
        }

        $identityKey = $this->getIdentityByDeviceUuid($deviceUuid);

        if (!$identityKey) {
            $identityKey = $this->createDeviceIdentityKey($deviceUuid, $customAttributes);

            $this->eventHandler->newDevice(
                $deviceUuid,
                $identityKey,
                $customAttributes
            );
        }

        if ($customerUuid) {
            $this->updateCustomerIdentityKey($customerUuid, $identityKey, $customAttributes);

            $this->eventHandler->customerNewIdentityKey(
                $customerUuid,
                $identityKey,
                $customAttributes
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

    private function createDeviceIdentityKey(string $deviceUuid, array $customAttributes = []): string
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

    private function updateCustomerIdentityKey(string $customerUuid, string $identityKey, array $customAttributes = []): void
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
