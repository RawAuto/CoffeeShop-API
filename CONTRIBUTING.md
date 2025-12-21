# Contributing to CoffeeShop API

Thank you for your interest in contributing! This document outlines the project structure and extension points for future development.

## 🚀 Getting Started

1. Fork the repository
2. Clone your fork
3. Run `make setup` to start the development environment
4. Create a feature branch: `git checkout -b feature/my-feature`
5. Make your changes
6. Run tests: `make test`
7. Commit and push
8. Open a Pull Request

## 📁 Project Structure

```
src/
├── Controller/     # HTTP handlers (thin layer)
├── Service/        # Business logic (main logic here)
├── Repository/     # Data access (interfaces + implementations)
├── Entity/         # Domain models
└── Http/           # Framework components (Router, Request, Response)
```

## 🔌 Extension Points

The following features are designed to be added without major refactoring:

### 1. Redis Caching

**Where to add**: Create `src/Cache/` directory

```php
interface CacheInterface {
    public function get(string $key): mixed;
    public function set(string $key, mixed $value, int $ttl = 3600): void;
    public function delete(string $key): void;
}
```

**Integration points**:
- Wrap `DrinkRepository` with a caching decorator
- Cache health check results
- Store session data (currently using database)

### 2. JWT Authentication

**Where to add**: Create `src/Auth/` directory

```php
// Middleware pattern
interface MiddlewareInterface {
    public function handle(Request $request, callable $next): Response;
}

class JwtAuthMiddleware implements MiddlewareInterface { ... }
```

**Integration points**:
- Add to `Router` before dispatching
- Protect order endpoints (allow drink listing without auth)
- Add `/api/v1/auth/login` and `/api/v1/auth/register` endpoints

### 3. Static Analysis (PHPStan)

**Setup**:
```bash
composer require --dev phpstan/phpstan
```

**Add to composer.json**:
```json
{
    "scripts": {
        "phpstan": "phpstan analyse src tests --level=6"
    }
}
```

**Enable in CI**: Uncomment the `static-analysis` job in `.github/workflows/ci.yml`

### 4. Code Coverage

**Setup**:
```bash
composer require --dev phpunit/php-code-coverage
```

**Run with coverage**:
```bash
./vendor/bin/phpunit --coverage-html coverage/
```

**Enable in CI**: Uncomment the `coverage` job in `.github/workflows/ci.yml`

### 5. Production Docker Configuration

Create `docker-compose.prod.yml`:
- Multi-stage build for smaller images
- Opcache configuration
- Remove xdebug
- Add health checks
- Configure logging

### 6. Database Migrations

**Current state**: Single `init.sql` file

**Recommended upgrade**: Use a migration library
- [Phinx](https://phinx.org/) - standalone migrations
- Create `migrations/` directory
- Version control schema changes

### 7. Event System

**Where to add**: Create `src/Event/` directory

```php
interface EventDispatcherInterface {
    public function dispatch(object $event): void;
}

// Example events
class OrderCreatedEvent { ... }
class OrderStatusChangedEvent { ... }
```

**Use cases**:
- Send notifications when order is ready
- Log audit trail
- Trigger webhooks

## 🧪 Testing Guidelines

### Unit Tests
- Test services in isolation with mocked repositories
- Test entity validation and business rules
- No database required

### Integration Tests
- Test API endpoints with real database
- Run in Docker or CI environment
- Clean database between tests

### Naming Conventions
```
tests/
├── Unit/
│   ├── Service/
│   │   └── DrinkServiceTest.php
│   └── Entity/
│       └── DrinkTest.php
└── Integration/
    └── Api/
        └── OrdersApiTest.php
```

## 📝 Code Style

- PSR-12 coding standard
- Strict types (`declare(strict_types=1)`)
- Type hints on all parameters and returns
- PHPDoc for complex methods
- Single responsibility principle

## 🔄 Git Workflow

1. `main` - production-ready code
2. `develop` - integration branch
3. `feature/*` - new features
4. `fix/*` - bug fixes
5. `docs/*` - documentation updates

## 📋 Pull Request Checklist

- [ ] Tests pass locally (`make test`)
- [ ] New code has tests
- [ ] Documentation updated if needed
- [ ] No debugging code left behind
- [ ] Follows existing code style

## 💡 Future Ideas

- [ ] GraphQL endpoint alongside REST
- [ ] WebSocket for real-time order updates
- [ ] Multi-tenant support
- [ ] Inventory management
- [ ] Payment integration
- [ ] Customer loyalty system
- [ ] Analytics dashboard

## ❓ Questions?

Open an issue for any questions about contributing!

