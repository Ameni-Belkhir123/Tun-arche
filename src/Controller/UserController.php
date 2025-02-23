<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Dompdf\Options; // Add this line
use Dompdf\Dompdf;

    #[Route('/user')]
class UserController extends AbstractController
{
    #[Route('/', name: 'app_user_index', methods: ['GET', 'POST'])]
    public function index(
        UserRepository $userRepository,
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $hashedPassword = $passwordHasher->hashPassword($user, $user->getPassword());
            $user->setPassword($hashedPassword);
            $entityManager->persist($user);
            $entityManager->flush();
            return $this->redirectToRoute('app_user_index');
        }

        return $this->render('user/index.html.twig', [
            'users' => $userRepository->findAll(),
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/make-admin', name: 'app_user_make_admin', methods: ['POST'])]
    public function makeAdmin(User $user, EntityManagerInterface $entityManager): Response
    {
        $user->setRole('admin');
        $entityManager->persist($user);
        $entityManager->flush();
        $this->addFlash('success', 'User is now an admin.');
        return $this->redirectToRoute('app_user_index');
    }

    #[Route('/new', name: 'app_user_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($user);
            $entityManager->flush();
            return $this->redirectToRoute('app_user_index');
        }
        return $this->render('user/new.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_user_show', methods: ['GET'])]
    public function show(User $user): Response
    {
        return $this->render('user/show.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_user_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        User $user,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('password')->getData();
            if ($plainPassword) {
                $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);
                $user->setPassword($hashedPassword);
            }
            $entityManager->flush();

            return $this->redirectToRoute('app_user_index');
        }

        return $this->render('user/edit.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_user_delete', methods: ['POST'])]
    public function delete(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
            $entityManager->remove($user);
            $entityManager->flush();
        }
        return $this->redirectToRoute('app_user_index');
    }

    #[Route('/{id}/pdf', name: 'app_user_pdf')]
    public function generateUserPdf(User $user): Response
    {
        // Configure Dompdf options
        $options = new Options();
        $options->set('defaultFont', 'Arial');

        // Instantiate Dompdf with the options
        $dompdf = new Dompdf($options);

        // Generate HTML content for the PDF
        $html = $this->renderView('user/pdf_template.html.twig', [
            'user' => $user,
        ]);

        // Load HTML content
        $dompdf->loadHtml($html);

        // Render the HTML as PDF
        $dompdf->render();

        // Output the generated PDF
        return new Response(
            $dompdf->output(),
            200,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="user_' . $user->getId() . '.pdf"',
            ]
        );
    }
}
