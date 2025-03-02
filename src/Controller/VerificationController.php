<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class VerificationController extends AbstractController
{
    #[Route('/verify/email/{token}', name: 'verify_email')]
    public function verifyEmail(string $token, EntityManagerInterface $entityManager): Response
    {
        $user = $entityManager->getRepository(User::class)->findOneBy(['verificationToken' => $token]);
        if (!$user) {
            $this->addFlash('error', 'Invalid verification token.');
            return $this->redirectToRoute('login');
        }
        $user->setIsVerified(true);
        $user->setVerificationToken(null);
        $entityManager->persist($user);
        $entityManager->flush();
        $this->addFlash('success', 'Your email has been verified.');
        return $this->redirectToRoute('login');
    }
}
