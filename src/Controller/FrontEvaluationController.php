<?php

namespace App\Controller;

use App\Entity\Formation;
use App\Entity\Evaluation;
use App\Repository\EvaluationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/evaluations')]
class FrontEvaluationController extends AbstractController
{
    #[Route('/formation/{id}/new', name: 'front_evaluation_new', methods: ['POST'])]
    public function new(Request $request, Formation $formation, EntityManagerInterface $entityManager, EvaluationRepository $evaluationRepo): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return new JsonResponse(['message' => 'User not authenticated'], Response::HTTP_UNAUTHORIZED);
        }

        $data = json_decode($request->getContent(), true);
        if (!isset($data['evaluation']) || !isset($data['commentaire'])) {
            return new JsonResponse(['message' => 'Invalid data'], Response::HTTP_BAD_REQUEST);
        }

        $evaluation = new Evaluation();
        $evaluation->setFormation($formation);
        $evaluation->setNote($data['evaluation']);
        $evaluation->setCommentaire($data['commentaire']);
        $evaluation->setUser($user);

        $entityManager->persist($evaluation);
        $entityManager->flush();

        $evaluations = $evaluationRepo->findBy(['formation' => $formation], ['id' => 'DESC']);

        return new JsonResponse([
            'success' => true,
            'message' => 'Evaluation submitted successfully',
            'evaluations' => $evaluations,
        ], Response::HTTP_OK);
    }
}
