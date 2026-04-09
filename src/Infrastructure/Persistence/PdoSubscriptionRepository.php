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

    public function exists(int $repoId, string $email): bool
    {
        $stmt = $this->db->prepare("SELECT 1 FROM subscriptions WHERE repository_id = ? AND email = ?");
        $stmt->execute([$repoId, $email]);
        return (bool) $stmt->fetchColumn();
    }

    public function add(int $repoId, string $email): void
    {
        $stmt = $this->db->prepare("INSERT INTO subscriptions (repository_id, email) VALUES (?, ?)");
        $stmt->execute([$repoId, $email]);
    }

    public function getEmailsByRepository(int $repoId): array
    {
        $stmt = $this->db->prepare("SELECT email FROM subscriptions WHERE repository_id = ?");
        $stmt->execute([$repoId]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
}
