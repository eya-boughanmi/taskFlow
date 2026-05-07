<?php

namespace App\Controller;

use App\Entity\Projet;
use App\Form\ProjetType;
use App\Repository\ProjetRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Service\ProjetStatsCalculator;
use Symfony\Component\HttpFoundation\RequestStack;
use App\Form\ProjetSearchType;

class ProjetController extends AbstractController
{
    #[Route('/projets', name: 'projet_index')]
public function index(
    Request $request,
    ProjetRepository $repo
): Response {

    $form = $this->createForm(ProjetSearchType::class);

    $form->handleRequest($request);

    $data = $form->getData();

    $projets = $repo->findByFilters(
        $data['nom'] ?? null,
        $data['statut'] ?? null,
        $data['createur'] ?? null,
        $data['etiquette'] ?? null
    );

    return $this->render('projet/index.html.twig', [
        'projets' => $projets,
        'searchForm' => $form->createView()
    ]);
}

    #[Route('/projets/nouveau', name: 'projet_new')]
    #[IsGranted('ROLE_CHEF_PROJET')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $projet = new Projet();

        $form = $this->createForm(ProjetType::class, $projet);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $projet->setDateCreation(new \DateTimeImmutable());
            $userRepo = $em->getRepository(\App\Entity\User::class);
            $projet->setCreateur($this->getUser());  
            $em->persist($projet);
            $em->flush();

            $this->addFlash('success', 'Projet ajouté');

            return $this->redirectToRoute('projet_index');
        }

        return $this->render('projet/form.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/projets/{id}', name: 'projet_show')]
public function show(
    Projet $projet,
    RequestStack $requestStack,
    ProjetRepository $repo
): Response {

    // 🔹 SESSION (recent projects)
    $session = $requestStack->getSession();

    $recent = $session->get('recent_projects', []);

    $recent = array_diff($recent, [$projet->getId()]);

    array_unshift($recent, $projet->getId());

    $recent = array_slice($recent, 0, 5);

    $session->set('recent_projects', $recent);

    $recentProjects = $repo->findBy([
        'id' => $recent
    ]);

    // 🔹 STATS
    $total = count($projet->getTaches());

    $done = 0;

    $counts = [
        'a_faire' => 0,
        'en_cours' => 0,
        'terminee' => 0
    ];

    foreach ($projet->getTaches() as $tache) {

        $counts[$tache->getStatut()]++;

        if ($tache->getStatut() === 'terminee') {
            $done++;
        }
    }

    $progress = $total > 0 ? round(($done / $total) * 100) : 0;

    // 🔹 retard
    $today = new \DateTime();

    $overdue = $projet->getDateLimite() < $today;

    // 🔹 jours restants
    $remaining = $today->diff($projet->getDateLimite())->days;

    return $this->render('projet/show.html.twig', [

        'projet' => $projet,

        'recentProjects' => $recentProjects,

        'progress' => $progress,

        'counts' => $counts,

        'overdue' => $overdue,

        'remaining' => $remaining
    ]);
}
    #[Route('/projets/{id}/modifier', name: 'projet_edit')]
    public function edit(Request $request, Projet $projet, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(ProjetType::class, $projet);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $em->flush();

            $this->addFlash('success', 'Projet modifié');

            return $this->redirectToRoute('projet_index');
        }
        if (
    $projet->getCreateur() !== $this->getUser()
    && !$this->isGranted('ROLE_ADMIN')
) {
    throw $this->createAccessDeniedException();
}

        return $this->render('projet/form.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/projets/{id}/supprimer', name: 'projet_delete', methods: ['POST'])]
    public function delete(Request $request, Projet $projet, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete'.$projet->getId(), $request->request->get('_token'))) {
            $em->remove($projet);
            $em->flush();
        }
        if (
    $projet->getCreateur() !== $this->getUser()
    && !$this->isGranted('ROLE_ADMIN')
) {
    throw $this->createAccessDeniedException();
}

        return $this->redirectToRoute('projet_index');
    }

}