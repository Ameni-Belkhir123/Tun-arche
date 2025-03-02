<?php

namespace App\Controller;

use App\Entity\Galerie;
use App\Form\GalerieType;
use App\Repository\GalerieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/galerie')]
final class GalerieController extends AbstractController
{
    #[Route(name: 'app_galerie_index', methods: ['GET'])]
    public function index(GalerieRepository $galerieRepository): Response
    {
        return $this->render('galerie/index.html.twig', [
            'galeries' => $galerieRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_galerie_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $galerie = new Galerie();
        $form = $this->createForm(GalerieType::class, $galerie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form->get('image')->getData();
            if ($file) {
                // Use client mime type to avoid fileinfo dependency
                $mimeType = $file->getClientMimeType();
                $contents = file_get_contents($file->getPathname());
                $base64 = 'data:' . $mimeType . ';base64,' . base64_encode($contents);
                $galerie->setImage($base64);
            }
            $entityManager->persist($galerie);
            $entityManager->flush();

            return $this->redirectToRoute('app_galerie_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('galerie/new.html.twig', [
            'galerie' => $galerie,
            'form'    => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_galerie_show', methods: ['GET'])]
    public function show(Galerie $galerie): Response
    {
        return $this->render('galerie/show.html.twig', [
            'galerie' => $galerie,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_galerie_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Galerie $galerie, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(GalerieType::class, $galerie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form->get('image')->getData();
            if ($file) {
                $mimeType = $file->getClientMimeType();
                $contents = file_get_contents($file->getPathname());
                $base64 = 'data:' . $mimeType . ';base64,' . base64_encode($contents);
                $galerie->setImage($base64);
            }
            $entityManager->flush();

            return $this->redirectToRoute('app_galerie_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('galerie/edit.html.twig', [
            'galerie' => $galerie,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_galerie_delete', methods: ['POST'])]
    public function delete(Request $request, Galerie $galerie, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $galerie->getId(), $request->get('_token'))) {
            $entityManager->remove($galerie);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_galerie_index', [], Response::HTTP_SEE_OTHER);
    }
}
