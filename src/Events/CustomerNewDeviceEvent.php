<?php

declare(strict_types=1);

namespace UniqueIdentityManager\Events;

use League\Event\EmitterInterface;
use League\Event\EventInterface;

class CustomerNewDeviceEvent implements EventInterface
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

    /**
     * Set the Emitter.
     *
     *
     * @return $this
     */
    public function setEmitter(EmitterInterface $emitter)
    {
        // TODO: Implement setEmitter() method.
    }

    /**
     * Get the Emitter.
     *
     * @return EmitterInterface
     */
    public function getEmitter()
    {
        // TODO: Implement getEmitter() method.
    }

    /**
     * Stop event propagation.
     *
     * @return $this
     */
    public function stopPropagation()
    {
        // TODO: Implement stopPropagation() method.
    }

    /**
     * Check whether propagation was stopped.
     *
     * @return bool
     */
    public function isPropagationStopped()
    {
        // TODO: Implement isPropagationStopped() method.
    }
}
