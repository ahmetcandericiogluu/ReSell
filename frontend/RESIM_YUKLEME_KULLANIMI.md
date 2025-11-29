# 📸 Resim Yükleme Sistemi - Kullanım Kılavuzu

## ✅ SİSTEM HAZIR!

Kullanıcılar artık ilanlarına resim ekleyebilir, yönetebilir ve görüntüleyebilir.

---

## 🎯 KULLANICI AKIŞLARI

### 1️⃣ Mevcut İlana Resim Ekleme

**Adım 1:** "İlanlarım" sayfasına git
- Sol menüden "İlanlarım" seçeneğine tıkla
- veya `/my-listings` adresine git

**Adım 2:** İlanını bul ve "Resimleri Yönet" butonuna tıkla
- Her ilan kartında "📸 Resimleri Yönet" butonu var
- Bu butona tıklayınca resim yönetim sayfası açılır

**Adım 3:** Resim seç
- "Resim Seçin" alanına tıkla
- veya resimleri sürükleyip bırak (drag & drop)
- Aynı anda en fazla 10 resim seçebilirsin

**Adım 4:** Yükle
- Seçilen resimlerin önizlemesi gösterilir
- Yanlış seçim varsa ✕ butonuyla kaldırabilirsin
- "X Resim Yükle" butonuna tıkla
- Resimler yüklenir ve listede görünür

### 2️⃣ Resim Silme

**"Resimleri Yönet" sayfasında:**
1. Silinecek resmin altında "🗑️ Sil" butonuna tıkla
2. Onay penceresinde "Tamam"a tıkla
3. Resim silinir

### 3️⃣ Resimleri Görüntüleme (İlan Detayı)

**İlan detay sayfasında:**
- Resimler varsa:
  - En üstte ana resim gösterilir
  - ‹ › butonlarıyla resimler arasında geçiş yapabilirsin
  - Altta küçük önizlemeler (thumbnail) gösterilir
  - Thumbnail'e tıklayarak o resme geçiş yapabilirsin
  - Sağ alt köşede "1 / 5" gibi resim sayacı gösterilir

- Resim yoksa:
  - "Henüz Resim Eklenmemiş" mesajı görünür
  - Eğer ilan sahibiysen "📸 Resim Ekle" butonu çıkar
  - Bu butona tıklayınca direkt resim yönetim sayfasına gidersin

---

## ⚙️ TEKNİK DETAYLAR

### Resim Gereksinimleri
- **Format:** JPEG, PNG, WebP
- **Maksimum Boyut:** 5 MB (her resim için)
- **Maksimum Sayı:** 10 resim (yönetim sayfasında tek seferde)

### Depolama
- **Local Development:** Resimler `backend/public/uploads/listings/{id}/` klasörüne kaydedilir
- **Production (R2):** Cloudflare R2 bucket'ına yüklenir

### Güvenlik
- ✅ Sadece ilan sahibi kendi ilanının resimlerini yönetebilir
- ✅ Her dosya validasyondan geçer (format, boyut)
- ✅ Dosya isimleri benzersiz (unique) olarak oluşturulur

---

## 🖼️ EKRAN GÖRÜNTÜLERİ (Açıklama)

### 1. İlanlarım Sayfası
```
┌─────────────────────────────┐
│  İlan Başlığı               │
│  Açıklama...                │
│  ₺1,000    📍 İstanbul      │
│                             │
│  [📸 Resimleri Yönet]      │
└─────────────────────────────┘
```

### 2. Resim Yönetim Sayfası
```
┌─────────────────────────────┐
│  Yeni Resim Ekle            │
│  ┌───────────────────────┐  │
│  │   📸                  │  │
│  │   Resim Seçin         │  │
│  │   veya sürükle-bırak  │  │
│  └───────────────────────┘  │
│                             │
│  Mevcut Resimler (3)        │
│  ┌────┐ ┌────┐ ┌────┐     │
│  │img1│ │img2│ │img3│     │
│  │#1  │ │#2  │ │#3  │     │
│  │[Sil]│ │[Sil]│ │[Sil]│  │
│  └────┘ └────┘ └────┘     │
└─────────────────────────────┘
```

### 3. İlan Detay - Resim Galerisi
```
┌─────────────────────────────┐
│  ‹    [ANA RESİM]      ›    │
│            1 / 5            │
│                             │
│  [o] [o] [o] [o] [o]       │
│  (thumbnail'ler)            │
└─────────────────────────────┘
```

---

## 🐛 SORUN GİDERME

### "Dosya boyutu çok büyük" hatası
- Her resim en fazla 5MB olabilir
- Resmi sıkıştır veya boyutunu küçült

### "Geçersiz dosya tipi" hatası
- Sadece JPEG, PNG, WebP formatları desteklenir
- Dosyanın uzantısını kontrol et

### "Yetkiniz yok" hatası
- Sadece kendi ilanlarınızın resimlerini yönetebilirsiniz
- Doğru hesapla giriş yaptığınızdan emin olun

### Resim yüklenmiyor
1. İnternet bağlantınızı kontrol edin
2. Sayfayı yenileyin (F5)
3. Tarayıcı konsolunu açıp hata mesajlarına bakın
4. Backend'in çalıştığından emin olun

---

## 📋 API ENDPOINTS (Geliştirici Bilgisi)

### GET `/api/listings/{id}`
İlan detayı + resimler

### GET `/api/listings/{id}/images`
Sadece resimler

### POST `/api/listings/{id}/images`
Yeni resim yükle (multipart/form-data)

### DELETE `/api/listings/{listingId}/images/{imageId}`
Resim sil

---

## ✨ ÖZELLİKLER

✅ Çoklu resim yükleme  
✅ Drag & drop desteği  
✅ Anlık önizleme  
✅ Resim sıralama (position)  
✅ Ana resim gösterimi  
✅ Resim galerisi (slider)  
✅ Thumbnail navigasyon  
✅ Responsive tasarım  
✅ Validasyon ve hata mesajları  
✅ Authorization kontrolü  

---

## 🚀 SONRAKI ADIMLAR (İleride Yapılabilecekler)

1. **Yeni İlan Oluştururken Resim Ekleme**
   - CreateListing sayfasına ImageUpload component'i ekle
   - İlan oluşturulduktan sonra resimleri otomatik yükle

2. **Resim Sıralama (Drag & Drop)**
   - Resimlerin sırasını değiştirme
   - Ana resmi seçme

3. **Resim Kırpma/Düzenleme**
   - Yüklemeden önce kırpma
   - Filtre uygulama

4. **Lazy Loading**
   - Büyük resimler için lazy loading
   - Thumbnail'ler için placeholder

5. **Zoom Özelliği**
   - Resme tıklayınca büyüt
   - Modal görünüm

---

**Sistem hazır ve çalışıyor! 🎉**

