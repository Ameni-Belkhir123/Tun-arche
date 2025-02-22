<?php

namespace App\Repository;

use App\Entity\Formation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Formation>
 */
class FormationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Formation::class);
    }

    //    /**
    //     * @return Formation[] Returns an array of Formation objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('f')
    //            ->andWhere('f.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('f.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Formation
    //    {
    //        return $this->createQueryBuilder('f')
    //            ->andWhere('f.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
    public function searchFormations(?string $query, ?string $sortBy): array
{
    $qb = $this->createQueryBuilder('f');

    if ($query) {
        $qb->andWhere('f.titre LIKE :query OR f.description LIKE :query')
           ->setParameter('query', '%' . $query . '%');
    }

    if ($sortBy === 'datedebut') {
        $qb->orderBy('f.datedebut', 'ASC');
    } elseif ($sortBy === 'nbrplaces') {
        $qb->orderBy('f.nbrplaces', 'ASC');
    }

    return $qb->getQuery()->getResult();
}
}
