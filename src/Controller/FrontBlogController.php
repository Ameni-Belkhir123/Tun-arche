<?php
namespace App\Controller;

use App\Repository\PublicationRepository;
use App\Form\CommantaireType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FrontBlogController extends AbstractController
{
    // Front blog index (pubfer)
    #[Route('/blog', name: 'front_blog_index', methods: ['GET'])]
    public function index(PublicationRepository $publicationRepository): Response
    {
        $publications = $publicationRepository->findAll();
        return $this->render('pub/pubfer.html.twig', [
            'publications' => $publications,
        ]);
    }

    // Front publication details with comments (pubcom)
    #[Route('/blog/{id}', name: 'front_pub_show', methods: ['GET', 'POST'])]
    public function show($id, PublicationRepository $publicationRepository, Request $request, EntityManagerInterface $entityManager): Response
    {
        $publication = $publicationRepository->find($id);
        if (!$publication) {
            throw $this->createNotFoundException('Publication not found.');
        }

        $form = $this->createForm(CommantaireType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $comment = $form->getData();
            $comment->setIdPub($publication);
            if (null === $comment->getDate()) {
                $comment->setDate(new \DateTime());
            }
            if ($this->getUser()) {
                $comment->setUser($this->getUser());
            }
            $entityManager->persist($comment);
            $entityManager->flush();
            $this->addFlash('success', 'Comment added successfully!');
            return $this->redirectToRoute('front_pub_show', ['id' => $publication->getId()]);
        }

        return $this->render('pub/pubcom.html.twig', [
            'publication' => $publication,
            'form' => $form->createView(),
        ]);
    }

}
