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

    /**
     * @var array
     */
    private $customAttributes;

    public function __construct(string $customerUuid, string $identityKey, array $customAttributes = [])
    {
        $this->identityKey = $identityKey;
        $this->customerUuid = $customerUuid;
        $this->customAttributes = $customAttributes;
    }

    public function getIdentityKey(): string
    {
        return $this->identityKey;
    }

    public function getCustomerUuid(): string
    {
        return $this->customerUuid;
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
