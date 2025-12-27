# ReSell Messaging Service

Microservice for handling conversations and messages between buyers and sellers.

## Features

- Create conversations for listings
- Send and receive messages
- Unread message count
- Mark conversations as read
- JWT authentication
- Realtime-ready event dispatching

## API Endpoints

### Conversations

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/conversations` | Create/get conversation for a listing |
| GET | `/api/conversations` | List user's conversations |
| GET | `/api/conversations/{id}` | Get conversation with messages |
| POST | `/api/conversations/{id}/messages` | Send a message |
| POST | `/api/conversations/{id}/read` | Mark as read |

### Health Check

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/health` | Service health status |

## Quick Start (Local Development)

### 1. Start with Docker Compose

```bash
# From project root
docker-compose up -d messaging-service messaging-db
```

### 2. Verify service is running

```bash
curl http://localhost:8083/health
```

## Production Database (Neon PostgreSQL)

This service uses **Neon** for PostgreSQL in production because Render's free plan limits the number of databases.

### Neon Setup

1. Create a free account at https://neon.tech
2. Create a new project and database named `messaging_service`
3. Copy the **pooled connection string** (uses PgBouncer)
4. Ensure `sslmode=require` is in the connection string

### Connection String Format

```
postgresql://USER:PASSWORD@ep-xxx-pooler.neon.tech/messaging_service?sslmode=require
```

### Important Notes

- **SSL Required**: Neon requires SSL/TLS connections (`sslmode=require`)
- **Pooled Endpoint**: Use the `-pooler` endpoint for better connection handling
- **DBAL 4**: Doctrine DBAL 4 handles PgBouncer compatibility automatically

### Render Configuration

Set `DATABASE_URL` manually in Render Dashboard (not in render.yaml) with the Neon connection string.

### Production Verification

After deploying to Render, verify the service works:

```bash
# 1. Health Check
curl https://resell-messaging-service.onrender.com/health
# Expected: {"status":"ok"}

# 2. Test conversation creation (requires valid JWT)
curl -X POST https://resell-messaging-service.onrender.com/api/conversations \
  -H "Authorization: Bearer YOUR_JWT_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"listing_id": 1}'
# Expected: Conversation object with Neon-generated UUID
```

## Environment Variables

| Variable | Description | Example |
|----------|-------------|---------|
| `APP_ENV` | Environment | `dev` |
| `APP_SECRET` | Symfony secret | `your-secret-key` |
| `DATABASE_URL` | PostgreSQL connection | `postgresql://user:pass@host:5432/db` |
| `CORS_ALLOW_ORIGIN` | CORS pattern | `^https?://localhost` |
| `AUTH_SERVICE_URL` | Auth service URL | `http://auth-service:8000` |
| `LISTING_SERVICE_URL` | Listing service URL | `http://listing-service:8000` |

## API Examples

### Create Conversation

```bash
curl -X POST http://localhost:8083/api/conversations \
  -H "Authorization: Bearer YOUR_JWT_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"listing_id": 123}'
```

Response:
```json
{
  "id": "01234567-89ab-cdef-0123-456789abcdef",
  "listing_id": 123,
  "listing_title": "iPhone 12",
  "buyer_id": 1,
  "seller_id": 2,
  "created_at": "2024-12-27T10:00:00+00:00",
  "updated_at": "2024-12-27T10:00:00+00:00",
  "last_message": null,
  "unread_count": 0
}
```

### List Conversations

```bash
curl http://localhost:8083/api/conversations \
  -H "Authorization: Bearer YOUR_JWT_TOKEN"
```

### Get Conversation with Messages

```bash
curl "http://localhost:8083/api/conversations/{id}?page=1&limit=30" \
  -H "Authorization: Bearer YOUR_JWT_TOKEN"
```

### Send Message

```bash
curl -X POST http://localhost:8083/api/conversations/{id}/messages \
  -H "Authorization: Bearer YOUR_JWT_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"content": "Is this still available?"}'
```

Response:
```json
{
  "id": "01234567-89ab-cdef-0123-456789abcdef",
  "conversation_id": "...",
  "sender_id": 1,
  "content": "Is this still available?",
  "created_at": "2024-12-27T10:05:00+00:00"
}
```

### Mark as Read

```bash
curl -X POST http://localhost:8083/api/conversations/{id}/read \
  -H "Authorization: Bearer YOUR_JWT_TOKEN"
```

## Database Schema

```
┌─────────────────────┐
│    conversations    │
├─────────────────────┤
│ id (UUID)           │
│ listing_id          │
│ buyer_id            │
│ seller_id           │
│ listing_title       │
│ created_at          │
│ updated_at          │
└─────────┬───────────┘
          │
          │ 1:N
          ▼
┌─────────────────────┐     ┌──────────────────────────┐
│      messages       │     │ conversation_participants │
├─────────────────────┤     ├──────────────────────────┤
│ id (UUID)           │◄────│ last_read_message_id     │
│ conversation_id     │     │ conversation_id          │
│ sender_id           │     │ user_id                  │
│ content             │     │ created_at               │
│ created_at          │     │ updated_at               │
└─────────────────────┘     └──────────────────────────┘
```

## Realtime Ready

The service dispatches `MessageCreatedEvent` when a new message is created.
This event can be used to integrate with:
- WebSockets
- Mercure
- Pusher
- Server-Sent Events

Event payload:
```php
MessageCreatedEvent(
    conversationId: Uuid,
    messageId: Uuid,
    senderId: int,
    recipientId: int
)
```

## Ports

| Service | Port |
|---------|------|
| Messaging Service (Local) | 8083 |
| Messaging DB (Local) | 5433 |

## Running Migrations

```bash
docker exec messaging-service php bin/console doctrine:migrations:migrate
```

## Testing

```bash
docker exec messaging-service php bin/phpunit
```

