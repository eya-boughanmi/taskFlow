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

    // ── Méthode qui retourne un tableau (pour usage sans pagination) ──
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

    // ── Méthode qui retourne une Query (pour KnpPaginator) ──
    public function findByFiltersQuery(
        ?string $nom,
        ?string $statut,
        ?User $createur,
        ?Etiquette $etiquette
    ): \Doctrine\ORM\Query {
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
                  ->getQuery();
    }

    // ── Méthode pour les 5 projets les plus récents (session sidebar) ──
    public function findMostRecentProjects(int $limit = 5): array
    {
        return $this->createQueryBuilder('p')
            ->orderBy('p.dateCreation', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}