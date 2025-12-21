# ☕ CoffeeShop API

A clean, modern REST API for managing coffee shop orders. Built with PHP 8.2, demonstrating senior-level engineering practices including Docker, clean architecture, comprehensive testing, and CI/CD.

[![CI](https://github.com/YOUR_USERNAME/CoffeeShop-API/actions/workflows/ci.yml/badge.svg)](https://github.com/YOUR_USERNAME/CoffeeShop-API/actions/workflows/ci.yml)

## 🚀 Quick Start

```bash
# Clone the repository
git clone https://github.com/YOUR_USERNAME/CoffeeShop-API.git
cd CoffeeShop-API

# Start everything with one command
make setup

# API is now available at http://localhost:8080
# Documentation at http://localhost:8080/docs
```

That's it! The `make setup` command:
1. Builds Docker containers
2. Starts MySQL, PHP-FPM, and Nginx
3. Installs Composer dependencies
4. Runs database migrations

## 📋 Available Commands

```bash
make help           # Show all available commands
make up             # Start containers
make down           # Stop containers
make logs           # View logs
make shell          # Open PHP container shell
make mysql          # Open MySQL CLI
make test           # Run all tests
make test-unit      # Run unit tests only
make test-integration  # Run integration tests only
```

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
│   ├── Entity/                # Domain models
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

## 🧪 Testing

```bash
# Run all tests
make test

# Run only unit tests (no database required)
make test-unit

# Run integration tests (requires database)
make test-integration
```

### Test Coverage

- **Unit Tests**: Service layer, Entity validation, business logic
- **Integration Tests**: API endpoints with real database

## 🔧 Technology Choices

| Technology | Why |
|------------|-----|
| **PHP 8.2** | Latest stable with enums, named arguments, union types |
| **No Framework** | Demonstrates understanding of fundamentals |
| **MySQL 8.0** | Reliable, widely-used RDBMS |
| **Docker** | Reproducible environments |
| **PHPUnit 10** | Industry-standard testing |
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

## 📄 License

MIT License - feel free to use this as a learning resource or starting point.

---

Built with ❤️ to demonstrate modern PHP development practices.

