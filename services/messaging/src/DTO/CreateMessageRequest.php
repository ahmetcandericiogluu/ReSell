<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class CreateMessageRequest
{
    #[Assert\NotBlank(message: 'content is required')]
    #[Assert\Length(min: 1, max: 5000, maxMessage: 'Message cannot exceed 5000 characters')]
    public string $content;
}

