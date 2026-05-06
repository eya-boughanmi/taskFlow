<?php

namespace App\Service;

use App\Entity\Projet;
use App\Repository\TacheRepository;

class ProjetStatsCalculator
{
    public function __construct(private TacheRepository $tacheRepository) {}

    public function getProgressPercentage(Projet $projet): int
    {
        $taches = $projet->getTaches();
        $total = count($taches);

        if ($total === 0) return 0;

        $done = 0;
        foreach ($taches as $t) {
            if ($t->getStatut() === 'terminee') {
                $done++;
            }
        }

        return (int)(($done / $total) * 100);
    }

    public function getTaskCountByStatus(Projet $projet): array
    {
        $result = [
            'a_faire' => 0,
            'en_cours' => 0,
            'terminee' => 0
        ];

        foreach ($projet->getTaches() as $t) {
            $result[$t->getStatut()]++;
        }

        return $result;
    }

    public function isOverdue(Projet $projet): bool
    {
        $today = new \DateTime();

        if ($projet->getDateLimite() < $today) {
            foreach ($projet->getTaches() as $t) {
                if ($t->getStatut() !== 'terminee') {
                    return true;
                }
            }
        }

        return false;
    }

    public function getRemainingDays(Projet $projet): int
    {
        $today = new \DateTime();
        $interval = $today->diff($projet->getDateLimite());

        return (int)$interval->format('%r%a');
    }
}