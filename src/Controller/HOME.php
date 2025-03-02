<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class HOME extends AbstractController
{
    #[Route('/home01', name: 'home01')]
    public function register(Request $request, EntityManagerInterface $entityManager, MailerInterface $mailer, UserPasswordHasherInterface $passwordHasher, UrlGeneratorInterface $urlGenerator): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $user->getPassword();
            $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);
            $user->setPassword($hashedPassword);
            $user->setIsVerified(false);
            // Generate a 6-character code (hex of 3 bytes)
            $user->setVerificationToken(bin2hex(random_bytes(3)));
            $user->setCodeSentAt(new \DateTime());
            $entityManager->persist($user);
            $entityManager->flush();
            // Store the user ID in session for verification
            $request->getSession()->set('verification_user_id', $user->getId());
            // Send the verification code via email
            $emailMessage = (new Email())
                ->from('choeurproject@gmail.com')
                ->to($user->getEmail())
                ->subject('Email Verification')
                ->html($this->renderView('emails/registration.html.twig', ['user' => $user, 'verificationCode' => $user->getVerificationToken()]));
            $mailer->send($emailMessage);
            return $this->redirectToRoute('registration_instructions');
        }
        return $this->render('user/register.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }
}
