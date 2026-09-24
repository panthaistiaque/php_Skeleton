<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Route container. A single route definition:
 *
 *   [$method, $pattern, 'Controller@action', ['middleware' | 'middleware:param']]
 */
final class Router
{
    public function __construct(private readonly array $routes) {}

    /**
     * @return array{controller:string,action:string,middleware:array,params:array,match:array}|null
     */
    public function match(string $method, string $path): ?array
    {
        $path = $path === '' ? '/' : '/' . ltrim($path, '/');
        $method = strtoupper($method);

        foreach ($this->routes as $route) {
            [$routeMethod, $pattern] = $route;
            if (strtoupper((string)$routeMethod) !== $method) {
                continue;
            }

            $params = $this->processPattern($pattern, $path);
            if ($params === null) {
                continue;
            }

            [$controller, $action] = explode('@', $route[2], 2);
            $middleware = $route[3] ?? [];

            return [
                'controller' => $controller,
                'action'     => $action,
                'middleware' => $middleware,
                'params'     => $params,
                'match'      => $route,
            ];
        }

        return null;
    }

    private function processPattern(string $pattern, string $path): ?array
    {
        $pattern = $pattern === '' ? '/' : '/' . ltrim($pattern, '/');

        // Static match shortcut
        if ($pattern === $path) {
            return [];
        }

        $regex = preg_replace('/\{([a-zA-Z_][a-zA-Z0-9_]*)\}/', '(?P<$1>[^/]+)', $pattern);
        if ($regex === null || !preg_match('#^' . $regex . '$#', $path, $matches)) {
            return null;
        }

        $params = [];
        foreach ($matches as $key => $value) {
            if (is_string($key)) {
                $params[$key] = $value;
            }
        }

        return $params;
    }
}