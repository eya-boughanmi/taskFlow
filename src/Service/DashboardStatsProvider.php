<?php

namespace App\Service;

use App\Repository\ProjetRepository;
use App\Repository\TacheRepository;
use App\Repository\UserRepository;

/**
 * Agrégations légères (COUNT) pour le tableau de bord — évite de charger toutes les entités.
 */
final class DashboardStatsProvider
{
    public function __construct(
        private UserRepository $userRepository,
        private ProjetRepository $projetRepository,
        private TacheRepository $tacheRepository,
    ) {
    }

    /**
     * @return array{
     *   users_total: int|null,
     *   projets_total: int,
     *   taches_total: int,
     *   taches_par_statut: array<string, int>,
     *   projets_par_statut: array<string, int>,
     *   top_projets_taches: list<array{id: int, nom: string, taches: int}>
     * }
     */
    public function getStats(bool $canSeeUserCount): array
    {
        $projetsTotal = (int) $this->projetRepository->createQueryBuilder('p')
            ->select('COUNT(p.id)')
            ->getQuery()
            ->getSingleScalarResult();

        $tachesTotal = (int) $this->tacheRepository->createQueryBuilder('t')
            ->select('COUNT(t.id)')
            ->getQuery()
            ->getSingleScalarResult();

        $tachesParStatut = $this->countTachesByStatut();
        $projetsParStatut = $this->countProjetsByStatut();
        $topProjets = $this->topProjetsByTaskCount(5);

        $usersTotal = null;
        if ($canSeeUserCount) {
            $usersTotal = (int) $this->userRepository->createQueryBuilder('u')
                ->select('COUNT(u.id)')
                ->getQuery()
                ->getSingleScalarResult();
        }

        return [
            'users_total' => $usersTotal,
            'projets_total' => $projetsTotal,
            'taches_total' => $tachesTotal,
            'taches_par_statut' => $tachesParStatut,
            'projets_par_statut' => $projetsParStatut,
            'top_projets_taches' => $topProjets,
        ];
    }

    /** @return array<string, int> */
    private function countTachesByStatut(): array
    {
        $rows = $this->tacheRepository->createQueryBuilder('t')
            ->select('t.statut AS s, COUNT(t.id) AS c')
            ->groupBy('t.statut')
            ->getQuery()
            ->getArrayResult();

        $out = ['a_faire' => 0, 'en_cours' => 0, 'terminee' => 0];
        foreach ($rows as $row) {
            $s = $row['s'] ?? '';
            $out[$s] = (int) $row['c'];
        }

        return $out;
    }

    /** @return array<string, int> */
    private function countProjetsByStatut(): array
    {
        $rows = $this->projetRepository->createQueryBuilder('p')
            ->select('p.statut AS s, COUNT(p.id) AS c')
            ->groupBy('p.statut')
            ->getQuery()
            ->getArrayResult();

        $keys = ['planifie', 'en_cours', 'termine', 'annule'];
        $out = array_fill_keys($keys, 0);
        foreach ($rows as $row) {
            $s = $row['s'] ?? '';
            if (isset($out[$s])) {
                $out[$s] = (int) $row['c'];
            }
        }

        return $out;
    }

    /**
     * @return list<array{id: int, nom: string, taches: int}>
     */
    private function topProjetsByTaskCount(int $limit): array
    {
        $rows = $this->projetRepository->createQueryBuilder('p')
            ->select('p.id AS id', 'p.nom AS nom', 'COUNT(t.id) AS cnt')
            ->leftJoin('p.taches', 't')
            ->groupBy('p.id', 'p.nom')
            ->orderBy('COUNT(t.id)', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getArrayResult();

        return array_map(static fn (array $r): array => [
            'id' => (int) $r['id'],
            'nom' => (string) $r['nom'],
            'taches' => (int) $r['cnt'],
        ], $rows);
    }
}
