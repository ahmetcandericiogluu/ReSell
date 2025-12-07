<?php

namespace App\DTO\Listing;

use App\Entity\Category;

class CategoryResponse
{
    public int $id;
    public string $name;
    public string $slug;
    public ?int $parentId = null;

    public static function fromEntity(Category $category): self
    {
        $response = new self();
        $response->id = $category->getId();
        $response->name = $category->getName();
        $response->slug = $category->getSlug();
        $response->parentId = $category->getParent()?->getId();
        
        return $response;
    }
}

