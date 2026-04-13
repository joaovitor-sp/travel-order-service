# Travel Order Service

Microservice for managing travel orders with JWT authentication, RabbitMQ queues, Redis cache, and Docker-based execution.

## 🚀 Technologies

* PHP 8.4
* Laravel 12
* MySQL 8
* Redis 7 (cache)
* RabbitMQ 3 (queues)
* Docker
* JWT Authentication (tymon/jwt-auth)

## 📋 Architecture

The project follows **DDD** (Domain, Application, Http) and authorization policies:

```
app/
├── Domain/         # Core business logic
│   ├── Models/         # Domain entities
│   ├── Enums/          # Fixed states (e.g., order status)
│   └── Events/         # Domain events
├── Application/    # Use case orchestration
│   ├── UseCases/       # Application use cases
│   ├── Listeners/      # Event listeners
│   └── Jobs/           # Async jobs
└── Http/           # Presentation layer (API)
    ├── Controllers/    # HTTP controllers
    ├── Middleware/     # Request interceptors
    ├── Requests/       # Validation
    ├── Policies/       # Authorization rules
    └── Resources/      # JSON transformers
```

## 🔧 Setup

### Using Docker Compose

```bash
cp .env.example .env
docker compose up -d --build
docker compose exec app composer install
docker compose exec app php artisan migrate
```

The `app` container starts the HTTP server on port 8000 and runs the RabbitMQ worker in the background.

## 🔐 JWT Authentication (stateless)

* Middleware: `jwt.stateless` (hydrates `Auth::user` from JWT payload)
* Public endpoint available to generate test tokens

### Token Endpoint

* `GET /api/test/generate-token` → returns a sample token (`sub`, `name`, `is_admin`)

Required header for protected routes:

```
Authorization: Bearer {your_token}
```

## 📚 API

Protected routes: `/api/v1/*`

| Method | Endpoint                      | Permission    | Description                                 |
| ------ | ----------------------------- | ------------- | ------------------------------------------- |
| GET    | `/api/v1/orders`              | Authenticated | List orders (user sees own, admin sees all) |
| POST   | `/api/v1/orders`              | Authenticated | Create order                                |
| GET    | `/api/v1/orders/{id}`         | Authenticated | Order details                               |
| POST   | `/api/v1/orders/{id}/approve` | Authenticated | Approve order                               |
| PATCH  | `/api/v1/orders/{id}`         | Authenticated | Update order                                |
| POST   | `/api/v1/orders/{id}/cancel`  | Authenticated | Cancel order                                |

### Filters

* status
* destination
* created_from / created_to
* departure_date_from / departure_date_to
* return_date_from / return_date_to
* per_page

### Fields

* Create: `destination`, `departure_date`, `return_date`
* Update: `destination`, `departure_date`, `return_date`

> Date format: `YYYY-MM-DD`

## 🧪 Examples

### Generate token

```bash
curl -s http://localhost:8000/api/test/generate-token | jq
```

### Update order

```bash
curl -s -X PATCH http://localhost:8000/api/v1/orders/1 \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "destination": "São Paulo",
    "departure_date": "2026-02-01",
    "return_date": "2026-02-05"
  }' | jq
```

### List orders

```bash
curl -s "http://localhost:8000/api/v1/orders?status=requested" \
  -H "Authorization: Bearer {token}" | jq
```

### Create order

```bash
curl -s -X POST http://localhost:8000/api/v1/orders \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "destination": "São Paulo",
    "departure_date": "2026-02-01",
    "return_date": "2026-02-05"
  }' | jq
```

### Approve (admin)

```bash
curl -s -X POST http://localhost:8000/api/v1/orders/1/approve \
  -H "Authorization: Bearer {admin_token}" | jq
```

### Cancel

```bash
curl -s -X POST http://localhost:8000/api/v1/orders/1/cancel \
  -H "Authorization: Bearer {admin_token}" | jq
```

## 📝 Business Rules

* Creation: authenticated user; `user_id` comes from JWT; initial status is `requested`
* Listing: users see only their own orders; admins see all
* Approval: admin only; cannot approve already approved orders
* Cancellation: admin only; cannot cancel approved or already canceled orders
* Update: only the owner; allowed only when status is `requested`; cannot change status via update

Policies registered in `AppServiceProvider` (e.g., `TravelOrderPolicy`)

## 📦 Database

Table: `travel_orders`

| Field          | Type      | Description                     |
| -------------- | --------- | ------------------------------- |
| id             | bigint    | Auto-increment PK               |
| user_id        | string    | User ID (JWT `sub`)             |
| destination    | string    | Destination                     |
| departure_date | date      | Departure                       |
| return_date    | date      | Return                          |
| status         | string    | requested / approved / canceled |
| created_at     | timestamp | Created at                      |
| updated_at     | timestamp | Updated at                      |

## 📨 Queues (RabbitMQ)

* `QUEUE_CONNECTION=rabbitmq`
* Worker runs inside `app` container
* Async listeners use `ShouldQueue`
* Event: `TravelOrderStatusUpdated`
* Listener: `QueueTravelOrderStatusNotification`

## ⚡ Cache (Redis)

* `CACHE_STORE=redis`
* `CACHE_TTL_SECONDS=30` (ajustável)
* Listagem e detalhe de ordens são cacheados; mudanças limpam o cache

Logs:

```bash
docker compose logs -f app
```

## 🧪 Tests

```bash
docker compose exec app php artisan test
```

## 🐳 Docker

Services:

* app (Laravel + queue worker)
* mysql
* redis
* rabbitmq

Start:

```bash
docker compose up -d --build
```

Stop:

```bash
docker compose down
```
