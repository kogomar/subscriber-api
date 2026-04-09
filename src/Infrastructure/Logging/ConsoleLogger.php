<?php

namespace App\Infrastructure\Logging;

use App\Domain\Logging\LoggerInterface;

class ConsoleLogger implements LoggerInterface
{
    public function info(string $message): void
    {
        echo "[" . date('Y-m-d H:i:s') . "] INFO: " . $message . "\n";
    }

    public function error(string $message): void
    {
        echo "[" . date('Y-m-d H:i:s') . "] ERROR: " . $message . "\n";
    }
}
