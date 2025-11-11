<?php

namespace App\Repository;

use App\Entity\Panier;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Panier>
 */
class PanierRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Panier::class);
    }

    public function findPanierActifByUser(User $user): ?Panier
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.user = :user')
            ->andWhere('p.modePanier = 0')
            ->setParameter('user', $user)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findCommandesByUser(User $user): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.user = :user')
            ->andWhere('p.modePanier = 1')
            ->orderBy('p.dateCmde', 'DESC')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();
    }

    /**
     * Retourne toutes les commandes (modePanier = true)
     * avec le montant total calculé par la base.
     */
    public function findCommandesAvecTotal(): array
    {
        $qb = $this->createQueryBuilder('p')
            ->select('
                p.id AS idCommande,
                p.dateCmde AS dateCommande,
                SUM(lp.quantite * pr.prix) AS montantTotal
            ')
            ->leftJoin('p.lignePaniers', 'lp')
            ->leftJoin('lp.produit', 'pr')
            ->andWhere('p.modePanier = :val')
            ->setParameter('val', true)
            ->groupBy('p.id, p.dateCmde')
            ->orderBy('p.id', 'ASC')
            ->addOrderBy('p.dateCmde', 'DESC');

        return $qb->getQuery()->getResult();
    }

}
