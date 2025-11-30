# ReSell Backend Architecture

## 🎯 Architecture Vision

ReSell backend follows a **feature-based, clean architecture** approach with:
- **Thin Controllers** (HTTP layer only)
- **Use-case Services** (business logic)
- **Repository Interfaces** (data access abstraction)
- **DTOs** (request/response contracts)
- **Shared Infrastructure** (cross-cutting concerns)

---

## 📁 Current Structure (Hybrid State)

We are in a **transitional phase**:
- ✅ **Shared layer** implemented (Exception, Security, Storage)
- 🔄 **Legacy structure** still active (backward compatibility)
- 📋 **Future migration** planned for feature-based modules

```
src/
├── Shared/               ✅ IMPLEMENTED
│   ├── Exception/        # Domain exceptions
│   ├── Security/         # CurrentUserProvider
│   ├── Storage/          # StorageInterface + implementations
│   └── EventListener/    # ExceptionListener
│
├── Controller/           🔄 LEGACY (to be migrated)
├── Entity/               🔄 LEGACY (to be migrated)
├── Repository/           🔄 LEGACY (to be migrated)
├── Service/              🔄 LEGACY (to be migrated)
└── DTO/                  🔄 LEGACY (to be migrated)
```

---

## 🎯 Target Structure (Future)

```
src/
├── Shared/               ✅ Done
│   ├── Exception/
│   │   ├── DomainException.php
│   │   ├── NotFoundException.php
│   │   ├── UnauthorizedException.php
│   │   └── ValidationException.php
│   ├── Security/
│   │   └── CurrentUserProvider.php
│   ├── Storage/
│   │   ├── StorageInterface.php
│   │   ├── LocalStorageService.php
│   │   └── R2StorageService.php
│   └── EventListener/
│       └── ExceptionListener.php
│
├── User/                 📋 Planned
│   ├── Controller/
│   │   └── AuthController.php
│   ├── Entity/
│   │   └── User.php
│   ├── Repository/
│   │   ├── UserRepositoryInterface.php
│   │   └── DoctrineUserRepository.php
│   ├── Service/
│   │   ├── RegisterUserService.php
│   │   ├── LoginUserService.php
│   │   └── GetCurrentUserService.php
│   └── DTO/
│       ├── RegisterRequestDTO.php
│       ├── LoginRequestDTO.php
│       └── UserResponseDTO.php
│
├── Listing/              📋 Planned
│   ├── Controller/
│   │   └── ListingController.php
│   ├── Entity/
│   │   └── Listing.php
│   ├── Repository/
│   │   ├── ListingRepositoryInterface.php
│   │   └── DoctrineListingRepository.php
│   ├── Service/
│   │   ├── CreateListingService.php
│   │   ├── UpdateListingService.php
│   │   ├── GetListingDetailService.php
│   │   ├── GetMyListingsService.php
│   │   └── GetListingListService.php
│   └── DTO/
│       ├── CreateListingRequestDTO.php
│       ├── UpdateListingRequestDTO.php
│       ├── ListingResponseDTO.php
│       └── ListingListItemDTO.php
│
└── ListingImage/         📋 Planned
    ├── Entity/
    │   └── ListingImage.php
    ├── Repository/
    │   ├── ListingImageRepositoryInterface.php
    │   └── DoctrineListingImageRepository.php
    ├── Service/
    │   ├── AttachImageService.php
    │   ├── RemoveImageService.php
    │   └── ReorderImagesService.php
    └── DTO/
        └── ListingImageResponseDTO.php
```

---

## ✅ What's Implemented (Shared Layer)

### 1. Exception Handling

**Domain Exceptions:**
- `DomainException` - Base for all business logic errors
- `NotFoundException` - Resource not found (404)
- `UnauthorizedException` - Forbidden access (403)
- `ValidationException` - Validation failures (422)

**Exception Listener:**
- Automatically converts exceptions → JSON responses
- Registered via `#[AsEventListener]` attribute
- No need to manually handle exceptions in controllers

**Usage:**
```php
use App\Shared\Exception\NotFoundException;

throw new NotFoundException('Listing not found');
// Automatically returns: {"error": "Listing not found"} with 404
```

### 2. Security

**CurrentUserProvider:**
```php
use App\Shared\Security\CurrentUserProvider;

class SomeService
{
    public function __construct(
        private readonly CurrentUserProvider $userProvider
    ) {}

    public function doSomething(): void
    {
        $user = $this->userProvider->getUserOrThrow();
        // ...
    }
}
```

### 3. Storage

**Interface-based storage** with two implementations:
- `LocalStorageService` - For local development
- `R2StorageService` - For Cloudflare R2 (production)

**Switch in `services.yaml`:**
```yaml
App\Shared\Storage\StorageInterface:
    alias: App\Shared\Storage\R2StorageService  # or LocalStorageService
```

