# Render Environment Variables Setup

## 🔐 Kritik: APP_SECRET

**Her iki backend servisinde de AYNI olmalı!**

### APP_SECRET Üretme:

```bash
openssl rand -base64 32
```

Örnek çıktı: `xK9mP2vL8nQ4rT6yU1zA3cB5dE7fG9hI0jK2lM4nO6pQ8rS`

Bu değeri **hem auth-service hem backend'de** aynı kullan!

---

## 📋 Environment Variables (Blueprint Sonrası Manuel Ekle)

### 1. resell-auth-service

**Dashboard → resell-auth-service → Environment**

```env
# OTOMATIK GELEN (blueprint'ten):
APP_ENV=prod
DATABASE_URL=<otomatik-postgresql-bağlantısı>
CORS_ALLOW_ORIGIN=*
PORT=8080

# MANUEL EKLE:
APP_SECRET=<ÜRET-VE-İKİ-SERVİSTE-DE-AYNI-KULLAN>
```

### 2. resell-backend

**Dashboard → resell-backend → Environment**

```env
# OTOMATIK GELEN (blueprint'ten):
APP_ENV=prod
DATABASE_URL=<otomatik-postgresql-bağlantısı>
CORS_ALLOW_ORIGIN=*
PORT=8080

# MANUEL EKLE:
APP_SECRET=<YUKARDA-ÜRET-AYNI-DEĞER>

# R2 Storage (Cloudflare):
R2_ENDPOINT=https://<account-id>.r2.cloudflarestorage.com
R2_REGION=auto
R2_BUCKET=resell-images
R2_ACCESS_KEY_ID=<your-r2-access-key>
R2_SECRET_ACCESS_KEY=<your-r2-secret-key>
R2_PUBLIC_BASE_URL=https://images.yourdomain.com
```

### 3. resell-frontend

**Dashboard → resell-frontend → Environment**

```env
# OTOMATIK GELEN (blueprint'ten):
VITE_AUTH_SERVICE_URL=https://resell-auth-service.onrender.com/auth
VITE_API_URL=https://resell-backend.onrender.com/api
PORT=3000

# Eğer custom domain kullanıyorsan, deploy sonrası güncelle:
# VITE_AUTH_SERVICE_URL=https://auth.yourdomain.com/auth
# VITE_API_URL=https://api.yourdomain.com/api
```

---

## 🚀 Deploy Adımları

### Adım 1: Blueprint ile Başlat

```bash
cd C:\Projects\ReSell-Project
git add .
git commit -m "feat: Mikroservis mimarisi - auth-service eklendi"
git push origin main
```

Render Dashboard:
1. **New → Blueprint**
2. **Repository seç**: ReSell-Project
3. **Approve** → Tüm servisler otomatik oluşturulacak

### Adım 2: APP_SECRET Ekle

⚠️ **ÇOK ÖNEMLİ!**

1. Terminal'de secret üret:
```bash
openssl rand -base64 32
# Çıktıyı kopyala: xK9mP2vL8nQ4rT6yU1zA3cB5dE7fG9hI0jK2lM4nO6pQ8rS
```

2. Render Dashboard:
   - **resell-auth-service** → Environment → **Add Environment Variable**
     - Key: `APP_SECRET`
     - Value: `<yukarıda-ürettiğin-değer>`
     - Save

   - **resell-backend** → Environment → **Add Environment Variable**
     - Key: `APP_SECRET`
     - Value: `<AYNI-DEĞER>` ⚠️
     - Save

3. **Manual Deploy** → Her iki servisi de redeploy et

### Adım 3: R2 Storage Ekle (Backend)

**resell-backend** → Environment:

```env
R2_ENDPOINT=https://<account-id>.r2.cloudflarestorage.com
R2_REGION=auto
R2_BUCKET=resell-images
R2_ACCESS_KEY_ID=<your-key>
R2_SECRET_ACCESS_KEY=<your-secret>
R2_PUBLIC_BASE_URL=<your-public-url>
```

**Manual Deploy** → Backend'i redeploy et

### Adım 4: Test

```bash
# 1. Health check (her iki servis)
curl https://resell-auth-service.onrender.com/health
curl https://resell-backend.onrender.com/

# 2. Register test
curl -X POST https://resell-auth-service.onrender.com/auth/register \
  -H "Content-Type: application/json" \
  -d '{"email":"test@test.com","password":"123456","name":"Test User"}'

# 3. Login test  
curl -X POST https://resell-auth-service.onrender.com/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"test@test.com","password":"123456"}'

# Token'ı kopyala ve backend'de test et:
curl https://resell-backend.onrender.com/api/me \
  -H "Authorization: Bearer <TOKEN>"

# 4. Frontend
# Browser'da https://resell-frontend.onrender.com aç
# Register/Login dene
```

---

## 🔍 Troubleshooting

### ❌ JWT Token Hatası

**Sebep:** APP_SECRET farklı

**Çözüm:**
1. Her iki serviste de Environment'ı kontrol et
2. APP_SECRET'ın **TAM OLARAK AYNI** olduğundan emin ol
3. Redeploy et

### ❌ CORS Hatası

**Sebep:** Frontend farklı domain'den istek atıyor

**Çözüm:**
```env
# Development:
CORS_ALLOW_ORIGIN=*

# Production (daha güvenli):
CORS_ALLOW_ORIGIN=https://resell-frontend.onrender.com
```

### ❌ Database Connection Hatası

**Sebep:** DATABASE_URL yanlış

**Çözüm:**
- Blueprint otomatik ayarlar
- Eğer manuel ayarladıysan: Dashboard → resell-db → Internal Database URL

### ❌ R2 Upload Hatası

**Sebep:** R2 credentials yanlış

**Çözüm:**
1. Cloudflare Dashboard → R2 → Manage R2 API Tokens
2. Create API Token
3. Credentials'ı backend'e ekle
4. Redeploy

---

## 📊 Environment Variables Özet

| Variable | auth-service | backend | frontend |
|----------|-------------|---------|----------|
| APP_SECRET | ✅ (AYNI) | ✅ (AYNI) | ❌ |
| DATABASE_URL | ✅ (AYNI) | ✅ (AYNI) | ❌ |
| CORS_ALLOW_ORIGIN | ✅ | ✅ | ❌ |
| R2_* | ❌ | ✅ | ❌ |
| VITE_AUTH_SERVICE_URL | ❌ | ❌ | ✅ |
| VITE_API_URL | ❌ | ❌ | ✅ |

---

## ✅ Deployment Checklist

- [ ] Blueprint ile servisler oluşturuldu
- [ ] PostgreSQL database oluşturuldu
- [ ] APP_SECRET üretildi
- [ ] APP_SECRET her iki backend'de AYNI şekilde eklendi
- [ ] R2 credentials backend'e eklendi
- [ ] Her servis başarıyla deploy oldu
- [ ] Health check endpoint'leri çalışıyor
- [ ] Register/Login çalışıyor
- [ ] JWT token doğrulaması çalışıyor
- [ ] İlan oluşturma çalışıyor
- [ ] Görsel upload çalışıyor (R2)

---

## 🎯 Production Checklist

- [ ] Custom domain ayarlandı
- [ ] SSL/HTTPS aktif
- [ ] CORS production domain'lerine güncellendi
- [ ] APP_SECRET production-grade (32+ karakter)
- [ ] Database backup planı var
- [ ] Monitoring kuruldu
- [ ] Error tracking (Sentry vs)
- [ ] Log aggregation

