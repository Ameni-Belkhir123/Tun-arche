<?php

namespace App\Repository;

use App\Entity\Evaluation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Evaluation>
 */
class EvaluationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Evaluation::class);
    }

    /**
     * Returns an array of evaluations for a given formation.
     *
     * @param mixed $formation
     * @return Evaluation[]
     */
    public function findByFormation($formation): array
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.formation = :formation')
            ->setParameter('formation', $formation)
            ->orderBy('e.id', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Returns an array of evaluations for a given user.
     *
     * @param mixed $user
     * @return Evaluation[]
     */
    public function findByUser($user): array
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.user = :user')
            ->setParameter('user', $user)
            ->orderBy('e.id', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
