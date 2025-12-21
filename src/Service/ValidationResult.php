<?php

declare(strict_types=1);

namespace CoffeeShop\Service;

/**
 * Validation Result
 * 
 * A simple value object to represent the result of a validation operation.
 * Can be extended to include multiple errors if needed.
 */
class ValidationResult
{
    private bool $valid;
    private ?string $error;

    private function __construct(bool $valid, ?string $error = null)
    {
        $this->valid = $valid;
        $this->error = $error;
    }

    /**
     * Create a successful validation result
     */
    public static function success(): self
    {
        return new self(true);
    }

    /**
     * Create a failed validation result
     */
    public static function failure(string $error): self
    {
        return new self(false, $error);
    }

    /**
     * Check if validation passed
     */
    public function isValid(): bool
    {
        return $this->valid;
    }

    /**
     * Get the error message (null if valid)
     */
    public function getError(): ?string
    {
        return $this->error;
    }
}

