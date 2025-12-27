# ReSell Project - Proje Durumu

> Son Güncelleme: 25 Aralık 2024

## 📊 Genel Bakış

ReSell, ikinci el ürün alım-satım platformudur. Proje şu anda **monolith'ten mikroservis mimarisine geçiş** aşamasındadır.

## 🏗️ Mimari

```
┌─────────────────────────────────────────────────────────────────┐
│                         FRONTEND                                 │
│                    (React + Vite + Tailwind)                    │
│                     localhost:3000 / Render                      │
└─────────────────────────────────────────────────────────────────┘
                              │
        ┌─────────────────────┼─────────────────────┐
        ▼                     ▼                     ▼
┌───────────────┐     ┌───────────────┐     ┌───────────────┐
│  AUTH SERVICE │     │LISTING SERVICE│     │    BACKEND    │
│   (Symfony)   │     │   (Symfony)   │     │   MONOLITH    │
│  Port: 8001   │     │  Port: 8082   │     │  Port: 8000   │
└───────┬───────┘     └───────┬───────┘     └───────┬───────┘
        │                     │                     │
        │                     ▼                     │
        │             ┌───────────────┐             │
        │             │ ELASTICSEARCH │             │
        │             │ (Elastic Cloud)│            │
        │             │  Port: 9243   │             │
        │             └───────────────┘             │
        │                     │                     │
        └─────────────────────┼─────────────────────┘
                              ▼
                    ┌───────────────┐
                    │  POSTGRESQL   │
                    │   (Render)    │
                    └───────────────┘
```

## 🔧 Servisler

### 1. Auth Service (`/auth-service`)
| Özellik | Değer |
|---------|-------|
| Framework | Symfony 7.3 |
| Port (Local) | 8001 |
| Port (Prod) | Render Web Service |
| Veritabanı | PostgreSQL (auth_service) |

**Endpoints:**
- `POST /api/auth/register` - Kullanıcı kaydı
- `POST /api/auth/login` - Giriş (JWT token döner)
- `GET /api/auth/me` - Kullanıcı bilgisi

**Durum:** ✅ Production'da çalışıyor

---

### 2. Listing Service (`/services/listing`)
| Özellik | Değer |
|---------|-------|
| Framework | Symfony 7.3 |
| Port (Local) | 8082 |
| Port (Prod) | Render Web Service |
| Veritabanı | PostgreSQL (listing_service) |
| Arama Motoru | Elasticsearch (Elastic Cloud) |

**Endpoints:**
- `GET /api/listings` - Tüm ilanlar (PostgreSQL)
- `GET /api/listings/search` - Arama (Elasticsearch) ⭐ YENİ
- `GET /api/listings/{id}` - İlan detayı
- `POST /api/listings` - İlan oluştur (JWT gerekli)
- `PUT /api/listings/{id}` - İlan güncelle (JWT gerekli)
- `DELETE /api/listings/{id}` - İlan sil (JWT gerekli)
- `GET /api/listings/my-listings` - Kullanıcının ilanları (JWT gerekli)
- `GET /api/categories` - Kategoriler

**Elasticsearch Özellikleri:**
- Full-text arama (title, description)
- Kategori filtresi
- Fiyat aralığı filtresi
- Lokasyon filtresi
- Sıralama (tarih, fiyat)
- Write-through senkronizasyon

**Durum:** ✅ Production'da çalışıyor (Elasticsearch dahil)

---

### 3. Backend Monolith (`/backend`)
| Özellik | Değer |
|---------|-------|
| Framework | Symfony 7.3 |
| Port (Local) | 8000 |
| Port (Prod) | Render Web Service |
| Veritabanı | PostgreSQL (resell_db) |
| Depolama | Cloudflare R2 |

**Endpoints:**
- `POST /api/listings/{id}/images` - Resim yükle
- `DELETE /api/listings/{id}/images/{imageId}` - Resim sil
- `GET /api/users/{id}` - Kullanıcı profili
- `PUT /api/users/profile` - Profil güncelle

**Durum:** ✅ Production'da çalışıyor

---

### 4. Frontend (`/frontend`)
| Özellik | Değer |
|---------|-------|
| Framework | React 18 + Vite |
| UI | Tailwind CSS |
| Port (Local) | 3000 |
| Port (Prod) | Render Static Site |

**Sayfalar:**
- `/login` - Giriş
- `/register` - Kayıt
- `/dashboard` - Ana sayfa
- `/listings` - Tüm ilanlar (Elasticsearch arama) ⭐ YENİ
- `/listings/{id}` - İlan detayı
- `/my-listings` - Kullanıcının ilanları
- `/listings/{id}/images` - Resim yönetimi
- `/profile` - Profil sayfası
- `/create-listing` - Yeni ilan oluştur

