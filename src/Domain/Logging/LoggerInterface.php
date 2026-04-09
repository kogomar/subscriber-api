<?php

namespace App\Domain\Logging;

interface LoggerInterface
{
    public function info(string $message): void;
    public function error(string $message): void;
}
