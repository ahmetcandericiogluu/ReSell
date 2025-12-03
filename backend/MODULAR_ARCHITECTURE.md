# Modular Monolith Architecture

## 📋 Genel Bakış

ReSell projesi **domain bazlı Modular Monolith** mimarisine dönüştürülmüştür. Bu yapı:

- ✅ Kodun domain'lere göre organize edilmesini sağlar
- ✅ Her domain'in kendi sorumluluğu vardır
- ✅ Gelecekte mikroservislere geçiş için kolaylık sağlar
- ✅ Tek bir Symfony uygulaması olarak çalışır (bundle kullanılmaz)

## 🏗️ Domain Yapısı

```
src/
├── User/                    # Kullanıcı Domain'i
│   ├── Entity/             # User entity
│   ├── Repository/         # User repository
│   ├── Service/            # UserService (register, login, profile update)
│   ├── Controller/         # AuthController, UserController
│   ├── DTO/                # LoginRequest, RegisterRequest, UserResponse, etc.
│   └── Security/           # AuthenticationEntryPoint, JsonLoginAuthenticator
│
├── Listing/                 # İlan Domain'i
│   ├── Entity/             # Listing, ListingImage
│   ├── Repository/         # ListingRepository, ListingImageRepository
│   ├── Service/            # ListingService, ListingImageService
│   ├── Controller/         # ListingController
│   └── DTO/                # CreateListingRequest, ListingResponse, etc.
│
├── Review/                  # Değerlendirme Domain'i
│   ├── Entity/             # Review
│   ├── Repository/         # ReviewRepository
│   ├── Controller/         # ReviewController
│   └── DTO/                # ReviewResponse
│
├── Messaging/               # Mesajlaşma Domain'i (İskelet)
│   ├── Entity/             # (Gelecekte: Conversation, Message)
│   ├── Repository/         
│   ├── Service/            
│   ├── Controller/         
│   └── DTO/                
│
└── Shared/                  # Ortak Katman
    ├── Exception/          # DomainException, NotFoundException, etc.
    ├── EventListener/      # ExceptionListener
    ├── Security/           # CurrentUserProvider
    └── Storage/            # StorageInterface, R2Storage, LocalStorage
```

## 🔧 Namespace Yapısı

Tüm namespace'ler domain'lere göre düzenlenmiştir:

- `App\User\Entity\User`
- `App\User\Repository\UserRepository`
- `App\User\Service\UserService`
- `App\User\Controller\AuthController`
- `App\User\DTO\LoginRequest`
- `App\Listing\Entity\Listing`
- `App\Listing\Service\ListingService`
- `App\Review\Entity\Review`
- `App\Shared\Storage\StorageInterface`

## 📝 Güncellenmiş Konfigürasyonlar

### 1. Doctrine Mapping (`config/packages/doctrine.yaml`)

```yaml
mappings:
    User:
        type: attribute
        is_bundle: false
        dir: '%kernel.project_dir%/src/User/Entity'
        prefix: 'App\User\Entity'
    Listing:
        type: attribute
        is_bundle: false
        dir: '%kernel.project_dir%/src/Listing/Entity'
        prefix: 'App\Listing\Entity'
    Review:
        type: attribute
        is_bundle: false
        dir: '%kernel.project_dir%/src/Review/Entity'
        prefix: 'App\Review\Entity'
```

### 2. Routes (`config/routes.yaml`)

```yaml
user_controllers:
    resource:
        path: ../src/User/Controller/
        namespace: App\User\Controller
    type: attribute

listing_controllers:
    resource:
        path: ../src/Listing/Controller/
        namespace: App\Listing\Controller
    type: attribute

review_controllers:
    resource:
        path: ../src/Review/Controller/
        namespace: App\Review\Controller
    type: attribute
```

### 3. Security (`config/packages/security.yaml`)

```yaml
providers:
    app_user_provider:
        entity:
            class: App\User\Entity\User
            property: email

firewalls:
    main:
        entry_point: App\User\Security\AuthenticationEntryPoint
        custom_authenticators:
            - App\User\Security\JsonLoginAuthenticator
```

### 4. Services (`config/services.yaml`)

```yaml
# Listing Image Service with storage driver parameter
App\Listing\Service\ListingImageService:
    arguments:
        $storageDriver: 'r2'
```

