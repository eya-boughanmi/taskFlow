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

class ProjetController extends AbstractController
{
    #[Route('/projets', name: 'projet_index')]
    public function index(ProjetRepository $repo): Response
    {
        return $this->render('projet/index.html.twig', [
            'projets' => $repo->findAll()
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
    public function show(Projet $projet, ProjetStatsCalculator $stats): Response
{
    return $this->render('projet/show.html.twig', [
        'projet' => $projet,
        'progress' => $stats->getProgressPercentage($projet),
        'counts' => $stats->getTaskCountByStatus($projet),
        'overdue' => $stats->isOverdue($projet),
        'remaining' => $stats->getRemainingDays($projet),
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