<?php

namespace App\Repository;

use App\Entity\Projet;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\User;
use App\Entity\Etiquette;
/**
 * @extends ServiceEntityRepository<Projet>
 */
class ProjetRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Projet::class);
    }

    //    /**
    //     * @return Projet[] Returns an array of Projet objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('p.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Projet
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
    public function findByFilters(
    ?string $nom,
    ?string $statut,
    ?User $createur,
    ?Etiquette $etiquette
): array {

    $qb = $this->createQueryBuilder('p');

    if ($nom) {
        $qb->andWhere('LOWER(p.nom) LIKE LOWER(:nom)')
           ->setParameter('nom', '%' . $nom . '%');
    }

    if ($statut) {
        $qb->andWhere('p.statut = :statut')
           ->setParameter('statut', $statut);
    }

    if ($createur) {
        $qb->andWhere('p.createur = :createur')
           ->setParameter('createur', $createur);
    }

    if ($etiquette) {
        $qb->innerJoin('p.taches', 't')
           ->innerJoin('t.etiquettes', 'e')
           ->andWhere('e = :etiquette')
           ->setParameter('etiquette', $etiquette);
    }

    return $qb->orderBy('p.dateCreation', 'DESC')
              ->getQuery()
              ->getResult();
}
}
