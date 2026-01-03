# 🚀 Production Deployment Checklist - Image Upload Migration

## ✅ Hazırlık (Local'de Test Edildi)

- [x] Listing Service'e image upload endpoint'leri eklendi
- [x] AWS SDK ve Symfony Mime component yüklendi
- [x] R2 Storage service eklendi
- [x] Frontend listing-service'e yönlendirildi
- [x] Backend'ten gereksiz image kodları temizlendi
- [x] Docker-compose'da R2 env variables eklendi

---

## 📦 Deploy Öncesi Kontroller

### 1. Git Push
```bash
git add .
git commit -m "feat: Migrate image upload to listing-service"
git push origin main
```

### 2. Render'da Environment Variables Ekle

#### `resell-listing-service` için:

```
R2_ENDPOINT = https://56751b8361542d95b3c6aa19e5807694.r2.cloudflarestorage.com
R2_REGION = auto
R2_BUCKET = resell-uploads-dev
R2_ACCESS_KEY_ID = ad8fbcac1bc20e2be3f659d0678078ab
R2_SECRET_ACCESS_KEY = 054281a76b2b91d8ef736a332460b44932443b21b09c629019820141cff48c76
R2_PUBLIC_BASE_URL = https://pub-f04ef83f9e1f46f2a18b4f27db086b8e.r2.dev
```

**ÖNEMLİ:** Bu değerleri Render Dashboard'dan ekle (manuel olarak)

---

## 🎯 Deployment Sonrası Test

### 1. Listing Oluştur
```
1. Frontend'e git: https://resell-frontend.onrender.com
2. Login ol
3. "İlan Ver" butonuna tıkla
4. Yeni bir listing oluştur
```

### 2. Resim Yükle
```
1. Oluşturduğun listing'i aç
2. "Resim Ekle" butonuna tıkla
3. Bir veya birden fazla resim seç (JPEG/PNG/WebP)
4. Upload et
```

### 3. Kontroller
- ✅ Resimler listing-service'e yüklendi mi?
- ✅ R2'ye kaydedildi mi? (R2 dashboard'da kontrol et)
- ✅ Resim URL'leri doğru mu?
- ✅ Elasticsearch'te resim bilgileri var mı?
- ✅ Frontend'te resimler görünüyor mu?

### 4. Resim Silme Testi
```
1. Bir listing'in resim yönetim sayfasına git
2. Bir resmi sil
3. R2'den de silindiğini kontrol et
```

---

## 🔍 Log Kontrolleri

### Listing Service Logs:
```
- Resim upload başarılı: "Uploaded image to R2"
- R2 error: "Failed to upload file to R2"
- Mime type error: "Mime component is not installed" (olmamalı)
```

### Backend Logs:
```
- Image endpoint çağrıları OLMAMALI (artık listing-service'e gidiyor)
```

---

## ⚠️ Bilinen Sorunlar ve Çözümleri

### Sorun 1: `Mime component not found`
**Çözüm:** Container'da `composer require symfony/mime` çalıştırıldı ✅

### Sorun 2: `AWS SDK not found`
**Çözüm:** `composer.json`'a `aws/aws-sdk-php` eklendi ✅

### Sorun 3: R2 bağlantı hatası
**Kontrol Et:**
- R2_ENDPOINT doğru mu?
- R2_ACCESS_KEY_ID ve SECRET doğru mu?
- Bucket adı doğru mu?

---

## 🗂️ Database Migration Notları

**ÖNEMLİ:** Backend ve Listing Service'te **ayrı `listing_images` tabloları** var!

- **Backend DB**: Eski data (migration yapılmayacak, read-only)
- **Listing Service DB**: Yeni data (yeni resimler buraya kaydedilecek)

**Migration gerekmiyor** çünkü:
1. Eski resimler zaten R2'de ve URL'leri değişmiyor
2. Yeni resimler listing-service'ten yüklenecek
3. Frontend her iki durumda da aynı URL formatını kullanıyor

---

## 📊 Rollback Planı (Gerekirse)

Eğer bir sorun çıkarsa:

### Hızlı Rollback (Frontend):
```javascript
// frontend/src/api/listingApi.js
// listingClient yerine monolithClient'a dön
uploadImages: async (listingId, files) => {
    // ... monolithClient.post() kullan
}
```

### Tam Rollback:
```bash
git revert HEAD
git push origin main
```

---

## ✅ Deploy Tamamlandı!

Deployment başarılı olduğunda:

- [  ] Resim upload çalışıyor
- [  ] Resim silme çalışıyor
- [  ] Listing detail sayfasında resimler görünüyor
- [  ] Elasticsearch'te resim bilgileri senkronize
- [  ] R2'de resimler doğru yerde

---

## 📝 Notlar

- **Performance:** Listing-service'ten direkt R2'ye yüklendiği için daha hızlı
- **Separation of Concerns:** Her servis kendi domain'ini yönetiyor
- **Scalability:** Listing-service'i scale etmek image upload'ı da scale eder

---

**Son güncellenme:** 3 Ocak 2026  
**Deploy eden:** [Senin adın]  
**Deploy durumu:** ⏳ Bekliyor

