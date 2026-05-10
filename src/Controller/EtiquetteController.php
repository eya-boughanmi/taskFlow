<?php

namespace App\Controller;

use App\Entity\Etiquette;
use App\Form\EtiquetteType;
use App\Repository\EtiquetteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class EtiquetteController extends AbstractController
{
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/etiquettes', name: 'etiquette_index')]
    public function index(EtiquetteRepository $repo): Response
    {
        return $this->render('etiquette/index.html.twig', [
            'etiquettes' => $repo->findAll()
        ]);
    }

    #[Route('/etiquettes/nouvelle', name: 'etiquette_new')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $etiquette = new Etiquette();

        $form = $this->createForm(EtiquetteType::class, $etiquette);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($etiquette);
            $em->flush();

            return $this->redirectToRoute('etiquette_index');
        }

        return $this->render('etiquette/form.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/etiquettes/{id}/supprimer', name: 'etiquette_delete', methods: ['POST'])]
    public function delete(Request $request, Etiquette $etiquette, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete'.$etiquette->getId(), $request->request->get('_token'))) {
            $em->remove($etiquette);
            $em->flush();
        }

        return $this->redirectToRoute('etiquette_index');
    }
}
