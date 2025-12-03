# Mikroservis Mimarisi: Auth Service Ayrıştırma

## 📋 Genel Bakış

ReSell projesinde **authentication ve user management** logic'i monolith'ten ayrılarak bağımsız bir mikroservise dönüştürüldü.

### 🎯 Amaç
- Kimlik doğrulama işlemlerini ayrı bir servise taşımak
- JWT tabanlı stateless authentication
- Monolith'i business logic'e odaklamak
- Mikroservis mimarisine geçişin ilk adımı

## 🏗️ Mimari

```
┌─────────────────────┐
│   Frontend/Client   │
└──────────┬──────────┘
           │
    ┌──────┴──────────────────────┐
    │                             │
    ▼                             ▼
┌─────────────┐          ┌──────────────────┐
│ auth-service│          │    monolith      │
│   (Port:?)  │          │   (backend/)     │
├─────────────┤          ├──────────────────┤
│ POST /auth/ │          │ GET /api/me      │
│   register  │          │ GET /api/users   │
│ POST /auth/ │◄─────────│ PATCH /api/me    │
│   login     │   JWT    │                  │
│ GET  /auth/ │ validate │ Listing APIs     │
│   me        │          │ Review APIs      │
│             │          │ Message APIs     │
│ Returns JWT │          │                  │
└─────────────┘          └──────────────────┘
      │                           │
      │                           │
      ▼                           ▼
┌─────────────────────────────────────┐
│      PostgreSQL (resell_auth)       │
│       users table (shared)          │
└─────────────────────────────────────┘
```

## 📦 Proje Yapısı

### auth-service/
```
auth-service/
├── config/
│   ├── packages/
│   │   ├── doctrine.yaml      # User entity mapping
│   │   └── security.yaml      # Stateless security
│   ├── routes.yaml             # Auth routes
│   └── services.yaml           # JWT secret config
├── src/
│   └── Auth/
│       ├── Controller/
│       │   └── AuthController.php      # register, login, me
│       ├── Entity/
│       │   └── User.php                # User entity
│       ├── Repository/
│       │   └── UserRepository.php      
│       ├── Service/
│       │   ├── AuthService.php         # Auth logic
│       │   └── JwtTokenManager.php     # JWT üretimi/doğrulama
│       └── DTO/
│           ├── LoginRequest.php
│           ├── RegisterRequest.php
│           └── UserResponse.php
└── .env.local                           # DATABASE_URL, APP_SECRET
```

### backend/ (monolith)
```
backend/
├── src/
│   ├── User/
│   │   ├── Entity/
│   │   │   └── User.php                # Kept for relations
│   │   ├── Repository/
│   │   │   └── UserRepository.php      
│   │   ├── Controller/
│   │   │   └── UserController.php      # Profile endpoints only
│   │   ├── DTO/
│   │   │   ├── UpdateProfileRequest.php
│   │   │   └── UserProfileResponse.php
│   │   └── Security/
│   │       ├── JwtAuthenticator.php    # JWT doğrulama
│   │       └── AuthenticationEntryPoint.php
│   ├── Listing/...
│   ├── Review/...
│   └── Shared/...
└── config/packages/security.yaml        # JWT authenticator kullanımı
```

## 🔐 Authentication Flow

### 1. Register Flow
```
Client
  │
  │ POST /auth/register
  │ {email, password, name}
  ▼
auth-service
  │
  ├─► Validate input
  ├─► Hash password
  ├─► Save to DB
  └─► Generate JWT
      │
      │ {user, token}
      ▼
    Client stores JWT
```

### 2. Login Flow
```
Client
  │
  │ POST /auth/login
  │ {email, password}
  ▼
auth-service
  │
  ├─► Find user by email
  ├─► Verify password
  └─► Generate JWT
      │
      │ {user, token}
      ▼
    Client stores JWT
```

### 3. Protected API Request Flow
```
Client
  │
  │ GET /api/listings/me
  │ Header: Authorization: Bearer <JWT>
  ▼
monolith
  │
  ├─► JwtAuthenticator
  │     ├─► Decode JWT (same APP_SECRET)
  │     ├─► Extract user email
  │     └─► Load User from DB
  │
  ├─► Security check passes
  └─► Execute business logic
      │
      │ Response
      ▼
    Client
```

## 🔧 Konfigürasyon

### auth-service

#### .env.local
```env
APP_SECRET=same-secret-as-monolith
DATABASE_URL="postgresql://app:!ChangeMe!@127.0.0.1:5432/resell_auth?serverVersion=16"
```

#### config/services.yaml
```yaml
App\Auth\Service\JwtTokenManager:
    arguments:
        $secret: '%env(APP_SECRET)%'
```

### monolith (backend)

#### config/services.yaml
```yaml
App\User\Security\JwtAuthenticator:
    arguments:
        $jwtSecret: '%env(APP_SECRET)%'
```

