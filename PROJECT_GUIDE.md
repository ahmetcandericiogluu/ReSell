# Project Guide – Second-Hand Marketplace

## 1. Product Vision

Bu proje, kullanıcıların ellerindeki ikinci el eşyaları satılığa çıkarabildiği ve diğer kullanıcıların bu ilanları görüp satıcıyla iletişime geçebildiği bir ikinci el pazar yeridir.

- Kullanıcılar ilan oluşturabilir (başlık, açıklama, fiyat, kategori, konum, fotoğraflar vb.).
- Diğer kullanıcılar ilanları listeleyip filtreleyebilir.
- İlgilenen kullanıcılar satıcıyla mesajlaşabilir veya belirtilen iletişim kanalı üzerinden ulaşabilir.
- Satın alma gerçekleştiğinde alıcı, satıcı hakkında yorum ve puanlama bırakabilir.

Şimdilik ödeme entegrasyonu zorunlu değil; “satın alındı” durumu sistem içinde basit bir kayıt / durum olarak tutulabilir.

---

## 2. Scope (MVP)

**MVP’de hedeflenen ana özellikler:**

1. **Kullanıcı Yönetimi**
   - Kayıt olma, giriş yapma, çıkış yapma
   - Profil bilgilerini düzenleme (ad, şehir, telefon vb.)
   - Şifre reset akışı (ileriki fazda)

2. **İlan Yönetimi**
   - İlan oluşturma, düzenleme, silme
   - İlan durumu: `draft`, `active`, `sold`, `deleted`
   - Kategoriye göre ve basit filtrelerle ilan listeleme
   - İlan detay sayfası

3. **Mesajlaşma / İletişim**
   - Alıcı ile satıcı arasında ilan bazlı konuşma (conversation + messages)
   - En azından basit metin mesajı (ileride dosya/fotoğraf vs. düşünülebilir)

4. **Yorum ve Değerlendirme**
   - Bir ilan üzerinden alışveriş tamamlandıktan sonra,
   - Alıcının satıcıya puan ve yorum bırakabilmesi
   - Satıcının ortalama puanının hesaplanması

5. **Temel Güvenlik / Yetkilendirme**
   - Sadece ilan sahibi kendi ilanını düzenleyebilir/silebilir.
   - Sadece ilgili kullanıcılar kendi mesajlaşmalarını görebilir.
   - Sadece gerçekten satın almış (veya “satın aldı” olarak işaretlenmiş) kullanıcı satıcıyı değerlendirebilir.

---

## 3. Tech Stack

Zorunlu:

- PHP 8.3+
- Symfony 7.3
- PostgreSQL 16+
- Composer

İleride entegre edilebilecek teknolojiler:

- Redis (cache, session, queue vs.)
- Docker (lokal geliştirme ve deployment için)
- Elasticsearch (ilan arama ve filtreleme için)
- Frontend: henüz net değil, muhtemelen React; başlangıçta Symfony template’leri de kullanılabilir.

Şu an odak: **Symfony backend + PostgreSQL** ile sağlam bir API ve domain yapısı kurmak.

---

## 4. Architecture & Code Style Rules

1. **Katmanlı yaklaşım (basit haliyle):**
   - **Controller**: HTTP isteklerini karşılar, request/response işleri. İnce tutulmalı.
   - **Service (Application / Domain Service)**: İş mantığının çoğu burada olmalı.
   - **Repository**: Doctrine üzerinden veri tabanı erişimi.
   - **Entity**: Domain modelleri, iş kurallarının bir kısmı burada.

2. **Naming & Code Style**
   - Kod dili: İngilizce
   - Açıklama / yorum satırları: İngilizce veya Türkçe olabilir, ama mümkünse kısa ve net.
   - PSR-12 uyumlu PHP kodu
   - Sınıf adları `PascalCase`, method ve değişken adları `camelCase`

3. **Controller Kuralları**
   - Bir endpoint’in içinde büyük iş mantığı olmamalı.  
     Örnek: “ilan oluşturma” işlemi için `ListingService` gibi bir servis kullanılmalı.
   - Response’lar mümkün olduğunca DTO / ViewModel benzeri yapılarla dönmeli.  
     (En azından array + normalizer yaklaşımı.)

4. **Veri Tabanı Kuralları**
   - Tablo isimleri: `snake_case` ve çoğul: `users`, `listings`, `messages`, `reviews` vb.
   - ID alanları: `id` (PK, bigserial/uuid)
   - `created_at`, `updated_at`, opsiyonel `deleted_at` alanları default pattern.
   - İlişkilerde foreign key kolonları: `user_id`, `seller_id`, `listing_id` vb.

---

## 5. Core Domain Model (MVP)

### 5.1 User

Temel alanlar:

- `id`
- `email` (unique)
- `password` (hashed)
- `name`
- `phone` (opsiyonel)
- `city` / `location` (basit text veya ileride ayrı tablo)
- `rating_average` (hesaplanabilir, DB’de de tutulabilir)
- `created_at`, `updated_at`

### 5.2 Listing (İlan)

- `id`
- `seller` (User relation)
- `title`
- `description`
- `price` (numeric / decimal)
- `currency` (örneğin `TRY`, `USD`)
- `status` (`draft`, `active`, `sold`, `deleted`)
- `category` (Category relation)
- `location` (metin, şehir/ilçe)
- `created_at`, `updated_at`

### 5.3 ListingImage

- `id`
- `listing` (Listing relation)
- `url` veya `path`
- `position` (sıralama için)

### 5.4 Category

- `id`
- `name`
- `slug`
- `parent_id` (opsiyonel, hiyerarşi için)

### 5.5 Conversation

- `id`
- `listing` (Listing relation)
- `buyer` (User)
- `seller` (User)
- `created_at`, `updated_at`

### 5.6 Message

- `id`
- `conversation` (Conversation relation)
- `sender` (User)
- `content` (text)
- `created_at`

### 5.7 Review (Satıcı Değerlendirmesi)

- `id`
- `listing` (Listing relation)
- `seller` (User)
- `buyer` (User)
- `rating` (örneğin 1–5)
- `comment`
- `created_at`
- `is_public` (bool)

İş kuralı:  
Bir `Review`, sadece ilgili `listing` için gerçekten alıcı olan kullanıcı tarafından oluşturulabilir.

---

## 6. Authentication & Authorization

- İlk aşamada klasik email + şifre tabanlı auth.
- Symfony Security kullanılsın.
- Başlangıç için session tabanlı veya token tabanlı (ör. JWT) auth seçilebilir.
- Cursor, seçilen stratejiye göre tüm güvenlik yapılandırmasını konsistent tutmalı.

---

## 7. Testing

- Kritik iş kurallarında (örn. ilan oluşturma, review oluşturma) **en azından basic unit/functional testler** yazılmalı.
- Test teknolojisi: Symfony + PHPUnit (varsayılan stack).

---

## 8. Future Extensions (Şimdilik sadece plan)

- Redis cache (sık kullanılan ilan listeleri, kategori listesi vs.)
- Arama ve filtreleme için Elasticsearch
- Bildirim mekanizması (mail / push bildirimleri)
- Admin panel (ilan ve kullanıcı moderasyonu)
