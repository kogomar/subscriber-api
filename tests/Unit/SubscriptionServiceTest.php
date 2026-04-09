<?php

namespace Unit;

use App\Application\SubscriptionService;
use App\Domain\Repository\SubscriptionRepositoryInterface;
use App\Domain\Client\GitHubClientInterface;
use App\Domain\Cache\CacheInterface;
use PHPUnit\Framework\TestCase;
use Exception;

class SubscriptionServiceTest extends TestCase
{
    private $repositoryMock;
    private $githubMock;
    private $cacheMock;
    private SubscriptionService $service;

    protected function setUp(): void
    {
        $this->repositoryMock = $this->createMock(SubscriptionRepositoryInterface::class);
        $this->githubMock = $this->createMock(GitHubClientInterface::class);
        $this->cacheMock = $this->createMock(CacheInterface::class);

        $this->service = new SubscriptionService(
            $this->repositoryMock,
            $this->githubMock,
            $this->cacheMock
        );
    }

    public function testSubscribeSuccessWhenRepositoryIsNew()
    {
        $repoName = 'owner/repo';
        $email = 'user@example.com';

        $this->cacheMock->method('get')->willReturn(null);
        $this->githubMock->method('getRepository')->with($repoName)->willReturn(['id' => 123]);
        $this->githubMock->method('getLatestRelease')->with($repoName)->willReturn('v1.0.0');

        $this->repositoryMock->method('findRepository')->willReturn(null);
        $this->repositoryMock->method('createRepository')->willReturn(1);
        $this->repositoryMock->method('subscriptionExists')->willReturn(false);

        $this->repositoryMock->expects($this->once())->method('beginTransaction');
        $this->repositoryMock->expects($this->once())->method('commit');
        $this->repositoryMock->expects($this->once())->method('addSubscription');

        $this->service->subscribe($email, $repoName);
    }

    public function testSubscribeThrows422IfAlreadySubscribed()
    {
        $repoName = 'owner/repo';
        $email = 'user@example.com';

        $this->cacheMock->method('get')->willReturn("1");
        $this->repositoryMock->method('findRepository')->willReturn(['id' => 1]);
        $this->repositoryMock->method('subscriptionExists')->willReturn(true);

        $this->expectException(Exception::class);
        $this->expectExceptionCode(422);
        $this->expectExceptionMessage("You are already subscribed to this repository");

        $this->service->subscribe($email, $repoName);
    }

    public function testSubscribeThrows404IfRepoNotFoundOnGithub()
    {
        $repoName = 'invalid/repo';
        $this->cacheMock->method('get')->willReturn(null);
        $this->githubMock->method('getRepository')->willReturn(null);

        $this->expectException(Exception::class);
        $this->expectExceptionCode(404);

        $this->service->subscribe('test@test.com', $repoName);
    }

    public function testSubscribeResilientToRedisFailure()
    {
        $this->cacheMock->method('get')->willThrowException(new Exception("Redis connection error"));
        $this->githubMock->method('getRepository')->willReturn(['id' => 123]);
        $this->repositoryMock->method('findRepository')->willReturn(['id' => 1]);
        $this->repositoryMock->method('subscriptionExists')->willReturn(false);
        $this->repositoryMock->expects($this->once())->method('commit');

        $this->service->subscribe('user@example.com', 'owner/repo');
    }
}
