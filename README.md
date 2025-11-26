# 🛍️ ReSell - İkinci El Pazar Yeri

Kullanıcıların ikinci el eşyaları satabildiği ve satın alabileceği modern bir marketplace platformu.

## 📁 Proje Yapısı (Monorepo)

```
ReSell-Project/
├── backend/        # Symfony 7.3 API
└── frontend/       # React + Vite SPA
```

## 🚀 Hızlı Başlangıç

### Backend (Symfony)

```bash
cd backend
composer install
php bin/console doctrine:migrations:migrate
php -S localhost:8000 -t public
```

**API Base URL:** http://localhost:8000/api

### Frontend (React)

```bash
cd frontend
npm install
npm run dev
```

**Frontend URL:** http://localhost:3000

---

## 📡 API Endpoints

### Authentication

- `POST /api/auth/register` - Yeni kullanıcı kaydı
- `POST /api/auth/login` - Kullanıcı girişi
- `POST /api/auth/logout` - Çıkış yap
- `GET /api/auth/me` - Mevcut kullanıcı bilgisi

---

## 🛠️ Tech Stack

### Backend
- PHP 8.3
- Symfony 7.3
- PostgreSQL 16
- Doctrine ORM
- Session-based Authentication

### Frontend
- React 18
- Vite
- React Router
- Axios
- Modern CSS

---

## 🗄️ Database

PostgreSQL 16+ gerekli.

**Lokal ayarlar (.env.local):**
```env
DATABASE_URL="postgresql://app:!ChangeMe!@127.0.0.1:5432/app?serverVersion=16&charset=utf8"
```

---

## 🔐 Güvenlik

- CORS yapılandırması aktif
- Session-based authentication
- Password hashing (bcrypt)
- CSRF protection
- Input validation

---

## 📦 Deployment

### Backend (Render.com)
```bash
cd backend
git push origin master
```

### Frontend (Vercel/Netlify)
```bash
cd frontend
npm run build
```

Detaylı deployment rehberi için `backend/DEPLOYMENT.md` dosyasına bakın.

---

## 🎯 Modüller

### ✅ Tamamlandı
- Authentication (Register, Login, Logout)
- User Management
- Session Management

### 🚧 Geliştirme Aşamasında
- Listing (İlan Yönetimi)
- Categories
- Messaging
- Reviews & Ratings
- Image Upload

---

## 🤝 Katkıda Bulunma

1. Fork edin
2. Feature branch oluşturun (`git checkout -b feature/amazing-feature`)
3. Commit edin (`git commit -m 'Add amazing feature'`)
4. Push edin (`git push origin feature/amazing-feature`)
5. Pull Request açın

---

## 📄 Lisans

Bu proje MIT lisansı altında lisanslanmıştır.

---

## 👨‍💻 Geliştirici

Ahmet Can Dericioğlu

