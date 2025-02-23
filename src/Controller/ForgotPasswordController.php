<?php
namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class ForgotPasswordController extends AbstractController
{
    #[Route('/forgot-password', name: 'forgot_password')]
    public function forgotPassword(
        Request $request,
        EntityManagerInterface $entityManager,
        MailerInterface $mailer,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        if ($request->isMethod('POST')) {
            $emailInput = $request->request->get('email');
            $user = $entityManager->getRepository(User::class)->findOneBy(['email' => $emailInput]);
            if (!$user) {
                $this->addFlash('error', 'No user found with that email address.');
                return $this->redirectToRoute('forgot_password');
            }
            $newPlainPassword = bin2hex(random_bytes(4));
            $hashedPassword = $passwordHasher->hashPassword($user, $newPlainPassword);
            $user->setPassword($hashedPassword);
            $entityManager->persist($user);
            $entityManager->flush();
            $emailMessage = (new Email())
                ->from('choeurproject@gmail.com')
                ->to($user->getEmail())
                ->subject('Your New Password')
                ->html($this->renderView('emails/forgot_password.html.twig', [
                    'user'       => $user,
                    'newPassword'=> $newPlainPassword,
                ]));
            $mailer->send($emailMessage);
            $this->addFlash('success', 'A new password has been sent to your email address.');
            return $this->redirectToRoute('forgot_password');
        }
        return $this->render('security/forgot_password.html.twig');
    }
}
