# 📸 Resim Yükleme Sistemi - Kurulum Tamamlandı

## ✅ YAPILAN İŞLEMLER

### 1. Storage Mimarisi
- ✅ `StorageInterface` oluşturuldu
- ✅ `LocalStorageService` implementasyonu (local disk storage)
- ✅ `R2StorageService` implementasyonu (Cloudflare R2/S3)

### 2. Domain Model
- ✅ `ListingImage` entity oluşturuldu
- ✅ Migration çalıştırıldı (listing_images tablosu oluşturuldu)
- ✅ Repository oluşturuldu

### 3. Business Logic
- ✅ `ListingImageService` oluşturuldu
- ✅ File validation (max 5MB, sadece jpeg/png/webp)
- ✅ Upload ve delete fonksiyonları

### 4. API Endpoints
- ✅ `POST /api/listings/{id}/images` - Resim yükleme
- ✅ `DELETE /api/listings/{listingId}/images/{imageId}` - Resim silme
- ✅ Authorization kontrolü (sadece ilan sahibi)

### 5. Dependencies
- ✅ AWS SDK PHP (^3.363) yüklendi
- ✅ Services.yaml konfigürasyonu yapıldı

---

## 🔧 ENVIRONMENT VARIABLES (YERLEŞTİRMEN GEREKEN)

Backend klasöründeki `.env` dosyasına ekle:

```bash
###> R2 / S3 STORAGE ###
R2_ENDPOINT=
R2_REGION=auto
R2_BUCKET=
R2_ACCESS_KEY_ID=
R2_SECRET_ACCESS_KEY=
R2_PUBLIC_BASE_URL=
###< R2 / S3 STORAGE ###
```

### Local Development (Şu an aktif)
Local geliştirme için yukarıdaki değerleri **BOŞ BIRAK**. 
Resimler `public/uploads/` klasörüne kaydedilecek.

### Production (R2 kullanmak için)

1. Cloudflare R2'den aşağıdaki bilgileri al:
```bash
R2_ENDPOINT=https://your-account-id.r2.cloudflarestorage.com
R2_REGION=auto
R2_BUCKET=resell-images
R2_ACCESS_KEY_ID=your-access-key-here
R2_SECRET_ACCESS_KEY=your-secret-key-here
R2_PUBLIC_BASE_URL=https://images.yourdomain.com
```

2. `backend/config/services.yaml` dosyasında şu satırları değiştir:
```yaml
# Bu satırı:
App\Storage\StorageInterface:
    alias: App\Storage\LocalStorageService

# Şuna çevir:
App\Storage\StorageInterface:
    alias: App\Storage\R2StorageService

# Ve:
App\Service\ListingImageService:
    arguments:
        $storageDriver: 'r2'  # 'local' yerine 'r2'
```

---

## 🚀 TEST İÇİN ÖRNEK CURL KOMUTLARI

### 1. Resim Yükleme

```bash
curl -X POST http://localhost:8000/api/listings/1/images \
  -H "Cookie: PHPSESSID=your-session-id" \
  -F "images[]=@/path/to/image1.jpg" \
  -F "images[]=@/path/to/image2.jpg"
```

**Başarılı Response (201 Created):**
```json
[
  {
    "id": 1,
    "url": "/uploads/listings/1/673abcd123456.jpg",
    "path": "listings/1/673abcd123456.jpg",
    "position": 1,
    "storage_driver": "local",
    "created_at": "2025-11-29 14:30:00"
  },
  {
    "id": 2,
    "url": "/uploads/listings/1/673abcd789012.jpg",
    "path": "listings/1/673abcd789012.jpg",
    "position": 2,
    "storage_driver": "local",
    "created_at": "2025-11-29 14:30:01"
  }
]
```

**Hata Response (403 Forbidden):**
```json
{
  "error": "You are not authorized to upload images for this listing"
}
```

**Hata Response (400 Bad Request):**
```json
{
  "error": "File size exceeds maximum allowed size of 5242880 bytes"
}
```
veya
```json
{
  "error": "Invalid file type. Allowed types: image/jpeg, image/png, image/webp"
}
```

### 2. Resim Silme

```bash
curl -X DELETE http://localhost:8000/api/listings/1/images/1 \
  -H "Cookie: PHPSESSID=your-session-id"
```

**Başarılı Response (200 OK):**
```json
{
  "status": "ok"
}
```

**Hata Response (404 Not Found):**
```json
{
  "error": "Image not found"
}
```

---

## 📋 RENDER DEPLOY İÇİN ENVIRONMENT VARIABLES

Render.com dashboard'da Environment sekmesine şunları ekle:

| Key | Value |
|-----|-------|
| `R2_ENDPOINT` | `https://your-account-id.r2.cloudflarestorage.com` |
| `R2_REGION` | `auto` |
| `R2_BUCKET` | `resell-images` |
| `R2_ACCESS_KEY_ID` | `your-r2-access-key-id` |
| `R2_SECRET_ACCESS_KEY` | `your-r2-secret-access-key` |
| `R2_PUBLIC_BASE_URL` | `https://images.yourdomain.com` |

Sonra `config/services.yaml` dosyasını yukarıda belirtildiği gibi değiştir ve deploy et.

---

## 📁 OLUŞTURULAN DOSYALAR

### Storage Layer
- `backend/src/Storage/StorageInterface.php`
- `backend/src/Storage/LocalStorageService.php`
- `backend/src/Storage/R2StorageService.php`

### Domain Layer
- `backend/src/Entity/ListingImage.php`
- `backend/src/Repository/ListingImageRepository.php`
- `backend/migrations/Version20251129142952.php`

### Application Layer
- `backend/src/Service/ListingImageService.php`
- `backend/src/Controller/ListingController.php` (güncellendi)

### Configuration
- `backend/config/services.yaml` (güncellendi)
- `backend/composer.json` (güncellendi - AWS SDK eklendi)

### Documentation
- `backend/ENV_TEMPLATE.md`
- `backend/STORAGE_SETUP.md` (bu dosya)

---

## ✅ SİSTEM HAZIR!

Resim yükleme sistemi tamamen kuruldu ve çalışır durumda. 
Local development için hemen kullanabilirsin.
Production'da R2 kullanmak için yukarıdaki adımları takip et.

