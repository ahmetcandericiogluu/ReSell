# Test Verileri (Seeder) Kullanımı

## 🎯 Ne İşe Yarar?

`AppFixtures` sınıfı, test için gerçekçi veriler oluşturur:
- **11 kullanıcı** (1 test + 10 random)
- **50 ilan** (farklı kategorilerde)
- Gerçekçi Türkçe isimler ve şehirler
- Çeşitli fiyat aralıkları

---

## 🚀 Kullanım

### 1. Veritabanını Sıfırla (UYARI: Tüm veriyi siler!)

```bash
php bin/console doctrine:database:drop --force
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate --no-interaction
```

### 2. Test Verilerini Yükle

```bash
php bin/console doctrine:fixtures:load --no-interaction
```

### 3. Giriş Yap

**Test kullanıcısı:**
- Email: `test@resell.com`
- Şifre: `test123`

**Diğer kullanıcılar:**
- Email: `ahmet.yilmaz1@example.com` (örnek)
- Şifre: `password`

---

## 📊 Oluşturulan Veriler

### Kullanıcılar
- 1 ana test kullanıcı
- 10 random kullanıcı
- Gerçekçi isimler (Ahmet Yılmaz, vb.)
- Türk şehirleri
- Telefon numaraları

### İlanlar
- 50 adet çeşitli ilan
- **Kategoriler:** Elektronik, Mobilya, Giyim, Kitap, Spor, vb.
- **Durumlar:** %80 aktif, %10 satıldı, %10 taslak
- **Fiyatlar:** Gerçekçi aralıklar (500₺ - 25.000₺)
- **Lokasyonlar:** İstanbul, Ankara, İzmir, vb.

### Örnek İlanlar
- iPhone 12 128GB
- MacBook Air M1
- İkea Koltuk Takımı
- PlayStation 5
- Nike Air Max Ayakkabı
- Harry Potter Seti
- Fitness Bisikleti
- Ve daha fazlası...

---

## 🔧 Özelleştirme

### Daha Fazla İlan Oluşturmak

`AppFixtures.php` dosyasında:

```php
// Create 50 listings
for ($i = 0; $i < 50; $i++) {  // 50 yerine 100 yap
```

### Yeni Ürün Eklemek

`PRODUCTS` array'ine yeni ürün ekle:

```php
['Ürün Adı', 'Açıklama', minFiyat, maxFiyat, 'TRY'],
```

### Yeni Şehir Eklemek

```php
private const CITIES = [
    'İstanbul', 'Ankara', 'Yeni Şehir'
];
```

---

## ⚠️ Önemli Notlar

1. **Veri Silme:** `doctrine:fixtures:load` komutu mevcut tüm verileri siler!
2. **Production:** Asla production'da fixtures çalıştırmayın!
3. **Resimler:** Şu an resim eklemiyor (manuel ekleyebilirsiniz)
4. **İlişkiler:** User-Listing ilişkileri otomatik oluşuyor

---

## 🎨 Gelişmiş Kullanım

### Sadece Yeni Veri Ekle (Eskiyi Silme)

```bash
php bin/console doctrine:fixtures:load --append
```

### Belirli Fixture Çalıştır

```php
// Yeni bir fixture sınıfı oluştur
class UserFixtures extends Fixture { }
class ListingFixtures extends Fixture { }
```

---

## 🧪 Test Senaryoları

### Senaryo 1: Çok İlan Testi
```bash
# 100 ilan oluştur (AppFixtures.php'de sayıyı değiştir)
php bin/console doctrine:fixtures:load --no-interaction
```

### Senaryo 2: Pagination Testi
- Frontend'de listings sayfasını aç
- Scroll yaparak çok ilanı gör
- Performansı test et

### Senaryo 3: Arama Testi
- Farklı şehirlerde ara
- Fiyat aralıklarını test et
- Kategorilere göre filtrele

---

## 📝 Fixture Geliştirme İpuçları

### Resim Eklemek İsterseniz

```php
// AppFixtures.php içinde
use App\Entity\ListingImage;

private function addImagesToListing(Listing $listing, ObjectManager $manager): void
{
    // Örnek resim URL'leri (Unsplash placeholder)
    $imageUrl = 'https://source.unsplash.com/random/800x600?product';
    
    $image = new ListingImage();
    $image->setListing($listing);
    $image->setPath('placeholder.jpg');
    $image->setUrl($imageUrl);
    $image->setPosition(1);
    $image->setStorageDriver('local');
    
    $manager->persist($image);
}
```

### Daha Fazla Veri Çeşitliliği

```php
// Faker kütüphanesi kullanabilirsin
composer require --dev fakerphp/faker

use Faker\Factory;

$faker = Factory::create('tr_TR'); // Türkçe
$listing->setDescription($faker->paragraph(3));
```

---

## 🐛 Sorun Giderme

**Hata: "Foreign key constraint fails"**
```bash
# Veritabanını temizle ve yeniden oluştur
php bin/console doctrine:database:drop --force
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
php bin/console doctrine:fixtures:load
```

**Hata: "Class not found"**
```bash
# Composer autoload'u güncelle
composer dump-autoload
```

**Çok yavaş çalışıyor**
```bash
# Batch insert kullan (AppFixtures.php'de)
// Her 20 kayıtta bir flush
if ($i % 20 === 0) {
    $manager->flush();
    $manager->clear(); // Memory temizle
}
```

