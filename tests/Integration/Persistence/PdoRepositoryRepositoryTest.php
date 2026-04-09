<?php

namespace Tests\Integration\Persistence;

use App\Infrastructure\Persistence\PdoRepositoryRepository;
use PHPUnit\Framework\TestCase;
use PDO;

class PdoRepositoryRepositoryTest extends TestCase
{
    private PDO $pdo;
    private PdoRepositoryRepository $repository;

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

        $this->repository = new PdoRepositoryRepository($this->pdo);
    }

    public function testCanCreateAndFindRepository()
    {
        $id = $this->repository->create('google', 'go', 'v1.0.0');
        $this->assertGreaterThan(0, $id);

        $found = $this->repository->find('google', 'go');
        $this->assertNotNull($found);
        $this->assertEquals($id, $found['id']);
    }
}
