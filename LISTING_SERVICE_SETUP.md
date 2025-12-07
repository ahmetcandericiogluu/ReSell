# Listing Service - Kurulum Tamamlandı ✅

Listing Service mikroservisi başarıyla oluşturuldu!

## 📁 Dizin Yapısı

```
services/listing/
├── config/
│   ├── packages/
│   │   ├── doctrine.yaml
│   │   ├── nelmio_cors.yaml
│   │   ├── security.yaml
│   │   └── ...
│   └── services.yaml
├── migrations/
│   └── Version20251207100000.php
├── src/
│   ├── Controller/
│   │   ├── CategoryController.php
│   │   ├── HealthCheckController.php
│   │   └── ListingController.php
│   ├── DataFixtures/
│   │   └── CategoryFixtures.php
│   ├── DTO/
│   │   └── Listing/
│   │       ├── CategoryResponse.php
│   │       ├── ListingCreateRequest.php
│   │       ├── ListingResponse.php
│   │       └── ListingUpdateRequest.php
│   ├── Entity/
│   │   ├── Category.php
│   │   ├── Listing.php
│   │   └── ListingImage.php
│   ├── EventListener/
│   │   └── ExceptionListener.php
│   ├── Repository/
│   │   ├── CategoryRepository.php
│   │   ├── ListingImageRepository.php
│   │   └── ListingRepository.php
│   ├── Security/
│   │   ├── AuthenticationEntryPoint.php
│   │   ├── JwtAuthenticator.php
│   │   ├── JwtTokenManager.php
│   │   └── JwtUser.php
│   ├── Service/
│   │   └── ListingService.php
│   └── Kernel.php
├── Dockerfile
├── docker-entrypoint.sh
├── README.md
└── composer.json
```

## ✅ Tamamlanan Görevler

1. ✅ **Proje Yapısı**: `services/listing` dizini oluşturuldu
2. ✅ **Symfony 7.3 Setup**: Symfony skeleton kuruldu
3. ✅ **Docker & PostgreSQL 16**: Docker yapılandırması tamamlandı
4. ✅ **Domain Entities**: Listing, Category, ListingImage entity'leri oluşturuldu
5. ✅ **Doctrine Migrations**: Initial migration oluşturuldu
6. ✅ **Layered Architecture**: Controller, Service, Repository, DTO katmanları oluşturuldu
7. ✅ **REST API Endpoints**: Tüm CRUD endpoint'leri hazır
8. ✅ **JWT Authentication**: JWT authenticator ve güvenlik yapılandırması tamamlandı
9. ✅ **Error Handling**: Exception listener ile hata yönetimi eklendi
10. ✅ **Health Check**: Health check endpoint'i eklendi

## 🚀 Çalıştırma

### Docker ile Çalıştırma (Önerilen)

1. Docker servislerini başlatın:

```bash
docker-compose up listing-db listing-service
```

2. Migration'ları çalıştırın (başka bir terminal'de):

```bash
docker exec -it listing-service php bin/console doctrine:migrations:migrate --no-interaction
```

3. Kategori fixture'larını yükleyin:

```bash
docker exec -it listing-service php bin/console doctrine:fixtures:load --append
```

4. Servis hazır! Test edin:

```bash
curl http://localhost:8082/health
```

### Manuel Çalıştırma

1. PostgreSQL 16'yı başlatın (port 5434)

2. Bağımlılıkları yükleyin:

```bash
cd services/listing
composer install
```

3. Migration'ları çalıştırın:

```bash
php bin/console doctrine:migrations:migrate
```

4. Kategori fixture'larını yükleyin:

```bash
php bin/console doctrine:fixtures:load --append
```

5. Sunucuyu başlatın:

```bash
php -S 0.0.0.0:8082 -t public/
```

## 📡 API Endpoints

### Public Endpoints

- `GET /health` - Health check
- `GET /listings` - İlan listesi (sayfalama ve filtreleme ile)
- `GET /listings/{id}` - Tek ilan detayı
- `GET /categories` - Kategori listesi

### Protected Endpoints (JWT Token Gerekli)

- `POST /listings` - Yeni ilan oluştur
- `PUT /listings/{id}` - İlan güncelle (sadece ilan sahibi)
- `DELETE /listings/{id}` - İlan sil (soft delete, sadece ilan sahibi)

## 🔐 Authentication

Listing Service kendi başına login yapmaz. Auth-service'den alınan JWT token'ı kullanır.

**Header formatı:**
```
Authorization: Bearer <JWT_TOKEN>
```

