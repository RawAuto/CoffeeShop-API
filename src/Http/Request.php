<?php

declare(strict_types=1);

namespace CoffeeShop\Http;

/**
 * HTTP Request wrapper
 *
 * Encapsulates the incoming HTTP request, providing a clean interface
 * for accessing request data without directly touching superglobals.
 */
class Request
{
    private string $method;
    private string $uri;
    private string $path;
    /** @var array<string, mixed> */
    private array $query;
    /** @var array<string, mixed> */
    private array $body;
    /** @var array<string, string> */
    private array $headers;

    /**
     * @param array<string, mixed> $query
     * @param array<string, mixed> $body
     * @param array<string, string> $headers
     */
    public function __construct(
        string $method,
        string $uri,
        array $query = [],
        array $body = [],
        array $headers = [],
    ) {
        $this->method = strtoupper($method);
        $this->uri = $uri;
        $this->path = $this->parsePath($uri);
        $this->query = $query;
        $this->body = $body;
        $this->headers = $headers;
    }

    /**
     * Create a Request instance from PHP superglobals
     */
    public static function createFromGlobals(): self
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $query = $_GET;

        // Parse JSON body for API requests
        $body = [];
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';

        if (str_contains($contentType, 'application/json')) {
            $rawBody = file_get_contents('php://input');
            if ($rawBody) {
                $body = json_decode($rawBody, true) ?? [];
            }
        } else {
            $body = $_POST;
        }

        // Parse headers
        $headers = [];
        foreach ($_SERVER as $key => $value) {
            if (str_starts_with($key, 'HTTP_')) {
                $headerName = str_replace('_', '-', substr($key, 5));
                $headers[$headerName] = $value;
            }
        }

        return new self($method, $uri, $query, $body, $headers);
    }

    /**
     * Extract path from URI (removes query string)
     */
    private function parsePath(string $uri): string
    {
        $path = parse_url($uri, PHP_URL_PATH);
        if ($path === false || $path === null) {
            return '/';
        }
        return rtrim($path, '/') ?: '/';
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getUri(): string
    {
        return $this->uri;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getQuery(string $key = null, mixed $default = null): mixed
    {
        if ($key === null) {
            return $this->query;
        }
        return $this->query[$key] ?? $default;
    }

    /**
     * @return array<string, mixed>|mixed
     */
    public function getBody(string $key = null, mixed $default = null): mixed
    {
        if ($key === null) {
            return $this->body;
        }
        return $this->body[$key] ?? $default;
    }

    public function getHeader(string $name, string $default = null): ?string
    {
        $name = strtoupper(str_replace('-', '_', $name));
        return $this->headers[$name] ?? $default;
    }

    public function isMethod(string $method): bool
    {
        return $this->method === strtoupper($method);
    }
}
