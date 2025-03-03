<?php

namespace App\Repository;

use App\Entity\Participation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Concours;

/**
 * @extends ServiceEntityRepository<Participation>
 */
class ParticipationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Participation::class);
    }

    //    /**
    //     * @return Participation[] Returns an array of Participation objects
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

    //    public function findOneBySomeField($value): ?Participation
    //    {
    //        return $this->createQueryBuilder('f')
    //            ->andWhere('f.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
    public function findByConcoursAndVotes(Concours $concours, ?int $minVotes = null, ?string $sortBy = null): array
    {
        $qb = $this->createQueryBuilder('p')
            ->andWhere('p.concours = :concours')
            ->setParameter('concours', $concours);

        // Filtrer par nombre minimum de votes
        if ($minVotes !== null) {
            $qb->andWhere('p.nbrVotes >= :minVotes')
                ->setParameter('minVotes', $minVotes);
        }

        // Trier les résultats
        if ($sortBy === 'votes_asc') {
            $qb->orderBy('p.nbrVotes', 'ASC');
        } elseif ($sortBy === 'votes_desc') {
            $qb->orderBy('p.nbrVotes', 'DESC');
        }

        return $qb->getQuery()->getResult();
    }
}
