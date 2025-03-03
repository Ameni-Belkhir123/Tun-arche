<?php

namespace App\Controller;

use App\Entity\Participation;
use App\Entity\Concours;
use App\Form\ParticipationType;
use App\Repository\ParticipationRepository;
use App\Repository\ConcoursRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/participation')]
class ParticipationController extends AbstractController
{
    #[Route('/', name: 'app_participation_index', methods: ['GET'])]
    public function index(ParticipationRepository $participationRepository): Response
    {
        return $this->render('participation/index.html.twig', [
            'participations' => $participationRepository->findAll(),
        ]);
    }

    #[Route('/new/{concoursId}', name: 'app_participation_new', methods: ['GET', 'POST'])]
    public function new(
        int $concoursId,
        Request $request,
        EntityManagerInterface $entityManager,
        ConcoursRepository $concoursRepository
    ): Response {
        // Retrieve the Concours by its id
        $concours = $concoursRepository->find($concoursId);
        if (!$concours) {
            throw $this->createNotFoundException('Concours not found.');
        }

        $participation = new Participation();
        // Set the required association
        $participation->setConcours($concours);

        $form = $this->createForm(ParticipationType::class, $participation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Process file upload (unmapped field)
            $imageFile = $form->get('imageFile')->getData();
            if ($imageFile) {
                try {
                    $fileContents = file_get_contents($imageFile->getPathname());
                    $base64Image = base64_encode($fileContents);
                    $participation->setImagePath($base64Image);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Erreur lors du traitement de l\'image.');
                }
            }
            $entityManager->persist($participation);
            $entityManager->flush();

            $this->addFlash('success', 'Participation créée avec succès.');
            return $this->redirectToRoute('app_participation_index');
        }

        return $this->render('participation/new.html.twig', [
            'form' => $form->createView(),
            'concours' => $concours, // Optionally pass concours data to the template
        ]);
    }

    #[Route('/{id}', name: 'app_participation_show', methods: ['GET'])]
    public function show(Participation $participation): Response
    {
        return $this->render('participation/show.html.twig', [
            'participation' => $participation,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_participation_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Participation $participation, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ParticipationType::class, $participation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Process image file if a new one is uploaded
            $imageFile = $form->get('imageFile')->getData();
            if ($imageFile) {
                try {
                    $fileContents = file_get_contents($imageFile->getPathname());
                    $base64Image = base64_encode($fileContents);
                    $participation->setImagePath($base64Image);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Erreur lors du traitement de l\'image.');
                }
            }
            $entityManager->flush();
            $this->addFlash('success', 'Participation mise à jour avec succès.');
            return $this->redirectToRoute('app_participation_index');
        }

        return $this->render('participation/edit.html.twig', [
            'form' => $form->createView(),
            'participation' => $participation,
        ]);
    }

    #[Route('/{id}', name: 'app_participation_delete', methods: ['POST'])]
    public function delete(Request $request, Participation $participation, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $participation->getId(), $request->request->get('_token'))) {
            $entityManager->remove($participation);
            $entityManager->flush();
            $this->addFlash('success', 'Participation supprimée avec succès.');
        }
        return $this->redirectToRoute('app_participation_index');
    }
}
