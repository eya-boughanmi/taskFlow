<?php

namespace App\Controller;

use App\Service\FileUploader;
use App\Entity\Projet;
use App\Entity\Tache;
use App\Form\TacheType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;

final class TacheController extends AbstractController
{
    public function __construct(
    #[\Symfony\Component\DependencyInjection\Attribute\Autowire(service: 'tachesUploader')]
    private FileUploader $tachesUploader
) {}

    #[IsGranted('ROLE_USER')]
    #[Route('/projets/{id}/taches/nouvelle', name: 'tache_new')]
    public function new(
        Request $request,
        Projet $projet,
        EntityManagerInterface $em,
        MailerInterface $mailer
    ): Response {

        $tache = new Tache();
        $tache->setProjet($projet);

        $form = $this->createForm(TacheType::class, $tache);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $tache->setDateCreation(new \DateTimeImmutable());

            // Upload fichier AVANT flush
            $file = $form->get('pieceJointe')->getData();
            if ($file) {
                $fileName = $this->tachesUploader->upload($file);
                $tache->setPieceJointeName($fileName);
            }

            $em->persist($tache);
            $em->flush();

            // Email APRÈS save
            if ($tache->getAssigneA()) {
                $email = (new TemplatedEmail())
                    ->from('noreply@taskflow.com')
                    ->to($tache->getAssigneA()->getEmail())
                    ->subject('✅ Nouvelle tâche assignée : ' . $tache->getTitre())
                    ->htmlTemplate('emails/tache_assignee.html.twig')
                    ->context([
                        'tache'       => $tache,
                        'assignateur' => $this->getUser()?->getPseudo() ?? 'Système',
                    ]);

                $mailer->send($email);
            }

            $this->addFlash('success', 'Tâche ajoutée avec succès.');

            return $this->redirectToRoute('projet_show', [
                'id' => $projet->getId(),
            ]);
        }

        return $this->render('tache/form.html.twig', [
            'form'   => $form->createView(),
            'projet' => $projet,
        ]);
    }

    #[Route('/taches/{id}/modifier', name: 'tache_edit')]
    public function edit(
        Request $request,
        Tache $tache,
        EntityManagerInterface $em
    ): Response {
        $form = $this->createForm(TacheType::class, $tache);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // Upload fichier AVANT flush
            $file = $form->get('pieceJointe')->getData();
            if ($file) {
                if ($tache->getPieceJointeName()) {
                    $this->tachesUploader->remove($tache->getPieceJointeName());
                }
                $fileName = $this->tachesUploader->upload($file);
                $tache->setPieceJointeName($fileName);
            }

            $em->flush();

            $this->addFlash('success', 'Tâche modifiée avec succès.');

            return $this->redirectToRoute('projet_show', [
                'id' => $tache->getProjet()->getId(),
            ]);
        }

        return $this->render('tache/form.html.twig', [
            'form'  => $form->createView(),
            'tache' => $tache,
        ]);
    }

    #[Route('/taches/{id}/supprimer', name: 'tache_delete', methods: ['POST'])]
    public function delete(
        Request $request,
        Tache $tache,
        EntityManagerInterface $em
    ): Response {
        $projetId = $tache->getProjet()->getId();

        if ($this->isCsrfTokenValid('delete' . $tache->getId(), $request->request->get('_token'))) {

            // Supprimer le fichier physique AVANT remove
            if ($tache->getPieceJointeName()) {
                $this->tachesUploader->remove($tache->getPieceJointeName());
            }

            $em->remove($tache);
            $em->flush();

            $this->addFlash('success', 'Tâche supprimée.');
        } else {
            $this->addFlash('error', 'Token CSRF invalide.');
        }

        return $this->redirectToRoute('projet_show', ['id' => $projetId]);
    }
}
