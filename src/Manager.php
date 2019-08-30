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
    private $identifyGenerator;

    public function __construct(Storage $storage, IdentityGenerator $identifyGenerator)
    {
        $this->storage = $storage;
        $this->identifyGenerator = $identifyGenerator;
    }

    /**
     * @param  string  $deviceUuid
     * @param  string|null  $customerUuid
     * @return string
     */
    public function identify(string $deviceUuid, ?string $customerUuid = null): string
    {
        $identifyKey = $this->getIdentifyByCustomerUuid($customerUuid);

        if($identifyKey) {
            return $identifyKey;
        }

        $identifyKey = $this->getIdentifyByDeviceUuid($deviceUuid);

        if(!$identifyKey) {
            $identifyKey = $this->createDeviceIdentifyKey($deviceUuid);
        }

        if($customerUuid) {
            $this->updateCustomerIdentifyKey($customerUuid, $identifyKey);
        }

        return $identifyKey;
    }

    /**
     * @param  string|null  $customerUuid
     * @return string|null
     */
    private function getIdentifyByCustomerUuid(?string $customerUuid): ?string
    {
        try {
            $identify = $this
                ->storage
                ->get(
                    sprintf(
                        self::CUSTOMER_KEY_IDENTIFICATION_NAME,
                        $customerUuid
                    )
                );

            return $identify;
        } catch (StorageKeyDoesNotExistsException $exception) {}

        return null;
    }

    /**
     * @param  string|null  $deviceUuid
     * @return string|null
     */
    private function getIdentifyByDeviceUuid(string $deviceUuid): ?string
    {
        try {
            $identify = $this
                ->storage
                ->get(
                    sprintf(
                        self::DEVICE_KEY_IDENTIFICATION_NAME,
                        $deviceUuid
                    )
                );

            return $identify;
        } catch (StorageKeyDoesNotExistsException $exception) {}

        return null;
    }

    /**
     * @param  string  $deviceUuid
     * @return string
     */
    private function createDeviceIdentifyKey(string $deviceUuid): string
    {
        $identifyKey = $this->identifyGenerator->generate();

        $this
            ->storage
            ->set(
                sprintf(
                    self::DEVICE_KEY_IDENTIFICATION_NAME,
                    $deviceUuid
                ),
                $identifyKey
            );

        return $identifyKey;
    }

    /**
     * @param  string  $customerUuid
     * @param  string  $identifyKey
     */
    private function updateCustomerIdentifyKey(string $customerUuid, string $identifyKey): void
    {
        $this
            ->storage
            ->set(
                sprintf(
                    self::CUSTOMER_KEY_IDENTIFICATION_NAME,
                    $customerUuid
                ),
                $identifyKey
            );
    }
}
