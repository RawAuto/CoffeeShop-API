<?php

declare(strict_types=1);

namespace CoffeeShop\Controller;

use CoffeeShop\Http\Request;
use CoffeeShop\Http\Response;
use CoffeeShop\Repository\Database;

/**
 * Health Check Controller
 * 
 * Provides an endpoint for monitoring tools and load balancers
 * to verify the API is running and can connect to dependencies.
 */
class HealthController extends AbstractController
{
    /**
     * GET /api/health
     * 
     * Returns the health status of the API and its dependencies.
     */
    public function check(Request $request, array $params): Response
    {
        $status = 'healthy';
        $checks = [];

        // Check database connection
        try {
            $db = Database::getInstance();
            $db->query('SELECT 1');
            $checks['database'] = [
                'status' => 'up',
                'message' => 'Connected to MySQL'
            ];
        } catch (\Throwable $e) {
            $status = 'unhealthy';
            $checks['database'] = [
                'status' => 'down',
                'message' => $e->getMessage()
            ];
        }

        $responseCode = $status === 'healthy' ? 200 : 503;

        return Response::json([
            'status' => $status,
            'timestamp' => date('c'),
            'version' => '1.0.0',
            'checks' => $checks
        ], $responseCode);
    }
}

