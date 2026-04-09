<?php

namespace App\Domain\Event;

class NewReleaseDetectedEvent
{
    public function __construct(
        public readonly int $repositoryId,
        public readonly string $repositoryName,
        public readonly string $tag,
    ) {
    }
}
