<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class CreateConversationRequest
{
    #[Assert\NotBlank(message: 'listing_id is required')]
    #[Assert\Positive(message: 'listing_id must be a positive integer')]
    public int $listing_id;
}

