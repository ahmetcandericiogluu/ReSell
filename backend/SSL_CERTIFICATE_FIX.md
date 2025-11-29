# 🔒 SSL Certificate Problem - Çözüm

## ❌ HATA MESAJI

```
cURL error 60: SSL certificate problem: unable to get local issuer certificate
```

## 🎯 SORUN

Windows'ta PHP, SSL sertifikalarını doğrulamak için gereken CA (Certificate Authority) bundle dosyasını bulamıyor. Bu yüzden HTTPS bağlantıları (Cloudflare R2 gibi) çalışmıyor.

---

## ✅ ÇÖZÜM 1: CA Certificate Bundle Ekle (ÖNERİLEN)

### Adım 1: CA Bundle İndir

**PowerShell ile (Otomatik):**
```powershell
# SSL klasörünü oluştur
New-Item -ItemType Directory -Force -Path "C:\php\extras\ssl"

# CA bundle'ı indir
Invoke-WebRequest -Uri "https://curl.se/ca/cacert.pem" -OutFile "C:\php\extras\ssl\cacert.pem"
```

**Manuel olarak:**
1. Tarayıcıda aç: https://curl.se/ca/cacert.pem
2. Sağ tıkla → "Farklı Kaydet"
3. Konum: `C:\php\extras\ssl\cacert.pem`

### Adım 2: php.ini Güncelle

**Dosyayı aç:**
```bash
notepad C:\php\php.ini
```

**Bul ve değiştir:**

**1. cURL için:**
```ini
# ÖNCEKİ:
;curl.cainfo =

# YENİ:
curl.cainfo = "C:\php\extras\ssl\cacert.pem"
```

**2. OpenSSL için:**
```ini
# ÖNCEKİ:
;openssl.cafile=

# YENİ:
openssl.cafile="C:\php\extras\ssl\cacert.pem"
```

### Adım 3: Kaydet ve Yeniden Başlat

**Kaydet:** Ctrl+S

**Web server'ı yeniden başlat:**
```bash
# Symfony CLI
symfony server:stop
symfony server:start

# veya PHP built-in
# Ctrl+C ile durdur, sonra:
php -S localhost:8000 -t public
```

### Adım 4: Doğrula

```bash
# PHP'nin SSL ayarlarını kontrol et
php -i | findstr cafile
php -i | findstr cainfo
```

Şöyle çıktılar görmelisin:
```
curl.cainfo => C:\php\extras\ssl\cacert.pem
openssl.cafile => C:\php\extras\ssl\cacert.pem
```

---

## ✅ ÇÖZÜM 2: Kod Seviyesinde SSL Doğrulamayı Kapat

**⚠️ SADECE LOCAL DEVELOPMENT İÇİN!**  
**Production'da ASLA kullanma - güvenlik riski!**

### R2StorageService.php'yi Güncelle

**Dosya:** `backend/src/Storage/R2StorageService.php`

```php
public function __construct(
    private readonly string $endpoint,
    private readonly string $region,
    private readonly string $bucket,
    private readonly string $accessKeyId,
    private readonly string $secretAccessKey,
    private readonly string $publicBaseUrl
) {
    $config = [
        'version' => 'latest',
        'region' => $this->region,
        'endpoint' => $this->endpoint,
        'credentials' => [
            'key' => $this->accessKeyId,
            'secret' => $this->secretAccessKey,
        ],
        'use_path_style_endpoint' => false,
    ];

    // SADECE LOCAL DEV İÇİN - SSL doğrulamayı kapat
    if ($_ENV['APP_ENV'] === 'dev') {
        $config['http'] = [
            'verify' => false,  // SSL doğrulamayı kapat
        ];
    }

    $this->s3Client = new S3Client($config);
}
```

**Avantajlar:**
- ✅ Hızlı çözüm
- ✅ Sadece dev ortamında çalışır

**Dezavantajlar:**
- ❌ Man-in-the-middle saldırılarına açık
- ❌ Production'a yanlışlıkla geçerse tehlikeli
- ❌ Best practice değil

---

## ✅ ÇÖZÜM 3: Environment-Specific SSL Ayarı

Daha güvenli bir yaklaşım:

### .env.local'e Ekle

```bash
# .env.local
SSL_VERIFY_PEER=false  # Sadece local dev için
```

### R2StorageService.php

