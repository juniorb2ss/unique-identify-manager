<?php

namespace UniqueIdentityManager;

use UniqueIdentityManager\Exceptions\StorageKeyDoesNotExistsException;

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

    public function __construct(Storage $storage, IdentityGenerator $identityGenerator)
    {
        $this->storage = $storage;
        $this->identityGenerator = $identityGenerator;
    }

    /**
     * @param  string  $deviceUuid
     * @param  string|null  $customerUuid
     * @return string
     */
    public function identify(string $deviceUuid, ?string $customerUuid = null): string
    {
        $identityKey = $this->getIdentityByCustomerUuid($customerUuid);

        if($identityKey) {
            return $identityKey;
        }

        $identityKey = $this->getIdentityByDeviceUuid($deviceUuid);

        if(!$identityKey) {
            $identityKey = $this->createDeviceIdentityKey($deviceUuid);
        }

        if($customerUuid) {
            $this->updateCustomerIdentityKey($customerUuid, $identityKey);
        }

        return $identityKey;
    }

    /**
     * @param  string|null  $customerUuid
     * @return string|null
     */
    private function getIdentityByCustomerUuid(?string $customerUuid): ?string
    {
        try {
            $identity = $this
                ->storage
                ->get(
                    sprintf(
                        self::CUSTOMER_KEY_IDENTIFICATION_NAME,
                        $customerUuid
                    )
                );

            return $identity;
        } catch (StorageKeyDoesNotExistsException $exception) {}

        return null;
    }

    /**
     * @param  string|null  $deviceUuid
     * @return string|null
     */
    private function getIdentityByDeviceUuid(string $deviceUuid): ?string
    {
        try {
            $identity = $this
                ->storage
                ->get(
                    sprintf(
                        self::DEVICE_KEY_IDENTIFICATION_NAME,
                        $deviceUuid
                    )
                );

            return $identity;
        } catch (StorageKeyDoesNotExistsException $exception) {}

        return null;
    }

    /**
     * @param  string  $deviceUuid
     * @return string
     */
    private function createDeviceIdentityKey(string $deviceUuid): string
    {
        $identityKey = $this->identityGenerator->generate();

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

    /**
     * @param  string  $customerUuid
     * @param  string  $identityKey
     */
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
