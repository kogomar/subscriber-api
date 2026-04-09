<?php

namespace Tests\Infrastructure\Persistence;

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

        $this->pdo->exec("CREATE TABLE repositories (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            owner TEXT NOT NULL,
            repo TEXT NOT NULL,
            last_seen_tag TEXT
        )");

        $this->pdo->exec("CREATE TABLE subscriptions (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            repository_id INTEGER NOT NULL,
            email TEXT NOT NULL
        )");

        $this->repository = new PdoSubscriptionRepository($this->pdo);
    }

    public function testCanCreateAndFindRepository()
    {
        $id = $this->repository->createRepository('google', 'go', 'v1.0.0');
        $this->assertGreaterThan(0, $id);

        $found = $this->repository->findRepository('google', 'go');
        $this->assertNotNull($found);
        $this->assertEquals($id, $found['id']);
        $this->assertEquals('v1.0.0', $found['last_seen_tag']);
    }

    public function testCanAddAndCheckSubscription()
    {
        $repoId = $this->repository->createRepository('owner', 'repo', null);
        $this->assertFalse($this->repository->subscriptionExists($repoId, 'test@example.com'));
        $this->repository->addSubscription($repoId, 'test@example.com');
        $this->assertTrue($this->repository->subscriptionExists($repoId, 'test@example.com'));
    }

    public function testFindNonExistentRepositoryReturnsNull()
    {
        $found = $this->repository->findRepository('none', 'none');
        $this->assertNull($found);
    }
}
