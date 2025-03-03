<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class TestEmailCommand extends Command
{
    protected static $defaultName = 'app:test-email';
    private MailerInterface $mailer;

    public function __construct(MailerInterface $mailer)
    {
        parent::__construct();
        $this->mailer = $mailer;
    }

    protected function configure()
    {
        $this->setDescription('Envoie un email de test via Mailtrap');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $email = (new Email())
            ->from('choeurproject@gmail.com') // Updated sender address
            ->to('test@mailtrap.io')
            ->subject('Test Mailtrap')
            ->text('Ceci est un test avec Mailtrap.')
            ->html('<p>Ceci est un test d’envoi d’email avec Symfony Mailer et Mailtrap.</p>');

        $this->mailer->send($email);

        $output->writeln('✅ Email envoyé avec succès !');

        return Command::SUCCESS;
    }
}
