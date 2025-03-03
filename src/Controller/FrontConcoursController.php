<?php

namespace App\Controller;

use App\Entity\Concours;
use App\Entity\Participation;
use App\Entity\Vote;
use App\Form\ParticipationType;
use App\Repository\ConcoursRepository;
use App\Repository\ParticipationRepository;
use App\Repository\VoteRepository;
use App\Service\EmailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/front/concours')]
class FrontConcoursController extends AbstractController
{
    private SluggerInterface $slugger;

    public function __construct(SluggerInterface $slugger)
    {
        $this->slugger = $slugger;
    }

    #[Route('/', name: 'front_concours_index', methods: ['GET'])]
    public function index(ConcoursRepository $concoursRepository): Response
    {
        return $this->render('front/concours/index.html.twig', [
            'concours' => $concoursRepository->findAll(),
        ]);
    }

    #[Route('/{id}', name: 'front_concours_show', methods: ['GET'])]
    public function show(Concours $concours): Response
    {
        return $this->render('front/concours/detail.html.twig', [
            'concours' => $concours,
        ]);
    }

    #[Route('/{id}/participer', name: 'front_participer', methods: ['GET', 'POST'])]
    public function participer(
        Request $request,
        Concours $concours,
        EntityManagerInterface $entityManager,
        EmailService $emailService
    ): Response {
        // Prevent duplicate participation.
        $existingParticipation = $entityManager->getRepository(\App\Entity\Participation::class)
            ->findOneBy(['artist' => $this->getUser(), 'concours' => $concours]);
        if ($existingParticipation) {
            $this->addFlash('error', 'Vous avez déjà participé à ce concours.');
            return $this->redirectToRoute('front_concours_show', ['id' => $concours->getId()]);
        }

        $participation = new \App\Entity\Participation();
        $participation->setArtist($this->getUser());
        $participation->setConcours($concours);

        // Create the form and pass the current user (for filtering the artworks).
        $form = $this->createForm(\App\Form\ParticipationType::class, $participation, ['user' => $this->getUser()]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // If an artwork is selected, get its image and remove any data URI prefix.
            if ($participation->getOeuvre() !== null) {
                $oeuvreImage = $participation->getOeuvre()->getImage();
                // Remove the data URI prefix (e.g., "data:image/jpeg;base64,")
                $pattern = '/^data:image\/[a-zA-Z]+;base64,/';
                $cleanImage = preg_replace($pattern, '', $oeuvreImage);
                $participation->setImagePath($cleanImage);
            }
            $entityManager->persist($participation);
            $entityManager->flush();

            $emailService->sendParticipationConfirmationEmail($participation);
            $this->addFlash('success', 'Votre participation a été soumise avec succès ! Veuillez vérifier votre email.');
            return $this->redirectToRoute('front_concours_show', ['id' => $concours->getId()]);
        }

        return $this->render('front/participation/participer.html.twig', [
            'concours' => $concours,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/vote', name: 'front_concours_vote', methods: ['GET'])]
    public function vote(Request $request, Concours $concours, ParticipationRepository $participationRepository, EntityManagerInterface $entityManager): Response
    {
        $minVotes = $request->query->get('min_votes');
        $sortBy = $request->query->get('sort_by');
        $participations = $participationRepository->findByConcoursAndVotes($concours, $minVotes ? (int)$minVotes : null, $sortBy);

        $user = $this->getUser();
        $voteRepository = $entityManager->getRepository(Vote::class);
        $existingVote = $voteRepository->findOneBy(['user' => $user, 'concours' => $concours]);
        $userVoteParticipationId = $existingVote ? $existingVote->getParticipation()->getId() : null;

        return $this->render('front/participation/vote.html.twig', [
            'concours' => $concours,
            'participations' => $participations,
            'minVotes' => $minVotes,
            'sortBy' => $sortBy,
            'userVoteParticipationId' => $userVoteParticipationId,
        ]);
    }

    #[Route('/participation/{id}/vote', name: 'front_vote', methods: ['POST'])]
    public function voteParticipation(Participation $participation, EntityManagerInterface $entityManager, VoteRepository $voteRepository): Response
    {
        $user = $this->getUser();
        $concours = $participation->getConcours();
        $existingVote = $voteRepository->findOneBy(['user' => $user, 'concours' => $concours]);

        if ($existingVote) {
            // If the user already voted for this participation, remove the vote.
            if ($existingVote->getParticipation()->getId() === $participation->getId()) {
                $entityManager->remove($existingVote);
                $participation->decrementVotes();
                $entityManager->flush();
                $this->addFlash('success', 'Votre vote a été retiré.');
            } else {
                // If the user has voted for a different participation, do not allow additional votes.
                $this->addFlash('error', 'Vous avez déjà voté pour une autre œuvre dans ce concours. Veuillez retirer votre vote pour voter sur une autre œuvre.');
            }
        } else {
            // Create a new vote record.
            $vote = new Vote();
            $vote->setUser($user);
            $vote->setConcours($concours);
            $vote->setParticipation($participation);
            $entityManager->persist($vote);
            $participation->incrementVotes();
            $entityManager->flush();
            $this->addFlash('success', 'Votre vote a été enregistré.');
        }

        return $this->redirectToRoute('front_concours_vote', ['id' => $concours->getId()]);
    }

    #[Route('/participation/{id}/inspect', name: 'front_participation_inspect', methods: ['GET'])]
    public function inspectParticipation(Participation $participation): Response
    {
        return $this->render('front/participation/inspect.html.twig', [
            'participation' => $participation,
        ]);
    }

}
