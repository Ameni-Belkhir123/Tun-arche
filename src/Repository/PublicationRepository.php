<?php

namespace App\Repository;
use App\Entity\Commantaire;
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
     * @return Publication[] Returns an array of Publication objects
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
 * @return Commantaire[] Returns an array of Commentaire objects for a specific publication
 */
public function findCommentairesByPublication(Publication $publication): array
{
    return $this->createQueryBuilder('p')
        ->select('c') // Sélectionner les commentaires
        ->join('p.commantaires', 'c') // Joindre la relation avec les commentaires
        ->andWhere('p.id = :pubId')
        ->setParameter('pubId', $publication->getId())
        ->orderBy('c.date_creation', 'DESC') // Trier par date de création (le plus récent en premier)
        ->getQuery()
        ->getResult();
}
    
}
