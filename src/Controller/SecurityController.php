<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{

    // #[Route('/login', name: 'login', methods: ['GET'])]
    // public function login(AuthenticationUtils $authenticationUtils): Response
    // {
    //     $error = $authenticationUtils->getLastAuthenticationError();
    //     return $this->render('security/login.html.twig', [
    //         'error' => $error,
    //     ]);
    // }

//
//     ????????????????
//
//     #[Route('/check-password', name: 'check_password', methods: ['POST'])]
//     public function checkPassword(
//         EntityManagerInterface $entityManager,
//         UserPasswordEncoderInterface $passwordEncoder,
//         Request $request
//     ): Response {
//         $email = $request->request->get('email');
//         $submittedPassword = $request->request->get('password');
//
//         if (!$email || !$submittedPassword) {
//             return new Response('Email and password are required', 400);
//         }
//
//         // Recherche de l'utilisateur avec son email
//         $user = $entityManager->getRepository(User::class)->findOneBy(['email' => $email]);
//
//         if (!$user) {
//             return new Response('User not found', 404);
//         }
//
//         // Vérification du mot de passe
//         if ($passwordEncoder->isPasswordValid($user, $submittedPassword)) {
//             return new Response('Password is correct!');
//         } else {
//             return new Response('Invalid password!', 401);
//         }
//     }


    #[Route('/login', name: 'login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        $error = $authenticationUtils->getLastAuthenticationError();
        return $this->render('security/login.html.twig', [
            'error' => $error,
        ]);
    }

    #[Route('/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it is intercepted by the logout key on your firewall.');
    }
}
