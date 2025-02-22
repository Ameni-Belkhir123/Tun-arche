<?php

namespace App\Controller;

use App\Entity\Evaluation;
use App\Form\EvaluationType;
use App\Repository\EvaluationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Formation;
use Symfony\Component\HttpFoundation\JsonResponse;

#[Route('/evaluation')]
final class EvaluationController extends AbstractController
{
    #[Route(name: 'app_evaluation_index', methods: ['GET'])]
    public function index(EvaluationRepository $evaluationRepository): Response
    {
        return $this->render('evaluation/index.html.twig', [
            'evaluations' => $evaluationRepository->findAll(),
        ]);
    }

    #[Route('/formation/{id}/evaluation/new', name: 'app_evaluation_new', methods: ['POST'])]
    public function addEvaluation(Request $request, Formation $formation, EntityManagerInterface $entityManager, EvaluationRepository $evaluationRepo): JsonResponse
   {
    $data = json_decode($request->getContent(), true);

    // Vérification des données envoyées
    if (!isset($data['evaluation']) || !isset($data['commentaire'])) {
        return new JsonResponse(['message' => 'Données invalides'], Response::HTTP_BAD_REQUEST);
    }

    // Créer une nouvelle évaluation (commentaire)
    $evaluation = new Evaluation();
    $evaluation->setFormation($formation);
    $evaluation->setNote($data['evaluation']);
    $evaluation->setCommentaire($data['commentaire']);

    // Sauvegarder l'évaluation dans la base de données
    $entityManager->persist($evaluation);
    $entityManager->flush();

    // Récupérer toutes les évaluations liées à cette formation
    $evaluations = $evaluationRepo->findBy(['formation' => $formation], ['id' => 'DESC']);

    // Retourner une réponse avec le succès et la liste des évaluations mises à jour
    return new JsonResponse([
        'success' => true,
        'message' => 'Évaluation enregistrée avec succès',
        'evaluations' => $evaluations, // Renvoie toutes les évaluations mises à jour
    ], Response::HTTP_OK);
    }


    #[Route('/{id}', name: 'app_evaluation_show', methods: ['GET'])]
    public function show(Evaluation $evaluation): Response
    {
        return $this->render('evaluation/show.html.twig', [
            'evaluation' => $evaluation,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_evaluation_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Evaluation $evaluation, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(EvaluationType::class, $evaluation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_evaluation_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('evaluation/edit.html.twig', [
            'evaluation' => $evaluation,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_evaluation_delete', methods: ['POST'])]
    public function delete(Request $request, Evaluation $evaluation, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$evaluation->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($evaluation);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_evaluation_index', [], Response::HTTP_SEE_OTHER);
    }
}
