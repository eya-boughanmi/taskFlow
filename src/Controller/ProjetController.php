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
use Symfony\Component\HttpFoundation\RequestStack;
use App\Form\ProjetSearchType;
use App\Service\FileUploader;

class ProjetController extends AbstractController
{
   public function __construct(
    #[\Symfony\Component\DependencyInjection\Attribute\Autowire(service: 'projetsUploader')]
    private FileUploader $projetsUploader
) {}

    #[Route('/projets', name: 'projet_index')]
    public function index(
        Request $request,
        ProjetRepository $repo
    ): Response {

        $form = $this->createForm(ProjetSearchType::class, null, [
            'method' => 'GET',
            'csrf_protection' => false,
        ]);

        $form->handleRequest($request);

        $nom       = null;
        $statut    = null;
        $createur  = null;
        $etiquette = null;

        if ($form->isSubmitted() && $form->isValid()) {
            $data      = $form->getData();
            $nom       = $data['nom'] ?? null;
            $statut    = $data['statut'] ?? null;
            $createur  = $data['createur'] ?? null;
            $etiquette = $data['etiquette'] ?? null;
        }

        $projets = $repo->findByFilters($nom, $statut, $createur, $etiquette);

        return $this->render('projet/index.html.twig', [
            'projets'    => $projets,
            'searchForm' => $form->createView(),
        ]);
    }

    #[Route('/projets/nouveau', name: 'projet_new')]
    #[IsGranted('ROLE_CHEF_PROJET')]
    public function new(
        Request $request,
        EntityManagerInterface $em
    ): Response {
        $projet = new Projet();

        $form = $this->createForm(ProjetType::class, $projet);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $projet->setDateCreation(new \DateTimeImmutable());
            $projet->setCreateur($this->getUser());

            $imageFile = $form->get('image')->getData();
            if ($imageFile) {
                $fileName = $this->projetsUploader->upload($imageFile);
                $projet->setImageName($fileName);
            }

            $em->persist($projet);
            $em->flush();

            $this->addFlash('success', 'Projet créé avec succès.');

            return $this->redirectToRoute('projet_index');
        }

        return $this->render('projet/form.html.twig', [
            'form'   => $form->createView(),
            'projet' => null,
        ]);
    }

    #[Route('/projets/{id}', name: 'projet_show')]
    public function show(
        Projet $projet,
        RequestStack $requestStack,
        ProjetRepository $repo
    ): Response {
        $session = $requestStack->getSession();
        $recent  = $session->get('recent_projects', []);
        $recent  = array_diff($recent, [$projet->getId()]);
        array_unshift($recent, $projet->getId());
        $recent  = array_slice($recent, 0, 5);
        $session->set('recent_projects', $recent);

        $recentProjects = $repo->findBy(['id' => $recent]);

        $total  = count($projet->getTaches());
        $done   = 0;
        $counts = ['a_faire' => 0, 'en_cours' => 0, 'terminee' => 0];

        foreach ($projet->getTaches() as $tache) {
            $counts[$tache->getStatut()]++;
            if ($tache->getStatut() === 'terminee') {
                $done++;
            }
        }

        $progress  = $total > 0 ? round(($done / $total) * 100) : 0;
        $today     = new \DateTime();
        $overdue   = $projet->getDateLimite() < $today;
        $remaining = $today->diff($projet->getDateLimite())->days;

        return $this->render('projet/show.html.twig', [
            'projet'         => $projet,
            'recentProjects' => $recentProjects,
            'progress'       => $progress,
            'counts'         => $counts,
            'overdue'        => $overdue,
            'remaining'      => $remaining,
        ]);
    }

    #[Route('/projets/{id}/modifier', name: 'projet_edit')]
    public function edit(
        Request $request,
        Projet $projet,
        EntityManagerInterface $em
    ): Response {
        if (
            $projet->getCreateur() !== $this->getUser()
            && !$this->isGranted('ROLE_ADMIN')
        ) {
            throw $this->createAccessDeniedException();
        }

        $form = $this->createForm(ProjetType::class, $projet);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $imageFile = $form->get('image')->getData();
            if ($imageFile) {
                if ($projet->getImageName()) {
                    $this->projetsUploader->remove($projet->getImageName());
                }
                $fileName = $this->projetsUploader->upload($imageFile);
                $projet->setImageName($fileName);
            }

            $em->flush();

            $this->addFlash('success', 'Projet modifié avec succès.');

            return $this->redirectToRoute('projet_show', ['id' => $projet->getId()]);
        }

        return $this->render('projet/form.html.twig', [
            'form'   => $form->createView(),
            'projet' => $projet,
        ]);
    }

    #[Route('/projets/{id}/supprimer', name: 'projet_delete', methods: ['POST'])]
    public function delete(
        Request $request,
        Projet $projet,
        EntityManagerInterface $em
    ): Response {
        if (
            $projet->getCreateur() !== $this->getUser()
            && !$this->isGranted('ROLE_ADMIN')
        ) {
            throw $this->createAccessDeniedException();
        }

        if ($this->isCsrfTokenValid('delete' . $projet->getId(), $request->request->get('_token'))) {

            if ($projet->getImageName()) {
                $this->projetsUploader->remove($projet->getImageName());
            }

            $em->remove($projet);
            $em->flush();

            $this->addFlash('success', 'Projet supprimé.');
        } else {
            $this->addFlash('error', 'Token CSRF invalide.');
        }

        return $this->redirectToRoute('projet_index');
    }
}
