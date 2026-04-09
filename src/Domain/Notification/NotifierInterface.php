<?php

namespace App\Domain\Notification;

interface NotifierInterface
{
    public function notify(string $email, string $repository, string $tag): void;
}
