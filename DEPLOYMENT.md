# Render Deployment Guide - ReSell Marketplace

## 📋 Deployment Adımları

### 1. Render Dashboard'a Giriş
- [Render.com](https://render.com) hesabınıza giriş yapın
- "New +" butonuna tıklayın
- **"Blueprint"** seçeneğini seçin

### 2. Repository Bağlantısı
- GitHub repository'nizi seçin
- Branch: `master` seçin
- `render.yaml` dosyası otomatik algılanacak

### 3. Database'ler (Otomatik Oluşturulacak)
Blueprint dosyası şu database'leri otomatik oluşturacak:
- ✅ **auth-db** - Auth Service için PostgreSQL
- ✅ **listing-db** - Listing Service için PostgreSQL  
- ✅ **backend-db** - Backend Monolith için PostgreSQL

**DİKKAT:** Database'leri manuel oluşturmanıza gerek YOK! Render otomatik yapacak.

### 4. Environment Variables (Önemli!)

#### 🔑 APP_SECRET Senkronizasyonu
Auth Service ve Listing Service **aynı `APP_SECRET`'i kullanmalı** (JWT için).

**Deployment sonrası yapılacaklar:**

1. **Auth Service'e gidin** → Settings → Environment
   - `APP_SECRET` değerini kopyalayın

2. **Listing Service'e gidin** → Settings → Environment
   - `APP_SECRET`'i auth service'den kopyaladığınız değer ile güncelleyin

#### Diğer Environment Variables (Otomatik Ayarlanacak):
- `DATABASE_URL` - Render otomatik bağlayacak
- `CORS_ALLOW_ORIGIN` - Blueprint'te tanımlı
- `APP_ENV=prod`
- `APP_DEBUG=0`

### 5. Deploy Sırası
Blueprint deployment sırası:
1. ✅ Database'ler oluşturulur
2. ✅ Auth Service deploy edilir + migration çalışır
3. ✅ Listing Service deploy edilir + migration + fixtures
4. ✅ Backend deploy edilir + migration
5. ✅ Frontend deploy edilir

### 6. Deployment Sonrası Kontroller

#### Test URL'leri:
```bash
# Auth Service Health Check
curl https://resell-auth-service.onrender.com/health

# Listing Service Health Check  
curl https://resell-listing-service.onrender.com/health

# Backend Health Check
curl https://resell-backend.onrender.com/

# Frontend
curl https://resell-frontend.onrender.com/
```

#### Kategorilerin Yüklendiğini Kontrol:
```bash
curl https://resell-listing-service.onrender.com/api/categories
```

### 7. Manuel Database Migration (Gerekirse)

Eğer migration otomatik çalışmazsa:

```bash
# Render Dashboard → Service → Shell

# Auth Service
php bin/console doctrine:migrations:migrate --no-interaction

# Listing Service
php bin/console doctrine:migrations:migrate --no-interaction
php bin/console doctrine:fixtures:load --no-interaction --append

# Backend
php bin/console doctrine:migrations:migrate --no-interaction
```

## 🔧 Önemli Notlar

### Free Plan Limitasyonları
- Her servis 750 saat/ay ücretsiz
- 15 dakika inaktivite sonrası sleep mode (ilk request yavaş olabilir)
- PostgreSQL: 256 MB RAM, 1 GB storage

### CORS Ayarları
Blueprint'te tüm `.onrender.com` domain'leri için CORS açık.

### Service URL'leri (Blueprint'ten sonra)
- **Auth Service**: `https://resell-auth-service.onrender.com`
- **Listing Service**: `https://resell-listing-service.onrender.com`
- **Backend**: `https://resell-backend.onrender.com`
- **Frontend**: `https://resell-frontend.onrender.com`

### APP_SECRET Senkronizasyon Kontrolü

Deployment sonrası test:
```bash
# 1. Login ol
curl -X POST https://resell-auth-service.onrender.com/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"test@resell.com","password":"test123"}'

# 2. Token'ı kopyala ve listing service'e test et
curl https://resell-listing-service.onrender.com/api/listings/me \
  -H "Authorization: Bearer <TOKEN>"
```

Eğer `401 Unauthorized` alırsanız → APP_SECRET'ler farklı demektir!

## 🚨 Sorun Giderme

### 1. Migration Hatası
**Hata**: `Connection refused`
**Çözüm**: Database hazır olana kadar bekleyin (2-3 dakika), sonra manuel redeploy

### 2. CORS Hatası
**Hata**: `CORS policy blocked`
**Çözüm**: Environment variables'da `CORS_ALLOW_ORIGIN` kontrol edin

### 3. 401 JWT Hatası
**Hata**: `Invalid token signature`
**Çözüm**: APP_SECRET'leri senkronize edin (yukarıya bakın)

### 4. Frontend API Bağlantı Hatası
**Hata**: `Network Error`
**Çözüm**: Frontend environment variables kontrol:
- `VITE_AUTH_SERVICE_URL`
- `VITE_LISTING_SERVICE_URL`
- `VITE_API_URL`

## 📝 Manuel Deployment (Blueprint Kullanmadan)

Eğer Blueprint kullanmak istemezseniz:

### 1. PostgreSQL Database'leri Oluşturun
Dashboard → New → PostgreSQL
- `auth-db`
- `listing-db`
- `backend-db`

### 2. Web Services Oluşturun
Her servis için:
- New → Web Service
- Docker runtime seçin
- Dockerfile path belirtin:
  - Auth: `./auth-service/Dockerfile.render`
  - Listing: `./services/listing/Dockerfile.render`
  - Backend: `./backend/Dockerfile.render`

### 3. Frontend Service
- New → Web Service
- Runtime: Node
- Build Command: `cd frontend && npm ci && npm run build`
- Start Command: `cd frontend && npm run preview -- --host 0.0.0.0 --port $PORT`

## 🎉 Başarılı Deployment!

Deployment tamamlandığında:
1. Frontend URL'ini tarayıcıda açın
2. Kayıt olun / Giriş yapın
3. İlan oluşturun
4. Mikroservis mimariniz canlıda! 🚀

