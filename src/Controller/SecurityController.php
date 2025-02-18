<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use App\Entity\User;

class SecurityController extends AbstractController
{
    #[Route('/login', name: 'login')]
    public function login(AuthenticationUtils $authenticationUtils, SessionInterface $session): Response
    {
        // Récupérer l'erreur de connexion s'il y en a une
        $error = $authenticationUtils->getLastAuthenticationError();
        
        // Dernier email saisi par l'utilisateur
        $lastEmail = $authenticationUtils->getLastUsername();

        // Chemin cible après connexion (s'il existe)
        $targetPath = $session->get('_security.main.target_path');

        return $this->render('security/login.html.twig', [
            'last_email' => $lastEmail,
            'error' => $error,
            'target_path' => $targetPath, // Utilisé pour la redirection après connexion
        ]);
    }

    #[Route('/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method will be intercepted by the logout key in security.yaml.');
    }

    #[Route('/check-password', name: 'check_password', methods: ['POST'])]
    public function checkPassword(
        EntityManagerInterface $entityManager,
        UserPasswordEncoderInterface $passwordEncoder,
        Request $request
    ): Response {
        $email = $request->request->get('email');
        $submittedPassword = $request->request->get('password');

        if (!$email || !$submittedPassword) {
            return new Response('Email and password are required', 400);
        }

        // Recherche de l'utilisateur avec son email
        $user = $entityManager->getRepository(User::class)->findOneBy(['email' => $email]);

        if (!$user) {
            return new Response('User not found', 404);
        }

        // Vérification du mot de passe
        if ($passwordEncoder->isPasswordValid($user, $submittedPassword)) {
            return new Response('Password is correct!');
        } else {
            return new Response('Invalid password!', 401);
        }
    }
}