#### config/packages/security.yaml
```yaml
security:
    firewalls:
        main:
            stateless: true
            custom_authenticators:
                - App\User\Security\JwtAuthenticator
    
    access_control:
        - { path: ^/api/listings$, roles: PUBLIC_ACCESS }
        - { path: ^/api/users/\d+$, roles: PUBLIC_ACCESS }
        - { path: ^/api, roles: ROLE_USER }
```

## 🚀 API Endpoints

### auth-service
| Method | Path            | Description              | Auth Required |
|--------|-----------------|--------------------------|---------------|
| POST   | /auth/register  | Yeni kullanıcı kaydı     | No            |
| POST   | /auth/login     | Giriş yap, JWT al        | No            |
| GET    | /auth/me        | JWT'den user bilgisi     | JWT Header    |

### monolith (backend)
| Method | Path                     | Description          | Auth Required |
|--------|--------------------------|----------------------|---------------|
| GET    | /api/me                  | Profil bilgisi       | JWT Header    |
| PATCH  | /api/me                  | Profil güncelle      | JWT Header    |
| GET    | /api/users/{id}          | Kullanıcı profili    | No            |
| GET    | /api/listings            | İlan listesi         | No            |
| POST   | /api/listings            | Yeni ilan            | JWT Header    |
| ...    | ...                      | ...                  | ...           |

## 📝 Değişiklikler

### ✅ auth-service'e Taşındı
- ✅ User entity (auth için)
- ✅ UserRepository
- ✅ Register logic
- ✅ Login logic
- ✅ Password hashing
- ✅ JWT token generation
- ✅ AuthController (register, login, me)
- ✅ Auth DTO'lar (LoginRequest, RegisterRequest, UserResponse)

### ✅ monolith'ten Kaldırıldı
- ✅ AuthController
- ✅ UserService (auth kısmı)
- ✅ Session-based authentication
- ✅ Login/Register routes
- ✅ JsonLoginAuthenticator (eski)
- ✅ LoginRequest, RegisterRequest, UserResponse DTO'ları

### ✅ monolith'te Eklendi
- ✅ JwtAuthenticator (JWT doğrulama)
- ✅ Stateless security config
- ✅ Public access için route patterns

### ✅ monolith'te Kaldı
- ✅ User entity (ilişkiler için: Listing, Review)
- ✅ UserRepository (profil işlemleri için)
- ✅ UserController (profil görüntüleme/güncelleme)
- ✅ UpdateProfileRequest, UserProfileResponse

## 🔄 Migration Stratejisi

### Database
- Her iki servis de aynı `users` tablosunu kullanıyor
- **Gelecekte:** Her servise ayrı database
  - auth-service → `resell_auth` DB
  - monolith → `resell_main` DB
  - User data sync gerekecek

### Development
1. auth-service'i başlat: `php -S localhost:8001 -t auth-service/public`
2. monolith'i başlat: `php -S localhost:8000 -t backend/public`
3. Frontend'den /auth/* için auth-service'e istek at
4. Diğer API'ler için monolith'e istek at

### Production
- auth-service: Ayrı domain (auth.resell.com)
- monolith: Ana domain (api.resell.com)
- Nginx/Load balancer ile routing

## 🧪 Testing

### auth-service Test
```bash
# Register
curl -X POST http://localhost:8001/auth/register \
  -H "Content-Type: application/json" \
  -d '{"email":"test@example.com","password":"123456","name":"Test User"}'

# Login
curl -X POST http://localhost:8001/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"test@example.com","password":"123456"}'

# Response: {"user":{...},"token":"eyJ0eXAiOiJKV1QiLCJhbGc..."}
```

### monolith Test
```bash
# Protected endpoint
curl -X GET http://localhost:8000/api/me \
  -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGc..."
```

## 📊 Sonraki Adımlar

1. **Database Separation**
   - auth-service → kendi database
   - User data replication stratejisi

2. **Service Communication**
   - monolith → auth-service token validation endpoint
   - Event-driven architecture (UserCreated, UserUpdated events)

3. **Additional Services**
   - messaging-service (Conversation, Message)
   - notification-service (Email, Push)
   - search-service (Elasticsearch)

4. **Infrastructure**
   - Docker containerization
   - Kubernetes orchestration
   - Service mesh (Istio)
   - API Gateway (Kong, Tyk)

5. **Monitoring & Logging**
   - Distributed tracing (Jaeger)
   - Centralized logging (ELK Stack)
   - Metrics (Prometheus, Grafana)

## ⚠️ Önemli Notlar

1. **APP_SECRET** her iki serviste de **aynı** olmalı (JWT signature için)
2. Database connection her iki serviste de aynı DB'ye bağlı (şimdilik)
3. Frontend'de login/register için auth-service URL'ini kullan
4. Monolith'teki korumalı endpoint'ler JWT header gerektirir
5. Session kullanımı tamamen kaldırıldı (stateless)

## 🔒 Security Checklist

- [x] JWT secret güvenli ve karmaşık
- [x] HTTPS kullanımı (production)
- [x] Token expiration (24 saat)
- [x] Password hashing (bcrypt)
- [x] Input validation
- [ ] Rate limiting (TODO)
- [ ] CORS configuration (TODO)
- [ ] Refresh token mechanism (TODO)

