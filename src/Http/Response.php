<?php

declare(strict_types=1);

namespace CoffeeShop\Http;

/**
 * HTTP Response wrapper
 * 
 * Provides a fluent interface for building and sending HTTP responses.
 * Handles JSON serialization and proper header management.
 */
class Response
{
    private int $statusCode;
    private array $headers;
    private mixed $body;

    public function __construct(mixed $body = null, int $statusCode = 200, array $headers = [])
    {
        $this->body = $body;
        $this->statusCode = $statusCode;
        $this->headers = $headers;
    }

    /**
     * Create a successful JSON response
     */
    public static function json(mixed $data, int $statusCode = 200): self
    {
        return new self($data, $statusCode, ['Content-Type' => 'application/json']);
    }

    /**
     * Create a 201 Created response
     */
    public static function created(mixed $data, string $location = null): self
    {
        $headers = ['Content-Type' => 'application/json'];
        if ($location) {
            $headers['Location'] = $location;
        }
        return new self($data, 201, $headers);
    }

    /**
     * Create a 204 No Content response
     */
    public static function noContent(): self
    {
        return new self(null, 204);
    }

    /**
     * Create an error response
     */
    public static function error(string $message, int $statusCode = 400, array $details = []): self
    {
        $body = [
            'error' => true,
            'message' => $message,
        ];

        if (!empty($details)) {
            $body['details'] = $details;
        }

        return new self($body, $statusCode, ['Content-Type' => 'application/json']);
    }

    /**
     * Create a 404 Not Found response
     */
    public static function notFound(string $message = 'Resource not found'): self
    {
        return self::error($message, 404);
    }

    /**
     * Create a 422 Validation Error response
     */
    public static function validationError(string $message, array $errors = []): self
    {
        return self::error($message, 422, $errors);
    }

    /**
     * Set a header
     */
    public function withHeader(string $name, string $value): self
    {
        $this->headers[$name] = $value;
        return $this;
    }

    /**
     * Set the status code
     */
    public function withStatus(int $statusCode): self
    {
        $this->statusCode = $statusCode;
        return $this;
    }

    /**
     * Get the status code
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * Get the body
     */
    public function getBody(): mixed
    {
        return $this->body;
    }

    /**
     * Send the response to the client
     */
    public function send(): void
    {
        // Set status code
        http_response_code($this->statusCode);

        // Set headers
        foreach ($this->headers as $name => $value) {
            header("$name: $value");
        }

        // Send body
        if ($this->body !== null) {
            if (is_array($this->body) || is_object($this->body)) {
                echo json_encode($this->body, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
            } else {
                echo $this->body;
            }
        }
    }
}

