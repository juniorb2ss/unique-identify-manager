<?php

declare(strict_types=1);

namespace UniqueIdentityManager\Events;

use League\Event\AbstractEvent;

class UpdateCustomerIdentityKeyEvent extends AbstractEvent
{
    const EVENT_NAME = 'identity-manager.event.update.customer.identity-key';

    /**
     * @var string
     */
    public $identityKey;

    /**
     * @var string
     */
    public $customerUuid;

    public function __construct(string $customerUuid, string $identityKey)
    {
        $this->identityKey = $identityKey;
        $this->customerUuid = $customerUuid;
    }

    public function getIdentityKey(): string
    {
        return $this->identityKey;
    }

    public function getCustomerUuid(): string
    {
        return $this->customerUuid;
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
