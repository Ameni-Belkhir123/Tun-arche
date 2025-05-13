<?php

namespace App\Controller;

use App\Entity\Event;
use App\Entity\Billet;
use App\Repository\BilletRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CalendarController extends AbstractController
{
    #[Route('/my-calendar', name: 'app_calendar_index', methods: ['GET'])]
    public function index(BilletRepository $billetRepository): Response
    {
        // 1) Get the logged-in user
        $user = $this->getUser();
        if (!$user) {
            // Or redirect to login if not authenticated
            throw $this->createAccessDeniedException('Please log in.');
        }

        // 2) Fetch all billets for this user
        $userBillets = $billetRepository->findBy(['buyer' => $user]);

        // 3) Extract events from these billets.
        //    Avoid duplicates: a user might have multiple billets for the same event.
        $events = [];
        foreach ($userBillets as $billet) {
            $event = $billet->getEvent();
            if ($event && !in_array($event, $events, true)) {
                $events[] = $event;
            }
        }

        // 4) Render the calendar page and pass those events
        return $this->render('calendar/index.html.twig', [
            'events' => $events
        ]);
    }
}
