<?php

namespace App\Application\Listener;

use App\Domain\Event\NewReleaseDetectedEvent;
use App\Domain\Repository\SubscriptionRepositoryInterface;
use App\Domain\Notification\NotifierInterface;
use App\Domain\Logging\LoggerInterface;

class SendReleaseNotificationsListener
{
    public function __construct(
        private SubscriptionRepositoryInterface $subRepo,
        private NotifierInterface $notifier,
        private LoggerInterface $logger,
    ) {
    }

    public function __invoke(NewReleaseDetectedEvent $event): void
    {
        $emails = $this->subRepo->getEmailsByRepository($event->repositoryId);

        foreach ($emails as $email) {
            $this->logger->info("Sending notification to {$email} for {$event->repositoryName}");
            $this->notifier->notify($email, $event->repositoryName, $event->tag);
        }
    }
}
