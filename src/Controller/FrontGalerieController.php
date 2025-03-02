<?php

namespace App\Controller;

use App\Repository\GalerieRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/explore/galeries')]
class FrontGalerieController extends AbstractController
{
    #[Route('/', name: 'front_galerie_index')]
    public function index(Request $request, GalerieRepository $galerieRepository): Response
    {
        // Get sorting and search query parameters
        $sort = $request->query->get('sort', 'views');
        $search = trim($request->query->get('search', ''));

        // Retrieve all galleries as an array
        $galeries = $galerieRepository->findAll();
        if (!is_array($galeries)) {
            $galeries = iterator_to_array($galeries);
        }

        // Filter by search term if provided (case-insensitive)
        if ($search !== '') {
            $galeries = array_filter($galeries, function($galerie) use ($search) {
                return stripos($galerie->getNom(), $search) !== false;
            });
            // Reindex the array to avoid gaps
            $galeries = array_values($galeries);
        }

        // Sort the galleries based on the chosen criteria
        usort($galeries, function($a, $b) use ($sort) {
            switch ($sort) {
                case 'time':
                    // Higher ID means newer (if ID is auto-increment)
                    return $b->getId() <=> $a->getId();
                case 'artworks':
                    return count($b->getOeuvres()) <=> count($a->getOeuvres());
                case 'views':
                default:
                    return $b->getTotalViews() <=> $a->getTotalViews();
            }
        });

        return $this->render('front/galerie/index.html.twig', [
            'galeries' => $galeries,
            'sort'     => $sort,
            'search'   => $search,
        ]);
    }

    #[Route('/{id}', name: 'front_galerie_show')]
    public function show(int $id, GalerieRepository $galerieRepository): Response
    {
        $galerie = $galerieRepository->find($id);
        if (!$galerie) {
            throw $this->createNotFoundException('Gallery not found');
        }

        return $this->render('front/galerie/show.html.twig', [
            'galerie' => $galerie,
        ]);
    }
}
