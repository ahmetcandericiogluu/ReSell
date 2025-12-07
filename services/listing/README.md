# Listing Service

Mikroservis mimarisi ile geliştirilmiş İlan Yönetim Servisi.

## Özellikler

- İlan oluşturma, güncelleme, silme (soft delete)
- İlan listeleme (filtreleme ve sayfalama ile)
- Kategori yönetimi
- JWT tabanlı kimlik doğrulama
- PostgreSQL 16 veritabanı
- Docker desteği
- RESTful API

## Teknolojiler

- Symfony 7.3
- PHP 8.3
- PostgreSQL 16
- Doctrine ORM
- JWT Authentication (Firebase PHP-JWT)
- Docker & Docker Compose

## Kurulum

### Docker ile Çalıştırma

1. Projenin root dizininde docker-compose ile servisleri başlatın:

```bash
docker-compose up listing-db listing-service
```

2. Migration'ları çalıştırın:

```bash
docker exec -it listing-service php bin/console doctrine:migrations:migrate --no-interaction
```

3. Servis http://localhost:8082 adresinde çalışacaktır.

### Manuel Kurulum

1. Bağımlılıkları yükleyin:

```bash
cd services/listing
composer install
```

2. `.env` dosyasını düzenleyin ve veritabanı bilgilerini girin.

3. Veritabanını oluşturun ve migration'ları çalıştırın:

```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```

4. Sunucuyu başlatın:

```bash
symfony server:start --port=8082
```

veya

```bash
php -S 0.0.0.0:8082 -t public/
```

## API Endpoints

### Health Check

```
GET /health
```

Response:
```json
{
  "status": "ok",
  "service": "listing-service",
  "timestamp": "2025-12-07 13:30:00"
}
```

### Kategoriler

#### Tüm Kategorileri Listele

```
GET /categories
```

Response:
```json
[
  {
    "id": 1,
    "name": "Elektronik",
    "slug": "elektronik",
    "parentId": null
  }
]
```

### İlanlar

#### İlanları Listele (Public)

```
GET /listings?page=1&limit=20&status=active&category_id=1&price_min=100&price_max=1000&location=Istanbul
```

Query Parameters:
- `page` (default: 1)
- `limit` (default: 20, max: 100)
- `status` (default: "active")
- `category_id` (optional)
- `price_min` (optional)
- `price_max` (optional)
- `location` (optional)

Response:
```json
{
  "data": [
    {
      "id": 1,
      "sellerId": 123,
      "title": "iPhone 15 Pro",
      "description": "Sıfır ayarında",
      "price": "45000.00",
      "currency": "TRY",
      "status": "active",
      "categoryId": 1,
      "categoryName": "Elektronik",
      "location": "Istanbul",
      "createdAt": "2025-12-07T10:00:00Z",
      "updatedAt": "2025-12-07T10:00:00Z",
      "images": [
        {
          "id": 1,
          "url": "https://example.com/image1.jpg",
          "position": 0
        }
      ]
    }
  ],
  "meta": {
    "page": 1,
    "limit": 20,
    "total": 1,
    "totalPages": 1
  }
}
```

#### Tek Bir İlanı Getir (Public)

```
GET /listings/{id}
```

Response: Tek bir ListingResponse objesi

#### İlan Oluştur (Auth Required)

```
POST /listings
Authorization: Bearer <JWT_TOKEN>
Content-Type: application/json

{
  "title": "iPhone 15 Pro",
  "description": "Sıfır ayarında, kutusunda",
  "price": 45000.00,
  "currency": "TRY",
  "categoryId": 1,
  "location": "Istanbul",
  "status": "active",
  "imageUrls": [
    "https://example.com/image1.jpg"
  ]
}
```

Response: 201 Created + ListingResponse

#### İlan Güncelle (Auth Required)

```
PUT /listings/{id}
Authorization: Bearer <JWT_TOKEN>
Content-Type: application/json

{
  "title": "iPhone 15 Pro - Updated",
  "price": 44000.00
}
```

Not: Sadece ilanın sahibi güncelleyebilir.

Response: 200 OK + ListingResponse

#### İlan Sil (Auth Required)

```
DELETE /listings/{id}
Authorization: Bearer <JWT_TOKEN>
```

Not: Soft delete yapılır. Sadece ilanın sahibi silebilir.

Response: 204 No Content

## Veritabanı Şeması

### categories

- `id` - SERIAL PRIMARY KEY
- `name` - VARCHAR(255)
- `slug` - VARCHAR(255) UNIQUE
- `parent_id` - INTEGER (self-reference)

### listings

- `id` - SERIAL PRIMARY KEY
- `seller_id` - INTEGER (User reference from auth-service)
- `category_id` - INTEGER (FK to categories)
- `title` - VARCHAR(255)
- `description` - TEXT
- `price` - NUMERIC(10,2)
- `currency` - VARCHAR(3)
- `status` - VARCHAR(50)
- `location` - VARCHAR(255)
- `created_at` - TIMESTAMP
- `updated_at` - TIMESTAMP
- `deleted_at` - TIMESTAMP (nullable)

### listing_images

- `id` - SERIAL PRIMARY KEY
- `listing_id` - INTEGER (FK to listings)
- `url` - VARCHAR(500)
- `position` - INTEGER

## Kimlik Doğrulama

Bu servis kendi başına login yapmaz. Auth-service'den alınan JWT token'ı kullanır.

JWT token'da beklenen payload:
```json
{
  "sub": 123,
  "email": "user@example.com",
  "name": "User Name",
  "iat": 1701950000,
  "exp": 1702036400
}
```

`sub` alanı kullanıcı ID'sini içerir ve sellerId olarak kullanılır.

## Hata Yönetimi

Tüm hatalar JSON formatında döner:

### 400 Bad Request (Validation Error)
```json
{
  "error": "Validation Failed",
  "errors": {
    "title": ["This value should not be blank."],
    "price": ["This value should be positive."]
  }
}
```

### 401 Unauthorized
```json
{
  "error": "Authentication required",
  "message": "Missing or invalid authentication token"
}
```

### 403 Forbidden
```json
{
  "error": "Forbidden",
  "message": "You are not authorized to update this listing"
}
```

### 404 Not Found
```json
{
  "error": "Not Found",
  "message": "Listing not found"
}
```

### 500 Internal Server Error
```json
{
  "error": "Internal Server Error",
  "message": "An error occurred"
}
```

## Geliştirme

### Migration Oluşturma

```bash
php bin/console doctrine:migrations:diff
```

### Migration Çalıştırma

```bash
php bin/console doctrine:migrations:migrate
```

### Cache Temizleme

```bash
php bin/console cache:clear
```

## Üretim Dağıtımı

1. `.env` dosyasını production için yapılandırın
2. `APP_ENV=prod` ve `APP_DEBUG=0` ayarlayın
3. Güvenli bir `JWT_SECRET` kullanın
4. Database credentials'ları güvenli bir şekilde saklayın
5. HTTPS kullanın
6. Rate limiting ekleyin

## Lisans

MIT

