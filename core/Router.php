<?php
namespace Core;

/**
 * Route table mapping METHOD + path to a [Controller, method] pair.
 * Supports named placeholders: '/report/{id}'.
 */
class Router
{
    private array $routes = ['GET' => [], 'POST' => []];

    public function get(string $path, array $action): self
    {
        return $this->add('GET', $path, $action);
    }

    public function post(string $path, array $action): self
    {
        return $this->add('POST', $path, $action);
    }

    private function add(string $method, string $path, array $action): self
    {
        $this->routes[$method][$this->normalise($path)] = $action;
        return $this;
    }

    /**
     * Match the current request and invoke the controller action.
     */
    public function dispatch(string $method, string $uri): void
    {
        $method = strtoupper($method) === 'POST' ? 'POST' : 'GET';
        $path   = $this->normalise(parse_url($uri, PHP_URL_PATH) ?? '/');

        foreach ($this->routes[$method] as $route => $action) {
            $params = $this->match($route, $path);
            if ($params !== null) {
                $this->invoke($action, $params);
                return;
            }
        }

        $this->notFound($path);
    }

    /**
     * Compare a route pattern against a path.
     * Returns captured params, or null when the route does not match.
     */
    private function match(string $route, string $path): ?array
    {
        $routeParts = $route === '/' ? [] : explode('/', trim($route, '/'));
        $pathParts  = $path  === '/' ? [] : explode('/', trim($path, '/'));

        if (count($routeParts) !== count($pathParts)) {
            return null;
        }

        $params = [];
        foreach ($routeParts as $i => $segment) {
            if (preg_match('/^\{(\w+)\}$/', $segment, $m)) {
                $params[$m[1]] = $pathParts[$i];
                continue;
            }
            if (strcasecmp($segment, $pathParts[$i]) !== 0) {
                return null;
            }
        }

        return $params;
    }

    private function invoke(array $action, array $params): void
    {
        [$class, $method] = $action;

        if (!class_exists($class) || !method_exists($class, $method)) {
            throw new \RuntimeException("Route target {$class}::{$method}() does not exist.");
        }

        (new $class())->{$method}(...array_values($params));
    }

    private function notFound(string $path): void
    {
        http_response_code(404);
        (new \App\Controllers\ErrorController())->notFound($path);
    }

    /** Collapse a path to a leading-slash, no-trailing-slash form. */
    private function normalise(string $path): string
    {
        $path = '/' . trim($path, '/');
        return $path === '//' ? '/' : $path;
    }
}
