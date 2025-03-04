<?php
// File: src/Repository/PublicationRepository.php

namespace App\Repository;

use App\Entity\Publication;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Publication>
 *
 * @method Publication|null find($id, $lockMode = null, $lockVersion = null)
 * @method Publication|null findOneBy(array $criteria, array $orderBy = null)
 * @method Publication[]    findAll()
 * @method Publication[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PublicationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Publication::class);
    }

    public function save(Publication $publication, bool $flush = false): void
    {
        $this->getEntityManager()->persist($publication);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Publication $publication, bool $flush = false): void
    {
        $this->getEntityManager()->remove($publication);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Returns an array of Publication objects filtered by title keyword.
     *
     * @return Publication[]
     */
    public function findByExampleField($value): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.titre LIKE :val')
            ->setParameter('val', '%' . $value . '%')
            ->orderBy('p.date_act', 'DESC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult();
    }

    /**
     * Returns all publications along with their associated comments.
     *
     * @return Publication[]
     */
    public function findPublicationsWithComments(): array
    {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.commantaires', 'c')
            ->addSelect('c')
            ->orderBy('p.date_act', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
