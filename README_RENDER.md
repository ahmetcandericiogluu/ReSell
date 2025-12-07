# 🚀 ReSell Marketplace - Render Deployment

Modern mikroservis mimarisi ile geliştirilmiş second-hand marketplace platformu.

## 🏗️ Mimari

```
┌─────────────────────────────────────────────────────────────┐
│                        Frontend (React)                      │
│                  https://resell-frontend.onrender.com        │
└───────────┬──────────────────┬──────────────────┬───────────┘
            │                  │                  │
            ▼                  ▼                  ▼
┌───────────────────┐ ┌──────────────────┐ ┌──────────────────┐
│  Auth Service     │ │ Listing Service  │ │ Backend Monolith │
│  (Symfony 7.3)    │ │  (Symfony 7.3)   │ │  (Symfony 7.2)   │
│  JWT Auth         │ │  İlan Yönetimi   │ │  Kullanıcılar    │
├───────────────────┤ ├──────────────────┤ ├──────────────────┤
│  PostgreSQL 16    │ │  PostgreSQL 16   │ │  PostgreSQL 16   │
│  auth-db          │ │  listing-db      │ │  backend-db      │
└───────────────────┘ └──────────────────┘ └──────────────────┘
```

## ✨ Özellikler

- 🔐 **JWT Authentication** - Güvenli kullanıcı yönetimi
- 📋 **Listing Service** - Mikroservis olarak ilan yönetimi
- 🏷️ **Kategori Sistemi** - Fixture'larla otomatik yüklenen kategoriler
- 🎨 **Modern UI** - React + Vite
- 🐳 **Docker Ready** - Her servis için Dockerfile
- 🚀 **Production Ready** - Render Blueprint ile tek tıkla deploy

## 🎯 Hızlı Başlangıç

### Local Development

```bash
# Tüm servisleri Docker ile başlat
docker-compose up -d

# Frontend'i başlat
cd frontend && npm install && npm run dev
```

**Servisler:**
- Frontend: http://localhost:5173
- Auth Service: http://localhost:8001
- Listing Service: http://localhost:8082
- Backend: http://localhost:8000

### Render'a Deploy

**Tek komut ile deploy:**

1. [Render Dashboard](https://dashboard.render.com) → **New Blueprint**
2. Repository seçin → **Apply**
3. 10-15 dakika bekleyin ☕
4. **APP_SECRET'leri senkronize edin** ([Detaylar](./RENDER_QUICK_START.md))

**Daha fazla bilgi:**
- 📖 [Hızlı Başlangıç](./RENDER_QUICK_START.md) - 5 dakikada deploy
- 📚 [Detaylı Deployment](./DEPLOYMENT.md) - Sorun giderme + manuel setup

## 🗂️ Proje Yapısı

```
ReSell-Project/
├── auth-service/          # JWT Authentication mikroservis
│   ├── src/
│   ├── config/
│   └── Dockerfile.render
├── services/
│   └── listing/           # Listing mikroservis
│       ├── src/
│       ├── config/
│       └── Dockerfile.render
├── backend/               # Ana backend (monolith)
│   ├── src/
│   └── Dockerfile.render
├── frontend/              # React frontend
│   └── src/
├── render.yaml           # Render Blueprint
└── docker-compose.yml    # Local development
```

## 🛠️ Teknolojiler

**Backend:**
- Symfony 7.3 (Auth + Listing)
- Symfony 7.2 (Backend)
- PostgreSQL 16
- JWT (firebase/php-jwt)
- Doctrine ORM

**Frontend:**
- React 18
- Vite
- Axios
- React Router

**DevOps:**
- Docker
- Render (Cloud Platform)
- GitHub Actions (CI/CD hazır)

## 📝 API Endpoints

### Auth Service
```
POST   /api/auth/register    # Kayıt ol
POST   /api/auth/login       # Giriş yap
GET    /api/auth/me          # Kullanıcı bilgileri
GET    /health               # Health check
```

### Listing Service
```
GET    /api/listings         # Tüm ilanlar (public)
POST   /api/listings         # Yeni ilan (auth)
GET    /api/listings/me      # Kullanıcının ilanları (auth)
GET    /api/listings/{id}    # İlan detay
PUT    /api/listings/{id}    # İlan güncelle (auth)
DELETE /api/listings/{id}    # İlan sil (auth)
GET    /api/categories       # Kategoriler
```

## 🔐 Environment Variables

### Production (Render)
Render otomatik ayarlıyor! Sadece:
- `APP_SECRET` - Auth ve Listing servislerde aynı olmalı

### Local Development
```bash
# .env dosyaları zaten hazır!
cp backend/.env.example backend/.env.local
cp auth-service/.env.example auth-service/.env.local
```

## 🧪 Test Kullanıcısı

Production'da otomatik fixture ile:
```
Email: test@resell.com
Şifre: test123
```

## 📊 Database Migrations

**Otomatik:** Render deployment sırasında çalışır.

**Manuel:**
```bash
# Auth Service
cd auth-service
php bin/console doctrine:migrations:migrate

# Listing Service (+ fixtures)
cd services/listing
php bin/console doctrine:migrations:migrate
php bin/console doctrine:fixtures:load --append

# Backend
cd backend
php bin/console doctrine:migrations:migrate
```

## 🤝 Katkıda Bulunma

1. Fork yapın
2. Feature branch oluşturun (`git checkout -b feature/amazing`)
3. Commit edin (`git commit -m 'Add amazing feature'`)
4. Push edin (`git push origin feature/amazing`)
5. Pull Request açın

## 📄 Lisans

MIT License

## 👨‍💻 Geliştirici

Mikroservis mimarisi ile geliştirilmiş modern marketplace platformu.

---

**Deployment Soruları:** [DEPLOYMENT.md](./DEPLOYMENT.md)  
**Hızlı Başlangıç:** [RENDER_QUICK_START.md](./RENDER_QUICK_START.md)  
**Listing Service:** [LISTING_SERVICE_SETUP.md](./LISTING_SERVICE_SETUP.md)

