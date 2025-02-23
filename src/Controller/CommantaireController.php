<?php

namespace App\Controller;

use App\Entity\Commantaire;
use App\Form\CommantaireType;
use App\Repository\CommantaireRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Service\EmailService;

final class CommantaireController extends AbstractController
{
    private EmailService $emailService;

    public function __construct(EmailService $emailService)
    {
        $this->emailService = $emailService;
    }

    #[Route('/commantaire', name: 'app_commantaire_index', methods: ['GET'])]
    public function index(CommantaireRepository $commantaireRepository): Response
    {
        return $this->render('commantaire/index.html.twig', [
            'commantaires' => $commantaireRepository->findAll(),
        ]);
    }

    #[Route('commantaire/new', name: 'app_commantaire_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $commantaire = new Commantaire();
        $form = $this->createForm(CommantaireType::class, $commantaire);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($commantaire);
            $entityManager->flush();

            // Envoi de l'e-mail après l'ajout du commentaire
            $this->emailService->sendEmail(
                'yassbenmanaa@gmail.com',
                'Nouveau commentaire ajouté',
                'Un nouveau commentaire a été ajouté : ' . $commantaire->getContenu()
            );

            return $this->redirectToRoute('app_commantaire_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('commantaire/new.html.twig', [
            'commantaire' => $commantaire,
            'form' => $form,
        ]);
    }

    #[Route('commantaire/{id}/show', name: 'app_commantaire_show', methods: ['GET'])]
    public function show(Commantaire $commantaire): Response
    {
        return $this->render('commantaire/show.html.twig', [
            'commantaire' => $commantaire,
        ]);
    }

    #[Route('commantaire/{id}/edit', name: 'app_commantaire_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Commantaire $commantaire, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CommantaireType::class, $commantaire);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_commantaire_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('commantaire/edit.html.twig', [
            'commantaire' => $commantaire,
            'form' => $form,
        ]);
    }

    #[Route('commantaire/{id}/delete', name: 'app_commantaire_delete', methods: ['POST'])]
    public function delete(Request $request, Commantaire $commantaire, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$commantaire->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($commantaire);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_commantaire_index', [], Response::HTTP_SEE_OTHER);
    }
}