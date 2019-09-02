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
    private $deviceUuid;

    /**
     * @var string
     */
    private $identityKey;

    /**w
     * @var array
     */
    private $customAttributes;

    public function __construct(string $deviceUuid, string $identityKey, array $customAttributes = [])
    {
        $this->deviceUuid = $deviceUuid;
        $this->identityKey = $identityKey;
        $this->customAttributes = $customAttributes;
    }

    public function getDeviceUuid(): string
    {
        return $this->deviceUuid;
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
