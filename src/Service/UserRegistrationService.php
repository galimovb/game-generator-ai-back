<?php

namespace App\Service;

use App\DTO\request\RegisterUserRequest;
use App\Entity\User;
use App\Enum\ErrorCode;
use App\Exception\ApiException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final readonly class UserRegistrationService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher,
        private ValidatorInterface $validator,
    ) {}

    public function register(RegisterUserRequest $request): User
    {
        $violations = $this->validator->validate($request);
        if ($violations->count() > 0) {
            throw new ApiException(ErrorCode::VALIDATION_FAILED);
        }

        $existing = $this->entityManager
            ->getRepository(User::class)
            ->findOneBy(['email' => $request->email]);

        if ($existing) {
            throw new ApiException(ErrorCode::EMAIL_EXIST);
        }

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