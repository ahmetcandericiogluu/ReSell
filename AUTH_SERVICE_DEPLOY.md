# Auth Service Deployment Guide

## 🚀 Render'a Deploy Etme

### 1. Render Dashboard'da Yeni Web Service Oluştur

1. **New +** → **Web Service**
2. **Repository**: ReSell-Project repository'sini seç
3. **Root Directory**: `auth-service` yaz
4. **Name**: `resell-auth-service`
5. **Runtime**: **PHP**
6. **Build Command**:
   ```bash
   composer install --no-dev --optimize-autoloader
   php bin/console cache:clear --env=prod --no-debug
   ```
7. **Start Command**:
   ```bash
   heroku-php-apache2 public/
   ```

### 2. Environment Variables Ekle

Dashboard → Environment sekmesinde:

```env
APP_ENV=prod
APP_SECRET=<GÜÇLÜ-BİR-SECRET-ÜRET>
DATABASE_URL=<RENDER-POSTGRESQL-URL>
CORS_ALLOW_ORIGIN=*
```

**ÖNEMLİ:** `APP_SECRET` monolith ile **AYNI** olmalı (JWT doğrulama için)!

### 3. Database Bağlantısı

- Monolith ile **aynı PostgreSQL** database'i kullan
- `DATABASE_URL` her iki serviste de aynı olmalı
- Database: `resell-db` (Render PostgreSQL)

### 4. Health Check

- **Health Check Path**: `/auth/me` (OPTIONS veya HEAD request)

### 5. Deploy

- **Create Web Service** butonuna tıkla
- Otomatik deploy başlayacak

## 🔗 Servis URL'leri

Deploy sonrası URL'ler:
- **Auth Service**: `https://resell-auth-service.onrender.com`
- **Endpoints**:
  - `POST /auth/register`
  - `POST /auth/login`
  - `GET /auth/me`

## 🧪 Test

```bash
# Register
curl -X POST https://resell-auth-service.onrender.com/auth/register \
  -H "Content-Type: application/json" \
  -d '{"email":"test@example.com","password":"123456","name":"Test User"}'

# Login
curl -X POST https://resell-auth-service.onrender.com/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"test@example.com","password":"123456"}'
```

## 🔐 Production CORS

`auth-service/config/packages/nelmio_cors.yaml`:

```yaml
when@prod:
    nelmio_cors:
        defaults:
            allow_origin:
                - 'https://resell-frontend.onrender.com'
                - 'https://www.yourproductiondomain.com'
```

## ⚠️ Önemli Notlar

1. **APP_SECRET** her iki serviste (auth + backend) **AYNI** olmalı
2. **DATABASE_URL** her iki serviste de **AYNI** olmalı
3. CORS production'da frontend domain'ine izin vermeli
4. Health check endpoint JWT gerektirmemeli (şu anda `/auth/me` JWT gerektirir, düzeltilmeli)

## 🔄 CI/CD

Render otomatik deploy:
- `main` branch'e push → Otomatik deploy
- `auth-service/` klasöründeki değişiklikler → Sadece auth-service deploy olur

## 📊 Monitoring

Render Dashboard'da:
- Logs
- Metrics
- Health checks
- Environment variables

