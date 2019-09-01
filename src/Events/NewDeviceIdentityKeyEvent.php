<?php

declare(strict_types=1);

namespace UniqueIdentityManager\Events;

use League\Event\AbstractEvent;

class NewDeviceIdentityKeyEvent extends AbstractEvent
{
    const EVENT_NAME = 'identity-manager.event.new.device.identity-key';

    /**
     * @var string
     */
    public $identityKey;

    /**
     * @var array
     */
    private $customAttributes;

    public function __construct(string $identityKey, array $customAttributes = [])
    {
        $this->identityKey = $identityKey;
        $this->customAttributes = $customAttributes;
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
