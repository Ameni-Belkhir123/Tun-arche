<?php
// src/Controller/FormationController.php

namespace App\Controller;

use App\Entity\Formation;
use App\Form\FormationType;
use App\Repository\FormationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

#[Route('/formation')]
final class FormationController extends AbstractController
{
    #[Route('/', name: 'app_formation_index', methods: ['GET'])]
    public function index(FormationRepository $formationRepository, Request $request): Response
    {
        $query = $request->query->get('search');
        $sortBy = $request->query->get('sort_by');

        $formations = $formationRepository->searchFormations($query, $sortBy);

        return $this->render('formation/index.html.twig', [
            'formations' => $formations,
        ]);
    }

    #[Route('/new', name: 'app_formation_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $formation = new Formation();
        $form = $this->createForm(FormationType::class, $formation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Convert uploaded image file to base64 string
            $imageFile = $form->get('imageFile')->getData();
            if ($imageFile) {
                $imageData = base64_encode(file_get_contents($imageFile->getPathname()));
                $formation->setImageBase64($imageData);
            }
            $entityManager->persist($formation);
            $entityManager->flush();

            return $this->redirectToRoute('app_formation_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('formation/new.html.twig', [
            'formation' => $formation,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_formation_show', methods: ['GET'])]
    public function show(Formation $formation): Response
    {
        return $this->render('formation/show.html.twig', [
            'formation' => $formation,
            'evaluations' => $formation->getEvaluation(),
        ]);
    }

    #[Route('/{id}/edit', name: 'app_formation_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Formation $formation, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(FormationType::class, $formation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('imageFile')->getData();
            if ($imageFile) {
                $imageData = base64_encode(file_get_contents($imageFile->getPathname()));
                $formation->setImageBase64($imageData);
            }
            $entityManager->flush();

            return $this->redirectToRoute('app_formation_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('formation/edit.html.twig', [
            'formation' => $formation,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_formation_delete', methods: ['POST'])]
    public function delete(Request $request, Formation $formation, EntityManagerInterface $entityManager): JsonResponse
    {
        if (!$this->isCsrfTokenValid('delete' . $formation->getId(), $request->getContent())) {
            return new JsonResponse(['success' => false, 'message' => 'Token CSRF invalide'], Response::HTTP_FORBIDDEN);
        }

        if (!$formation->getEvaluation()->isEmpty()) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Cette formation contient des évaluations. Voulez-vous vraiment la supprimer avec ses évaluations ?',
                'requiresConfirmation' => true
            ]);
        }

        $entityManager->remove($formation);
        $entityManager->flush();

        return new JsonResponse(['success' => true, 'message' => 'Formation supprimée avec succès'], Response::HTTP_OK);
    }

    #[Route('/{id}/force-delete', name: 'app_formation_force_delete', methods: ['POST'])]
    public function forceDelete(Request $request, Formation $formation, EntityManagerInterface $entityManager): JsonResponse
    {
        if (!$this->isCsrfTokenValid('delete' . $formation->getId(), $request->getContent())) {
            return new JsonResponse(['success' => false, 'message' => 'Token CSRF invalide'], Response::HTTP_FORBIDDEN);
        }

        foreach ($formation->getEvaluation() as $evaluation) {
            $entityManager->remove($evaluation);
        }

        $entityManager->remove($formation);
        $entityManager->flush();

        return new JsonResponse(['success' => true, 'message' => 'Formation et évaluations supprimées avec succès'], Response::HTTP_OK);
    }

    #[Route('/{id}/participer', name: 'formation_participer', methods: ['POST'])]
    public function participer(
        Formation $formation,
        EntityManagerInterface $entityManager,
        \App\Repository\FormationParticipationRepository $fpRepository
    ): JsonResponse {

        $user = $this->getUser();
        if (!$user) {
            return new JsonResponse(['success' => false, 'message' => 'User not authenticated.'], 401);
        }

        $existingParticipation = $fpRepository->findOneByUserAndFormation($user, $formation);
        if ($existingParticipation) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Vous avez déjà participé à cette formation.'
            ]);
        }

        if ($formation->getNbrplaces() > 0) {
            $formation->setNbrplaces($formation->getNbrplaces() - 1);

            $participation = new \App\Entity\FormationParticipation();
            $participation->setUser($user);
            $participation->setFormation($formation);
            $entityManager->persist($participation);

            $entityManager->flush();

            return new JsonResponse([
                'success' => true,
                'nbrplaces' => $formation->getNbrplaces(),
                'message' => 'Votre participation a été enregistrée avec succès !'
            ]);
        }

        return new JsonResponse([
            'success' => false,
            'message' => 'La formation est complète, vous ne pouvez plus participer.'
        ]);
    }

}
