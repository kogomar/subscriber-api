<?php

namespace App\Infrastructure\Persistence;

use App\Domain\Repository\SubscriptionRepositoryInterface;
use PDO;

class PdoSubscriptionRepository implements SubscriptionRepositoryInterface
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function findRepository(string $owner, string $repo): ?array
    {
        $stmt = $this->db->prepare("SELECT id, last_seen_tag FROM repositories WHERE owner = ? AND repo = ?");
        $stmt->execute([$owner, $repo]);
        return $stmt->fetch() ?: null;
    }

    public function createRepository(string $owner, string $repo, ?string $latestRelease): int
    {
        $stmt = $this->db->prepare("INSERT INTO repositories (owner, repo, last_seen_tag) VALUES (?, ?, ?)");
        $stmt->execute([$owner, $repo, $latestRelease]);
        return (int) $this->db->lastInsertId();
    }

    public function subscriptionExists(int $repoId, string $email): bool
    {
        $stmt = $this->db->prepare("SELECT 1 FROM subscriptions WHERE repository_id = ? AND email = ?");
        $stmt->execute([$repoId, $email]);
        return (bool) $stmt->fetchColumn();
    }

    public function addSubscription(int $repoId, string $email): void
    {
        $stmt = $this->db->prepare("INSERT INTO subscriptions (repository_id, email) VALUES (?, ?)");
        $stmt->execute([$repoId, $email]);
    }

    public function beginTransaction(): void
    {
        $this->db->beginTransaction();
    }

    public function commit(): void
    {
        $this->db->commit();
    }

    public function rollBack(): void
    {
        $this->db->rollBack();
    }
}
