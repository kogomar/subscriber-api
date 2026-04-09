<?php

namespace App\Domain\Repository;

interface SubscriptionRepositoryInterface
{
    public function exists(int $repoId, string $email): bool;
    public function add(int $repoId, string $email): void;
    public function getEmailsByRepository(int $repoId): array;
}
