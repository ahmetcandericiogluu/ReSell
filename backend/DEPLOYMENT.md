# 🚀 ReSell Deployment Guide

## Deploy Platformları

### Option 1: Render.com (Önerilen)

#### Adımlar:

1. **GitHub'a Push**
```bash
git add .
git commit -m "Add authentication module"
git push origin main
```

2. **Render.com'a Git**
   - https://render.com adresine git
   - "New +" → "Web Service" seç
   - GitHub repo'nu bağla

3. **Ayarlar:**
   - **Name:** resell
   - **Runtime:** Docker
   - **Branch:** main
   - **Instance Type:** Free

4. **Environment Variables:**
```
APP_ENV=prod
APP_SECRET=<32-karakter-random-string>
DATABASE_URL=postgresql://user:pass@host:5432/dbname
```

5. **PostgreSQL Database Ekle:**
   - Render Dashboard → "New +" → "PostgreSQL"
   - **Name:** resell-db
   - **Database:** resell_db
   - **User:** resell_user
   - **Plan:** Free
   
6. **DATABASE_URL'i Güncelle:**
   - PostgreSQL'in "Internal Connection String"ini kopyala
   - Web Service'te DATABASE_URL olarak ekle

7. **Deploy!**
   - "Create Web Service" butonuna tıkla
   - Otomatik build ve deploy başlar

---

### Option 2: Railway.app

#### Adımlar:

1. **Railway'e Git**
   - https://railway.app adresine git
   - "Start a New Project" → "Deploy from GitHub repo"

2. **PostgreSQL Ekle:**
   - "New" → "Database" → "Add PostgreSQL"

3. **Environment Variables:**
```
APP_ENV=prod
APP_SECRET=<random-string>
DATABASE_URL=${{Postgres.DATABASE_URL}}
PORT=${{PORT}}
```

4. **Build Settings:**
   - **Build Command:** `bash build.sh`
   - **Start Command:** `php -S 0.0.0.0:$PORT -t public`

5. **Deploy!**
   - Otomatik deploy başlar

---

### Option 3: Heroku

#### Adımlar:

1. **Heroku CLI Kur**
```bash
# Windows
winget install Heroku.HerokuCLI
```

2. **Heroku'ya Login**
```bash
heroku login
```

3. **Uygulama Oluştur**
```bash
heroku create resell-app
```

4. **PostgreSQL Ekle**
```bash
heroku addons:create heroku-postgresql:essential-0
```

5. **Environment Variables**
```bash
heroku config:set APP_ENV=prod
heroku config:set APP_SECRET=$(openssl rand -hex 32)
```

6. **Deploy**
```bash
git push heroku main
```

7. **Migration Çalıştır**
```bash
heroku run php bin/console doctrine:migrations:migrate --no-interaction
```

---

### Option 4: Docker Compose (VPS/Cloud Server)

#### Gereksinimler:
- Ubuntu/Debian server
- Docker ve Docker Compose kurulu

#### Adımlar:

1. **Server'a Bağlan**
```bash
ssh user@your-server-ip
```

2. **Projeyi Clone'la**
```bash
git clone https://github.com/yourusername/resell.git
cd resell
```

3. **Environment Dosyası Oluştur**
```bash
cp .env.example .env.local
nano .env.local
```

```env
APP_ENV=prod
APP_SECRET=your-secret-key-here
DATABASE_URL="postgresql://app:!ChangeMe!@database:5432/app?serverVersion=16&charset=utf8"
```

4. **Docker Compose Başlat**
```bash
docker-compose up -d
```

5. **Migration Çalıştır**
```bash
docker-compose exec web php bin/console doctrine:migrations:migrate --no-interaction
```

6. **Nginx Reverse Proxy (Opsiyonel)**
```nginx
server {
    listen 80;
    server_name yourdomain.com;

    location / {
        proxy_pass http://localhost:8000;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
    }
}
```

---

## 🔒 Production Checklist

- [ ] `APP_ENV=prod` set edildi
- [ ] `APP_SECRET` güçlü random string
- [ ] DATABASE_URL production database'e işaret ediyor
- [ ] Migration'lar çalıştırıldı
- [ ] Cache production için optimize edildi
- [ ] HTTPS aktif (SSL sertifikası)
- [ ] Error reporting kapalı
- [ ] Session güvenliği yapılandırıldı
- [ ] CORS ayarları yapıldı (gerekiyorsa)

---

## 🧪 Production Test

Deploy sonrası test et:

```bash
# Health check
curl https://your-app.com/

# Register test
curl -X POST https://your-app.com/api/auth/register \
  -H "Content-Type: application/json" \
  -d '{"email":"test@example.com","password":"test123","name":"Test User"}'

# Login test
curl -X POST https://your-app.com/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"test@example.com","password":"test123"}'
```

---

## 📊 Monitoring

### Logs İzleme:

**Render:**
```
Dashboard → Your Service → Logs
```

**Railway:**
```
Project → Service → Deployments → View Logs
```

**Heroku:**
```bash
heroku logs --tail
```

**Docker:**
```bash
docker-compose logs -f web
```

---

## 🔄 Güncellemeler

### Git ile Güncelleme:

```bash
git add .
git commit -m "Update feature"
git push origin main
```

Render/Railway otomatik deploy başlatır.

### Manuel Deploy (Heroku):

```bash
git push heroku main
```

### Docker Güncelleme:

```bash
git pull
docker-compose down
docker-compose up -d --build
docker-compose exec web php bin/console doctrine:migrations:migrate --no-interaction
```

---

## 💡 İpuçları

1. **Free Plan Limitler:**
   - Render: 750 saat/ay, auto-sleep after 15 min inactivity
   - Railway: $5 credit/ay
   - Heroku: Eco dyno $5/ay

2. **Database Backup:**
   - Düzenli backup alın
   - Migration'ları versiyon kontrolünde tutun

3. **Environment Variables:**
   - Production secret'ları asla Git'e commit etmeyin
   - Her platformda ayrı secret kullanın

4. **Performance:**
   - OPCache aktif edin (production)
   - Database connection pooling kullanın
   - CDN kullanın (static assets için)

---

## 🆘 Sorun Giderme

### Database Bağlantı Hatası:
```bash
# Connection string'i kontrol et
php bin/console doctrine:query:sql "SELECT 1"
```

### Migration Hatası:
```bash
# Migration durumunu kontrol et
php bin/console doctrine:migrations:status

# Tekrar dene
php bin/console doctrine:migrations:migrate --no-interaction
```

### Cache Sorunu:
```bash
# Cache'i temizle
php bin/console cache:clear --env=prod
php bin/console cache:warmup --env=prod
```

### 500 Error:
```bash
# Log'ları kontrol et
tail -f var/log/prod.log
```

