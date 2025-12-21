<?php

declare(strict_types=1);

namespace CoffeeShop\Http;

/**
 * Simple HTTP Router
 * 
 * Matches incoming requests to registered routes and dispatches
 * to the appropriate controller action. Supports path parameters.
 */
class Router
{
    private array $routes = [];
    private static ?self $instance = null;

    /**
     * Get the singleton instance (for route registration)
     */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Register a GET route
     */
    public function get(string $path, array $handler): self
    {
        return $this->addRoute('GET', $path, $handler);
    }

    /**
     * Register a POST route
     */
    public function post(string $path, array $handler): self
    {
        return $this->addRoute('POST', $path, $handler);
    }

    /**
     * Register a PUT route
     */
    public function put(string $path, array $handler): self
    {
        return $this->addRoute('PUT', $path, $handler);
    }

    /**
     * Register a DELETE route
     */
    public function delete(string $path, array $handler): self
    {
        return $this->addRoute('DELETE', $path, $handler);
    }

    /**
     * Add a route to the routing table
     */
    private function addRoute(string $method, string $path, array $handler): self
    {
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'pattern' => $this->pathToPattern($path),
            'handler' => $handler,
        ];
        return $this;
    }

    /**
     * Convert a route path to a regex pattern
     * 
     * Examples:
     *   /api/v1/drinks -> /^\/api\/v1\/drinks$/
     *   /api/v1/drinks/{id} -> /^\/api\/v1\/drinks\/([^\/]+)$/
     */
    private function pathToPattern(string $path): string
    {
        // Escape forward slashes
        $pattern = preg_quote($path, '/');
        
        // Convert {param} to capturing groups
        $pattern = preg_replace('/\\\{([a-zA-Z_]+)\\\}/', '([^\/]+)', $pattern);
        
        return '/^' . $pattern . '$/';
    }

    /**
     * Extract parameter names from a route path
     */
    private function extractParamNames(string $path): array
    {
        preg_match_all('/\{([a-zA-Z_]+)\}/', $path, $matches);
        return $matches[1] ?? [];
    }

    /**
     * Dispatch the request to the appropriate handler
     */
    public function dispatch(Request $request): Response
    {
        $method = $request->getMethod();
        $path = $request->getPath();

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            if (preg_match($route['pattern'], $path, $matches)) {
                array_shift($matches); // Remove full match

                // Extract parameter names and create associative array
                $paramNames = $this->extractParamNames($route['path']);
                $params = [];
                foreach ($paramNames as $index => $name) {
                    $params[$name] = $matches[$index] ?? null;
                }

                return $this->callHandler($route['handler'], $request, $params);
            }
        }

        return Response::notFound('Endpoint not found: ' . $method . ' ' . $path);
    }

    /**
     * Call the route handler
     */
    private function callHandler(array $handler, Request $request, array $params): Response
    {
        [$controllerClass, $method] = $handler;

        if (!class_exists($controllerClass)) {
            return Response::error("Controller not found: $controllerClass", 500);
        }

        $controller = new $controllerClass();

        if (!method_exists($controller, $method)) {
            return Response::error("Method not found: $controllerClass::$method", 500);
        }

        return $controller->$method($request, $params);
    }
}

