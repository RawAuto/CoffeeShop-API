# ☕ CoffeeShop API

A clean, modern REST API for managing coffee shop orders. Built with PHP 8.2, demonstrating senior-level engineering practices including Docker, clean architecture, comprehensive testing, static analysis, and CI/CD.

[![CI](https://github.com/RawAuto/CoffeeShop-API/actions/workflows/ci.yml/badge.svg)](https://github.com/RawAuto/CoffeeShop-API/actions/workflows/ci.yml)

## 📋 Prerequisites

- **Docker Desktop** installed and running ([Download](https://www.docker.com/products/docker-desktop/))
- **Git** for cloning the repository

No PHP, MySQL, or Composer installation required — everything runs in Docker containers.

## 🚀 Quick Start

### Option A: Using Make (Linux/macOS/WSL)

```bash
# Clone the repository
git clone https://github.com/RawAuto/CoffeeShop-API.git
cd CoffeeShop-API

# Start everything with one command
make setup

# API is now available at http://localhost:8080
# Documentation at http://localhost:8080/docs
```

### Option B: Without Make (Windows/PowerShell)

```powershell
# Clone the repository
git clone https://github.com/RawAuto/CoffeeShop-API.git
cd CoffeeShop-API

# Build and start containers
docker-compose build --no-cache
docker-compose up -d

# Wait a few seconds for MySQL to initialize, then install dependencies
docker-compose exec php composer install

# API is now available at http://localhost:8080
# Documentation at http://localhost:8080/docs
```

### Verify It's Working

```bash
# Health check (should return JSON with status "healthy")
curl http://localhost:8080/api/health

# Or just open in your browser:
# http://localhost:8080/api/health
```

### Stopping the Application

```bash
# With Make
make down

# Without Make
docker-compose down
```

The setup process:
1. Builds Docker containers (PHP-FPM, Nginx, MySQL)
2. Starts all services
3. Installs Composer dependencies
4. Runs database migrations automatically

## 📋 Available Commands

### With Make (Linux/macOS/WSL)

```bash
make help              # Show all available commands
make up                # Start containers
make down              # Stop containers
make logs              # View logs
make shell             # Open PHP container shell
make mysql             # Open MySQL CLI
make test              # Run all tests
make test-unit         # Run unit tests only
make test-integration  # Run integration tests only
make analyse           # Run PHPStan static analysis
make check             # Run static analysis + all tests
```

### Without Make (Windows/PowerShell)

```powershell
docker-compose up -d                           # Start containers
docker-compose down                            # Stop containers
docker-compose logs -f                         # View logs
docker-compose exec php sh                     # Open PHP container shell
docker-compose exec mysql mysql -u coffeeshop -psecret coffeeshop  # MySQL CLI
docker-compose exec php composer test          # Run all tests
docker-compose exec php composer test:unit     # Run unit tests only
docker-compose exec php composer test:integration  # Run integration tests
docker-compose exec php composer analyse       # Run PHPStan
docker-compose exec php composer check         # Run analysis + tests
```

## ✨ PHP 8.2 Features Used

This project leverages modern PHP 8.2 features:

| Feature | Example |
|---------|---------|
| **Enums** | `OrderStatus::Pending`, `DrinkSize::Medium`, `DrinkType::Coffee` |
| **Readonly Classes** | `readonly class Drink`, `readonly class OrderItem` |
| **Constructor Property Promotion** | `public function __construct(public string $name)` |
| **Union Types** | `Order|ValidationResult`, `OrderStatus|string` |
| **Named Arguments** | `new Drink(name: 'Latte', slug: 'latte', ...)` |
| **Nullsafe Operator** | `$this->createdAt?->format('c')` |
| **Match Expressions** | `match ($this) { self::Small => 1.0, ... }` |

## 🏗️ Architecture

```
┌─────────────────────────────────────────────────────────────────┐
│                         HTTP Request                             │
└─────────────────────────────────────────────────────────────────┘
                               │
                               ▼
┌─────────────────────────────────────────────────────────────────┐
│  Router                                                          │
│  - Pattern matching                                              │
│  - Parameter extraction                                          │
└─────────────────────────────────────────────────────────────────┘
                               │
                               ▼
┌─────────────────────────────────────────────────────────────────┐
│  Controller Layer                                                │
│  - Request validation                                            │
│  - Response formatting                                           │
│  - HTTP status codes                                             │
└─────────────────────────────────────────────────────────────────┘
                               │
                               ▼
┌─────────────────────────────────────────────────────────────────┐
│  Service Layer                                                   │
│  - Business logic                                                │
│  - Validation rules                                              │
│  - Orchestration                                                 │
└─────────────────────────────────────────────────────────────────┘
                               │
                               ▼
┌─────────────────────────────────────────────────────────────────┐
│  Repository Layer                                                │
│  - Data access abstraction                                       │
│  - Interface-based design                                        │
└─────────────────────────────────────────────────────────────────┘
                               │
                               ▼
┌─────────────────────────────────────────────────────────────────┐
│  Database (MySQL 8.0)                                            │
└─────────────────────────────────────────────────────────────────┘
```

### Directory Structure

```
CoffeeShop-API/
├── docker/                    # Docker configuration
│   ├── nginx/                 # Nginx config
│   ├── php/                   # PHP-FPM Dockerfile
│   └── mysql/                 # Database init script
├── src/
│   ├── Controller/            # HTTP request handlers
│   ├── Service/               # Business logic
│   ├── Repository/            # Data access layer
│   ├── Entity/                # Domain models (readonly)
│   ├── Enum/                  # PHP 8.1+ enums
│   └── Http/                  # Request/Response/Router
├── tests/
│   ├── Unit/                  # Unit tests
│   └── Integration/           # Integration tests
├── docs/
│   ├── openapi.yaml           # API specification
│   └── swagger-ui/            # Interactive documentation
├── public/
│   └── index.php              # Application entry point
├── docker-compose.yml
├── Makefile
├── phpstan.neon               # Static analysis config
└── phpunit.xml
```

## 📡 API Endpoints

### Health Check
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/health` | API health status |

### Drinks
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/v1/drinks` | List all drinks |
| GET | `/api/v1/drinks/{id}` | Get drink details |

### Orders
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/v1/orders` | List orders (paginated) |
| POST | `/api/v1/orders` | Create new order |
| GET | `/api/v1/orders/{id}` | Get order details |
| PUT | `/api/v1/orders/{id}` | Update order |
| DELETE | `/api/v1/orders/{id}` | Delete order |

### Example: Create Order

```bash
curl -X POST http://localhost:8080/api/v1/orders \
  -H "Content-Type: application/json" \
  -d '{
    "customer_name": "Jane Doe",
    "items": [
      {"drink_id": 2, "size": "medium", "quantity": 1, "cup_text": "Jane"},
      {"drink_id": 1, "size": "small", "quantity": 2}
    ],
    "notes": "Extra hot please"
  }'
```

## 🧪 Testing & Quality

```bash
# Run all tests
make test                                    # or: docker-compose exec php composer test

# Run only unit tests (no database required)
make test-unit                               # or: docker-compose exec php composer test:unit

# Run integration tests (requires database)
make test-integration                        # or: docker-compose exec php composer test:integration

# Run PHPStan static analysis (level 8)
make analyse                                 # or: docker-compose exec php composer analyse

# Run everything (analysis + tests)
make check                                   # or: docker-compose exec php composer check
```

### Quality Tools

| Tool | Purpose | Level |
|------|---------|-------|
| **PHPUnit 10** | Unit & integration testing | - |
| **PHPStan** | Static analysis | Level 8 (strictest) |

### Test Coverage

- **Unit Tests**: Service layer, Entity validation, business logic
- **Integration Tests**: API endpoints with real database

## 🔧 Technology Choices

| Technology | Why |
|------------|-----|
| **PHP 8.2** | Enums, readonly classes, constructor promotion, union types |
| **No Framework** | Demonstrates understanding of fundamentals |
| **MySQL 8.0** | Reliable, widely-used RDBMS |
| **Docker** | Reproducible environments |
| **PHPUnit 10** | Industry-standard testing |
| **PHPStan** | Catch bugs before runtime |
| **OpenAPI 3.0** | Standard API documentation |

## 🎯 Design Decisions

### Why No Framework?

This project intentionally avoids frameworks like Laravel or Symfony to demonstrate:
1. Understanding of PHP fundamentals and HTTP
2. Ability to structure code without framework scaffolding
3. Clean architecture principles that transfer to any language

### Repository Pattern

Interfaces allow swapping implementations:
- Production: MySQL repository
- Testing: In-memory repository (mocks)
- Future: Redis cache layer

### Validation in Service Layer

Business rules live in services, not controllers:
- Controllers handle HTTP concerns only
- Services are framework-agnostic
- Easier to test business logic in isolation

### Type-Safe Enums

Using PHP 8.1+ enums instead of string constants:
- Compile-time type checking
- IDE autocomplete
- Impossible to pass invalid values
- Self-documenting code

## 📊 Business Rules

| Drink | Allowed Sizes |
|-------|---------------|
| Espresso | Small only |
| Latte | Small, Medium |
| Americano | Small, Medium, Large |
| English Tea | Medium, Large |

## 🔐 Security Notes

This is a demo project. For production, add:
- JWT or OAuth2 authentication
- Rate limiting
- Input sanitization
- HTTPS enforcement
- Database connection pooling

## 🤖 Built With AI Assistance

This project was developed collaboratively with agentic AI (Claude). The AI assisted with:
- Code generation and architecture implementation
- Docker configuration and setup
- Test writing and documentation
- Debugging and refinement

All architectural decisions, requirements, and code reviews were directed by a human engineer. This reflects modern development workflows where AI tools augment—but don't replace—engineering judgment and expertise.

## 📄 License

MIT License - feel free to use this as a learning resource or starting point.

---

Built with ❤️ (and 🤖) to demonstrate modern PHP development practices.
