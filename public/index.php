<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Router;
use App\Database;
use App\Domain\Repository\SubscriptionRepositoryInterface;
use App\Domain\Client\GitHubClientInterface;
use App\Domain\Cache\CacheInterface;
use App\Infrastructure\Persistence\PdoSubscriptionRepository;
use App\Infrastructure\ExternalApi\GuzzleGitHubClient;
use App\Infrastructure\Cache\RedisCache;
use App\Controller\SubscriptionController;
use App\Controller\DefaultController;
use App\Middleware\AuthMiddleware;
use Prometheus\CollectorRegistry;
use Prometheus\Storage\Redis as PrometheusRedis;
use Predis\Client as RedisClient;
use DI\ContainerBuilder;

$containerBuilder = new ContainerBuilder();
$containerBuilder->addDefinitions([
    PDO::class => function () {
        return Database::getConnection();
    },
    SubscriptionRepositoryInterface::class => DI\autowire(PdoSubscriptionRepository::class),
    GitHubClientInterface::class => DI\autowire(GuzzleGitHubClient::class),
    CacheInterface::class => DI\autowire(RedisCache::class),
    RedisClient::class => function (Psr\Container\ContainerInterface $c) {
        return $c->get(CacheInterface::class)->getRawClient();
    },
    CollectorRegistry::class => function () {
        $storage = new PrometheusRedis([
            'host' => getenv('REDIS_HOST') ?: '127.0.0.1',
            'port' => 6379,
        ]);
        return new CollectorRegistry($storage);
    },
]);

$container = $containerBuilder->build();

$router = new Router($container);
$router->addMiddleware(new AuthMiddleware());
$router->registerController(SubscriptionController::class);
$router->registerController(DefaultController::class);

echo $router->run();
