<?php

namespace App\Controller;

use App\Repository\BilletRepository;
use Knp\Snappy\Pdf;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="purchase_history.pdf"',
            ]
        );
    }
}
