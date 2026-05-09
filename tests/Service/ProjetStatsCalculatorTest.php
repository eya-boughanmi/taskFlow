<?php

namespace App\Tests\Service;

use App\Entity\Projet;
use App\Entity\Tache;
use App\Service\ProjetStatsCalculator;
use PHPUnit\Framework\TestCase;
use App\Repository\TacheRepository;

class ProjetStatsCalculatorTest extends TestCase
{
    private ProjetStatsCalculator $service;

    protected function setUp(): void
    {
        $repo = $this->createMock(TacheRepository::class);
        $this->service = new ProjetStatsCalculator($repo);
    }

    public function testProgressPercentageReturns0(): void
    {
        $projet = new Projet();
        $t1 = new Tache();
        $t1->setStatut('a_faire');
        $projet->getTaches()->add($t1);

        $this->assertEquals(0, $this->service->getProgressPercentage($projet));
    }

    public function testProgressPercentageReturns100(): void
    {
        $projet = new Projet();
        $t1 = new Tache();
        $t1->setStatut('terminee');
        $t2 = new Tache();
        $t2->setStatut('terminee');
        $projet->getTaches()->add($t1);
        $projet->getTaches()->add($t2);

        $this->assertEquals(100, $this->service->getProgressPercentage($projet));
    }

    public function testIsOverdueReturnsTrue(): void
    {
        $projet = new Projet();
        $projet->setDateLimite(new \DateTime('-2 days'));
        $t1 = new Tache();
        $t1->setStatut('en_cours');
        $projet->getTaches()->add($t1);

        $this->assertTrue($this->service->isOverdue($projet));
    }

    public function testRemainingDaysNegative(): void
    {
        $projet = new Projet();
        $projet->setDateLimite(new \DateTime('-3 days'));
        $days = $this->service->getRemainingDays($projet);
        $this->assertLessThan(0, $days);
    }
}