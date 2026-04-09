<?php

namespace Tests\Integration\Persistence;

use App\Infrastructure\Persistence\PdoSubscriptionRepository;
use PHPUnit\Framework\TestCase;
use PDO;

class PdoSubscriptionRepositoryTest extends TestCase
{
    private PDO $pdo;
    private PdoSubscriptionRepository $repository;

    protected function setUp(): void
    {
        $this->pdo = new PDO('sqlite::memory:');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $this->pdo->exec("CREATE TABLE subscriptions (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            repository_id INTEGER NOT NULL,
            email TEXT NOT NULL
        )");

        $this->repository = new PdoSubscriptionRepository($this->pdo);
    }

    public function testCanAddAndCheckSubscription()
    {
        $repoId = 123;

        $this->assertFalse($this->repository->exists($repoId, 'test@example.com'));

        $this->repository->add($repoId, 'test@example.com');

        $this->assertTrue($this->repository->exists($repoId, 'test@example.com'));
    }
}
