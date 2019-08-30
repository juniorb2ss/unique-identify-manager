<?php

namespace UniqueIdentityManager;

use Predis\ClientInterface;
use UniqueIdentityManager\Exceptions\StorageKeyDoesNotExistsException;

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
     * @param  string  $identity
     * @return string|null
     * @throws StorageKeyDoesNotExistsException
     */
    public function get(string $identity): ?string
    {
        $value = $this->client->get($identity);

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
