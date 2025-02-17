<?php

namespace App\Controller;

use App\Entity\Publication;
use App\Form\PublicationType;
use App\Repository\PublicationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/pub')]
final class PubController extends AbstractController{
    #[Route(name: 'app_pub_index', methods: ['GET'])]
    public function index(PublicationRepository $publicationRepository): Response
    {
        return $this->render('pub/index.html.twig', [
            'publications' => $publicationRepository->findAll(),
            
        ]);
    }

    #[Route('/n23',name: 'app_pub_index3', methods: ['GET'])]
    public function index4(PublicationRepository $publicationRepository): Response
    {
        return $this->render('pub/pubfer.html.twig', [
            'publications' => $publicationRepository->findAll(),
            
        ]);
    }

    #[Route('/n',name: 'app_pub_n', methods: ['GET'])]
    public function index2(PublicationRepository $publicationRepository): Response
    {
        return $this->render('pub/ferfer.html.twig', [
            'publications' => $publicationRepository->findAll(),
            
        ]);
    }

    #[Route('/new', name: 'app_pub_new')]
    public function new(Request $request, EntityManagerInterface $entityManager , SluggerInterface $slugger): Response
    {
        $publication = new Publication();
        $form = $this->createForm(PublicationType::class, $publication);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
             /** @var UploadedFile $imageFile */
             $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
             $imageFile = $form->get('image')->getData();

             if ($imageFile) {
                 $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                 $safeFilename = $slugger->slug($originalFilename);
                 $newFilename = $safeFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();
                 $extension = strtolower($imageFile->guessExtension()); 

                 if (!in_array($extension, $allowedExtensions)) {
                    $this->addFlash('danger', 'Format d\'image non autorisé.');
                    return $this->redirectToRoute('app_pub_new');
                }
 
                 try {
                     $imageFile->move(
                         $this->getParameter('post_images_directory'),
                         $newFilename
                     );
                     $publication->setImage($newFilename);
                 } catch (FileException $e) {
                     $this->addFlash('error', 'Échec du téléchargement de l\'image.');
                 }
             }
            $entityManager->persist($publication);
            $entityManager->flush();

            return $this->redirectToRoute('app_pub_index');
        }

        return $this->render('pub/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_pub_show', methods: ['GET'])]
    public function show(Publication $publication): Response
    {
        return $this->render('pub/show.html.twig', [
            'publication' => $publication,
        ]);
    }
    #[Route('/p/{id}', name: 'pp', methods: ['GET'])]
    public function show1(Publication $publication): Response
    {
        return $this->render('pub/show1.html.twig', [
            'publication' => $publication,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_pub_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Publication $publication, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(PublicationType::class, $publication);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_pub_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('pub/edit.html.twig', [
            'publication' => $publication,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_pub_delete', methods: ['POST'])]
    public function delete(Request $request, Publication $publication, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$publication->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($publication);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_pub_index', [], Response::HTTP_SEE_OTHER);
    }
}
