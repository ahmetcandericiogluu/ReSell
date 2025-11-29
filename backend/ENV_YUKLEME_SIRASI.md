# 📋 Symfony ENV Dosyaları Yükleme Sırası

## 🔄 YÜKLEME SIRASI (Öncelik Sırası)

Symfony aşağıdaki sırayla environment dosyalarını yükler. **Son yüklenen dosya önceliklidir** (üzerine yazar):

```
1. .env                    (Base - herkeste aynı)
2. .env.local              (Local overrides - gitignore'da)
3. .env.{APP_ENV}          (.env.dev, .env.prod, .env.test)
4. .env.{APP_ENV}.local    (.env.dev.local, .env.prod.local)
```

### Örnek Senaryo

Diyelim ki:
- `APP_ENV=dev` (development modunda)
- Aşağıdaki dosyalar var:
  - `.env`
  - `.env.local`
  - `.env.dev`

**Yükleme sırası:**
```
1. .env         → DATABASE_URL=postgres://localhost/base_db
2. .env.local   → DATABASE_URL=postgres://localhost/my_local_db  
3. .env.dev     → DATABASE_URL=postgres://localhost/dev_db

SONUÇ: DATABASE_URL=postgres://localhost/dev_db kullanılır ✅
```

---

## 📂 DOSYA AÇIKLAMALARI

### `.env` (Committed - Git'e eklenir)
- **Amaç:** Tüm ortamlar için varsayılan değerler
- **İçerik:** Placeholder değerler, örnekler
- **Git:** ✅ Commit edilir
- **Örnek:**
```bash
DATABASE_URL="postgresql://app:!ChangeMe!@127.0.0.1:5432/app"
R2_ENDPOINT=
R2_BUCKET=
```

### `.env.local` (NOT Committed - Gitignore'da)
- **Amaç:** Kişisel local overrides (tüm environment'larda)
- **İçerik:** Local geliştirme ayarları
- **Git:** ❌ Gitignore'da (asla commit edilmez)
- **Örnek:**
```bash
DATABASE_URL="postgresql://myuser:mypass@localhost:5432/my_local_db"
APP_SECRET=my-super-secret-key-123
```

### `.env.dev` (Committed - Git'e eklenir)
- **Amaç:** Development ortamına özel ayarlar
- **İçerik:** Dev'e özel configuration
- **Git:** ✅ Commit edilir
- **Örnek:**
```bash
APP_ENV=dev
APP_DEBUG=1
```

### `.env.prod` (Committed - Git'e eklenir)
- **Amaç:** Production ortamına özel ayarlar
- **İçerik:** Production configuration
- **Git:** ✅ Commit edilir
- **Örnek:**
```bash
APP_ENV=prod
APP_DEBUG=0
```

### `.env.test` (Committed)
- **Amaç:** Test ortamına özel
- **İçerik:** Test database, mocks vs.
- **Git:** ✅ Commit edilir

### `.env.{ENV}.local` (NOT Committed)
- **Amaç:** Environment-specific local overrides
- **Git:** ❌ Gitignore'da
- **Örnek:** `.env.dev.local`, `.env.prod.local`

---

## ⚙️ SENARYOLAR

### Senaryo 1: Local Development (Sen)
**Dosyalar:**
- `.env` → Varsayılan değerler
- `.env.local` → Senin local DB bilgilerin
- `.env.dev` → Dev ortam ayarları

**APP_ENV=dev olduğunda yükleme:**
```
1. .env
2. .env.local      ← Senin ayarların burada
3. .env.dev        ← Dev ayarları
```

**Sonuç:** `.env.local` ve `.env.dev` değerleri `.env`'i override eder.

### Senaryo 2: Başka Geliştirici
**Dosyalar:**
- `.env` (Git'ten aldı)
- `.env.local` (kendisi oluşturdu, farklı DB şifresi)
- `.env.dev` (Git'ten aldı)

**Sonuç:** Herkes `.env.local`'de kendi ayarlarını tutar, birbirini etkilemez.

### Senaryo 3: Production (Render.com)
**Dosyalar:**
- `.env` (deploy edildi)
- `.env.prod` (deploy edildi)
- Environment Variables (Render dashboard'da set edilmiş)

**Sonuç:** Render'daki environment variables en üstte gelir.

---

## 🎯 SENİN DURUMUN

### Hangi Dosyalar Var?
```bash
.env        → ✅ Base configuration
.env.local  → ✅ Senin local overrides
.env.dev    → ✅ Development settings
```

### APP_ENV=dev İken Yükleme:
```
1. .env         (base)
2. .env.local   (senin özel ayarların) 👈 BURASI ÖNEMLİ
3. .env.dev     (dev ayarları)
```

**En son .env.dev yüklenir, bu yüzden:**
- `.env.dev` içindeki değerler **EN ÖNCELİKLİ**
- `.env.local` içindeki değerler `.env`'i override eder ama `.env.dev`'i edemez
- `.env` sadece default değerler için

---

## 💡 TAVSİYELER

### 1. **Local Development İçin**
**`.env.local` kullan (R2 bilgileri için):**
```bash
# .env.local
R2_ENDPOINT=https://your-account.r2.cloudflarestorage.com
R2_BUCKET=my-bucket
R2_ACCESS_KEY_ID=your-key
R2_SECRET_ACCESS_KEY=your-secret
R2_PUBLIC_BASE_URL=https://images.yourdomain.com
```

**Neden `.env.local`?**
- ✅ Git'e commit edilmez (güvenli)
- ✅ Sadece senin bilgisayarında
- ✅ `.env.dev`'i override eder

### 2. **Team İçin**
**`.env` - Placeholder değerler:**
```bash
# .env
R2_ENDPOINT=
R2_BUCKET=
R2_ACCESS_KEY_ID=
R2_SECRET_ACCESS_KEY=
R2_PUBLIC_BASE_URL=
```

### 3. **Production İçin**
- Render.com dashboard'da Environment Variables set et
- Dosyaya yazmak yerine platform'da sakla (daha güvenli)

---

## 🔍 KONTROL ET

### Hangi Değerler Yükleniyor?

```bash
# Symfony command ile
cd backend
php bin/console debug:container --env-vars

# Veya belirli bir değişken
php bin/console debug:container --env-var=R2_ENDPOINT
```

### APP_ENV Nedir?

```bash
php bin/console about
```

---

## ✅ SONUÇ

**Şu anki durumun:**
- `APP_ENV=dev` ise → `.env` → `.env.local` → `.env.dev` sırasıyla yüklenir
- **R2 bilgilerini `.env.local`'e koy** (gitignore'da, güvenli)
- `.env` dosyasında placeholder bırak
- `.env.dev` dosyasında dev-specific ayarlar tut

**Hangi dosyaya ne koymalı:**
```
.env         → Placeholder/default değerler (DB_URL="postgresql://...")
.env.local   → Gerçek credentials (R2 keys, DB password)
.env.dev     → Debug=true, dev environment settings
```

Böylece hem güvenli, hem de team-friendly çalışırsın! 🎉

