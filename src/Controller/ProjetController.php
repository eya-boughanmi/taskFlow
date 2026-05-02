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
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $projet = new Projet();

        $form = $this->createForm(ProjetType::class, $projet);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $projet->setDateCreation(new \DateTimeImmutable());
            $userRepo = $em->getRepository(\App\Entity\User::class);
            $projet->setCreateur($userRepo->find(1));   
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
    public function show(Projet $projet): Response
    {
        return $this->render('projet/show.html.twig', [
            'projet' => $projet
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

        return $this->redirectToRoute('projet_index');
    }
}