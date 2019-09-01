<?php

declare(strict_types=1);

namespace UniqueIdentityManager;

use Predis\ClientInterface;
use UniqueIdentityManager\Contracts\StorageInterface;
use UniqueIdentityManager\Exceptions\StorageKeyDoesNotExistsException;

class RedisStorage implements StorageInterface
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
     * @throws StorageKeyDoesNotExistsException
     */
    public function exists(string $key): bool
    {
        if (!$this->client->exists($key)) {
            throw new StorageKeyDoesNotExistsException();
        }

        return true;
    }

    public function get(string $key): ?string
    {
        try {
            $this->exists($key);

            return $this->client->get($key);
        } catch (StorageKeyDoesNotExistsException $exception) {
            return null;
        }
    }

    public function set(string $key, string $value): void
    {
        $this->client->set($key, $value);
    }
}
