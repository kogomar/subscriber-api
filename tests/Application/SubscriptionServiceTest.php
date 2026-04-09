<?php

namespace Tests\Application;

use App\Application\SubscriptionService;
use App\Domain\Repository\RepositoryRepositoryInterface;
use App\Domain\Repository\SubscriptionRepositoryInterface;
use App\Domain\Client\GitHubClientInterface;
use App\Domain\Cache\CacheInterface;
use PHPUnit\Framework\TestCase;
use Exception;

class SubscriptionServiceTest extends TestCase
{
    private $repoRepoMock;
    private $subRepoMock;
    private $githubMock;
    private $cacheMock;
    private SubscriptionService $service;

    protected function setUp(): void
    {
        $this->repoRepoMock = $this->createMock(RepositoryRepositoryInterface::class);
        $this->subRepoMock = $this->createMock(SubscriptionRepositoryInterface::class);
        $this->githubMock = $this->createMock(GitHubClientInterface::class);
        $this->cacheMock = $this->createMock(CacheInterface::class);

        $this->service = new SubscriptionService(
            $this->repoRepoMock,
            $this->subRepoMock,
            $this->githubMock,
            $this->cacheMock,
        );
    }

    public function testSubscribeSuccess()
    {
        $this->cacheMock->method('get')->willReturn(null);
        $this->githubMock->method('getRepository')->willReturn(['id' => 123]);
        $this->githubMock->method('getLatestRelease')->willReturn('v1.0.0');

        $this->repoRepoMock->method('find')->willReturn(null);
        $this->repoRepoMock->method('create')->willReturn(1);
        $this->subRepoMock->method('exists')->willReturn(false);

        $this->repoRepoMock->expects($this->once())->method('beginTransaction');
        $this->repoRepoMock->expects($this->once())->method('commit');
        $this->subRepoMock->expects($this->once())->method('add');

        $this->service->subscribe('user@test.com', 'owner/repo');
    }

    public function testSubscribeResilientToCacheFailure()
    {
        $this->cacheMock->method('get')->willThrowException(new Exception("Redis Down"));
        $this->githubMock->method('getRepository')->willReturn(['id' => 123]);
        $this->repoRepoMock->method('find')->willReturn(['id' => 1]);
        $this->subRepoMock->method('exists')->willReturn(false);

        $this->service->subscribe('user@test.com', 'owner/repo');

        $this->assertTrue(true);
    }
}
