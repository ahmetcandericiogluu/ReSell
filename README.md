# ReSell - İkinci El Alışveriş Platformu

Symfony 7.3 ile geliştirilmiş modern ikinci el ürün satış platformu.

## Gereksinimler

- PHP 8.2+
- PostgreSQL 16+
- Composer

## Kurulum

```bash
# Bağımlılıkları yükle
composer install

# .env.local oluştur
cp .env .env.local
# DATABASE_URL'i düzenle

# Veritabanı migration'larını çalıştır
php bin/console doctrine:migrations:migrate

# Sunucuyu başlat
symfony serve
```

## Render Deployment

Proje, Render.com'da otomatik deploy için yapılandırılmıştır.

1. GitHub'a push yapın
2. Render dashboard'da "New +" → "Blueprint" seçin
3. Repository'nizi seçin
4. `render.yaml` otomatik algılanacaktır

## Teknolojiler

- Symfony 7.3
- PostgreSQL
- Doctrine ORM
- Twig
- Stimulus & Turbo

