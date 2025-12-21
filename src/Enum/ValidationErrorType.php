<?php

declare(strict_types=1);

namespace CoffeeShop\Enum;

/**
 * Validation Error Type Enum
 *
 * Categorizes validation errors for proper HTTP status code mapping.
 */
enum ValidationErrorType: string
{
    case NotFound = 'not_found';
    case InvalidInput = 'invalid_input';
    case BusinessRule = 'business_rule';
}

