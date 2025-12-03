# Mikroservis Deployment Rehberi - Render

## 📦 Servisler

Projenin 3 ana servisi var:

1. **auth-service** → Authentication mikroservisi
2. **backend** → Monolith (Listings, Reviews, User profiles)
3. **frontend** → React uygulaması

## 🎯 Render'da Açılacak Servisler

### 1. PostgreSQL Database
- **Name**: `resell-db`
- **Plan**: Free tier
- Her iki backend servisi de bu DB'yi kullanacak

### 2. Auth Service (Web Service)
- **Name**: `resell-auth-service`
- **Root Directory**: `auth-service`
- **Runtime**: PHP
- **Build**: `composer install --no-dev --optimize-autoloader && php bin/console cache:clear --env=prod`
- **Start**: `heroku-php-apache2 public/`
- **Health Check**: `/health`

### 3. Backend Service (Web Service)
- **Name**: `resell-backend`
- **Root Directory**: `backend`
- **Runtime**: PHP
- **Build**: `composer install --no-dev --optimize-autoloader && php bin/console cache:clear --env=prod && php bin/console doctrine:migrations:migrate --no-interaction`
- **Start**: `heroku-php-apache2 public/`
- **Health Check**: `/`

### 4. Frontend (Static Site)
- **Name**: `resell-frontend`
- **Root Directory**: `frontend`
- **Build**: `npm install && npm run build`
- **Publish**: `dist`

## 🔧 Environment Variables

### auth-service
```env
APP_ENV=prod
APP_SECRET=<SAME-AS-BACKEND>
DATABASE_URL=<FROM-RENDER-DB>
CORS_ALLOW_ORIGIN=https://resell-frontend.onrender.com
```

### backend
```env
APP_ENV=prod
APP_SECRET=<SAME-AS-AUTH-SERVICE>
DATABASE_URL=<FROM-RENDER-DB>
CORS_ALLOW_ORIGIN=https://resell-frontend.onrender.com
R2_ENDPOINT=<YOUR-R2-ENDPOINT>
R2_REGION=<YOUR-R2-REGION>
R2_BUCKET=<YOUR-R2-BUCKET>
R2_ACCESS_KEY_ID=<YOUR-KEY>
R2_SECRET_ACCESS_KEY=<YOUR-SECRET>
R2_PUBLIC_BASE_URL=<YOUR-R2-PUBLIC-URL>
```

### frontend
```env
VITE_AUTH_SERVICE_URL=https://resell-auth-service.onrender.com/auth
VITE_API_URL=https://resell-backend.onrender.com/api
```

## ⚠️ KRİTİK NOTLAR

### 1. APP_SECRET
**Her iki backend servisinde de AYNI olmalı!**
- JWT token signature'ı için kullanılıyor
- auth-service token üretiyor
- backend token doğruluyor
- Farklı olursa JWT doğrulaması başarısız olur

```bash
# Güvenli secret üret:
openssl rand -base64 32
```

### 2. Database
- Her iki servis de **aynı PostgreSQL** instance'ını kullanıyor
- `users` tablosu paylaşımlı
- Migration'lar sadece backend'de çalıştırılmalı

### 3. CORS
- Production'da frontend domain'ine izin vermeli
- Development'ta `*` kullanılıyor
- `when@prod` section otomatik devreye giriyor

## 📝 Deployment Sırası

1. ✅ **PostgreSQL Database oluştur**
   - Render Dashboard → New → PostgreSQL
   - Name: `resell-db`
   - Plan seç, Create

2. ✅ **auth-service deploy et**
   - New → Web Service
   - Repository seç, Root: `auth-service`
   - Environment variables ekle
   - DATABASE_URL → PostgreSQL'den al
   - Deploy

3. ✅ **backend deploy et**
   - New → Web Service
   - Repository seç, Root: `backend`
   - Environment variables ekle
   - DATABASE_URL → Aynı PostgreSQL
   - Deploy (migration otomatik çalışacak)

4. ✅ **frontend deploy et**
   - New → Static Site
   - Repository seç, Root: `frontend`
   - Environment variables ekle
   - Deploy

## 🔄 Frontend URL Güncelleme

Frontend deploy olduktan sonra:
1. Frontend URL'ini al (örn: `https://resell-frontend.onrender.com`)
2. Her iki backend servisinde `CORS_ALLOW_ORIGIN` güncelle
3. Servisleri redeploy et (veya otomatik deploy bekle)

## 🧪 Test

```bash
# 1. Auth service test
curl https://resell-auth-service.onrender.com/health

# 2. Backend test
curl https://resell-backend.onrender.com/

# 3. Register test
curl -X POST https://resell-auth-service.onrender.com/auth/register \
  -H "Content-Type: application/json" \
  -d '{"email":"test@example.com","password":"123456","name":"Test"}'

# 4. Token ile backend test
curl https://resell-backend.onrender.com/api/me \
  -H "Authorization: Bearer <TOKEN>"
```

## 📊 Servis İletişimi

```
Frontend (Static Site)
    │
    ├──► auth-service (login/register)
    │    └──► PostgreSQL
    │
    └──► backend (listings/reviews/profile)
         └──► PostgreSQL (same DB)
         └──► Cloudflare R2 (images)
```

## 🐛 Troubleshooting

### CORS Hatası
- `CORS_ALLOW_ORIGIN` doğru mu?
- Frontend URL'i doğru mu?
- Production config aktif mi? (`APP_ENV=prod`)

### JWT Hatası
- `APP_SECRET` her iki serviste aynı mı?
- Token expire olmamış mı?
- Authorization header doğru mu? (`Bearer <token>`)

### Database Hatası
- `DATABASE_URL` doğru mu?
- Her iki serviste de aynı DB mi?
- Migration çalıştı mı?

## 💰 Maliyet

**Free Tier:**
- PostgreSQL: 1 instance (256MB)
- Web Services: 2 × 750 saat/ay (sleep after inactivity)
- Static Site: Unlimited

**Not:** Free tier servisleri 15 dakika aktivite yoksa uyur. İlk istek yavaş olabilir.

## 🚀 Sonraki Adımlar

1. Custom domain ekle
2. SSL/HTTPS otomatik (Render tarafından)
3. Monitoring ve alerting
4. Backup stratejisi
5. Staging environment

