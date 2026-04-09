<?php

namespace Tests\Application;

use App\Application\ScannerService;
use App\Domain\Repository\RepositoryRepositoryInterface;
use App\Domain\Client\GitHubClientInterface;
use App\Domain\Event\EventDispatcherInterface;
use App\Domain\Event\NewReleaseDetectedEvent;
use App\Domain\Logging\LoggerInterface;
use PHPUnit\Framework\TestCase;

class ScannerServiceTest extends TestCase
{
    public function testRunScanDispatchesEventOnNewRelease()
    {
        $repoRepo = $this->createMock(RepositoryRepositoryInterface::class);
        $github = $this->createMock(GitHubClientInterface::class);
        $dispatcher = $this->createMock(EventDispatcherInterface::class);
        $logger = $this->createMock(LoggerInterface::class);

        $repoRepo->method('iterateAll')->willReturn([
            ['id' => 1, 'owner' => 'owner', 'repo' => 'repo', 'last_seen_tag' => 'v1.0.0']
        ]);

        $github->method('getLatestRelease')->willReturn('v1.1.0');

        $dispatcher->expects($this->once())
            ->method('dispatch')
            ->with($this->isInstanceOf(NewReleaseDetectedEvent::class));

        $repoRepo->expects($this->once())
            ->method('updateTag')
            ->with(1, 'v1.1.0');

        $service = new ScannerService($repoRepo, $github, $dispatcher, $logger);
        $service->runScan();
    }

    public function testRunScanDoesNothingIfNoNewRelease()
    {
        $repoRepo = $this->createMock(RepositoryRepositoryInterface::class);
        $github = $this->createMock(GitHubClientInterface::class);
        $dispatcher = $this->createMock(EventDispatcherInterface::class);
        $logger = $this->createMock(LoggerInterface::class);

        $repoRepo->method('iterateAll')->willReturn([
            ['id' => 1, 'owner' => 'owner', 'repo' => 'repo', 'last_seen_tag' => 'v1.0.0']
        ]);

        $github->method('getLatestRelease')->willReturn('v1.0.0');

        $dispatcher->expects($this->never())->method('dispatch');

        $service = new ScannerService($repoRepo, $github, $dispatcher, $logger);
        $service->runScan();
    }
}
