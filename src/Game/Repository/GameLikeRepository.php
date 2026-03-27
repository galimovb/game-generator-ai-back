<?php

namespace App\Game\Repository;

use App\Game\Entity\GameLike;
use App\User\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<GameLike>
 */
class GameLikeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GameLike::class);
    }

//    /**
//     * @return GameLike[] Returns an array of GameLike objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('g')
//            ->andWhere('g.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('g.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?GameLike
//    {
//        return $this->createQueryBuilder('g')
//            ->andWhere('g.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }

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
}
