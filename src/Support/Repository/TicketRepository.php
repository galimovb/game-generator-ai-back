<?php

namespace App\Support\Repository;

use App\Support\Entity\Ticket;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Ticket>
 */
class TicketRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Ticket::class);
    }

    //    /**
    //     * @return Ticket[] Returns an array of Ticket objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('t')
    //            ->andWhere('t.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('t.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Ticket
    //    {
    //        return $this->createQueryBuilder('t')
    //            ->andWhere('t.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

    public function findWithSearch(array $criteria, ?string $search, int $limit, int $offset): array
    {
        if (null !== $search) {
            $search = '%'.trim(preg_replace('/\s+/', ' ', $search)).'%';
        }

        $qb = $this->createQueryBuilder('t');

        foreach ($criteria as $field => $value) {
            $qb->andWhere("t.$field = :$field")->setParameter($field, $value);
        }

        if ($search) {
            $qb->andWhere('t.subject LIKE :search OR t.description LIKE :search')
                ->setParameter('search', "%$search%");
        }

        $items = $qb->orderBy('t.createdAt', 'DESC')
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        $countQb = $this->createQueryBuilder('t')
            ->select('COUNT(t.id)');

        foreach ($criteria as $field => $value) {
            $countQb->andWhere("t.$field = :$field")->setParameter($field, $value);
        }

        if ($search) {
            $countQb->andWhere('t.subject LIKE :search OR t.description LIKE :search')
                ->setParameter('search', "%$search%");
        }

        $total = (int) $countQb->getQuery()->getSingleScalarResult();

        return ['items' => $items, 'total' => $total];
    }
}
