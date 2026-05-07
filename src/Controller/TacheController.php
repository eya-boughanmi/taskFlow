<?php

namespace App\Controller;

use App\Entity\Projet;
use App\Entity\Tache;
use App\Form\TacheType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;

final class TacheController extends AbstractController
{
    #[IsGranted('ROLE_USER')]
    #[Route('/projets/{id}/taches/nouvelle', name: 'tache_new')]
    public function new(
    Request $request,
    Projet $projet,
    EntityManagerInterface $em,
    MailerInterface $mailer
): Response
    {
        $tache = new Tache();
        $tache->setProjet($projet);

        $form = $this->createForm(TacheType::class, $tache);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $tache->setDateCreation(new \DateTimeImmutable());
            $em->persist($tache);
            $em->flush();
            if ($tache->getAssigneA()) {

    $email = (new TemplatedEmail())
        ->from('noreply@taskflow.com')
        ->to($tache->getAssigneA()->getEmail())
        ->subject('✅ Nouvelle tâche assignée : ' . $tache->getTitre())
        ->htmlTemplate('emails/tache_assignee.html.twig')
        ->context([
            'tache' => $tache,
            'assignateur' => $this->getUser(),
        ]);

    $mailer->send($email);
}
            $this->addFlash('success', 'Tâche ajoutée avec succès');
            return $this->redirectToRoute('projet_show', ['id' => $projet->getId()]);
        }

        return $this->render('tache/form.html.twig', [
            'form'   => $form->createView(),
            'projet' => $projet,
        ]);
    }

    #[Route('/taches/{id}/modifier', name: 'tache_edit')]
    public function edit(Request $request, Tache $tache, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(TacheType::class, $tache);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Tâche modifiée avec succès');
            return $this->redirectToRoute('projet_show', [
                'id' => $tache->getProjet()->getId()
            ]);
        }

        return $this->render('tache/form.html.twig', [
            'form'  => $form->createView(),
            'tache' => $tache,
        ]);
    }

    #[Route('/taches/{id}/supprimer', name: 'tache_delete', methods: ['POST'])]
    public function delete(Request $request, Tache $tache, EntityManagerInterface $em): Response
    {
        $projetId = $tache->getProjet()->getId();

        if ($this->isCsrfTokenValid('delete'.$tache->getId(), $request->request->get('_token'))) {
            $em->remove($tache);
            $em->flush();
            $this->addFlash('success', 'Tâche supprimée');
        }

        return $this->redirectToRoute('projet_show', ['id' => $projetId]);
    }
    #[Route('/test-mail', name: 'app_tache_test')]
public function test(MailerInterface $mailer): Response
{
    $email = (new TemplatedEmail())
        ->from('test@test.com')
        ->to('test@test.com')
        ->subject('Test Mail')
        ->html('<p>Hello from Symfony</p>');

    $mailer->send($email);

    dd('sent');
}
}