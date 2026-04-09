<?php

namespace App\Middleware;

use App\Dto\ApiResponseDto;

class AuthMiddleware implements MiddlewareInterface
{
    public function handle(array $routeData, callable $next)
    {
        $requiresAuth = $routeData['auth'] ?? false;

        if ($requiresAuth) {
            $expectedKey = $_ENV['API_KEY'] ?? getenv('API_KEY') ?: '';
            $providedKey = $_SERVER['HTTP_APP_API_KEY'] ?? '';

            if ($providedKey !== $expectedKey) {
                header('Content-Type: application/json');
                http_response_code(403);
                return (string) ApiResponseDto::error('Forbidden. Invalid API Key');
            }
        }

        return $next($routeData);
    }
}
