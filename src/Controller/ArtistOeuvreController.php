<?php

namespace App\Controller;

use App\Entity\Oeuvre;
use App\Form\ArtistOeuvreType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/artist/oeuvre')]
class ArtistOeuvreController extends AbstractController
{
    #[Route('/', name: 'artist_oeuvre_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        // Retrieve only the artworks belonging to the current artist.
        $user = $this->getUser();
        $oeuvres = $entityManager->getRepository(Oeuvre::class)->findBy(['artist' => $user]);

        return $this->render('front/oeuvre/index_artist.html.twig', [
            'oeuvres' => $oeuvres,
        ]);
    }

    #[Route('/new', name: 'artist_oeuvre_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $oeuvre = new Oeuvre();
        // Automatically assign the current artist.
        $oeuvre->setArtist($this->getUser());

        $form = $this->createForm(\App\Form\ArtistOeuvreType::class, $oeuvre);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form->get('image')->getData();
            if ($file) {
                $mimeType = $file->getClientMimeType();
                $contents = file_get_contents($file->getPathname());
                $base64 = 'data:' . $mimeType . ';base64,' . base64_encode($contents);
                $oeuvre->setImage($base64);
            }
            $entityManager->persist($oeuvre);
            $entityManager->flush();

            $this->addFlash('success', 'Votre œuvre a été ajoutée avec succès.');
            return $this->redirectToRoute('artist_oeuvre_index');
        }

        return $this->render('front/oeuvre/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/delete', name: 'artist_oeuvre_delete', methods: ['POST'])]
    public function delete(Oeuvre $oeuvre, Request $request, EntityManagerInterface $entityManager): Response
    {
        // Ensure the current user is the owner of the artwork.
        if ($oeuvre->getArtist() !== $this->getUser()) {
            throw $this->createAccessDeniedException("Vous n'êtes pas autorisé à supprimer cette œuvre.");
        }

        if ($this->isCsrfTokenValid('delete' . $oeuvre->getId(), $request->request->get('_token'))) {
            $entityManager->remove($oeuvre);
            $entityManager->flush();
            $this->addFlash('success', 'L\'œuvre a été supprimée avec succès.');
        }
        return $this->redirectToRoute('artist_oeuvre_index');
    }
}
