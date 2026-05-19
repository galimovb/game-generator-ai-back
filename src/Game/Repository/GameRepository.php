<?php

namespace App\Game\Repository;

use App\Game\DTO\Request\GameListFilters;
use App\Game\Entity\Game;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Game>
 */
class GameRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Game::class);
    }

    //    /**
    //     * @return Game[] Returns an array of Game objects
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

    //    public function findOneBySomeField($value): ?Game
    //    {
    //        return $this->createQueryBuilder('g')
    //            ->andWhere('g.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

    public function findPublicGamesWithFilters(GameListFilters $filters): array
    {
        $qb = $this->createQueryBuilder('g')
            ->where('g.isPublic = :isPublic')
            ->setParameter('isPublic', true);

        // Фильтры
        if ($filters->minAge !== null) {
            $qb->andWhere('g.age >= :minAge')->setParameter('minAge', $filters->minAge);
        }
        if ($filters->maxAge !== null) {
            $qb->andWhere('g.age <= :maxAge')->setParameter('maxAge', $filters->maxAge);
        }
        if ($filters->locationType !== null) {
            $qb->andWhere('g.locationType = :locationType')->setParameter('locationType', $filters->locationType);
        }
        if ($filters->activityLevel !== null) {
            $qb->andWhere('g.activityLevel = :activityLevel')->setParameter('activityLevel', $filters->activityLevel);
        }
        if ($filters->minPlayers !== null) {
            $qb->andWhere('g.players >= :minPlayers')->setParameter('minPlayers', $filters->minPlayers);
        }
        if ($filters->maxPlayers !== null) {
            $qb->andWhere('g.players <= :maxPlayers')->setParameter('maxPlayers', $filters->maxPlayers);
        }

        // Сортировка
        $sortBy = in_array($filters->sortBy, ['createdAt', 'updatedAt', 'age', 'players', 'duration'])
            ? $filters->sortBy
            : 'createdAt';
        $qb->orderBy("g.{$sortBy}", $filters->sortOrder === 'ASC' ? 'ASC' : 'DESC');

        // Пагинация
        $qb->setFirstResult(($filters->page - 1) * $filters->limit)
            ->setMaxResults($filters->limit);

        $query = $qb->getQuery();

        return [
            'items' => $query->getResult(),
            'total' => $this->countPublicGamesWithFilters($filters)
        ];
    }

    private function countPublicGamesWithFilters(GameListFilters $filters): int
    {
        $qb = $this->createQueryBuilder('g')
            ->select('COUNT(g.id)')
            ->where('g.isPublic = :isPublic')
            ->setParameter('isPublic', true);

        // Повторяем те же фильтры
        if ($filters->minAge !== null) {
            $qb->andWhere('g.age >= :minAge')->setParameter('minAge', $filters->minAge);
        }
        if ($filters->maxAge !== null) {
            $qb->andWhere('g.age <= :maxAge')->setParameter('maxAge', $filters->maxAge);
        }
        if ($filters->locationType !== null) {
            $qb->andWhere('g.locationType = :locationType')->setParameter('locationType', $filters->locationType);
        }
        if ($filters->activityLevel !== null) {
            $qb->andWhere('g.activityLevel = :activityLevel')->setParameter('activityLevel', $filters->activityLevel);
        }
        if ($filters->minPlayers !== null) {
            $qb->andWhere('g.players >= :minPlayers')->setParameter('minPlayers', $filters->minPlayers);
        }
        if ($filters->maxPlayers !== null) {
            $qb->andWhere('g.players <= :maxPlayers')->setParameter('maxPlayers', $filters->maxPlayers);
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    public function findTopLikedGames(int $page, int $limit): array
    {
        $items = $this->createQueryBuilder('g')
            ->select('g', 'COUNT(l.id) AS HIDDEN likesCount')
            ->leftJoin('g.likes', 'l')
            ->where('g.isPublic = :isPublic')
            ->setParameter('isPublic', true)
            ->groupBy('g.id')
            ->orderBy('likesCount', 'DESC')
            ->addOrderBy('g.createdAt', 'DESC')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        $total = (int) $this->createQueryBuilder('g')
            ->select('COUNT(g.id)')
            ->where('g.isPublic = :isPublic')
            ->setParameter('isPublic', true)
            ->getQuery()
            ->getSingleScalarResult();

        return ['items' => $items, 'total' => $total];
    }
}
