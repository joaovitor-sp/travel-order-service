# Travel Order Service

Microserviço para gerenciamento de pedidos de viagem com autenticação JWT, filas em Redis e execução via Docker.

## 🚀 Tecnologias

- PHP 8.4
- Laravel 12
- MySQL 8
- Redis 7 (filas)
- Docker
- JWT Authentication (tymon/jwt-auth)

## 📋 Arquitetura

Projeto seguindo **DDD** (Domain, Application, Http) e políticas de autorização:

```
app/
├── Domain/         # regra de negócio pura 
│   ├── Models/         # Entidades do domínio
│   ├── Enums/          # Estados fixos, como status de pedidos
│   └── Events/         # Eventos que representam algo que aconteceu no domínio 
├── Application/    # coordena a execução de uma tarefa, casos de uso    
│   ├── UseCases/       # Casos de uso da aplicação 
│   ├── Listeners/      # Escutam eventos e disparam ações
│   └── Jobs/           # Trabalhos assíncronos
└── Http/           # Camada de apresentação, responsável por expor a API
    ├── Controllers/    # REST controllers que recebem requisições HTTP
    ├── Middleware/     # Camada que intercepta requisições
    ├── Requests/       # Validações de dados
    ├── Policies/       # Regras de autorização
    └── Resources/      # Transformação de dados para retorno em JSON

```

## 🔧 Setup

### Com Docker compose

```bash
docker compose up -d --build
docker compose exec app php artisan migrate
```

O container `app` inicializa o servidor HTTP na porta 8000 e o worker de filas em background (Redis).

## 🔐 Autenticação JWT (stateless)

- Middleware: alias `jwt.stateless` (hidrata `Auth::user` pelo payload do JWT).
- Geração de token de teste: endpoint público.

### Endpoints de token

- `GET /api/test/generate-token` → retorna um token de exemplo (`sub`, `name`, `is_admin`).

Header obrigatório nas rotas protegidas:

```
Authorization: Bearer {seu_token}
```

## 📚 API

Grupo protegido por `jwt.stateless`: `/api/v1/*`

| Método | Endpoint | Permissão | Descrição |
|--------|----------|-----------|-----------|
| GET | `/api/v1/orders` | Autenticado | Lista pedidos (usuário vê os seus; admin vê todos) |
| POST | `/api/v1/orders` | Autenticado | Cria pedido |
| GET | `/api/v1/orders/{id}` | Autenticado | Detalhe do pedido |
| POST | `/api/v1/orders/{id}/approve` | Autheticado | Aprova pedido |
| PATCH | `/api/v1/orders/{id}` | Autenticado | Atualiza pedido (parcial) |
| POST | `/api/v1/orders/{id}/cancel` | Autenticado | Cancela pedido |

Filtros na listagem: `status`, `destination`, `created_from/created_to`, `departure_date_from/departure_date_to`, `return_date_from/return_date_to`, `per_page`.

## 🧪 Exemplos

Gerar token de teste:
```bash
curl -s http://localhost:8000/api/test/generate-token | jq
```

Atualizar pedido (parcial):
```bash
curl -s -X PATCH http://localhost:8000/api/v1/orders/1 \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "destination": "Rio de Janeiro"
  }' | jq
```

Listar pedidos:
```bash
curl -s "http://localhost:8000/api/v1/orders?status=requested" \
  -H "Authorization: Bearer {token}" | jq
```

Criar pedido:
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

Aprovar (admin):
```bash
curl -s -X POST http://localhost:8000/api/v1/orders/1/approve \
  -H "Authorization: Bearer {token_userAdmin}" | jq
```

Cancelar:
```bash
curl -s -X POST http://localhost:8000/api/v1/orders/1/cancel \
  -H "Authorization: Bearer {token_userAdmin}" | jq
```

## 📝 Regras de Negócio

- Criação: usuário autenticado; `user_id` vêm do JWT; status inicial `requested`.
- Listagem: não-admin vê somente seus pedidos; admin vê todos; filtros e paginação.
- Aprovação: apenas admin; não aprova já aprovado.
- Cancelamento: apenas admin; não cancela já cancelado; não cancela aprovado.
- Atualização: apenas dono do pedido; permitido enquanto `status` for `requested`; não é possível alterar `status` via update.

Políticas registradas em `AppServiceProvider` (ex.: `TravelOrderPolicy`).

## 📦 Banco de Dados

Tabela `travel_orders`:

| Campo | Tipo | Descrição |
|-------|------|-----------|
| id | bigint | PK auto-increment |
| user_id | string | ID do usuário (JWT `sub`) |
| destination | string | Destino |
| departure_date | date | Partida |
| return_date | date | Retorno |
| status | string | requested/approved/canceled |
| created_at | timestamp | Criado em |
| updated_at | timestamp | Atualizado em |

## 📨 Filas (Redis)

- `QUEUE_CONNECTION=redis` no `.env`.
- Worker roda dentro do container `app` (via script/start). Listeners que implementam `ShouldQueue` são enfileirados automaticamente.
- Evento de domínio: `TravelOrderStatusUpdated` → listener `QueueTravelOrderStatusNotification` (registrado em `AppServiceProvider`).

Logs do worker/APP:
```bash
docker compose logs -f app
```

## 🧪 Testes

```bash
docker compose exec app php artisan test
```

## 🐳 Docker

Serviços:
- `app`: PHP + Laravel (serve + queue worker)
- `mysql`: banco
- `redis`: filas

Subir:
```bash
docker compose up -d --build
```

Derrubar:
```bash
docker compose down
```
