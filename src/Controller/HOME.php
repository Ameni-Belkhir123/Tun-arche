<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class HOME extends AbstractController
{
    #[Route('/home', name: 'home')]
    public function index(): Response
    {
        return $this->render('back/base.html.twig', [
            'home' => 'home',
        ]);
    }

    #[Route('/home1', name: 'home1')]
    public function index1(): Response
    {
        return $this->render('front1/base.html.twig', [
            'controller_name' => 'UserController',
        ]);
    }

    #[Route('/home2', name: 'home2')]
    public function index2(): Response
    {
        return $this->render('front1/test.html.twig', [
            'test' => 'test',
        ]);
    }

    #[Route('/home3', name: 'home3')]
    public function index3(): Response
    {
        return $this->render('back/test.html.twig', [
            'test' => 'test',
        ]);
    }

    #[Route('/login', name: 'login')]
    public function index4(): Response
    {
        // This route renders the login form.
        return $this->render('security/login.html.twig', [
            'test' => 'test',
        ]);
    }

    #[Route('/home01', name: 'home01')]
    public function index5(
        Request $request,
        EntityManagerInterface $entityManager,
        MailerInterface $mailer,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Retrieve the plain password from the form.
            // (Assuming your UserType form field "password" provides the plain text.)
            $plainPassword = $user->getPassword();

            // Hash the plain password before saving.
            $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);
            $user->setPassword($hashedPassword);

            $entityManager->persist($user);
            $entityManager->flush();

            // Send a welcome email.
            $emailMessage = (new Email())
                ->from('choeurproject@gmail.com')
                ->to($user->getEmail())
                ->subject('Welcome to Tunarche!')
                ->html(
                    $this->renderView('emails/registration.html.twig', [
                        'user' => $user,
                    ])
                );
            $mailer->send($emailMessage);

            // After successful registration, redirect to the login page.
            return $this->redirectToRoute('login');
        }

        return $this->render('user/register.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }
}
