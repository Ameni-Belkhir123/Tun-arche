<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class CodeVerificationController extends AbstractController
{
    #[Route('/registration/instructions', name: 'registration_instructions')]
    public function instructions(Request $request, EntityManagerInterface $entityManager): Response
    {
        $session = $request->getSession();
        $userId = $session->get('verification_user_id');
        if (!$userId) {
            $this->addFlash('error', 'No user found for verification.');
            return $this->redirectToRoute('login');
        }
        $user = $entityManager->getRepository(User::class)->find($userId);
        if (!$user) {
            $this->addFlash('error', 'User not found.');
            return $this->redirectToRoute('login');
        }
        return $this->render('registration/instructions.html.twig', ['user' => $user]);
    }

    #[Route('/registration/verify-code', name: 'verify_code', methods: ['POST'])]
    public function verifyCode(Request $request, EntityManagerInterface $entityManager): Response
    {
        $session = $request->getSession();
        $userId = $session->get('verification_user_id');
        if (!$userId) {
            $this->addFlash('error', 'No user found for verification.');
            return $this->redirectToRoute('login');
        }
        $user = $entityManager->getRepository(User::class)->find($userId);
        if (!$user) {
            $this->addFlash('error', 'User not found.');
            return $this->redirectToRoute('login');
        }
        $inputCode = $request->request->get('verification_code');
        if ($inputCode === $user->getVerificationToken()) {
            $user->setIsVerified(true);
            $user->setVerificationToken(null);
            $user->setCodeSentAt(null);
            $entityManager->persist($user);
            $entityManager->flush();
            $session->remove('verification_user_id');
            $this->addFlash('success', 'Your account has been verified. You can now log in.');
            return $this->redirectToRoute('login');
        } else {
            $this->addFlash('error', 'Invalid verification code.');
            return $this->redirectToRoute('registration_instructions');
        }
    }

    #[Route('/registration/resend-code', name: 'resend_code')]
    public function resendCode(Request $request, EntityManagerInterface $entityManager, MailerInterface $mailer, UrlGeneratorInterface $urlGenerator): Response
    {
        $session = $request->getSession();
        $userId = $session->get('verification_user_id');
        if (!$userId) {
            $this->addFlash('error', 'No user found for verification.');
            return $this->redirectToRoute('login');
        }
        $user = $entityManager->getRepository(User::class)->find($userId);
        if (!$user) {
            $this->addFlash('error', 'User not found.');
            return $this->redirectToRoute('login');
        }
        $now = new \DateTime();
        $lastSent = $user->getCodeSentAt();
        if ($lastSent && ($now->getTimestamp() - $lastSent->getTimestamp()) < 120) {
            $this->addFlash('error', 'Please wait before resending the code.');
            return $this->redirectToRoute('registration_instructions');
        }
        $newCode = bin2hex(random_bytes(3));
        $user->setVerificationToken($newCode);
        $user->setCodeSentAt($now);
        $entityManager->persist($user);
        $entityManager->flush();
        $emailMessage = (new Email())
            ->from('choeurproject@gmail.com')
            ->to($user->getEmail())
            ->subject('Your New Verification Code')
            ->html("<p>Your new verification code is: <strong>{$newCode}</strong></p>");
        $mailer->send($emailMessage);
        $this->addFlash('success', 'A new verification code has been sent to your email.');
        return $this->redirectToRoute('registration_instructions');
    }
}
