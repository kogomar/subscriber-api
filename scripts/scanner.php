<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Application\ScannerService;
use App\Application\Listener\SendReleaseNotificationsListener;
use App\Domain\Repository\RepositoryRepositoryInterface;
use App\Domain\Repository\SubscriptionRepositoryInterface;
use App\Domain\Client\GitHubClientInterface;
use App\Domain\Notification\NotifierInterface;
use App\Domain\Event\EventDispatcherInterface;
use App\Domain\Event\NewReleaseDetectedEvent;
use App\Domain\Logging\LoggerInterface;
use App\Domain\Cache\CacheInterface;
use App\Infrastructure\Cache\RedisCache;
use App\Infrastructure\Event\SimpleEventDispatcher;
use App\Infrastructure\ExternalApi\GuzzleGitHubClient;
use App\Infrastructure\Logging\ConsoleLogger;
use App\Infrastructure\Notification\EmailNotifier;
use App\Infrastructure\Persistence\PdoRepositoryRepository;
use App\Infrastructure\Persistence\PdoSubscriptionRepository;
use DI\ContainerBuilder;

$containerBuilder = new ContainerBuilder();
$containerBuilder->addDefinitions([
    PDO::class => function () {
        return App\Database::getConnection();
    },
    RepositoryRepositoryInterface::class => DI\autowire(PdoRepositoryRepository::class),
    SubscriptionRepositoryInterface::class => DI\autowire(PdoSubscriptionRepository::class),
    GitHubClientInterface::class => DI\autowire(GuzzleGitHubClient::class),
    NotifierInterface::class => DI\autowire(EmailNotifier::class),
    CacheInterface::class => DI\autowire(RedisCache::class),
    LoggerInterface::class => DI\autowire(ConsoleLogger::class),
    EventDispatcherInterface::class => DI\autowire(SimpleEventDispatcher::class),
    ScannerService::class => DI\autowire(ScannerService::class),
]);

$container = $containerBuilder->build();

/** @var EventDispatcherInterface $dispatcher */
$dispatcher = $container->get(EventDispatcherInterface::class);
/** @var SendReleaseNotificationsListener $notificationListener */
$notificationListener = $container->get(SendReleaseNotificationsListener::class);

$dispatcher->addListener(NewReleaseDetectedEvent::class, $notificationListener);

/** @var ScannerService $scanner */
$scanner = $container->get(ScannerService::class);
/** @var LoggerInterface $logger */
$logger = $container->get(LoggerInterface::class);

$interval = (int)(getenv('SCANNER_INTERVAL') ?: 60);

$logger->info("Starting background scanner (Interval: {$interval}s)...");

while (true) {
    $logger->info("Starting scan cycle...");
    $scanner->runScan();
    sleep($interval);
}
