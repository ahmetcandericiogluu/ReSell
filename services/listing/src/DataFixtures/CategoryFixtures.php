<?php

namespace App\DataFixtures;

use App\Entity\Category;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class CategoryFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $categories = [
            ['name' => 'Elektronik', 'slug' => 'elektronik'],
            ['name' => 'Moda & Giyim', 'slug' => 'moda-giyim'],
            ['name' => 'Ev & Yaşam', 'slug' => 'ev-yasam'],
            ['name' => 'Araç & Motorsiklet', 'slug' => 'arac-motorsiklet'],
            ['name' => 'Kitap & Hobi', 'slug' => 'kitap-hobi'],
            ['name' => 'Spor & Outdoor', 'slug' => 'spor-outdoor'],
            ['name' => 'Evcil Hayvan', 'slug' => 'evcil-hayvan'],
            ['name' => 'Diğer', 'slug' => 'diger'],
        ];

        foreach ($categories as $categoryData) {
            $category = new Category();
            $category->setName($categoryData['name']);
            $category->setSlug($categoryData['slug']);
            $manager->persist($category);
        }

        $manager->flush();
    }
}