```php
public function __construct(
    private readonly string $endpoint,
    private readonly string $region,
    private readonly string $bucket,
    private readonly string $accessKeyId,
    private readonly string $secretAccessKey,
    private readonly string $publicBaseUrl,
    private readonly bool $sslVerify = true  // Default: SSL doğrula
) {
    $config = [
        'version' => 'latest',
        'region' => $this->region,
        'endpoint' => $this->endpoint,
        'credentials' => [
            'key' => $this->accessKeyId,
            'secret' => $this->secretAccessKey,
        ],
        'use_path_style_endpoint' => false,
    ];

    if (!$this->sslVerify) {
        $config['http'] = ['verify' => false];
    }

    $this->s3Client = new S3Client($config);
}
```

### services.yaml

```yaml
App\Storage\R2StorageService:
    arguments:
        $endpoint: '%env(R2_ENDPOINT)%'
        $region: '%env(R2_REGION)%'
        $bucket: '%env(R2_BUCKET)%'
        $accessKeyId: '%env(R2_ACCESS_KEY_ID)%'
        $secretAccessKey: '%env(R2_SECRET_ACCESS_KEY)%'
        $publicBaseUrl: '%env(R2_PUBLIC_BASE_URL)%'
        $sslVerify: '%env(default:sslVerify_default:bool:SSL_VERIFY_PEER)%'

parameters:
    sslVerify_default: true  # Production default
```

---

## 🔍 SORUN TESPİT

### SSL Ayarlarını Kontrol Et

```bash
# CA bundle konumu
php -i | findstr cafile
php -i | findstr cainfo

# cURL versiyonu
php -m | findstr curl

# OpenSSL versiyonu
php -i | findstr "OpenSSL"
```

### Test Script

**test-ssl.php** oluştur:
```php
<?php
// test-ssl.php
$ch = curl_init('https://www.google.com');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
$result = curl_exec($ch);

if (curl_errno($ch)) {
    echo "cURL Hatası: " . curl_error($ch) . "\n";
} else {
    echo "SSL çalışıyor! ✅\n";
}

curl_close($ch);
```

Çalıştır:
```bash
php test-ssl.php
```

---

## 🎯 ÖNERİLEN YAKLAŞIM

### Local Development (Şimdi):
✅ **ÇÖZÜM 1** kullan (CA bundle ekle)
- Güvenli
- Best practice
- Bir kere yapılır, her zaman çalışır

### Alternatif (Acil durum):
⚠️ **ÇÖZÜM 2 veya 3** kullan
- Sadece test için
- Production'a geçmeden önce düzelt

### Production (İleride):
- ÇÖZÜM 1 zaten çalışacak
- Hosting provider genelde CA bundle'ı hazır sağlar
- Render.com'da ekstra ayar gerekmez

---

## 📋 ÖZET

### Hızlı Çözüm (5 Dakika):

**1. CA Bundle İndir:**
```powershell
New-Item -ItemType Directory -Force -Path "C:\php\extras\ssl"
Invoke-WebRequest -Uri "https://curl.se/ca/cacert.pem" -OutFile "C:\php\extras\ssl\cacert.pem"
```

**2. php.ini Güncelle:**
```bash
notepad C:\php\php.ini
```

Bul ve değiştir:
```ini
curl.cainfo = "C:\php\extras\ssl\cacert.pem"
openssl.cafile="C:\php\extras\ssl\cacert.pem"
```

**3. Server'ı Yeniden Başlat**

**4. Test Et!** 🚀

---

## 🐛 Sorun Devam Ederse

1. PHP restart edildi mi kontrol et
2. cacert.pem dosyasının varlığını kontrol et: `dir C:\php\extras\ssl\cacert.pem`
3. php.ini'de path'lerin doğru olduğunu kontrol et
4. Firewall/Antivirus HTTPS bağlantılarını blokluyor olabilir

---

## 📚 Kaynaklar

- Official CA Bundle: https://curl.se/ca/cacert.pem
- cURL Errors: https://curl.haxx.se/libcurl/c/libcurl-errors.html
- PHP SSL Configuration: https://www.php.net/manual/en/curl.configuration.php

---

**Not:** CA bundle dosyası her 3-6 ayda bir güncellenir. Eski sertifikalar iptal edilebilir, yeni bundle'ı indirmen gerekebilir.

