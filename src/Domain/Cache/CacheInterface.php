<?php

namespace App\Domain\Cache;

interface CacheInterface
{
    public function get(string $key): ?string;
    public function setex(string $key, int $seconds, string $value): void;
}
