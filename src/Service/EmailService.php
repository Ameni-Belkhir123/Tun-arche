<?php

namespace App\Service;

use App\Entity\Participation;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;

class EmailService
{
    private MailerInterface $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    public function sendParticipationConfirmationEmail(Participation $participation): void
    {
        $email = (new TemplatedEmail())
            ->from('choeurproject@gmail.com')
            ->to($participation->getArtist()->getEmail())
            ->subject('Confirmation de votre inscription au concours')
            ->htmlTemplate('emails/participation_confirmation.html.twig')
            ->context([
                'participation' => $participation,
            ]);

        $this->mailer->send($email);
    }
}
