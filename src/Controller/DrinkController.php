<?php

declare(strict_types=1);

namespace CoffeeShop\Controller;

use CoffeeShop\Http\Request;
use CoffeeShop\Http\Response;
use CoffeeShop\Service\DrinkService;

/**
 * Drink Controller
 *
 * Handles HTTP requests for drink-related endpoints.
 */
class DrinkController extends AbstractController
{
    private DrinkService $drinkService;

    public function __construct(?DrinkService $drinkService = null)
    {
        $this->drinkService = $drinkService ?? new DrinkService();
    }

    /**
     * GET /api/v1/drinks
     *
     * List all available drinks
     *
     * @param array<string, string|null> $params
     */
    public function index(Request $request, array $params): Response
    {
        $drinks = $this->drinkService->getAllDrinks();

        return Response::json([
            'data' => array_map(fn($drink) => $drink->toArray(), $drinks),
            'count' => count($drinks),
        ]);
    }

    /**
     * GET /api/v1/drinks/{id}
     *
     * Get a single drink by ID
     *
     * @param array<string, string|null> $params
     */
    public function show(Request $request, array $params): Response
    {
        $id = $this->getIdParam($params);

        if ($id === null) {
            return $this->invalidIdResponse();
        }

        $drink = $this->drinkService->getDrinkById($id);

        if ($drink === null) {
            return Response::notFound("Drink with ID $id not found");
        }

        return Response::json([
            'data' => $drink->toArray(),
        ]);
    }
}
