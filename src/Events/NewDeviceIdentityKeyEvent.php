<?php

declare(strict_types=1);

namespace UniqueIdentityManager\Events;

use League\Event\EmitterInterface;
use League\Event\EventInterface;

class NewDeviceIdentityKeyEvent implements EventInterface
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
