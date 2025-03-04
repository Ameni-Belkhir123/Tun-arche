<?php
namespace App\Controller;

use App\Repository\PublicationRepository;
use App\Repository\CommantaireRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class StatisticspubcomController extends AbstractController
{
    #[Route('/statisticspubcom', name: 'app_statisticspubcom_index', methods: ['GET'])]
    public function index(PublicationRepository $publicationRepository, CommantaireRepository $commantaireRepository): Response
    {

        $publications = $publicationRepository->findAll();

        $totalPublications = count($publications);
        $totalComments = count($commantaireRepository->findAll());

        $mostCommented = [];
        $totalLikes = 0;
        $totalUnlikes = 0;
        $pubInteractions = [];
        $pubInteractionPercents = [];
        $dropdownData = [];

        $totalInteractions = 0;
        $now = new \DateTime();

        foreach ($publications as $publication) {

            $commentCount = count($publication->getCommantaires());
            $mostCommented[$publication->getTitre()] = $commentCount;

            $totalLikes += $publication->getLikes();
            $totalUnlikes += $publication->getUnlikes();

            $interactions = $publication->getLikes() + $publication->getUnlikes() + $commentCount;
            $pubInteractions[$publication->getTitre()] = $interactions;
            $totalInteractions += $interactions;

            $publicationDate = $publication->getDateAct();
            $interval = $now->diff($publicationDate);
            $days = $interval->days;
            if ($days < 1) {
                $days = 1;
            }
            $dropdownData[] = [
                'titre' => $publication->getTitre(),
                'interactionsPerDay' => round($interactions / $days, 2)
            ];
        }

        if ($totalInteractions > 0) {
            foreach ($pubInteractions as $titre => $count) {
                $pubInteractionPercents[$titre] = round(($count / $totalInteractions) * 100, 2);
            }
        }

        $mostInteracted = $pubInteractions;
        arsort($mostInteracted);
        $leastInteracted = $pubInteractions;
        asort($leastInteracted);

        return $this->render('statisticspubcom/index.html.twig', [
            'totalPublications'     => $totalPublications,
            'totalComments'         => $totalComments,
            'totalLikes'            => $totalLikes,
            'totalUnlikes'          => $totalUnlikes,
            'mostCommented'         => $mostCommented,
            'pubInteractions'       => $pubInteractions,
            'pubInteractionPercents'=> $pubInteractionPercents,
            'mostInteracted'        => $mostInteracted,
            'leastInteracted'       => $leastInteracted,
            'dropdownData'          => $dropdownData,
        ]);
    }
}
