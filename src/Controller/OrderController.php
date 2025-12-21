<?php

declare(strict_types=1);

namespace CoffeeShop\Controller;

use CoffeeShop\Http\Request;
use CoffeeShop\Http\Response;
use CoffeeShop\Service\OrderService;
use CoffeeShop\Service\ValidationResult;

/**
 * Order Controller
 * 
 * Handles HTTP requests for order-related endpoints.
 */
class OrderController extends AbstractController
{
    private OrderService $orderService;

    public function __construct(?OrderService $orderService = null)
    {
        $this->orderService = $orderService ?? new OrderService();
    }

    /**
     * GET /api/v1/orders
     * 
     * List all orders with pagination
     */
    public function index(Request $request, array $params): Response
    {
        $limit = (int)($request->getQuery('limit') ?? 50);
        $offset = (int)($request->getQuery('offset') ?? 0);

        // Validate pagination params
        $limit = max(1, min(100, $limit)); // Between 1 and 100
        $offset = max(0, $offset);

        $result = $this->orderService->getAllOrders($limit, $offset);

        return Response::json([
            'data' => array_map(fn($order) => $order->toArray(), $result['orders']),
            'meta' => [
                'total' => $result['total'],
                'limit' => $result['limit'],
                'offset' => $result['offset'],
            ],
        ]);
    }

    /**
     * GET /api/v1/orders/{id}
     * 
     * Get a single order by ID
     */
    public function show(Request $request, array $params): Response
    {
        $id = $this->getIdParam($params);
        
        if ($id === null) {
            return $this->invalidIdResponse();
        }

        $order = $this->orderService->getOrderById($id);

        if ($order === null) {
            return Response::notFound("Order with ID $id not found");
        }

        return Response::json([
            'data' => $order->toArray(),
        ]);
    }

    /**
     * POST /api/v1/orders
     * 
     * Create a new order
     * 
     * Request body:
     * {
     *   "customer_name": "John Doe",
     *   "items": [
     *     {"drink_id": 1, "size": "small", "quantity": 1, "cup_text": "John"}
     *   ],
     *   "notes": "Optional notes"
     * }
     */
    public function store(Request $request, array $params): Response
    {
        // Validate required fields
        $missing = $this->validateRequired($request, ['customer_name', 'items']);
        if ($missing !== null) {
            return $this->missingFieldsResponse($missing);
        }

        $body = $request->getBody();
        
        // Validate items is an array
        if (!is_array($body['items'])) {
            return Response::validationError('Items must be an array');
        }

        $result = $this->orderService->createOrder(
            customerName: $body['customer_name'],
            items: $body['items'],
            notes: $body['notes'] ?? null
        );

        if ($result instanceof ValidationResult) {
            return Response::validationError($result->getError());
        }

        return Response::created(
            ['data' => $result->toArray()],
            '/api/v1/orders/' . $result->getId()
        );
    }

    /**
     * PUT /api/v1/orders/{id}
     * 
     * Update an existing order
     * 
     * Request body (all optional):
     * {
     *   "customer_name": "Jane Doe",
     *   "status": "preparing",
     *   "notes": "Updated notes"
     * }
     */
    public function update(Request $request, array $params): Response
    {
        $id = $this->getIdParam($params);
        
        if ($id === null) {
            return $this->invalidIdResponse();
        }

        $result = $this->orderService->updateOrder($id, $request->getBody());

        if ($result instanceof ValidationResult) {
            // Check if it's a not found error
            if (str_contains($result->getError(), 'not found')) {
                return Response::notFound($result->getError());
            }
            return Response::validationError($result->getError());
        }

        return Response::json([
            'data' => $result->toArray(),
        ]);
    }

    /**
     * DELETE /api/v1/orders/{id}
     * 
     * Delete an order
     */
    public function destroy(Request $request, array $params): Response
    {
        $id = $this->getIdParam($params);
        
        if ($id === null) {
            return $this->invalidIdResponse();
        }

        $deleted = $this->orderService->deleteOrder($id);

        if (!$deleted) {
            return Response::notFound("Order with ID $id not found");
        }

        return Response::noContent();
    }
}

