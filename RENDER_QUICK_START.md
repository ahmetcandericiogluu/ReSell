# 🚀 Render Quick Start - ReSell Marketplace

## Hızlı Başlangıç (5 Dakika)

### 1️⃣ Render'a Git
👉 https://render.com → Sign in with GitHub

### 2️⃣ Blueprint ile Deploy Et
1. Dashboard'da **"New +"** → **"Blueprint"** seçin
2. Repository'nizi seçin: **ReSell-Project**
3. Branch: **master**
4. **"Apply"** butonuna tıklayın

✅ **Render otomatik olarak yapacak:**
- 3 PostgreSQL database oluşturacak
- 4 web service deploy edecek
- Migration'ları çalıştıracak
- Fixture'ları yükleyecek

⏱️ İlk deployment: **10-15 dakika**

### 3️⃣ APP_SECRET'leri Senkronize Et (Önemli!)

**Auth Service ve Listing Service aynı JWT secret'i kullanmalı!**

```bash
# 1. Auth Service'in SECRET'ini kopyala
Render Dashboard → resell-auth-service → Environment
→ APP_SECRET değerini kopyala (örn: abc123xyz...)

# 2. Listing Service'e yapıştır
Render Dashboard → resell-listing-service → Environment
→ APP_SECRET'i güncelle (auth service'den kopyaladığın değer)
→ "Save Changes" → Servis otomatik redeploy olacak
```

### 4️⃣ Test Et!

Deployment bitince (yeşil ✅ işareti):

**Frontend URL'inizi açın:**
```
https://resell-frontend.onrender.com
```

**Test Kullanıcısı:**
- Email: `test@resell.com`
- Şifre: `test123`

veya yeni kullanıcı kayıt edin!

---

## 🔍 Deployment Durumu Kontrol

### Service URL'leri:
- 🔐 Auth: https://resell-auth-service.onrender.com/health
- 📋 Listing: https://resell-listing-service.onrender.com/health
- ⚙️ Backend: https://resell-backend.onrender.com/
- 🎨 Frontend: https://resell-frontend.onrender.com/

### Health Check:
```bash
# Tüm servisleri kontrol et
curl https://resell-auth-service.onrender.com/health
curl https://resell-listing-service.onrender.com/health
curl https://resell-backend.onrender.com/

# Kategoriler yüklendi mi?
curl https://resell-listing-service.onrender.com/api/categories
```

---

## ⚠️ Yaygın Sorunlar

### 1. "Build failed" hatası
**Çözüm**: Logs'u kontrol et, genellikle dependency sorunu
```bash
Dashboard → Service → Logs
```

### 2. "Database connection failed"
**Çözüm**: Database hazır olana kadar bekle (2-3 dk), sonra manuel redeploy
```bash
Dashboard → Service → Manual Deploy → Deploy latest commit
```

### 3. "401 Unauthorized" (JWT hatası)
**Çözüm**: APP_SECRET'leri senkronize et (yukarıya bak)

### 4. Frontend boş sayfa gösteriyor
**Çözüm**: Browser console'a bak, environment variables kontrol:
```bash
Dashboard → resell-frontend → Environment
→ VITE_AUTH_SERVICE_URL
→ VITE_LISTING_SERVICE_URL
→ VITE_API_URL
```

---

## 💰 Free Tier Limitleri

✅ **Her servis için:**
- 750 saat/ay ücretsiz
- 15 dakika inaktivite = sleep mode
- İlk request yavaş olabilir (cold start)

✅ **PostgreSQL:**
- 256 MB RAM
- 1 GB storage
- Yeterli test/demo için!

---

## 📚 Detaylı Bilgi

Daha fazla bilgi için: [DEPLOYMENT.md](./DEPLOYMENT.md)

---

## 🎉 Başarıyla Deploy Ettiniz!

Sorun yaşarsanız:
1. Render Dashboard → Service → Logs kontrol edin
2. DEPLOYMENT.md dosyasına bakın
3. GitHub Issues açın

**Mikroservis mimariniz artık canlıda!** 🚀