## 🎯 Domain Sorumlulukları

### User Domain
- Kullanıcı kaydı (register)
- Giriş/çıkış (login/logout)
- Profil yönetimi
- Kimlik doğrulama (authentication)
- Kullanıcı sorguları

### Listing Domain
- İlan oluşturma, güncelleme, silme
- İlan listeleme ve filtreleme
- İlan görseli yönetimi (upload/delete)
- Cloudflare R2 entegrasyonu
- İlan durum yönetimi (draft/active/sold/deleted)

### Review Domain
- Satıcı değerlendirmesi
- Rating hesaplama
- Review listeleme
- Public/private review yönetimi

### Messaging Domain (İskelet)
- Gelecekte: Conversation yönetimi
- Gelecekte: Message gönderme/alma
- Gelecekte: İlan bazlı mesajlaşma

### Shared Domain
- Ortak exception'lar
- Storage abstraction (R2/Local)
- Event listener'lar
- Security helper'lar

## 🔄 Domain'ler Arası İlişkiler

- **Listing → User**: Listing'in bir seller'ı var (User entity referansı)
- **Review → User**: Review'da buyer ve seller var (User entity referansları)
- **Review → Listing**: Review bir listing'e bağlı
- **Listing → Shared**: Storage interface kullanıyor (dependency injection)

### Bağımlılık Yönü
```
User ← Listing ← Review
         ↓
      Shared
```

## ✅ Yapılan Değişiklikler

1. ✅ Tüm entity'ler domain klasörlerine taşındı
2. ✅ Repository'ler domain klasörlerine taşındı
3. ✅ Service'ler domain klasörlerine taşındı
4. ✅ Controller'lar domain klasörlerine taşındı
5. ✅ DTO'lar domain klasörlerine taşındı
6. ✅ Security sınıfları User domain'ine taşındı
7. ✅ Namespace'ler güncellendi
8. ✅ Use import'ları düzeltildi
9. ✅ Doctrine mapping güncellendi
10. ✅ Routes güncellendi
11. ✅ Security config güncellendi
12. ✅ Services config güncellendi
13. ✅ DataFixtures güncellendi
14. ✅ Composer autoload yenilendi
15. ✅ Cache temizlendi

## 🧪 Doğrulama

Tüm sistemler test edildi:

```bash
# Route'lar
php bin/console debug:router
✅ 29 route bulundu

# Entity mapping
php bin/console doctrine:mapping:info
✅ 4 entity mapped

# Schema validation
php bin/console doctrine:schema:validate
✅ Mapping ve database sync

# Container
php bin/console debug:container UserService
✅ Tüm servisler autowired
```

## 🚀 Sonraki Adımlar

1. **Messaging Domain Implementasyonu**
   - Conversation ve Message entity'leri
   - Mesajlaşma servisleri
   - WebSocket/Long-polling desteği

2. **Domain Event System**
   - UserRegistered event
   - ListingCreated event
   - ReviewCreated event
   - Domain event handler'lar

3. **Domain Service Interface'leri**
   - Cross-domain bağımlılıkları interface'ler ile ayır
   - Örn: `UserReadServiceInterface` for listing domain

4. **Testing**
   - Domain bazlı unit testler
   - Integration testler
   - API testleri

## 📚 Kurallar

### Domain İçi İletişim
- Controller → Service → Repository → Entity
- DTO'lar sadece kendi domain'inde kullanılır
- Service'ler iş mantığını yönetir

### Domain'ler Arası İletişim
- Entity referansları kullanılabilir (ör: `Listing->getSeller(): User`)
- Mümkün olduğunda interface kullan
- Shared domain tüm domain'ler tarafından kullanılabilir

### Yeni Kod Ekleme
1. İlgili domain klasörüne ekle
2. Namespace'i doğru ayarla
3. Dependency injection kullan
4. Domain sorumluluğuna dikkat et

## 📖 Kaynaklar

- [Symfony Best Practices](https://symfony.com/doc/current/best_practices.html)
- [Domain-Driven Design](https://en.wikipedia.org/wiki/Domain-driven_design)
- [Modular Monolith Architecture](https://www.kamilgrzybek.com/blog/posts/modular-monolith-primer)

