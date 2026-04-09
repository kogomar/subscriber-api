<?php

namespace App\Application;

use App\Domain\Repository\RepositoryRepositoryInterface;
use App\Domain\Client\GitHubClientInterface;
use App\Domain\Event\EventDispatcherInterface;
use App\Domain\Event\NewReleaseDetectedEvent;
use App\Domain\Logging\LoggerInterface;
use Exception;

class ScannerService
{
    public function __construct(
        private readonly RepositoryRepositoryInterface $repoRepo,
        private readonly GitHubClientInterface $github,
        private readonly EventDispatcherInterface $dispatcher,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function runScan(): void
    {
        $repositories = $this->repoRepo->iterateAll();

        foreach ($repositories as $repo) {
            $fullName = "{$repo['owner']}/{$repo['repo']}";

            try {
                $latestTag = $this->github->getLatestRelease($fullName);

                if ($latestTag && $latestTag !== $repo['last_seen_tag']) {
                    $this->logger->info("New release found for {$fullName}: {$latestTag}");

                    $this->dispatcher->dispatch(new NewReleaseDetectedEvent(
                        (int)$repo['id'],
                        $fullName,
                        $latestTag,
                    ));

                    $this->repoRepo->updateTag($repo['id'], $latestTag);
                }
            } catch (Exception $e) {
                $this->logger->error("Scanner error for {$fullName}: " . $e->getMessage());
            }
        }
    }
}
