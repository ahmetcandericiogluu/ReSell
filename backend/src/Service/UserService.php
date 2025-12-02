<?php

namespace App\Service;

use App\DTO\Auth\LoginRequest;
use App\DTO\Auth\RegisterRequest;
use App\DTO\User\UpdateProfileRequest;
use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserService
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly UserPasswordHasherInterface $passwordHasher
    ) {
    }

    public function register(RegisterRequest $request): User
    {
        // Check if user already exists
        $existingUser = $this->userRepository->findByEmail($request->email);
        if ($existingUser) {
            throw new ConflictHttpException('Bu e-posta adresi ile kayıtlı kullanıcı zaten mevcut');
        }

        // Create new user
        $user = new User();
        $user->setEmail($request->email);
        $user->setName($request->name);
        
        if ($request->phone) {
            $user->setPhone($request->phone);
        }
        
        if ($request->city) {
            $user->setCity($request->city);
        }

        // Hash password
        $hashedPassword = $this->passwordHasher->hashPassword($user, $request->password);
        $user->setPassword($hashedPassword);

        // Persist user
        $this->userRepository->save($user, true);

        return $user;
    }

    public function login(LoginRequest $request): User
    {
        // Find user by email
        $user = $this->userRepository->findByEmail($request->email);
        if (!$user) {
            throw new UnauthorizedHttpException('', 'Geçersiz e-posta veya şifre');
        }

        // Verify password
        if (!$this->passwordHasher->isPasswordValid($user, $request->password)) {
            throw new UnauthorizedHttpException('', 'Geçersiz e-posta veya şifre');
        }

        return $user;
    }

    public function updateProfile(User $user, UpdateProfileRequest $request): User
    {
        if ($request->name !== null) {
            $user->setName($request->name);
        }

        if ($request->city !== null) {
            $user->setCity($request->city);
        }

        if ($request->phone !== null) {
            $user->setPhone($request->phone);
        }

        $this->userRepository->save($user, true);

        return $user;
    }
}

