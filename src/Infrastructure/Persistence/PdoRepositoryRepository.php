<?php

namespace App\Infrastructure\Persistence;

use App\Domain\Repository\RepositoryRepositoryInterface;
use PDO;

class PdoRepositoryRepository implements RepositoryRepositoryInterface
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function find(string $owner, string $repo): ?array
    {
        $stmt = $this->db->prepare("SELECT id, last_seen_tag FROM repositories WHERE owner = ? AND repo = ?");
        $stmt->execute([$owner, $repo]);
        return $stmt->fetch() ?: null;
    }

    public function create(string $owner, string $repo, ?string $latestRelease): int
    {
        $stmt = $this->db->prepare("INSERT INTO repositories (owner, repo, last_seen_tag) VALUES (?, ?, ?)");
        $stmt->execute([$owner, $repo, $latestRelease]);
        return (int) $this->db->lastInsertId();
    }

    public function iterateAll(): iterable
    {
        $stmt = $this->db->query("SELECT * FROM repositories");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            yield $row;
        }
    }

    public function updateTag(int $id, string $newTag): void
    {
        $stmt = $this->db->prepare("UPDATE repositories SET last_seen_tag = ? WHERE id = ?");
        $stmt->execute([$newTag, $id]);
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
