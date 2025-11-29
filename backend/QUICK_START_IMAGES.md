# 🚀 HIZLI BAŞLANGIÇ - Resim Yükleme

## ✅ HER ŞEY HAZIR!

Resim yükleme sistemi kurulumu tamamlandı. Şu an **local storage** modunda çalışıyor.

---

## 📝 YAPMAN GEREKEN (Local Development)

### HİÇBİR ŞEY! ✨

Sistem şu an çalışır durumda:
- Resimler `backend/public/uploads/` klasörüne kaydediliyor
- Dosyalar `/uploads/listings/{id}/filename.jpg` URL'sinden erişilebilir
- Environment variables ayarlamana gerek yok

---

## 🧪 HEMEN TEST ET

### 1. Backend'i başlat
```bash
cd backend
symfony server:start
# veya
php -S localhost:8000 -t public
```

### 2. Postman veya curl ile test et

**Resim Yükle:**
```bash
curl -X POST http://localhost:8000/api/listings/1/images \
  -H "Cookie: PHPSESSID=your-session-id" \
  -F "images[]=@/path/to/test-image.jpg"
```

**Resim Sil:**
```bash
curl -X DELETE http://localhost:8000/api/listings/1/images/1 \
  -H "Cookie: PHPSESSID=your-session-id"
```

---

## 🔧 CLOUDFLARE R2 KULLANMAK İSTERSEN

### 1. `.env` dosyasını aç ve ekle:

```bash
###> R2 / S3 STORAGE ###
R2_ENDPOINT=https://your-account-id.r2.cloudflarestorage.com
R2_REGION=auto
R2_BUCKET=your-bucket-name
R2_ACCESS_KEY_ID=your-access-key
R2_SECRET_ACCESS_KEY=your-secret-key
R2_PUBLIC_BASE_URL=https://images.yourdomain.com
###< R2 / S3 STORAGE ###
```

### 2. `config/services.yaml` dosyasında değiştir:

**41. satır civarı - Interface alias'ı:**
```yaml
# ÖNCEKİ:
App\Storage\StorageInterface:
    alias: App\Storage\LocalStorageService

# YENİ:
App\Storage\StorageInterface:
    alias: App\Storage\R2StorageService
```

**45. satır civarı - Storage driver:**
```yaml
# ÖNCEKİ:
App\Service\ListingImageService:
    arguments:
        $storageDriver: 'local'

# YENİ:
App\Service\ListingImageService:
    arguments:
        $storageDriver: 'r2'
```

### 3. Cache temizle ve test et:
```bash
php bin/console cache:clear
```

---

## 📋 API ENDPOINTS

### Upload Images
- **URL:** `POST /api/listings/{id}/images`
- **Auth:** Required (ilan sahibi olmalı)
- **Body:** `multipart/form-data`
- **Field:** `images[]` (array)
- **Validation:**
  - Max file size: 5MB
  - Allowed types: jpeg, png, webp

### Delete Image
- **URL:** `DELETE /api/listings/{listingId}/images/{imageId}`
- **Auth:** Required (ilan sahibi olmalı)

---

## 🐛 SORUN YAŞARSAN

### "Directory not writable" hatası:
```bash
chmod -R 755 backend/public/uploads
```

### "Storage driver not found" hatası:
- `composer install` çalıştır
- Cache temizle: `php bin/console cache:clear`

### R2 connection hatası:
- ENV değişkenlerini kontrol et
- R2 bucket'ın public olduğundan emin ol
- CORS ayarlarını kontrol et

---

## 📚 Detaylı Dokümantasyon

- `STORAGE_SETUP.md` - Tam kurulum detayları
- `ENV_TEMPLATE.md` - Environment variables açıklaması
- `.env.r2.template` - R2 konfigürasyon template'i

