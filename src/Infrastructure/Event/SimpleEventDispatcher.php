<?php

namespace App\Infrastructure\Event;

use App\Domain\Event\EventDispatcherInterface;

class SimpleEventDispatcher implements EventDispatcherInterface
{
    private array $listeners = [];

    public function addListener(string $eventClass, callable $listener): void
    {
        $this->listeners[$eventClass][] = $listener;
    }

    public function dispatch(object $event): void
    {
        $class = get_class($event);
        if (isset($this->listeners[$class])) {
            foreach ($this->listeners[$class] as $listener) {
                $listener($event);
            }
        }
    }
}
