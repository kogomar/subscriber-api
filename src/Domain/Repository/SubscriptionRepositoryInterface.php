<?php

namespace App\Domain\Repository;

interface SubscriptionRepositoryInterface
{
    public function findRepository(string $owner, string $repo): ?array;
    public function createRepository(string $owner, string $repo, ?string $latestRelease): int;
    public function subscriptionExists(int $repoId, string $email): bool;
    public function addSubscription(int $repoId, string $email): void;
    public function beginTransaction(): void;
    public function commit(): void;
    public function rollBack(): void;
}
