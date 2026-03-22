<?php

namespace App\Repository;

use App\Contracts\Repository\GameLikeRepositoryInterface;
use App\Entity\GameLike;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<GameLike>
 */
class GameLikeRepository extends ServiceEntityRepository implements GameLikeRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GameLike::class);
    }

    /**
     * Найти лайк по ID
     */
    public function findById(int $id): ?GameLike
    {
        return $this->find($id);
    }

    /**
     * Найти лайк пользователя к конкретной игре
     */
    public function findByUserAndGame(User $user, int $gameId): ?GameLike
    {
        return $this->createQueryBuilder('gl')
            ->where('gl.author = :user')
            ->andWhere('gl.game = :gameId')
            ->setParameter('user', $user)
            ->setParameter('gameId', $gameId)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Получить ID игр, которые лайкнул пользователь
     * 
     * @return int[]
     */
    public function findLikedGameIds(User $user, array $gameIds): array
    {
        if (empty($gameIds)) {
            return [];
        }

        $result = $this->createQueryBuilder('gl')
            ->select('IDENTITY(gl.game) as gameId')
            ->where('gl.author = :user')
            ->andWhere('gl.game IN (:gameIds)')
            ->setParameter('user', $user)
            ->setParameter('gameIds', $gameIds)
            ->getQuery()
            ->getScalarResult();

        return array_column($result, 'gameId');
    }

    /**
     * Получить игры, которые лайкнул пользователь, с пагинацией
     * 
     * @return array{items: GameLike[], total: int}
     */
    public function findByUser(User $user, int $page, int $limit): array
    {
        $items = $this->createQueryBuilder('gl')
            ->where('gl.author = :user')
            ->setParameter('user', $user)
            ->orderBy('gl.createdAt', 'DESC')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        $total = $this->count(['author' => $user]);

        return [
            'items' => $items,
            'total' => $total
        ];
    }

    /**
     * Сохранить лайк
     */
    public function save(GameLike $like): void
    {
        $this->getEntityManager()->persist($like);
        $this->getEntityManager()->flush();
    }

    /**
     * Удалить лайк
     */
    public function remove(GameLike $like): void
    {
        $this->getEntityManager()->remove($like);
        $this->getEntityManager()->flush();
    }

    /**
     * Подсчитать количество лайков пользователя
     */
    public function countByUser(User $user): int
    {
        return $this->count(['author' => $user]);
    }
}
