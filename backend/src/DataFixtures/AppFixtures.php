<?php

namespace App\DataFixtures;

use App\User\Entity\User;
use App\Listing\Entity\Listing;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private const CITIES = [
        'İstanbul', 'Ankara', 'İzmir', 'Bursa', 'Antalya', 
        'Adana', 'Konya', 'Gaziantep', 'Kocaeli', 'Mersin',
        'Eskişehir', 'Diyarbakır', 'Samsun', 'Kayseri', 'Trabzon'
    ];

    private const CATEGORIES = [
        'Elektronik', 'Mobilya', 'Giyim', 'Kitap', 'Spor',
        'Oyuncak', 'Ev Eşyası', 'Bahçe', 'Otomotiv', 'Hobi'
    ];

    private const PRODUCTS = [
        // Elektronik
        ['iPhone 12 128GB', 'Temiz kullanılmış iPhone 12, ekran koruyucu ve kılıf ile birlikte. Hiç çizik yok, batarya sağlığı %92.', 15000, 20000, 'TRY'],
        ['MacBook Air M1', 'Az kullanılmış MacBook Air, 8GB RAM 256GB SSD. Kılıf ve mouse hediye.', 18000, 25000, 'TRY'],
        ['Samsung 55" Smart TV', '2022 model Samsung Smart TV, hiç kullanılmadı. Kutusunda duruyor.', 8000, 12000, 'TRY'],
        ['Sony WH-1000XM4 Kulaklık', 'Noise cancelling kulaklık, çok az kullanıldı. Tüm aksesuarlar mevcut.', 3500, 5000, 'TRY'],
        ['iPad Pro 11" 2021', 'Apple Pencil ve Magic Keyboard ile birlikte. 256GB.', 12000, 16000, 'TRY'],
        ['Canon EOS 200D Fotoğraf Makinesi', '18-55mm lens ile birlikte, az kullanılmış durumda.', 7000, 9000, 'TRY'],
        ['PlayStation 5', 'Orijinal kutusunda, 2 kol ve 3 oyun hediye.', 12000, 15000, 'TRY'],
        ['AirPods Pro 2. Nesil', 'Yeni gibi, 2 ay kullanıldı. Garantisi devam ediyor.', 4000, 5500, 'TRY'],
        
        // Mobilya
        ['İkea Koltuk Takımı', '3+2+1 koltuk takımı, gri renk. Temiz ve bakımlı.', 8000, 12000, 'TRY'],
        ['Çalışma Masası ve Sandalye', 'Ahşap çalışma masası ve ergonomik sandalye seti.', 3000, 5000, 'TRY'],
        ['Yemek Masası 6 Kişilik', 'Masif ahşap yemek masası, 6 sandalye ile birlikte.', 5000, 8000, 'TRY'],
        ['Dolap (Gardrop)', '3 kapılı sürgülü dolap, aynalı, temiz durumda.', 4000, 6000, 'TRY'],
        ['Kitaplık', 'Beyaz kitaplık, 5 raflı, modern tasarım.', 1500, 2500, 'TRY'],
        
        // Giyim
        ['Deri Ceket Erkek', 'Siyah deri ceket, M beden, hiç kullanılmadı.', 1200, 2000, 'TRY'],
        ['Nike Air Max Ayakkabı', '42 numara, orijinal, çok az giyildi.', 800, 1200, 'TRY'],
        ['Kış Montu North Face', 'Kadın L beden, sıfır ayarında.', 2000, 3000, 'TRY'],
        
        // Kitap
        ['Harry Potter Seti', 'Tüm seriler, İngilizce orijinal baskı.', 500, 800, 'TRY'],
        ['Programlama Kitapları', 'Clean Code, Design Patterns gibi 10 adet kitap.', 600, 1000, 'TRY'],
        
        // Spor
        ['Fitness Bisikleti', 'Evde kullanım için fitness bisikleti, çok az kullanıldı.', 2500, 4000, 'TRY'],
        ['Yoga Matı ve Ekipmanları', 'Yoga matı, blok ve kayış seti.', 300, 500, 'TRY'],
        ['Koşu Bandı', 'Elektrikli koşu bandı, çalışır durumda.', 3000, 5000, 'TRY'],
        
        // Ev Eşyası
        ['Vestel Buzdolabı', 'No-frost buzdolabı, A++ enerji sınıfı.', 5000, 7000, 'TRY'],
        ['Arçelik Çamaşır Makinesi', '9 kg, az kullanılmış, garantili.', 4000, 6000, 'TRY'],
        ['Tefal Cookware Set', '12 parça tencere seti, granit kaplama.', 1200, 1800, 'TRY'],
        ['Dyson Süpürge', 'Kablosuz süpürge, tüm aksesuarlarıyla birlikte.', 3500, 5000, 'TRY'],
        
        // Hobi
        ['Gitar Fender', 'Elektro gitar, amplifikatör hediye.', 4000, 6000, 'TRY'],
        ['Drone DJI Mini 2', 'Kamera drone, 4K çekim yapıyor.', 5000, 7000, 'TRY'],
        ['Akvaryum Seti', '100 litre akvaryum, filtre ve ışıklandırma ile.', 1500, 2500, 'TRY'],
    ];

    private const FIRST_NAMES = [
        'Ahmet', 'Mehmet', 'Ayşe', 'Fatma', 'Ali', 'Zeynep', 
        'Mustafa', 'Elif', 'Can', 'Deniz', 'Ece', 'Burak',
        'Selin', 'Emre', 'Merve', 'Kerem', 'İrem', 'Oğuz'
    ];

    private const LAST_NAMES = [
        'Yılmaz', 'Kaya', 'Demir', 'Şahin', 'Çelik', 'Yıldız',
        'Öztürk', 'Aydın', 'Arslan', 'Koç', 'Kurt', 'Özdemir'
    ];

    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        // Create test users
        $users = $this->createUsers($manager);
        
        // Create listings
        $this->createListings($manager, $users);

        $manager->flush();
    }

    private function createUsers(ObjectManager $manager): array
    {
        $users = [];

        // Create main test user
        $mainUser = new User();
        $mainUser->setEmail('test@resell.com');
        $mainUser->setName('Test Kullanıcı');
        $mainUser->setPhone('0555 123 4567');
        $mainUser->setCity('İstanbul');
        $mainUser->setPassword($this->passwordHasher->hashPassword($mainUser, 'test123'));
        $manager->persist($mainUser);
        $users[] = $mainUser;

        // Create 10 random users
        for ($i = 1; $i <= 10; $i++) {
            $firstName = self::FIRST_NAMES[array_rand(self::FIRST_NAMES)];
            $lastName = self::LAST_NAMES[array_rand(self::LAST_NAMES)];
            $name = $firstName . ' ' . $lastName;
            
            $user = new User();
            $user->setEmail(strtolower($firstName) . '.' . strtolower($lastName) . $i . '@example.com');
            $user->setName($name);
            $user->setPhone('05' . rand(10, 99) . ' ' . rand(100, 999) . ' ' . rand(1000, 9999));
            $user->setCity(self::CITIES[array_rand(self::CITIES)]);
            $user->setPassword($this->passwordHasher->hashPassword($user, 'password'));
            
            $manager->persist($user);
            $users[] = $user;
        }

        return $users;
    }

    private function createListings(ObjectManager $manager, array $users): void
    {
        $statuses = ['active', 'active', 'active', 'active', 'sold', 'draft']; // More active listings

        // Create 50 listings
        for ($i = 0; $i < 50; $i++) {
            $product = self::PRODUCTS[array_rand(self::PRODUCTS)];
            
            $listing = new Listing();
            $listing->setSeller($users[array_rand($users)]);
            $listing->setTitle($product[0]);
            $listing->setDescription($product[1]);
            
            // Random price within range
            $minPrice = $product[2];
            $maxPrice = $product[3];
            $price = rand($minPrice, $maxPrice);
            $listing->setPrice((string) $price);
            
            $listing->setCurrency($product[4]);
            $listing->setLocation(self::CITIES[array_rand(self::CITIES)]);
            $listing->setStatus($statuses[array_rand($statuses)]);
            
            // Random category (1-10)
            if (rand(0, 1)) {
                $listing->setCategoryId(rand(1, 10));
            }

            $manager->persist($listing);
        }
    }
}
