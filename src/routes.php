<?php

declare(strict_types=1);

/**
 * API Route Definitions
 * 
 * All API routes are defined here. The router instance is available
 * in this file's scope from the index.php entry point.
 */

use CoffeeShop\Http\Router;
use CoffeeShop\Controller\DrinkController;
use CoffeeShop\Controller\OrderController;
use CoffeeShop\Controller\HealthController;

/** @var Router $router */

// Health check endpoint
$router->get('/api/health', [HealthController::class, 'check']);

// Drinks endpoints
$router->get('/api/v1/drinks', [DrinkController::class, 'index']);
$router->get('/api/v1/drinks/{id}', [DrinkController::class, 'show']);

// Orders endpoints
$router->get('/api/v1/orders', [OrderController::class, 'index']);
$router->post('/api/v1/orders', [OrderController::class, 'store']);
$router->get('/api/v1/orders/{id}', [OrderController::class, 'show']);
$router->put('/api/v1/orders/{id}', [OrderController::class, 'update']);
$router->delete('/api/v1/orders/{id}', [OrderController::class, 'destroy']);

