<?php
// src/Repository/FormationParticipationRepository.php

namespace App\Repository;

use App\Entity\FormationParticipation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<FormationParticipation>
 */
class FormationParticipationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FormationParticipation::class);
    }

    /**
     * Finds an existing formation participation for the given user and formation.
     *
     * @param mixed $user
     * @param mixed $formation
     * @return FormationParticipation|null
     */
    public function findOneByUserAndFormation($user, $formation): ?FormationParticipation
    {
        return $this->createQueryBuilder('fp')
            ->andWhere('fp.user = :user')
            ->andWhere('fp.formation = :formation')
            ->setParameter('user', $user)
            ->setParameter('formation', $formation)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
