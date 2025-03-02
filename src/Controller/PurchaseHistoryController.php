<?php

namespace App\Controller;

use App\Repository\BilletRepository;
use Doctrine\ORM\EntityManagerInterface;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Knp\Snappy\Pdf;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PurchaseHistoryController extends AbstractController
{
    private Pdf $pdfGenerator;

    public function __construct(Pdf $pdfGenerator)
    {
        $this->pdfGenerator = $pdfGenerator;
    }

    #[Route('/purchase-history', name: 'purchase_history_index')]
    public function index(BilletRepository $billetRepository): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('login');
        }

        // Retrieve tickets purchased by the current user.
        $tickets = $billetRepository->findBy(['buyer' => $user]);

        return $this->render('purchase_history/index.html.twig', [
            'tickets' => $tickets,
        ]);
    }

//    #[Route('/purchase-history/qr/{id}', name: 'purchase_history_qr')]
//    public function qr(int $id, BilletRepository $billetRepository): Response
//    {
//        $ticket = $billetRepository->find($id);
//        $user = $this->getUser();
//        if (!$ticket || $ticket->getBuyer() !== $user) {
//            throw $this->createNotFoundException("Ticket not found.");
//        }
//
//        // Prepare ticket information for the QR code
//        $ticketInfo = [
//            'ticket_number' => $ticket->getNumero(),
//            'event'         => $ticket->getEvent() ? $ticket->getEvent()->getNameEvent() : 'N/A',
//            'date_emission' => $ticket->getDateEmission() ? $ticket->getDateEmission()->format('Y-m-d') : '',
//            'payment_mode'  => $ticket->getModePaiement(),
//            'ticket_type'   => $ticket->getType(),
//        ];
//        $ticketData = json_encode($ticketInfo);
//
//        // Generate the QR code using the Endroid QR Code v6 Builder with setter methods
//        $result = \Endroid\QrCode\Builder\Builder::create()
//            ->writer(new \Endroid\QrCode\Writer\PngWriter())
//            ->setData($ticketData)
//            ->setSize(300)
//            ->setMargin(10)
//            ->build();
//
//        // Encode the generated QR code image for embedding in HTML
//        $qrImageData = base64_encode($result->getString());
//
//        return $this->render('purchase_history/qr.html.twig', [
//            'qrImageData' => $qrImageData,
//            'ticket'      => $ticket,
//        ]);
//    }


    #[Route('/purchase-history/pdf', name: 'purchase_history_pdf')]
    public function pdf(BilletRepository $billetRepository): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('login');
        }

        $tickets = $billetRepository->findBy(['buyer' => $user]);

        // Render the PDF view (a simple HTML template).
        $html = $this->renderView('purchase_history/pdf.html.twig', [
            'tickets' => $tickets,
        ]);

        $pdfContent = $this->pdfGenerator->getOutputFromHtml($html);

        return new Response(
            $pdfContent,
            200,
            [
                'Content-Type'        => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="purchase_history.pdf"',
            ]
        );
    }

    #[Route('/purchase-history/refund/{id}', name: 'purchase_history_refund', methods: ['POST'])]
    public function refund(Request $request, BilletRepository $billetRepository, EntityManagerInterface $entityManager, int $id): Response
    {
        $ticket = $billetRepository->find($id);
        $user = $this->getUser();
        if (!$ticket || $ticket->getBuyer() !== $user) {
            throw $this->createNotFoundException("Ticket not found.");
        }

        // Check CSRF token.
        if (!$this->isCsrfTokenValid('refund' . $ticket->getId(), $request->request->get('_token'))) {
            $this->addFlash('error', 'Invalid CSRF token.');
            return $this->redirectToRoute('purchase_history_index');
        }

        $event = $ticket->getEvent();
        // Refund is allowed only if the event start date is in the future.
        if ($event->getDateStart() <= new \DateTime()) {
            $this->addFlash('error', 'Refund is not allowed after the event has started.');
            return $this->redirectToRoute('purchase_history_index');
        }

        // Process refund: decrement soldTickets and remove the ticket.
        $event->setSoldTickets($event->getSoldTickets() - 1);
        $entityManager->remove($ticket);
        $entityManager->flush();

        $this->addFlash('success', 'Refund processed successfully.');
        return $this->redirectToRoute('purchase_history_index');
    }
}
