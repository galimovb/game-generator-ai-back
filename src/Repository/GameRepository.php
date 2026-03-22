<?php

namespace App\Repository;

use App\Contracts\Repository\GameRepositoryInterface;
use App\Entity\Game;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Game>
 */
class GameRepository extends ServiceEntityRepository implements GameRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Game::class);
    }

    /**
     * Найти игру по ID
     */
    public function findById(int $id): ?Game
    {
        return $this->find($id);
    }

    /**
     * Найти игру по ID с подгрузкой этапов
     */
    public function findWithStages(int $id): ?Game
    {
        return $this->createQueryBuilder('g')
            ->leftJoin('g.stages', 's')
            ->addSelect('s')
            ->where('g.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Получить публичные игры с пагинацией
     * 
     * @return array{items: Game[], total: int}
     */
    public function findPublicGames(int $page, int $limit): array
    {
        $items = $this->createQueryBuilder('g')
            ->where('g.isPublic = true')
            ->orderBy('g.createdAt', 'DESC')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        $total = $this->count(['isPublic' => true]);

        return [
            'items' => $items,
            'total' => $total
        ];
    }

    /**
     * Получить игры пользователя с пагинацией
     * 
     * @return array{items: Game[], total: int}
     */
    public function findByAuthor(User $author, int $page, int $limit): array
    {
        $items = $this->createQueryBuilder('g')
            ->where('g.author = :author')
            ->setParameter('author', $author)
            ->orderBy('g.createdAt', 'DESC')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        $total = $this->count(['author' => $author]);

        return [
            'items' => $items,
            'total' => $total
        ];
    }

    /**
     * Сохранить игру
     */
    public function save(Game $game): void
    {
        $this->getEntityManager()->persist($game);
        $this->getEntityManager()->flush();
    }

    /**
     * Удалить игру
     */
    public function remove(Game $game): void
    {
        $this->getEntityManager()->remove($game);
        $this->getEntityManager()->flush();
    }

    /**
     * Подсчитать количество публичных игр
     */
    public function countPublicGames(): int
    {
        return $this->count(['isPublic' => true]);
    }

    /**
     * Подсчитать количество игр пользователя
     */
    public function countByAuthor(User $author): int
    {
        return $this->count(['author' => $author]);
    }
}
