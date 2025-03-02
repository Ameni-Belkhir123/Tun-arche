<?php

namespace App\Controller;

use App\Entity\Oeuvre;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/explore/artwork')]
class FrontOeuvreController extends AbstractController
{
    #[Route('/{id}', name: 'front_oeuvre_show')]
    public function show(Oeuvre $oeuvre, EntityManagerInterface $entityManager): Response
    {
        $oeuvre->incrementViews();
        $entityManager->flush();

        return $this->render('front/oeuvre/show.html.twig', [
            'oeuvre' => $oeuvre,
        ]);
    }
}
