<?php

namespace App\Application;

use App\Domain\Repository\RepositoryRepositoryInterface;
use App\Domain\Repository\SubscriptionRepositoryInterface;
use App\Domain\Client\GitHubClientInterface;
use App\Domain\Cache\CacheInterface;
use Exception;

class SubscriptionService
{
    private const CACHE_KEY_REPO_EXISTS = 'repo_exists_:';
    private const CACHE_KEY_LATEST_RELEASE = 'latest_release_:';

    public function __construct(
        private RepositoryRepositoryInterface $repoRepo,
        private SubscriptionRepositoryInterface $subRepo,
        private GitHubClientInterface $github,
        private CacheInterface $cache,
    ) {
    }

    /**
     * @throws Exception
     */
    public function subscribe(string $email, string $repositoryName): void
    {
        $cacheKey = self::CACHE_KEY_REPO_EXISTS . $repositoryName;
        $cachedExists = $this->safeCacheGet($cacheKey);

        if ($cachedExists === "0") {
            throw new Exception("Repository not found", 404);
        }

        if ($cachedExists === null) {
            $repoInfo = $this->github->getRepository($repositoryName);
            if (!$repoInfo) {
                $this->safeCacheSet($cacheKey, "0", 600);
                throw new Exception("Repository not found", 404);
            }
            $this->safeCacheSet($cacheKey, "1", 600);
        }

        [$owner, $repo] = explode('/', $repositoryName);

        $this->repoRepo->beginTransaction();

        try {
            $repoData = $this->repoRepo->find($owner, $repo);

            if (!$repoData) {
                $releaseCacheKey = self::CACHE_KEY_LATEST_RELEASE . $repositoryName;
                $latestRelease = $this->safeCacheGet($releaseCacheKey);

                if ($latestRelease === null) {
                    $latestRelease = $this->github->getLatestRelease($repositoryName);
                    $this->safeCacheSet($releaseCacheKey, $latestRelease ?: "NONE", 600);
                }

                if ($latestRelease === "NONE") {
                    $latestRelease = null;
                }

                $repoId = $this->repoRepo->create($owner, $repo, $latestRelease);
            } else {
                $repoId = $repoData['id'];
            }

            if (!$this->subRepo->exists($repoId, $email)) {
                $this->subRepo->add($repoId, $email);
            } else {
                throw new Exception("You are already subscribed to this repository", 422);
            }

            $this->repoRepo->commit();
        } catch (Exception $e) {
            $this->repoRepo->rollBack();
            throw $e;
        }
    }

    private function safeCacheGet(string $key): ?string
    {
        try {
            return $this->cache->get($key);
        } catch (Exception $e) {
            error_log("Cache port error: " . $e->getMessage());
            return null;
        }
    }

    private function safeCacheSet(string $key, string $value, int $ttl): void
    {
        try {
            $this->cache->setex($key, $ttl, $value);
        } catch (Exception $e) {
            error_log("Cache port error: " . $e->getMessage());
        }
    }
}
