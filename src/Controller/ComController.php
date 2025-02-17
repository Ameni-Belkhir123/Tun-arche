<?php

namespace App\Controller;

use App\Entity\Commantaire;
use App\Entity\Publication;
use App\Form\CommantaireType;
use App\Repository\CommantaireRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

#[Route('/com')]
final class ComController extends AbstractController
{
    // ✅ Afficher tous les commentaires
    #[Route('/', name: 'app_com_index', methods: ['GET'])]
    public function index(CommantaireRepository $commantaireRepository): Response
    {
        return $this->render('com/index.html.twig', [
            'commantaires' => $commantaireRepository->findAll(),
        ]);
    }
    
    // ✅ Ajouter un commentaire via AJAX (REST API)
    #[Route('/publication/{id}/new', name: 'app_com_new', methods: ['POST'])]
    public function addCommantaire(Request $request, Publication $publication, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['commentaire']) || empty(trim($data['commentaire']))) {
            return new JsonResponse(['message' => 'Le commentaire ne peut pas être vide.'], Response::HTTP_BAD_REQUEST);
        }

        $commantaire = new Commantaire();
        $commantaire->setIdPub($publication);
        $commantaire->setContenu($data['commentaire']);

        $entityManager->persist($commantaire);
        $entityManager->flush();

        return new JsonResponse([
            'message' => 'Commentaire ajouté avec succès.',
            'commentaire' => [
                'id' => $commantaire->getId(),
                'contenu' => $commantaire->getContenu(),
                'publication_id' => $publication->getId(),
            ]
        ], Response::HTTP_OK);
    }

    // ✅ Afficher un commentaire spécifique
    #[Route('/{id}', name: 'app_com_show', methods: ['GET'])]
    public function show(Commantaire $commantaire): Response
    {
        return $this->render('com/show.html.twig', [
            'commantaire' => $commantaire,
        ]);
    }

    // ✅ Modifier un commentaire
    #[Route('/{id}/edit', name: 'app_com_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Commantaire $commantaire, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CommantaireType::class, $commantaire);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            return $this->redirectToRoute('app_com_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('com/edit.html.twig', [
            'commantaire' => $commantaire,
            'form' => $form,
        ]);
    }

    // ✅ Supprimer un commentaire
    #[Route('/{id}', name: 'app_com_delete', methods: ['POST'])]
    public function delete(Request $request, Commantaire $commantaire, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $commantaire->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($commantaire);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_com_index', [], Response::HTTP_SEE_OTHER);
    }
}
