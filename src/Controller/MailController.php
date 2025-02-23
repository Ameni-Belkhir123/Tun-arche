<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\EmailService;

class MailController extends AbstractController
{
    private EmailService $emailService;

    public function __construct(EmailService $emailService)
    {
        $this->emailService = $emailService;
    }

    #[Route('/test-email', name: 'test_email')]
    public function sendTestEmail(): Response
    {
        $this->emailService->sendEmail(
            'yassbenmanaa@gmail.com',
            'Test Email',
            'This is a test email.'
        );

        return new Response('Test email sent.');
    }
}