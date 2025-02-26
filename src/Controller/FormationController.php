<?php

namespace App\Controller;

use App\Entity\Formation;
use App\Form\FormationType;
use App\Repository\FormationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

#[Route('/formation')]
final class FormationController extends AbstractController
{
    #[Route(name: 'app_formation_index', methods: ['GET'])]
    public function index(FormationRepository $formationRepository, Request $request): Response
    {
        /// Récupérer le paramètre 'view' pour distinguer front et back
    $view = $request->query->get('view', 'front');

    // Choisir le bon template en fonction de la vue demandée
    $template = ($view === 'back') ? 'formation/index.html.twig' : 'formation/show_all.html.twig';

    // Récupération des paramètres de recherche et de tri
    $query = $request->query->get('search');
    $sortBy = $request->query->get('sort_by');

    // Récupérer les formations filtrées et triées
    $formations = $formationRepository->searchFormations($query, $sortBy);

    // Rendu de la vue avec les formations filtrées
    return $this->render($template, [
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
            $entityManager->persist($formation);
            $entityManager->flush();
            $imageFile = $form->get('imageFile')->getData();
            if ($imageFile) {
                // Le fichier est déjà géré par VichUploaderBundle grâce au champ `imageFile` dans l'entité
            }


            return $this->redirectToRoute('app_formation_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('formation/new.html.twig', [
            'formation' => $formation,
            'form' => $form,
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
            $entityManager->flush();

            return $this->redirectToRoute('app_formation_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('formation/edit.html.twig', [
            'formation' => $formation,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_formation_delete', methods: ['POST'])]
    public function delete(Request $request, Formation $formation, EntityManagerInterface $entityManager): JsonResponse
{
    if (!$this->isCsrfTokenValid('delete'.$formation->getId(), $request->getPayload()->getString('_token'))) {
        return new JsonResponse(['success' => false, 'message' => 'Token CSRF invalide'], Response::HTTP_FORBIDDEN);
    }

    // Vérifie si la formation a des commentaires
    if (!$formation->getEvaluation()->isEmpty()) {
        return new JsonResponse([
            'success' => false,
            'message' => 'Cette formation contient des commentaires. Voulez-vous vraiment la supprimer avec ses commentaires ?',
            'requiresConfirmation' => true
        ]);
    }

    // Supprime la formation si elle n'a pas d'évaluations
    $entityManager->remove($formation);
    $entityManager->flush();

    return new JsonResponse(['success' => true, 'message' => 'Formation supprimée avec succès'], Response::HTTP_OK);
}

#[Route('/{id}/force-delete', name: 'app_formation_force_delete', methods: ['POST'])]
public function forceDelete(Request $request, Formation $formation, EntityManagerInterface $entityManager): JsonResponse
{
    if (!$this->isCsrfTokenValid('delete'.$formation->getId(), $request->getPayload()->getString('_token'))) {
        return new JsonResponse(['success' => false, 'message' => 'Token CSRF invalide'], Response::HTTP_FORBIDDEN);
    }

    // Supprime les commentaires liés à la formation
    foreach ($formation->getEvaluation() as $evaluation) {
        $entityManager->remove($evaluation);
    }

    // Supprime la formation
    $entityManager->remove($formation);
    $entityManager->flush();

    return new JsonResponse(['success' => true, 'message' => 'Formation et commentaires supprimés avec succès'], Response::HTTP_OK);
}
    #[Route('/formation/{id}/participer', name: 'formation_participer', methods: ['POST'])]
    public function participer(int $id, FormationRepository $repo, EntityManagerInterface $entityManager, Formation $formation): JsonResponse {
        if ($formation->getNbrplaces() > 0) {
            $formation->setNbrplaces($formation->getNbrplaces() - 1);
            $entityManager->flush();
    
            return $this->json([
                'success' => true,
                'nbrplaces' => $formation->getNbrplaces(),
                'message' => 'Votre participation a été enregistrée avec succès !'
            ]);
        }
    
        return $this->json([
            'success' => false,
            'message' => 'La formation est complète, vous ne pouvez plus participer.'
        ]);
     }
}
