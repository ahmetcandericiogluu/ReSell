# 🚀 ReSell Deployment Guide

## Local Development (Docker)

### 1. Docker Compose ile Başlatma

```bash
# Servisleri başlat
docker-compose up -d

# Container'a gir
docker-compose exec web bash

# Migration çalıştır
php bin/console doctrine:migrations:migrate --no-interaction

# Cache temizle
php bin/console cache:clear
```

### 2. Lokal Geliştirme (Docker olmadan)

```bash
# PostgreSQL'i başlat (lokal)
# Varsayılan: localhost:5432, db: app, user: app, password: !ChangeMe!

# Dependencies
composer install

# Migration
php bin/console doctrine:migrations:migrate

# Symfony server başlat
symfony server:start
# veya
php -S localhost:8000 -t public
```

**Uygulama çalışıyor:** http://localhost:8000

---

## 🧪 Test Demo Sayfası

Ana sayfa: http://localhost:8000

Bu sayfa üzerinden:
- ✅ Kayıt olabilirsiniz
- ✅ Giriş yapabilirsiniz
- ✅ Kullanıcı bilgilerinizi görebilirsiniz
- ✅ Çıkış yapabilirsiniz

---

## 📡 API Endpoints

### Authentication

**Kayıt Ol**
```bash
POST /api/auth/register
Content-Type: application/json

{
  "email": "test@example.com",
  "password": "123456",
  "name": "Test User",
  "phone": "05551234567",
  "city": "Istanbul"
}
```

**Giriş Yap**
```bash
POST /api/auth/login
Content-Type: application/json

{
  "email": "test@example.com",
  "password": "123456"
}
```

**Mevcut Kullanıcı**
```bash
GET /api/auth/me
Cookie: PHPSESSID=...
```

**Çıkış Yap**
```bash
POST /api/auth/logout
Cookie: PHPSESSID=...
```

---

## 🔧 Environment Variables

`.env.local` dosyası oluşturun:

```env
APP_ENV=dev
APP_SECRET=your-secret-key-here
DATABASE_URL="postgresql://app:!ChangeMe!@127.0.0.1:5432/app?serverVersion=16&charset=utf8"
```

---

## 🐳 Docker Commands

```bash
# Logları izle
docker-compose logs -f web

# Container'ı yeniden başlat
docker-compose restart web

# Tüm servisleri durdur
docker-compose down

# Volume'ları da sil (DB datası silinir!)
docker-compose down -v
```

---

## 🚀 Production Deployment (Render/Railway/Heroku)

### Gerekli Environment Variables:

```
APP_ENV=prod
APP_SECRET=<random-32-char-string>
DATABASE_URL=postgresql://user:pass@host:5432/dbname
```

### Build Commands:

```bash
composer install --no-dev --optimize-autoloader
php bin/console doctrine:migrations:migrate --no-interaction
php bin/console cache:clear --env=prod
```

### Start Command:

```bash
php -S 0.0.0.0:$PORT -t public
```

veya Nginx/Apache ile FPM kullanın.

---

## 📝 Test Scenarios

1. **Kayıt Testi**
   - Demo sayfasında "Kayıt Ol" tab'ına geç
   - Tüm alanları doldur (phone/city opsiyonel)
   - "Kayıt Ol" butonuna tıkla
   - Başarılı mesajı görmelisiniz

2. **Giriş Testi**
   - "Giriş Yap" tab'ına geç
   - Email/password gir
   - Kullanıcı bilgileriniz görünmeli

3. **Duplicate Email Testi**
   - Aynı email ile tekrar kayıt olmaya çalışın
   - "Bu e-posta adresi ile kayıtlı kullanıcı zaten mevcut" hatası almalısınız

4. **Validation Testi**
   - Şifre 6 karakterden az girin
   - Geçersiz email formatı deneyin
   - Validation hataları görmelisiniz

---

## 🎯 Next Steps

- [ ] Listing (İlan) modülü
- [ ] Category modülü
- [ ] Mesajlaşma modülü
- [ ] Review/Rating modülü
- [ ] Image upload
- [ ] Email verification
- [ ] Password reset

