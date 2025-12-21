<?php

declare(strict_types=1);

namespace CoffeeShop\Controller;

use CoffeeShop\Http\Request;
use CoffeeShop\Http\Response;

/**
 * Base controller with common functionality
 * 
 * All API controllers should extend this class to inherit
 * common validation and response helper methods.
 */
abstract class AbstractController
{
    /**
     * Validate required fields in request body
     * 
     * @param Request $request
     * @param array $fields List of required field names
     * @return array|null Returns array of missing fields, or null if all present
     */
    protected function validateRequired(Request $request, array $fields): ?array
    {
        $body = $request->getBody();
        $missing = [];

        foreach ($fields as $field) {
            if (!isset($body[$field]) || $body[$field] === '') {
                $missing[] = $field;
            }
        }

        return empty($missing) ? null : $missing;
    }

    /**
     * Get a validated integer ID from route params
     */
    protected function getIdParam(array $params, string $key = 'id'): ?int
    {
        $value = $params[$key] ?? null;
        
        if ($value === null) {
            return null;
        }

        if (!is_numeric($value) || (int)$value <= 0) {
            return null;
        }

        return (int)$value;
    }

    /**
     * Return a validation error response for missing fields
     */
    protected function missingFieldsResponse(array $fields): Response
    {
        return Response::validationError(
            'Missing required fields',
            ['missing_fields' => $fields]
        );
    }

    /**
     * Return an invalid ID response
     */
    protected function invalidIdResponse(): Response
    {
        return Response::validationError('Invalid ID provided');
    }
}