**Durum:** ✅ Production'da çalışıyor

---

## 🗄️ Veritabanları

| Veritabanı | Servis | Tablo Sayısı |
|------------|--------|--------------|
| auth_service | Auth Service | 1 (users) |
| listing_service | Listing Service | 3 (listings, categories, listing_images) |
| resell_db | Backend Monolith | Paylaşımlı |

---

## 🔍 Elasticsearch

| Özellik | Değer |
|---------|-------|
| Provider | Elastic Cloud |
| Region | us-central1 (GCP) |
| Index | `listings_v1` |
| Endpoint | `https://xxx.us-central1.gcp.cloud.es.io:443` |

**Index Mapping:**
```json
{
  "id": "keyword",
  "seller_id": "integer",
  "category_id": "integer",
  "title": "text + keyword",
  "description": "text",
  "price": "scaled_float",
  "currency": "keyword",
  "status": "keyword",
  "location": "text + keyword",
  "images": "nested (id, url, position)",
  "created_at": "date",
  "updated_at": "date"
}
```

**Senkronizasyon:**
- PostgreSQL → Elasticsearch (write-through)
- Sadece `status=active` ve `deleted_at=NULL` kayıtlar indexlenir

---

## 🚀 Deployment

| Servis | Platform | URL |
|--------|----------|-----|
| Auth Service | Render | https://resell-auth-service.onrender.com |
| Listing Service | Render | https://resell-listing-service.onrender.com |
| Backend | Render | https://resell-backend.onrender.com |
| Frontend | Render | https://resell-frontend.onrender.com |
| Elasticsearch | Elastic Cloud | (internal) |
| PostgreSQL | Render | (internal) |
| R2 Storage | Cloudflare | (public CDN) |

---

## 📁 Proje Yapısı

```
ReSell-Project/
├── auth-service/          # JWT Authentication Microservice
├── services/
│   └── listing/           # Listing Microservice + Elasticsearch
├── backend/               # Monolith (images, profiles)
├── frontend/              # React SPA
├── docker-compose.yml     # Local development
├── render.yaml            # Render deployment blueprint
└── PROJECT_STATUS.md      # Bu dosya
```

---

## 🔐 Environment Variables

### Listing Service (Production)
```
APP_ENV=prod
APP_SECRET=<generated>
DATABASE_URL=<render_postgres>
CORS_ALLOW_ORIGIN=^https?://(localhost|.*\.onrender\.com)(:\d+)?$
ELASTICSEARCH_URL=https://elastic:PASSWORD@xxx.es.cloud.es.io:443
ELASTICSEARCH_API_KEY=<optional>
FORCE_REINDEX=<true for manual reindex>
```

---

## 📈 İstatistikler

| Metrik | Değer |
|--------|-------|
| Toplam Listing | 51 |
| Aktif Listing (Indexed) | 39 |
| Kategori Sayısı | 8 |
| Kullanıcı Sayısı | ~25 |

---

## 🛠️ Geliştirme Ortamı

### Gereksinimler
- Docker Desktop
- PHP 8.3+
- Node.js 20+
- Composer

### Başlatma
```bash
# Tüm servisleri başlat
docker-compose up -d

# Elasticsearch + Kibana
docker-compose up -d elasticsearch kibana

# Frontend (ayrı terminal)
cd frontend && npm run dev
```

### Portlar (Local)
| Servis | Port |
|--------|------|
| Frontend | 3000 |
| Backend | 8000 |
| Auth Service | 8001 |
| Listing Service | 8082 |
| Elasticsearch | 9200 |
| Kibana | 5601 |
| PostgreSQL | 5432 |

---

## ✅ Tamamlanan Özellikler

- [x] JWT Authentication (Auth Service)
- [x] Listing CRUD (Listing Service)
- [x] Kategori yönetimi
- [x] Resim yükleme (R2 Storage)
- [x] Elasticsearch entegrasyonu
- [x] Full-text arama
- [x] Filtreler (kategori, fiyat, lokasyon)
- [x] Sıralama (tarih, fiyat)
- [x] Sayfalama
- [x] Write-through sync (PostgreSQL → ES)
- [x] Otomatik reindex (deploy sırasında)
- [x] Frontend arama UI
- [x] Production deployment (Render + Elastic Cloud)

---

## 🔮 Gelecek Geliştirmeler

- [ ] Türkçe analyzer (stemming, synonyms)
- [ ] Autocomplete/suggest
- [ ] Arama sonuçlarında highlight
- [ ] Messaging Service (kullanıcılar arası mesajlaşma)
- [ ] Review Service (değerlendirmeler)
- [ ] Push notifications
- [ ] Admin panel

---

## 📞 Destek

Sorular için: [GitHub Issues](https://github.com/your-repo/issues)

