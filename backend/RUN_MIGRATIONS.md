# 🗄️ Production'da Migration Çalıştırma

## Render.com

### Method 1: Web Dashboard
1. Render Dashboard'a git
2. Web Service'ini seç (resell)
3. **Shell** tab'ına tıkla
4. Komutları çalıştır:

```bash
cd /app
php bin/console doctrine:migrations:migrate --no-interaction
```

### Method 2: Render CLI
```bash
# Render CLI kur (eğer yoksa)
brew install render  # Mac
# veya https://render.com/docs/cli

# Login
render login

# Shell'e bağlan
render shell resell

# Migration çalıştır
php bin/console doctrine:migrations:migrate --no-interaction
```

---

## Railway.app

### Railway Dashboard
1. Railway Dashboard → Project
2. PostgreSQL servisine tıkla
3. Web service'e tıkla
4. **Deployments** tab → Son deployment'i seç
5. **View Logs** → üstte "⋮" → **Shell**
6. Komut çalıştır:

```bash
php bin/console doctrine:migrations:migrate --no-interaction
```

### Railway CLI
```bash
# Railway CLI kur
npm i -g @railway/cli

# Login
railway login

# Project'e bağlan
railway link

# Shell aç
railway run bash

# Migration
php bin/console doctrine:migrations:migrate --no-interaction
```

---

## Heroku

```bash
# Heroku CLI ile
heroku run php bin/console doctrine:migrations:migrate --no-interaction -a resell-app

# Veya interactive shell
heroku run bash -a resell-app
php bin/console doctrine:migrations:migrate --no-interaction
```

---

## Docker (VPS/Cloud Server)

```bash
# SSH ile server'a bağlan
ssh user@your-server-ip

# Container'a gir
docker-compose exec web bash

# Migration çalıştır
php bin/console doctrine:migrations:migrate --no-interaction

# Veya tek satırda
docker-compose exec web php bin/console doctrine:migrations:migrate --no-interaction
```

---

## 🔍 Migration Durumunu Kontrol Et

```bash
# Migration listesi
php bin/console doctrine:migrations:list

# Migration durumu
php bin/console doctrine:migrations:status

# Bekleyen migration'lar
php bin/console doctrine:migrations:list --no-interaction
```

---

## ⚠️ Sorun Giderme

### Database bağlantı hatası
```bash
# DATABASE_URL'i kontrol et
echo $DATABASE_URL

# Test sorgusu çalıştır
php bin/console doctrine:query:sql "SELECT 1"
```

### Migration zaten çalışmış gibi görünüyor ama tablo yok
```bash
# Migration history tablosunu kontrol et
php bin/console doctrine:query:sql "SELECT version FROM doctrine_migration_versions"

# Tüm tabloları listele
php bin/console doctrine:query:sql "SELECT table_name FROM information_schema.tables WHERE table_schema='public'"
```

### Migration'ı sıfırdan çalıştır
```bash
# Dikkat: Bu sadece development'ta yapılmalı!
# Production'da ASLA bu komutu çalıştırma (veri kaybı!)

# Migration history'yi temizle
php bin/console doctrine:query:sql "DELETE FROM doctrine_migration_versions"

# Migration'ı tekrar çalıştır
php bin/console doctrine:migrations:migrate --no-interaction
```

---

## 🎯 Hızlı Çözüm (Her Platform İçin)

1. **Platform dashboard'una git**
2. **Shell/Console/Terminal bul**
3. **Komutu çalıştır:**
   ```bash
   php bin/console doctrine:migrations:migrate --no-interaction
   ```
4. **Kontrol et:**
   ```bash
   php bin/console doctrine:query:sql "SELECT * FROM users LIMIT 1"
   ```

---

## 📝 Otomatik Migration İçin

`docker-entrypoint.sh` zaten migration'ı otomatik çalıştırıyor:

```bash
php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration
```

Eğer çalışmadıysa:
- Build log'larını kontrol et
- DATABASE_URL doğru mu kontrol et
- Container restart et

