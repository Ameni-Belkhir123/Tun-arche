<?php


namespace App\Controller;

use App\Repository\FormationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/formations')]
class FrontFormationController extends AbstractController
{
    #[Route('/', name: 'front_formation_index', methods: ['GET'])]
    public function index(FormationRepository $formationRepository, Request $request): Response
    {

        $query = $request->query->get('search');
        $sortBy = $request->query->get('sort_by');

        $formations = $formationRepository->searchFormations($query, $sortBy);

        return $this->render('formation/show_all.html.twig', [
            'formations' => $formations,
        ]);
    }

    #[Route('/{id}', name: 'front_formation_show', methods: ['GET'])]
    public function show($id, FormationRepository $formationRepository): Response
    {
        $formation = $formationRepository->find($id);
        if (!$formation) {
            throw $this->createNotFoundException('Formation not found');
        }

        return $this->render('formation/front_show.html.twig', [
            'formation' => $formation,
        ]);
    }
}
