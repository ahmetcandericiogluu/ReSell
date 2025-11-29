# 🚀 Production'da R2 Storage Kullanımı

## 📋 ŞU AN DURUM

Local development'ta **LocalStorageService** kullanılıyor.
Production'da (Render.com) **R2StorageService** kullanmak için değişiklik gerekiyor.

---

## ✅ ADIM ADIM DEĞİŞİKLİK

### 1. Render.com Environment Variables Kontrol

**Render.com Dashboard'a git:**
- Service seç
- "Environment" sekmesine tıkla

**Şu değişkenlerin olduğundan emin ol:**
```
R2_ENDPOINT=https://your-account-id.r2.cloudflarestorage.com
R2_REGION=auto
R2_BUCKET=resell-uploads-prod
R2_ACCESS_KEY_ID=your-access-key
R2_SECRET_ACCESS_KEY=your-secret-key
R2_PUBLIC_BASE_URL=https://pub-xxxxxxx.r2.dev
```

**Yoksa ekle!** ✅

---

### 2. config/services.yaml Değiştir

**Dosya:** `backend/config/services.yaml`

**41. satır civarı - ŞU AN:**
```yaml
App\Storage\StorageInterface:
    alias: App\Storage\LocalStorageService  # 👈 ŞU AN BU
```

**DEĞİŞTİR:**
```yaml
App\Storage\StorageInterface:
    alias: App\Storage\R2StorageService  # 👈 BUNU YAP
```

**45. satır civarı - ŞU AN:**
```yaml
App\Service\ListingImageService:
    arguments:
        $storageDriver: 'local'  # 👈 ŞU AN BU
```

**DEĞİŞTİR:**
```yaml
App\Service\ListingImageService:
    arguments:
        $storageDriver: 'r2'  # 👈 BUNU YAP
```

---

### 3. Commit ve Push

```bash
git add backend/config/services.yaml
git commit -m "chore: Production için R2 storage kullan"
git push origin master
```

---

### 4. Render.com Otomatik Deploy Olacak

- Render.com yeni commit'i görünce otomatik deploy başlatır
- Migration'lar çalışır
- Artık resimler R2'ye yüklenecek! 🎉

---

## 🔄 ALTERNATİF: Environment-Based Seçim

Daha esnek bir yaklaşım için storage'ı environment variable ile kontrol edebiliriz:

### services.yaml'ı Güncelle

```yaml
parameters:
    # Default storage driver
    storage_driver: '%env(default:default_storage_driver:STORAGE_DRIVER)%'
    default_storage_driver: 'r2'  # Production default

services:
    # ...existing services...

    # Conditional storage interface
    App\Storage\StorageInterface:
        alias: '@App\Storage\R2StorageService'  # Production default
        # Local dev .env.local'de STORAGE_DRIVER=local varsa override eder

    App\Service\ListingImageService:
        arguments:
            $storageDriver: '%storage_driver%'
```

### .env.local (Local Dev)

```bash
# .env.local - sadece local'de
STORAGE_DRIVER=local
```

### Production (Render.com)

```bash
# Environment Variables - Render dashboard'da
STORAGE_DRIVER=r2
```

**Avantaj:** Config dosyasını değiştirmeden environment variable ile kontrol edebilirsin.

---

## ⚠️ ÖNEMLİ NOTLAR

### 1. Public URL Kontrol

R2 bucket'ının **public** olduğundan emin ol:

**Cloudflare Dashboard:**
- R2 → Bucket seçimi
- Settings → Public Access
- "Allow Access" olmalı

### 2. CORS Ayarları

Eğer frontend'den direkt resim görüntülemede sorun olursa CORS ayarla:

**R2 Bucket Settings → CORS Policy:**
```json
[
  {
    "AllowedOrigins": [
      "https://your-frontend-domain.com",
      "http://localhost:3000"
    ],
    "AllowedMethods": ["GET", "HEAD"],
    "AllowedHeaders": ["*"],
    "MaxAgeSeconds": 3600
  }
]
```

### 3. Migration Durumu

`listing_images` tablosunun production'da da oluşturulduğundan emin ol:

```bash
# Render dashboard'da Shell aç
php bin/console doctrine:migrations:status
```

Eğer pending migration varsa:
```bash
php bin/console doctrine:migrations:migrate --no-interaction
```

---

## 🧪 TEST ET

Deploy sonrası:

1. Production sitene git
2. Giriş yap
3. Bir ilana resim yükle
4. Cloudflare R2 dashboard'a git
5. Bucket'ta dosyayı göreceksin! ✅

---

## 🔍 SORUN GİDERME

### "Failed to upload to R2" Hatası

**Kontrol:**
1. Environment variables doğru mu?
2. R2 bucket public mu?
3. Access key/secret doğru mu?

**Test için R2 credentials:**
```bash
# Render Shell'de
php bin/console debug:container --env-var=R2_ENDPOINT
php bin/console debug:container --env-var=R2_BUCKET
```

### "Permission denied" Hatası

R2 API Token'ının yeterli izinlere sahip olduğundan emin ol:
- Object Read
- Object Write
- Object Delete

### SSL Certificate Hatası

Production'da (Render.com) bu sorun olmaz çünkü sistem CA bundle'ı var.

Eğer hala sorun varsa, R2StorageService.php'deki SSL fix'i kaldır:
```php
// BUNU SİL (sadece dev için gerekiyordu):
if (getenv('APP_ENV') === 'dev' || ($_ENV['APP_ENV'] ?? 'prod') === 'dev') {
    $config['http'] = ['verify' => false];
}
```

---

## 📊 KARŞILAŞTIRMA

### Local Storage (Dev)
- ✅ Kurulum kolay
- ✅ Ücretsiz
- ❌ Scalable değil
- ❌ CDN yok
- 📁 Konum: `backend/public/uploads/`

### R2 Storage (Production)
- ✅ Scalable
- ✅ CDN benzeri hız
- ✅ Güvenilir
- ✅ Backup
- 💰 Çok ucuz (10GB ücretsiz)
- ☁️ Konum: Cloudflare R2 bucket

---

## 🎯 HIZLI ÖZET

**Production'da R2 kullanmak için:**

1. ✅ Render.com'da R2 environment variables var mı kontrol et
2. ✅ `config/services.yaml` dosyasını değiştir:
   - `alias: App\Storage\R2StorageService`
   - `$storageDriver: 'r2'`
3. ✅ Commit + Push
4. ✅ Render otomatik deploy eder
5. ✅ Test et!

**Geri almak için:**
- Aynı dosyayı eski haline çevir
- Commit + Push

---

## 📞 YARDIM

Sorun yaşarsan:
1. Render logs'a bak (Dashboard → Logs)
2. R2 dashboard'da bucket'ı kontrol et
3. Environment variables'ları doğrula

**Başarılar!** 🚀

