# ReSell Project - Deployment Guide

## Render.com Deployment (Recommended)

### Otomatik Deployment

1. **Render.com'a Giriş Yapın**
   - https://render.com adresine gidin
   - GitHub hesabınızla giriş yapın

2. **Blueprint Kullanarak Deploy Edin**
   - "New +" → "Blueprint" seçin
   - Repository'nizi seçin
   - `render.yaml` dosyası otomatik algılanacak
   - "Apply" butonuna tıklayın

3. **Environment Variables (Otomatik Ayarlanır)**
   - `APP_SECRET`: Otomatik generate edilir
   - `DATABASE_URL`: PostgreSQL'den otomatik gelir
   - `CORS_ALLOW_ORIGIN`: Frontend URL'i (örn: https://resell-frontend.onrender.com)
   - `VITE_API_URL`: Backend URL'i (örn: https://resell-backend.onrender.com)

### Manuel Deployment

#### Backend Service

1. **New Web Service** oluşturun:
   ```
   Name: resell-backend
   Environment: Docker
   Root Directory: backend
   Dockerfile Path: ./Dockerfile
   Plan: Free
   ```

2. **Environment Variables**:
   ```
   APP_ENV=prod
   APP_SECRET=[otomatik generate edin]
   DATABASE_URL=[PostgreSQL connection string]
   CORS_ALLOW_ORIGIN=https://resell-frontend.onrender.com
   PORT=8080
   ```

3. **PostgreSQL Database** oluşturun:
   ```
   Name: resell-db
   Database: resell
   User: resell
   Plan: Free
   ```

#### Frontend Service

1. **New Web Service** oluşturun:
   ```
   Name: resell-frontend
   Environment: Node
   Root Directory: frontend
   Build Command: npm install && npm run build
   Start Command: npm run preview -- --host 0.0.0.0 --port $PORT
   Plan: Free
   ```

2. **Environment Variables**:
   ```
   VITE_API_URL=https://resell-backend.onrender.com
   PORT=3000
   ```

## Vercel + Render (Alternative)

### Backend: Render.com
Yukarıdaki backend adımlarını takip edin.

### Frontend: Vercel

1. **Vercel'e Deploy**:
   ```bash
   cd frontend
   npm install -g vercel
   vercel --prod
   ```

2. **Environment Variables** (Vercel Dashboard):
   ```
   VITE_API_URL=https://resell-backend.onrender.com
   ```

3. **Build Settings**:
   ```
   Root Directory: frontend
   Build Command: npm run build
   Output Directory: dist
   ```

## Local Development

### Backend
```bash
cd backend
composer install
php bin/console doctrine:migrations:migrate
php -S localhost:8000 -t public
```

### Frontend
```bash
cd frontend
npm install
npm run dev
```

## Post-Deployment Checklist

- [ ] Backend health check: `https://your-backend.onrender.com/`
- [ ] Frontend açılıyor: `https://your-frontend.onrender.com/`
- [ ] API endpoints çalışıyor: `/api/auth/register`, `/api/auth/login`
- [ ] Database migrations çalıştı
- [ ] CORS ayarları doğru
- [ ] Environment variables set edildi

## Troubleshooting

### CORS Hatası
```yaml
# backend/config/packages/nelmio_cors.yaml
CORS_ALLOW_ORIGIN environment variable'ını frontend URL'iniz ile güncelleyin
```

### Database Connection Error
```bash
# Render Dashboard'da DATABASE_URL'in doğru set edildiğinden emin olun
# Migration'ları kontrol edin: php bin/console doctrine:migrations:status
```

### Build Fails
```bash
# Logs'u kontrol edin
# composer.json ve package.json'daki dependency'leri kontrol edin
```

## Monitoring

- **Render Dashboard**: Logs, metrics, ve deployment history
- **Error Tracking**: Backend logs için `var/log/prod.log`
- **Database**: PostgreSQL dashboard'dan monitoring yapabilirsiniz

## Scaling

Free tier limitasyonları:
- Backend: 512 MB RAM, Sleeps after 15 min inactivity
- Database: 1 GB storage, 97 hours/month
- Frontend: Unlimited bandwidth

Upgrade için Render.com pricing sayfasını kontrol edin.

