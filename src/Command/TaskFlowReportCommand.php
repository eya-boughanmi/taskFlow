<?php

namespace App\Command;

use App\Repository\ProjetRepository;
use App\Repository\TacheRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:taskflow:report',
    description: 'Génère un rapport sur l\'état des projets',
)]
class TaskFlowReportCommand extends Command
{
    public function __construct(
        private ProjetRepository $projetRepo,
        private TacheRepository $tacheRepo
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('projet', null, InputOption::VALUE_REQUIRED);
        $this->addOption('overdue', null, InputOption::VALUE_NONE);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('📊 Rapport TaskFlow');

        $projects = $this->projetRepo->findAll();
        $tasks = $this->tacheRepo->findAll();

        $io->success('Total projets : '.count($projects));
        $io->success('Total tâches : '.count($tasks));

        $rows = [];

        foreach ($projects as $p) {
            $rows[] = [
                $p->getId(),
                $p->getNom(),
                $p->getStatut(),
                $p->getDateLimite()?->format('Y-m-d'),
            ];
        }

        $io->table(
            ['ID', 'Nom', 'Statut', 'Date limite'],
            $rows
        );

        if ($input->getOption('overdue')) {
            $io->warning('Projets en retard');

            $late = [];

            foreach ($projects as $p) {
                if ($p->getDateLimite() < new \DateTime()) {
                    $late[] = [$p->getId(), $p->getNom()];
                }
            }

            $io->table(['ID', 'Nom'], $late);
        }

        return Command::SUCCESS;
    }
}