<?php

namespace App\Repository;

use App\Contracts\Repository\UserRepositoryInterface;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository implements UserRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Найти пользователя по ID
     */
    public function findById(int $id): ?User
    {
        return $this->find($id);
    }

    /**
     * Найти пользователя по email
     */
    public function findByEmail(string $email): ?User
    {
        return $this->findOneBy(['email' => $email]);
    }

    /**
     * Найти пользователя по логину
     */
    public function findByLogin(string $login): ?User
    {
        return $this->findOneBy(['login' => $login]);
    }

    /**
     * Проверить существование email
     */
    public function emailExists(string $email): bool
    {
        return $this->count(['email' => $email]) > 0;
    }

    /**
     * Проверить существование логина
     */
    public function loginExists(string $login): bool
    {
        return $this->count(['login' => $login]) > 0;
    }

    /**
     * Сохранить пользователя
     */
    public function save(User $user): void
    {
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    /**
     * Удалить пользователя
     */
    public function remove(User $user): void
    {
        $this->getEntityManager()->remove($user);
        $this->getEntityManager()->flush();
    }
}
