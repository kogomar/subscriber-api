<?php

namespace Tests\Http;

use App\Middleware\AuthMiddleware;
use PHPUnit\Framework\TestCase;

class AuthMiddlewareTest extends TestCase
{
    protected function setUp(): void
    {
        unset($_SERVER['HTTP_APP_API_KEY']);
        $_ENV['API_KEY'] = 'secret_key';
    }

    public function testHandlesValidApiKey()
    {
        $_SERVER['HTTP_APP_API_KEY'] = 'secret_key';
        $middleware = new AuthMiddleware();

        $nextCalled = false;
        $next = function ($params) use (&$nextCalled) {
            $nextCalled = true;
            return 'next_result';
        };

        $result = $middleware->handle(['auth' => true], $next);

        $this->assertTrue($nextCalled);
        $this->assertEquals('next_result', $result);
    }

    public function testBlocksInvalidApiKey()
    {
        $_SERVER['HTTP_APP_API_KEY'] = 'wrong_key';
        $middleware = new AuthMiddleware();

        $next = function () {
            return 'should_not_be_called';
        };

        $result = $middleware->handle(['auth' => true], $next);

        $this->assertStringContainsString('Forbidden', $result);
        $this->assertStringContainsString('false', $result); // success: false
    }

    public function testBlocksMissingApiKey()
    {
        $middleware = new AuthMiddleware();
        $next = function () {
            return 'should_not_be_called';
        };

        $result = $middleware->handle(['auth' => true], $next);

        $this->assertStringContainsString('Forbidden', $result);
    }

    public function testSkipsAuthWhenNotRequired()
    {
        $middleware = new AuthMiddleware();
        $nextCalled = false;
        $next = function () use (&$nextCalled) {
            $nextCalled = true;
            return 'ok';
        };

        $result = $middleware->handle(['auth' => false], $next);

        $this->assertTrue($nextCalled);
        $this->assertEquals('ok', $result);
    }
}
