# ReSell Project

İkinci el ürün alım-satım platformu. Symfony (Backend) + React (Frontend) ile geliştirilmiş full-stack web uygulaması.

## 🚀 Hızlı Başlangıç

### Backend (Symfony)

```bash
cd backend
composer install
php bin/console doctrine:migrations:migrate
php -S localhost:8000 -t public
```

Backend: http://localhost:8000

### Frontend (React + Vite)

```bash
cd frontend
npm install
npm run dev
```

Frontend: http://localhost:3000

## 📦 Teknoloji Stack

### Backend
- PHP 8.3
- Symfony 7.2
- Doctrine ORM
- PostgreSQL
- JWT Authentication (Session-based)

### Frontend
- React 19
- Vite
- Axios
- React Router
- Context API

## 🌐 API Endpoints

### Authentication
- `POST /api/auth/register` - Yeni kullanıcı kaydı
- `POST /api/auth/login` - Giriş yapma
- `POST /api/auth/logout` - Çıkış yapma
- `GET /api/auth/me` - Kullanıcı bilgisi

## 🛠️ Geliştirme

### Database Migration
```bash
cd backend
php bin/console make:migration
php bin/console doctrine:migrations:migrate
```

### Yeni Entity Oluşturma
```bash
cd backend
php bin/console make:entity
```

### Cache Temizleme
```bash
cd backend
php bin/console cache:clear
```

## 📚 Deployment

Deployment için detaylı bilgi: [DEPLOYMENT_GUIDE.md](./DEPLOYMENT_GUIDE.md)

### Render.com (Önerilen)
1. Repository'yi GitHub'a push edin
2. Render.com'a gidin
3. "New +" → "Blueprint" seçin
4. `render.yaml` otomatik algılanacak
5. "Apply" butonuna tıklayın

### Environment Variables

**Backend:**
```
APP_ENV=prod
APP_SECRET=[generate]
DATABASE_URL=[PostgreSQL]
CORS_ALLOW_ORIGIN=https://your-frontend.onrender.com
```

**Frontend:**
```
VITE_API_URL=https://your-backend.onrender.com
```

## 📁 Proje Yapısı

```
ReSell-Project/
├── backend/                 # Symfony API
│   ├── src/
│   │   ├── Controller/     # API Controllers
│   │   ├── Entity/         # Database Entities
│   │   ├── Repository/     # Database Repositories
│   │   ├── Service/        # Business Logic
│   │   └── DTO/            # Data Transfer Objects
│   ├── config/             # Symfony Config
│   ├── migrations/         # Database Migrations
│   └── public/             # Public Directory
│
├── frontend/               # React App
│   ├── src/
│   │   ├── components/    # React Components
│   │   ├── pages/         # Page Components
│   │   ├── context/       # Context API
│   │   └── api/           # API Services
│   └── public/            # Static Assets
│
├── render.yaml            # Render Deployment Config
└── DEPLOYMENT_GUIDE.md    # Deployment Documentation
```

## 🔐 Güvenlik

- CORS koruması
- CSRF koruması
- Session-based authentication
- Password hashing (bcrypt)
- SQL injection koruması (Doctrine ORM)

## 📝 Lisans

MIT

## 👥 Katkıda Bulunma

1. Fork edin
2. Feature branch oluşturun (`git checkout -b feature/AmazingFeature`)
3. Commit edin (`git commit -m 'Add some AmazingFeature'`)
4. Push edin (`git push origin feature/AmazingFeature`)
5. Pull Request açın

## 📧 İletişim

Proje Link: [https://github.com/yourusername/ReSell-Project](https://github.com/yourusername/ReSell-Project)
