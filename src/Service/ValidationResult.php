<?php

declare(strict_types=1);

namespace CoffeeShop\Service;

use CoffeeShop\Enum\ValidationErrorType;

/**
 * Validation Result
 *
 * A simple value object to represent the result of a validation operation.
 * Includes error type for proper HTTP status code mapping.
 */
readonly class ValidationResult
{
    private function __construct(
        public bool $valid,
        public ?string $error = null,
        public ValidationErrorType $errorType = ValidationErrorType::InvalidInput,
    ) {}

    /**
     * Create a successful validation result
     */
    public static function success(): self
    {
        return new self(valid: true);
    }

    /**
     * Create a failed validation result
     */
    public static function failure(
        string $error,
        ValidationErrorType $type = ValidationErrorType::InvalidInput,
    ): self {
        return new self(valid: false, error: $error, errorType: $type);
    }

    /**
     * Create a not found validation result
     */
    public static function notFound(string $error): self
    {
        return new self(valid: false, error: $error, errorType: ValidationErrorType::NotFound);
    }

    /**
     * Check if validation passed
     */
    public function isValid(): bool
    {
        return $this->valid;
    }

    /**
     * Check if this is a not found error
     */
    public function isNotFound(): bool
    {
        return !$this->valid && $this->errorType === ValidationErrorType::NotFound;
    }

    /**
     * Get the error message (null if valid)
     */
    public function getError(): ?string
    {
        return $this->error;
    }

    /**
     * Get the error type
     */
    public function getErrorType(): ValidationErrorType
    {
        return $this->errorType;
    }
}
