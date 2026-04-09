<?php

namespace App;

use App\Attributes\Route;
use App\Dto\ApiResponseDto;
use App\Http\Request;
use Psr\Container\ContainerInterface;
use ReflectionClass;

class Router
{
    private array $routes = [];
    private array $middlewares = [];
    private ?ContainerInterface $container = null;

    public function __construct(?ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function addMiddleware(object $middleware): void
    {
        $this->middlewares[] = $middleware;
    }

    public function registerController(string $controllerClass): void
    {
        $reflection = new ReflectionClass($controllerClass);
        foreach ($reflection->getMethods() as $method) {
            $attributes = $method->getAttributes(Route::class);
            foreach ($attributes as $attribute) {
                $route = $attribute->newInstance();
                $path = '/' . ltrim($route->path, '/');
                if (strlen($path) > 1) {
                    $path = rtrim($path, '/');
                }
                $this->routes[strtoupper($route->method)][$path] = [
                    'class' => $controllerClass,
                    'method' => $method->getName(),
                    'auth' => $route->auth
                ];
            }
        }
    }

    public function run()
    {
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $path = parse_url($uri, PHP_URL_PATH);

        $path = '/' . ltrim($path, '/');
        if (strlen($path) > 1) {
            $path = rtrim($path, '/');
        }

        if (isset($this->routes[$method][$path])) {
            $routeData = $this->routes[$method][$path];
            $request = new Request();

            $pipeline = function ($data) use ($request) {
                $controllerClass = $data['class'];
                $methodName = $data['method'];

                $controller = $this->container
                    ? $this->container->get($controllerClass)
                    : new $controllerClass();

                return $controller->$methodName($request);
            };

            foreach (array_reverse($this->middlewares) as $middleware) {
                $next = $pipeline;
                $pipeline = function ($data) use ($middleware, $next) {
                    return $middleware->handle($data, $next);
                };
            }

            return $pipeline($routeData);
        }

        http_response_code(404);
        header('Content-Type: application/json');
        return (string) ApiResponseDto::error([
            'message' => 'Not Found',
            'requested_path' => $path,
            'requested_method' => $method
        ]);
    }
}
