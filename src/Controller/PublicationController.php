<?php
// File: src/Controller/PublicationController.php

namespace App\Controller;

use App\Entity\Publication;
use App\Form\PublicationType;
use App\Repository\PublicationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/publication')]
final class PublicationController extends AbstractController
{
    #[Route('', name: 'app_pub_index', methods: ['GET'])]
    public function index(PublicationRepository $publicationRepository): Response
    {
        return $this->render('pub/index.html.twig', [
            'publications' => $publicationRepository->findAll(),
        ]);
    }

    #[Route('/back', name: 'backlist', methods: ['GET', 'POST'])]
    public function indexBack(Request $request, PublicationRepository $publicationRepository): Response
    {
        return $this->render('pub/backlist.html.twig', [
            'publications' => $publicationRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_pub_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $publication = new Publication();
        $form = $this->createForm(PublicationType::class, $publication);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Process the image file: convert to base64 string with data URI prefix.
            $imageFile = $form->get('image')->getData();
            if ($imageFile) {
                try {
                    $contents = file_get_contents($imageFile->getPathname());
                    // Use getClientMimeType() as in your OeuvreController
                    $mimeType = $imageFile->getClientMimeType();
                    $base64 = 'data:' . $mimeType . ';base64,' . base64_encode($contents);
                    $publication->setImage($base64);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Échec de la conversion de l\'image.');
                }
            }
            // Automatically set the currently logged-in user as the author
            $user = $this->getUser();
            if ($user) {
                $publication->setAuthor($user);
            }
            $entityManager->persist($publication);
            $entityManager->flush();

            return $this->redirectToRoute('app_pub_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('pub/new.html.twig', [
            'publication' => $publication,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/show/{id}', name: 'app_pub_show', methods: ['GET'])]
    public function show(Publication $publication): Response
    {
        return $this->render('pub/show.html.twig', [
            'publication' => $publication,
        ]);
    }

    #[Route('/edit/{id}', name: 'app_pub_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Publication $publication, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $form = $this->createForm(PublicationType::class, $publication);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('image')->getData();
            if ($imageFile) {
                try {
                    $contents = file_get_contents($imageFile->getPathname());
                    // Use getClientMimeType() to get the MIME type of the file.
                    $mimeType = $imageFile->getClientMimeType();
                    $base64 = 'data:' . $mimeType . ';base64,' . base64_encode($contents);
                    $publication->setImage($base64);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Échec de la conversion de l\'image.');
                }
            }
            $entityManager->flush();

            return $this->redirectToRoute('app_pub_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('pub/edit.html.twig', [
            'publication' => $publication,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/delete/{id}', name: 'app_pub_delete', methods: ['POST'])]
    public function delete(Request $request, Publication $publication, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $publication->getId(), $request->get('_token'))) {
            $entityManager->remove($publication);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_pub_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/comments', name: 'app_pub_comments', methods: ['GET'])]
    public function comments(Publication $publication): Response
    {
        return $this->render('pub/comments.html.twig', [
            'publication' => $publication,
            'comments' => $publication->getCommantaires(),
        ]);
    }

    #[Route('/pubcom', name: 'app_pub_com', methods: ['GET'])]
    public function pubcom(PublicationRepository $publicationRepository): Response
    {
        return $this->render('pub/pubcom.html.twig', [
            'publications' => $publicationRepository->findAll(),
        ]);
    }

    #[Route('/publication/{id}', name: 'pp', methods: ['GET'])]
    public function showFront(int $id, PublicationRepository $publicationRepository): Response
    {
        $publication = $publicationRepository->find($id);
        if (!$publication) {
            throw $this->createNotFoundException('Publication not found.');
        }
        return $this->render('front/publication/show.html.twig', [
            'publication' => $publication,
        ]);
    }


}
