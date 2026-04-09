<?php

namespace App\Domain\Repository;

interface RepositoryRepositoryInterface
{
    public function find(string $owner, string $repo): ?array;
    public function create(string $owner, string $repo, ?string $latestRelease): int;

    /**
     * @return iterable<array>
     */
    public function iterateAll(): iterable;
    public function updateTag(int $id, string $newTag): void;
    public function beginTransaction(): void;
    public function commit(): void;
    public function rollBack(): void;
}
