<?php

declare(strict_types=1);

namespace CoffeeShop\Tests\Integration\Api;

use CoffeeShop\Controller\HealthController;
use CoffeeShop\Http\Request;
use PHPUnit\Framework\TestCase;

/**
 * Integration tests for the Health Check API endpoint
 * 
 * Note: These tests require a running database connection.
 * In CI, they run inside Docker with the full stack available.
 */
class HealthCheckTest extends TestCase
{
    private HealthController $controller;

    protected function setUp(): void
    {
        $this->controller = new HealthController();
    }

    public function testHealthCheckReturnsJsonResponse(): void
    {
        $request = new Request('GET', '/api/health');
        
        $response = $this->controller->check($request, []);
        
        $body = $response->getBody();
        
        $this->assertIsArray($body);
        $this->assertArrayHasKey('status', $body);
        $this->assertArrayHasKey('timestamp', $body);
        $this->assertArrayHasKey('version', $body);
        $this->assertArrayHasKey('checks', $body);
    }

    public function testHealthCheckIncludesDatabaseStatus(): void
    {
        $request = new Request('GET', '/api/health');
        
        $response = $this->controller->check($request, []);
        
        $body = $response->getBody();
        
        $this->assertArrayHasKey('database', $body['checks']);
        $this->assertArrayHasKey('status', $body['checks']['database']);
    }

    public function testHealthyResponseReturns200(): void
    {
        $request = new Request('GET', '/api/health');
        
        $response = $this->controller->check($request, []);
        
        // If database is up, should return 200
        if ($response->getBody()['status'] === 'healthy') {
            $this->assertEquals(200, $response->getStatusCode());
        } else {
            // If database is down, should return 503
            $this->assertEquals(503, $response->getStatusCode());
        }
    }
}

