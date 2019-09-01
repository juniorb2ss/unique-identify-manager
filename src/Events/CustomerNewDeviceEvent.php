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

    public function __construct(string $deviceUuid, string $customerUuid, string $identityKey)
    {
        $this->deviceUuid = $deviceUuid;
        $this->customerUuid = $customerUuid;
        $this->identityKey = $identityKey;
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

    /**
     * Get the event name.
     *
     * @return string
     */
    public function getName()
    {
        return self::EVENT_NAME;
    }
}
