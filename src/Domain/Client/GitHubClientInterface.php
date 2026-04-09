<?php

namespace App\Domain\Client;

interface GitHubClientInterface
{
    public function getRepository(string $repository): ?array;
    public function getLatestRelease(string $repository): ?string;
}
