<?php

namespace UniqueIdentifyManager;

use Predis\ClientInterface;
use UniqueIdentifyManager\Exceptions\StorageKeyDoesNotExistsException;

class Storage
{
    /**
     * @var ClientInterface
     */
    private $client;

    public function __construct(ClientInterface $client)
    {
        $this->client = $client;
    }

    /**
     * @param  string  $identify
     * @return string|null
     * @throws StorageKeyDoesNotExistsException
     */
    public function get(string $identify): ?string
    {
        $value = $this->client->get($identify);

        if(!$value) {
            throw new StorageKeyDoesNotExistsException();
        }

        return $value;
    }

    /**
     * @param  string  $key
     * @param  string  $value
     * @return bool
     */
    public function set(string $key, string $value): void
    {
        $this->client->set($key, $value);
    }
}