**Usage:**
```php
use App\Shared\Storage\StorageInterface;

class ImageService
{
    public function __construct(
        private readonly StorageInterface $storage
    ) {}

    public function upload(UploadedFile $file): array
    {
        return $this->storage->upload($file, 'listings/123');
    }
}
```

---

## 🔄 Migration Strategy (Kademeli Refactoring)

### Phase 1: Infrastructure ✅ DONE
- [x] Shared layer (Exception, Security, Storage)
- [x] Exception handling
- [x] Services.yaml configuration

### Phase 2: User Module 📋 Next
When adding new User features:
1. Create `User/` directory structure
2. Move User entity, repository, DTOs
3. Split UserService into use-case services:
   - `RegisterUserService`
   - `LoginUserService`
   - `GetCurrentUserService`
4. Create `UserRepositoryInterface`
5. Update AuthController to use new services

### Phase 3: Listing Module 📋 Later
When modifying Listing features:
1. Create `Listing/` directory structure
2. Move Listing entity, repository, DTOs
3. Split into use-case services
4. Create repository interface
5. Update ListingController

### Phase 4: ListingImage Module 📋 Later
Similar process for ListingImage

---

## 📐 Architecture Principles

### 1. Controllers (Thin)
```php
#[Route('/api/listings', name: 'api_listings_')]
class ListingController extends AbstractController
{
    public function __construct(
        private readonly CreateListingService $createListing
    ) {}

    #[Route('', methods: ['POST'])]
    public function create(
        #[MapRequestPayload] CreateListingRequestDTO $request
    ): JsonResponse {
        $listing = $this->createListing->execute($request);
        $response = ListingResponseDTO::fromEntity($listing);
        
        return $this->json($response, 201);
    }
}
```

**Controller responsibilities:**
- Accept HTTP request
- Validate DTO (automatic via Symfony Validator)
- Call service
- Return JSON response

**Controllers must NOT:**
- Contain business logic
- Access repositories directly
- Handle exceptions manually (let ExceptionListener do it)

### 2. Services (Use-case oriented)

One service = One business action

```php
class CreateListingService
{
    public function __construct(
        private readonly ListingRepositoryInterface $repository,
        private readonly CurrentUserProvider $userProvider
    ) {}

    public function execute(CreateListingRequestDTO $dto): Listing
    {
        $user = $this->userProvider->getUserOrThrow();

        $listing = new Listing();
        $listing->setSeller($user);
        $listing->setTitle($dto->title);
        // ... business logic

        $this->repository->save($listing);

        return $listing;
    }
}
```

### 3. Repository Interfaces

```php
interface ListingRepositoryInterface
{
    public function save(Listing $listing): void;
    public function findById(int $id): ?Listing;
    public function findBySeller(int $sellerId): array;
    public function findActiveListings(int $limit = 20): array;
}
```

Benefits:
- Testable (mock repositories)
- Flexible (swap implementations)
- Clear contracts

### 4. DTOs (Data Transfer Objects)

**Request DTOs:**
```php
class CreateListingRequestDTO
{
    #[Assert\NotBlank]
    #[Assert\Length(min: 3, max: 255)]
    public string $title;

    #[Assert\NotBlank]
    #[Assert\Positive]
    public float $price;
}
```

**Response DTOs:**
```php
class ListingResponseDTO
{
    public static function fromEntity(Listing $listing): self
    {
        $dto = new self();
        $dto->id = $listing->getId();
        $dto->title = $listing->getTitle();
        // ...
        return $dto;
    }
}
```

---

## 🧪 Testing Strategy

### Unit Tests
- Test services in isolation
- Mock repository interfaces
- Test business logic

### Integration Tests
- Test controller → service → database flow
- Use test database
- Test API endpoints

---

## 📚 Learning Resources

**Symfony Best Practices:**
- [Symfony Architecture Best Practices](https://symfony.com/doc/current/best_practices.html)
- [Domain-Driven Design in Symfony](https://symfony.com/doc/current/components/messenger.html)

**Clean Architecture:**
- Robert C. Martin - Clean Architecture
- Hexagonal Architecture (Ports & Adapters)

---

## 🎓 Learning Goals

This architecture teaches:
- ✅ Separation of concerns
- ✅ Dependency injection
- ✅ Interface-based programming
- ✅ Use-case driven design
- ✅ Clean code principles
- ✅ Testable code structure

---

## ⚠️ Important Notes

1. **Backward Compatibility:**
   - Old namespace aliases maintained in `services.yaml`
   - Gradual migration prevents breaking changes

2. **No Rush:**
   - Migrate one feature at a time
   - Test thoroughly after each migration
   - Keep existing code working

3. **Future-Proof:**
   - New features should use new structure
   - Old features migrate when modified
   - Clean architecture emerges gradually

