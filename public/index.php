<?php

declare(strict_types=1);

/**
 * CoffeeShop API - Entry Point
 * 
 * All requests are routed through this single entry point.
 * The router will dispatch to the appropriate controller.
 */

// Error reporting for development
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Define application root
define('APP_ROOT', dirname(__DIR__));

// Autoload dependencies
require_once APP_ROOT . '/vendor/autoload.php';

use CoffeeShop\Http\Router;
use CoffeeShop\Http\Request;
use CoffeeShop\Http\Response;

// Set JSON content type for API responses
header('Content-Type: application/json; charset=utf-8');

// CORS headers for development
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

try {
    // Create request from globals
    $request = Request::createFromGlobals();
    
    // Initialize router and register routes
    $router = new Router();
    
    // Register API routes
    require_once APP_ROOT . '/src/routes.php';
    
    // Dispatch the request
    $response = $router->dispatch($request);
    
    // Send the response
    $response->send();
    
} catch (\Throwable $e) {
    // Global error handler
    $statusCode = $e->getCode() >= 400 && $e->getCode() < 600 ? $e->getCode() : 500;
    
    http_response_code($statusCode);
    
    $errorResponse = [
        'error' => true,
        'message' => $e->getMessage(),
    ];
    
    // Include stack trace in development
    if (getenv('APP_ENV') === 'development') {
        $errorResponse['trace'] = $e->getTraceAsString();
    }
    
    echo json_encode($errorResponse, JSON_PRETTY_PRINT);
}