**JWT Payload (beklenen):**
```json
{
  "sub": 123,           // User ID (sellerId olarak kullanılır)
  "email": "user@example.com",
  "name": "User Name",
  "iat": 1701950000,
  "exp": 1702036400
}
```

## 🔧 Konfigürasyon

### Environment Variables

Aşağıdaki ortam değişkenlerini yapılandırın:

```env
# Database
DATABASE_URL=postgresql://listing_user:listing_password@listing-db:5432/listing_service?serverVersion=16&charset=utf8

# JWT (auth-service ile aynı olmalı!)
JWT_SECRET=your-jwt-secret-key-must-match-auth-service
JWT_ALGORITHM=HS256

# CORS
CORS_ALLOW_ORIGIN='^https?://(localhost|127\.0\.0\.1)(:[0-9]+)?$'

# App
APP_ENV=dev
APP_SECRET=your-secret-key-change-this-in-production
```

## 🗄️ Veritabanı Şeması

### categories
- id (SERIAL)
- name (VARCHAR 255)
- slug (VARCHAR 255, UNIQUE)
- parent_id (INTEGER, nullable)

### listings
- id (SERIAL)
- seller_id (INTEGER) - Auth service'den gelen user ID
- category_id (INTEGER) - FK to categories
- title (VARCHAR 255)
- description (TEXT)
- price (NUMERIC 10,2)
- currency (VARCHAR 3)
- status (VARCHAR 50)
- location (VARCHAR 255, nullable)
- created_at (TIMESTAMP)
- updated_at (TIMESTAMP)
- deleted_at (TIMESTAMP, nullable) - Soft delete

### listing_images
- id (SERIAL)
- listing_id (INTEGER) - FK to listings
- url (VARCHAR 500)
- position (INTEGER)

## 📝 Test Örnekleri

### Kategori Listesini Getir

```bash
curl http://localhost:8082/categories
```

### İlanları Listele

```bash
curl "http://localhost:8082/listings?page=1&limit=20&status=active"
```

### İlan Oluştur (JWT Token ile)

```bash
curl -X POST http://localhost:8082/listings \
  -H "Authorization: Bearer YOUR_JWT_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "title": "iPhone 15 Pro",
    "description": "Sıfır ayarında",
    "price": 45000.00,
    "currency": "TRY",
    "categoryId": 1,
    "location": "Istanbul",
    "status": "active"
  }'
```

### İlan Güncelle (JWT Token ile)

```bash
curl -X PUT http://localhost:8082/listings/1 \
  -H "Authorization: Bearer YOUR_JWT_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "title": "iPhone 15 Pro - Updated",
    "price": 44000.00
  }'
```

### İlan Sil (JWT Token ile)

```bash
curl -X DELETE http://localhost:8082/listings/1 \
  -H "Authorization: Bearer YOUR_JWT_TOKEN"
```

## 🔍 Sorun Giderme

### Database Connection Hatası

Eğer "connection refused" hatası alıyorsanız:
1. PostgreSQL container'ının çalıştığından emin olun: `docker ps`
2. Database URL'nin doğru olduğunu kontrol edin
3. Container'lar aynı network'te olmalı

### JWT Validation Hatası

1. JWT_SECRET'in auth-service ile aynı olduğundan emin olun
2. Token'ın süresi dolmamış olmalı
3. Token formatı: `Bearer <token>`

### Migration Hatası

Cache'i temizleyin:
```bash
php bin/console cache:clear
php bin/console doctrine:migrations:migrate
```

## 🎯 Sonraki Adımlar

Listing Service hazır! Şimdi yapabilecekleriniz:

1. **Test**: API endpoint'lerini test edin
2. **Frontend Entegrasyonu**: Frontend'den listing servisi kullanın
3. **Monitoring**: Logs ve metrics ekleyin
4. **Rate Limiting**: API rate limiting ekleyin
5. **Image Upload**: Resim yükleme özelliği ekleyin
6. **Search**: Full-text search implementasyonu
7. **Pagination Optimization**: Cursor-based pagination
8. **Caching**: Redis cache layer ekleyin

## 📚 Dökümantasyon

Detaylı API dökümantasyonu için: `services/listing/README.md`

## ⚠️ Önemli Notlar

1. **JWT Secret**: Production'da mutlaka güçlü bir secret kullanın
2. **CORS**: Production'da sadece güvenilir origin'lere izin verin
3. **Database**: Production'da connection pooling kullanın
4. **Images**: Şu anda sadece URL saklanıyor, gerçek image storage ekleyin
5. **Soft Delete**: Silinen ilanlar database'de kalır, periyodik temizlik yapın

---

🎉 **Listing Service başarıyla kuruldu ve kullanıma hazır!**

