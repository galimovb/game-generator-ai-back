<?php

namespace App\User\Service;

use App\User\DTO\Request\RegisterUserRequest;
use App\User\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final readonly class UserRegistrationService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher,
        private UserService $userService,
    ) {}

    public function register(RegisterUserRequest $request): User
    {
        $this->userService->checkEmailUnique($request->email);
        $this->userService->checkLoginUnique($request->login);

        $user = new User();
        $user->setEmail($request->email);
        $user->setLogin($request->login);
        $user->setName($request->name);
        $user->setLastName($request->lastName);
        $user->setMiddleName($request->middleName);
        $user->setPassword($this->passwordHasher->hashPassword($user, $request->password));

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }
}