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

    public function __construct(string $identityKey)
    {
        $this->identityKey = $identityKey;
    }

    public function getIdentityKey(): string
    {
        return $this->identityKey;
    }

    public function getName(): string
    {
        return self::EVENT_NAME;
    }
}
