<?php

namespace Tests\Application\Listener;

use App\Application\Listener\SendReleaseNotificationsListener;
use App\Domain\Event\NewReleaseDetectedEvent;
use App\Domain\Repository\SubscriptionRepositoryInterface;
use App\Domain\Notification\NotifierInterface;
use App\Domain\Logging\LoggerInterface;
use PHPUnit\Framework\TestCase;

class SendReleaseNotificationsListenerTest extends TestCase
{
    public function testHandleSendsNotificationsToAllSubscribers()
    {
        $subRepo = $this->createMock(SubscriptionRepositoryInterface::class);
        $notifier = $this->createMock(NotifierInterface::class);
        $logger = $this->createMock(LoggerInterface::class);

        $event = new NewReleaseDetectedEvent(1, 'owner/repo', 'v1.1.0');

        $subRepo->method('getEmailsByRepository')
            ->with(1)
            ->willReturn(['user1@test.com', 'user2@test.com']);

        $notifier->expects($this->exactly(2))->method('notify');

        $listener = new SendReleaseNotificationsListener($subRepo, $notifier, $logger);
        $listener($event);
    }
}
