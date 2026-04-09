<?php

namespace App\Infrastructure\Cache;

use App\Domain\Cache\CacheInterface;
use Predis\Client as RedisClient;
use Predis\Connection\ConnectionException;
use Exception;

class RedisCache implements CacheInterface
{
    private RedisClient $client;

    public function __construct()
    {
        $this->client = new RedisClient([
            'scheme' => 'tcp',
            'host'   => getenv('REDIS_HOST') ?: '127.0.0.1',
            'port'   => 6379,
        ]);
    }

    public function get(string $key): ?string
    {
        try {
            return $this->client->get($key);
        } catch (ConnectionException | Exception $e) {
            error_log("Redis GET failed: " . $e->getMessage());
            return null;
        }
    }

    public function setex(string $key, int $seconds, string $value): void
    {
        try {
            $this->client->setex($key, $seconds, $value);
        } catch (ConnectionException | Exception $e) {
            error_log("Redis SETEX failed: " . $e->getMessage());
        }
    }

    public function getRawClient(): RedisClient
    {
        return $this->client;
    }
}
