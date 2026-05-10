<?php

namespace App\Controller;

use App\Service\DashboardStatsProvider;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class DashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'app_dashboard')]
    public function index(DashboardStatsProvider $dashboardStatsProvider): Response
    {
        $canSeeUsers = $this->isGranted('ROLE_ADMIN');
        $stats = $dashboardStatsProvider->getStats($canSeeUsers);

        return $this->render('dashboard/index.html.twig', [
            'stats' => $stats,
        ]);
    }

    #[Route('/', name: 'app_home')]
    public function home(): Response
    {
        return $this->redirectToRoute('app_dashboard');
    }
}
