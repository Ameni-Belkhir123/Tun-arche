<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ForgotPasswordController extends AbstractController
{
    #[Route('/forgot-password', name: 'forgot_password')]
    public function forgotPassword(Request $request, EntityManagerInterface $entityManager, MailerInterface $mailer): Response
    {
        if ($request->isMethod('POST')) {
            $emailInput = $request->request->get('email');
            $user = $entityManager->getRepository(User::class)->findOneBy(['email' => $emailInput]);
            if (!$user) {
                $this->addFlash('error', 'No user found with that email address.');
                return $this->redirectToRoute('forgot_password');
            }
            $code = bin2hex(random_bytes(3));
            $user->setVerificationToken($code);
            $user->setCodeSentAt(new \DateTime());
            $entityManager->persist($user);
            $entityManager->flush();
            $request->getSession()->set('forgot_password_user_id', $user->getId());
            $emailMessage = (new Email())
                ->from('choeurproject@gmail.com')
                ->to($user->getEmail())
                ->subject('Password Reset Verification Code')
                ->html($this->renderView('emails/forgot_password_code.html.twig', ['user' => $user, 'verificationCode' => $code]));
            $mailer->send($emailMessage);
            return $this->redirectToRoute('forgot_password_instructions');
        }
        return $this->render('forgot_password/forgot_email.html.twig');
    }

    #[Route('/forgot-password/instructions', name: 'forgot_password_instructions')]
    public function instructions(Request $request, EntityManagerInterface $entityManager): Response
    {
        $session = $request->getSession();
        $userId = $session->get('forgot_password_user_id');
        if (!$userId) {
            $this->addFlash('error', 'No user found for password reset.');
            return $this->redirectToRoute('forgot_password');
        }
        $user = $entityManager->getRepository(User::class)->find($userId);
        if (!$user) {
            $this->addFlash('error', 'User not found.');
            return $this->redirectToRoute('forgot_password');
        }
        return $this->render('forgot_password/instructions.html.twig', ['user' => $user]);
    }

    #[Route('/forgot-password/verify-code', name: 'verify_forgot_code', methods: ['POST'])]
    public function verifyCode(Request $request, EntityManagerInterface $entityManager): Response
    {
        $session = $request->getSession();
        $userId = $session->get('forgot_password_user_id');
        if (!$userId) {
            $this->addFlash('error', 'No user found for password reset.');
            return $this->redirectToRoute('forgot_password');
        }
        $user = $entityManager->getRepository(User::class)->find($userId);
        if (!$user) {
            $this->addFlash('error', 'User not found.');
            return $this->redirectToRoute('forgot_password');
        }
        $inputCode = $request->request->get('verification_code');
        if ($inputCode === $user->getVerificationToken()) {
            $session->set('forgot_password_verified', true);
            return $this->redirectToRoute('set_new_password');
        } else {
            $this->addFlash('error', 'Invalid verification code.');
            return $this->redirectToRoute('forgot_password_instructions');
        }
    }

    #[Route('/forgot-password/resend-code', name: 'resend_forgot_code')]
    public function resendCode(Request $request, EntityManagerInterface $entityManager, MailerInterface $mailer): Response
    {
        $session = $request->getSession();
        $userId = $session->get('forgot_password_user_id');
        if (!$userId) {
            $this->addFlash('error', 'No user found for password reset.');
            return $this->redirectToRoute('forgot_password');
        }
        $user = $entityManager->getRepository(User::class)->find($userId);
        if (!$user) {
            $this->addFlash('error', 'User not found.');
            return $this->redirectToRoute('forgot_password');
        }
        $now = new \DateTime();
        $lastSent = $user->getCodeSentAt();
        if ($lastSent && ($now->getTimestamp() - $lastSent->getTimestamp()) < 120) {
            $this->addFlash('error', 'Please wait before resending the code.');
            return $this->redirectToRoute('forgot_password_instructions');
        }
        $newCode = bin2hex(random_bytes(3));
        $user->setVerificationToken($newCode);
        $user->setCodeSentAt($now);
        $entityManager->persist($user);
        $entityManager->flush();
        $emailMessage = (new Email())
            ->from('choeurproject@gmail.com')
            ->to($user->getEmail())
            ->subject('Your New Password Reset Verification Code')
            ->html("<p>Your new verification code is: <strong>{$newCode}</strong></p>");
        $mailer->send($emailMessage);
        $this->addFlash('success', 'A new verification code has been sent to your email.');
        return $this->redirectToRoute('forgot_password_instructions');
    }

    #[Route('/forgot-password/set-new-password', name: 'set_new_password', methods: ['GET', 'POST'])]
    public function setNewPassword(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        $session = $request->getSession();
        if (!$session->get('forgot_password_verified')) {
            $this->addFlash('error', 'Please verify your code first.');
            return $this->redirectToRoute('forgot_password_instructions');
        }
        $userId = $session->get('forgot_password_user_id');
        if (!$userId) {
            $this->addFlash('error', 'No user found for password reset.');
            return $this->redirectToRoute('forgot_password');
        }
        $user = $entityManager->getRepository(User::class)->find($userId);
        if (!$user) {
            $this->addFlash('error', 'User not found.');
            return $this->redirectToRoute('forgot_password');
        }
        if ($request->isMethod('POST')) {
            $newPassword = $request->request->get('new_password');
            $confirmPassword = $request->request->get('confirm_password');
            if ($newPassword !== $confirmPassword) {
                $this->addFlash('error', 'Passwords do not match.');
                return $this->redirectToRoute('set_new_password');
            }
            $hashedPassword = $passwordHasher->hashPassword($user, $newPassword);
            $user->setPassword($hashedPassword);
            $user->setVerificationToken(null);
            $user->setCodeSentAt(null);
            $entityManager->persist($user);
            $entityManager->flush();
            $session->remove('forgot_password_user_id');
            $session->remove('forgot_password_verified');
            $this->addFlash('success', 'Your password has been reset. You can now log in.');
            return $this->redirectToRoute('login');
        }
        return $this->render('forgot_password/set_new_password.html.twig', ['user' => $user]);
    }
}
