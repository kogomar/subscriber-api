<?php

namespace App\Middleware;

interface MiddlewareInterface
{
    public function handle(array $routeData, callable $next);
}
