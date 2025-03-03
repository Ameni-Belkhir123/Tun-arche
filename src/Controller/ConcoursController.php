<?php

namespace App\Controller;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use App\Repository\ParticipationRepository;
use App\Service\EmailService;
use App\Entity\Concours;
use App\Form\ConcoursType;
use App\Repository\ConcoursRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use App\Entity\Participation;
use App\Form\ParticipationType;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/concours')]
final class ConcoursController extends AbstractController
{
    private SluggerInterface $slugger;

    public function __construct(SluggerInterface $slugger)
    {
        $this->slugger = $slugger;
    }

    #[Route(name: 'app_concours_index', methods: ['GET'])]
    public function index(ConcoursRepository $concoursRepository): Response
    {
        return $this->render('concours/index.html.twig', [
            'concours' => $concoursRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_concours_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $concour = new Concours();
        $form = $this->createForm(ConcoursType::class, $concour);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($concour);
            $entityManager->flush();

            return $this->redirectToRoute('app_concours_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('concours/new.html.twig', [
            'concour' => $concour,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_concours_show', methods: ['GET'])]
    public function show(Concours $concour): Response
    {
        return $this->render('concours/detail.html.twig', [
            'concour' => $concour,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_concours_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Concours $concour, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ConcoursType::class, $concour);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_concours_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('concours/edit.html.twig', [
            'concour' => $concour,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_concours_delete', methods: ['POST'])]
    public function delete(Request $request, Concours $concour, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $concour->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($concour);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_concours_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/participer', name: 'app_participer', methods: ['GET', 'POST'])]
    public function participer(
        Request $request,
        Concours $concour,
        EntityManagerInterface $entityManager,
        EmailService $emailService
    ): Response {
        $participation = new Participation();
        $participation->setConcours($concour);
        // Do not set idOeuvre – instead, if needed later you can set a relation to an Oeuvre.

        $form = $this->createForm(ParticipationType::class, $participation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Process image upload: convert the file to base64
            $imageFile = $form->get('imageFile')->getData();
            if ($imageFile) {
                try {
                    $fileContents = file_get_contents($imageFile->getPathname());
                    $base64Image = base64_encode($fileContents);
                    $participation->setImagePath($base64Image);
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Erreur lors du traitement de l\'image.');
                    return $this->redirectToRoute('app_participer', ['id' => $concour->getId()]);
                }
            }

            $entityManager->persist($participation);
            $entityManager->flush();

            // Send confirmation email
            $emailService->sendParticipationConfirmationEmail($participation);

            $this->addFlash('success', 'Votre participation a été soumise avec succès !');
            return $this->redirectToRoute('app_concours_show', ['id' => $concour->getId()]);
        }

        return $this->render('participation/show2.html.twig', [
            'concour' => $concour,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/concours/{id}/vote', name: 'app_concours_vote', methods: ['GET'])]
    public function vote(Request $request, Concours $concour, ParticipationRepository $participationRepository): Response
    {
        $minVotes = $request->query->get('min_votes');
        $sortBy = $request->query->get('sort_by');

        $participations = $participationRepository->findByConcoursAndVotes($concour, $minVotes ? (int)$minVotes : null, $sortBy);

        return $this->render('participation/vote.html.twig', [
            'concour' => $concour,
            'participations' => $participations,
            'minVotes' => $minVotes,
            'sortBy' => $sortBy,
        ]);
    }
}
