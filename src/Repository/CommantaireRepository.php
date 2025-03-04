<?php
// File: src/Repository/CommantaireRepository.php

namespace App\Repository;

use App\Entity\Commantaire;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Commantaire>
 *
 * @method Commantaire|null find($id, $lockMode = null, $lockVersion = null)
 * @method Commantaire|null findOneBy(array $criteria, array $orderBy = null)
 * @method Commantaire[]    findAll()
 * @method Commantaire[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CommantaireRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Commantaire::class);
    }

    public function save(Commantaire $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Commantaire $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Returns an array of comments for a specific publication.
     *
     * @return Commantaire[]
     */
    public function findCommentairesByPublication($publicationId): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.id_pub = :pubId')
            ->setParameter('pubId', $publicationId)
            ->orderBy('c.date', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
