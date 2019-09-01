<?php

declare(strict_types=1);

namespace UniqueIdentityManager\Events;

use League\Event\AbstractEvent;

class CustomerNewDeviceEvent extends AbstractEvent
{
    const EVENT_NAME = 'identity-manager.event.customer.new.device';

    /**
     * @var string
     */
    private $deviceUuid;

    /**
     * @var string
     */
    private $customerUuid;

    /**
     * @var string
     */
    private $identityKey;

    /**
     * @var array
     */
    private $customAttributes;

    public function __construct(
        string $deviceUuid,
        string $customerUuid,
        string $identityKey,
        array $customAttributes = []
    ) {
        $this->deviceUuid = $deviceUuid;
        $this->customerUuid = $customerUuid;
        $this->identityKey = $identityKey;
        $this->customAttributes = $customAttributes;
    }

    public function getDeviceUuid(): string
    {
        return $this->deviceUuid;
    }

    public function getCustomerUuid(): string
    {
        return $this->customerUuid;
    }

    public function getIdentityKey(): string
    {
        return $this->identityKey;
    }

    public function getName(): string
    {
        return self::EVENT_NAME;
    }

    public function getCustomAttributes(): array
    {
        return $this->customAttributes;
    }
}
