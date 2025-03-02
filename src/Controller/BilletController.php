<?php

namespace App\Controller;

use App\Entity\Billet;
use App\Form\BilletType;
use App\Repository\BilletRepository;
use App\Repository\EventRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/billet')]
final class BilletController extends AbstractController
{
    #[Route('/', name: 'app_billet_index', methods: ['GET', 'POST'])]
    public function index(EventRepository $eventRepository, Request $request, EntityManagerInterface $entityManager, BilletRepository $billetRepository, MailerInterface $mailer): Response
    {
        $billet = new Billet();
        $billet->setDateEmission(new \DateTime());
        $form = $this->createForm(BilletType::class, $billet);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $event = $billet->getEvent();
            if (!$event) {
                $this->addFlash('error', 'No event selected.');
                return $this->redirectToRoute('app_billet_index');
            }
            if (($event->getTotalTickets() - $event->getSoldTickets()) <= 0) {
                $this->addFlash('error', 'Sold out! No tickets available for this event.');
                return $this->redirectToRoute('app_billet_index');
            }
            $ticketNumber = $event->getSoldTickets() + 1;
            $billet->setNumero($ticketNumber);
            $event->setSoldTickets($event->getSoldTickets() + 1);
            $billet->setBuyer($this->getUser());
            $entityManager->persist($billet);
            $entityManager->flush();
            $user = $this->getUser();
            $emailMessage = (new Email())
                ->from('choeurproject@gmail.com')
                ->to($user->getEmail())
                ->subject('Ticket Purchase Confirmation')
                ->html($this->renderView('emails/ticket_confirmation.html.twig', [
                    'billet' => $billet,
                    'event'  => $event,
                    'user'   => $user,
                ]));
            $mailer->send($emailMessage);
            $this->addFlash('success', 'Ticket purchased successfully!');
            return $this->redirectToRoute('app_billet_index', [], Response::HTTP_SEE_OTHER);
        }
        return $this->render('billet/index.html.twig', [
            'billets' => $billetRepository->findAll(),
            'events'  => $eventRepository->findAll(),
            'form'    => $form->createView(),
        ]);
    }

    #[Route('/new/{eventId}', name: 'app_billet_new2', methods: ['GET', 'POST'])]
    public function newBillet(Request $request, EntityManagerInterface $entityManager, EventRepository $eventRepository, int $eventId, BilletRepository $billetRepository, MailerInterface $mailer): Response
    {
        $event = $eventRepository->find($eventId);
        if (!$event) {
            throw $this->createNotFoundException('Event not found');
        }
        if (($event->getTotalTickets() - $event->getSoldTickets()) <= 0) {
            $this->addFlash('error', 'Sold out! No tickets available for this event.');
            return $this->redirectToRoute('app_event_indexfront');
        }
        $billet = new Billet();
        $ticketNumber = $event->getSoldTickets() + 1;
        $billet->setNumero($ticketNumber);
        $billet->setDateEmission(new \DateTime());
        $billet->setEvent($event);
        $form = $this->createForm(BilletType::class, $billet);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $event->setSoldTickets($event->getSoldTickets() + 1);
            $billet->setBuyer($this->getUser());
            $entityManager->persist($billet);
            $entityManager->flush();
            $user = $this->getUser();
            $emailMessage = (new Email())
                ->from('choeurproject@gmail.com')
                ->to($user->getEmail())
                ->subject('Ticket Purchase Confirmation')
                ->html($this->renderView('emails/ticket_confirmation.html.twig', [
                    'billet' => $billet,
                    'event'  => $event,
                    'user'   => $user,
                ]));
            $mailer->send($emailMessage);
            $this->addFlash('success', 'Ticket purchased successfully!');
            return $this->redirectToRoute('app_event_indexfront');
        }
        return $this->render('billet/newfront.html.twig', [
            'form'  => $form->createView(),
            'event' => $event,
        ]);
    }

    #[Route('/new', name: 'app_billet_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, BilletRepository $billetRepository, MailerInterface $mailer): Response
    {
        $billet = new Billet();
        $form = $this->createForm(BilletType::class, $billet);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $event = $billet->getEvent();
            if (!$event) {
                $this->addFlash('error', 'No event selected.');
                return $this->redirectToRoute('app_billet_new');
            }
            if (($event->getTotalTickets() - $event->getSoldTickets()) <= 0) {
                $this->addFlash('error', 'Sold out! No tickets available for this event.');
                return $this->redirectToRoute('app_billet_new');
            }
            $ticketNumber = $event->getSoldTickets() + 1;
            $billet->setNumero($ticketNumber);
            $billet->setDateEmission(new \DateTime());
            $event->setSoldTickets($event->getSoldTickets() + 1);
            $billet->setBuyer($this->getUser());
            $entityManager->persist($billet);
            $entityManager->flush();
            $user = $this->getUser();
            $emailMessage = (new Email())
                ->from('choeurproject@gmail.com')
                ->to($user->getEmail())
                ->subject('Ticket Purchase Confirmation')
                ->html($this->renderView('emails/ticket_confirmation.html.twig', [
                    'billet' => $billet,
                    'event'  => $event,
                    'user'   => $user,
                ]));
            $mailer->send($emailMessage);
            return $this->redirectToRoute('app_billet_index', [], Response::HTTP_SEE_OTHER);
        }
        return $this->render('billet/new.html.twig', [
            'billet' => $billet,
            'form'   => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_billet_show', methods: ['GET'])]
    public function show(Billet $billet): Response
    {
        return $this->render('billet/show.html.twig', [
            'billet' => $billet,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_billet_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Billet $billet, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(BilletType::class, $billet);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            return $this->redirectToRoute('app_billet_index', [], Response::HTTP_SEE_OTHER);
        }
        return $this->render('billet/edit.html.twig', [
            'billet' => $billet,
            'form'   => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_billet_delete', methods: ['POST'])]
    public function delete(Request $request, Billet $billet, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $billet->getId(), $request->getPayload()->getString('_token'))) {
            $event = $billet->getEvent();
            if ($event) {
                $event->setSoldTickets($event->getSoldTickets() - 1);
                $entityManager->persist($event);
            }
            $entityManager->remove($billet);
            $entityManager->flush();
        }
        return $this->redirectToRoute('app_billet_index', [], Response::HTTP_SEE_OTHER);
    }
}
