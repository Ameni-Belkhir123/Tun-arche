<?php

namespace App\Controller;

use App\Repository\EventRepository;
use App\Repository\BilletRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class StatisticsController extends AbstractController
{
    #[Route('/statistics', name: 'app_statistics_index')]
    public function index(EventRepository $eventRepository, BilletRepository $billetRepository): Response
    {
        $events = $eventRepository->findAll();
        $totalEvents = count($events);

        $eventsPerMonth = [];
        foreach ($events as $event) {
            $month = $event->getDateStart()->format('Y-m');
            if (!isset($eventsPerMonth[$month])) {
                $eventsPerMonth[$month] = 0;
            }
            $eventsPerMonth[$month]++;
        }
        ksort($eventsPerMonth);
        $months = array_keys($eventsPerMonth);
        $eventsCounts = array_values($eventsPerMonth);

        $billets = $billetRepository->findAll();
        $paymentMethods = [];
        foreach ($billets as $billet) {
            $method = $billet->getModePaiement();
            if (!$method) continue;
            if (!isset($paymentMethods[$method])) {
                $paymentMethods[$method] = 0;
            }
            $paymentMethods[$method]++;
        }
        $paymentLabels = array_keys($paymentMethods);
        $paymentData = array_values($paymentMethods);

        $ticketsPerDay = [
            'Monday' => 0,
            'Tuesday' => 0,
            'Wednesday' => 0,
            'Thursday' => 0,
            'Friday' => 0,
            'Saturday' => 0,
            'Sunday' => 0,
        ];
        foreach ($billets as $billet) {
            $date = $billet->getDateEmission();
            if ($date) {
                $day = $date->format('l');
                $ticketsPerDay[$day]++;
            }
        }
        $dayLabels = array_keys($ticketsPerDay);
        $dayData = array_values($ticketsPerDay);

        return $this->render('statistics/index.html.twig', [
            'totalEvents'   => $totalEvents,
            'months'        => json_encode($months),
            'eventsCounts'  => json_encode($eventsCounts),
            'paymentLabels' => json_encode($paymentLabels),
            'paymentData'   => json_encode($paymentData),
            'dayLabels'     => json_encode($dayLabels),
            'dayData'       => json_encode($dayData),
        ]);
    }
}
