<?php

namespace App\Infrastructure\ExternalApi;

use App\Domain\Client\GitHubClientInterface;
use GuzzleHttp\Client as GuzzleClient;
use Exception;

class GuzzleGitHubClient implements GitHubClientInterface
{
    private string $userAgent = 'PHP-Release-Subscriber-App';
    private GuzzleClient $client;

    public function __construct()
    {
        $options = [
            'base_uri' => 'https://api.github.com/',
            'headers'  => [
                'User-Agent' => $this->userAgent,
                'Accept'     => 'application/vnd.github.v3+json',
            ],
            'http_errors' => false,
        ];

        $this->client = new GuzzleClient($options);
    }

    public function getRepository(string $repository): ?array
    {
        $response = $this->client->get("repos/{$repository}");
        $statusCode = $response->getStatusCode();

        if ($statusCode === 404) {
            return null;
        }

        if ($statusCode === 200) {
            return json_decode($response->getBody(), true);
        }

        if ($statusCode === 403 || $statusCode === 429) {
            throw new Exception("Rate Limit Exceeded", 429);
        }

        throw new Exception("GitHub API Error: " . $response->getBody(), $statusCode);
    }

    public function getLatestRelease(string $repository): ?string
    {
        $response = $this->client->get("repos/{$repository}/releases/latest");
        $statusCode = $response->getStatusCode();

        if ($statusCode === 404) {
            return null;
        }

        if ($statusCode === 200) {
            $data = json_decode($response->getBody(), true);
            return $data['tag_name'] ?? null;
        }

        if ($statusCode === 403 || $statusCode === 429) {
            throw new Exception("Rate Limit Exceeded", 429);
        }

        throw new Exception("GitHub API Error: " . $response->getBody(), $statusCode);
    }
}
