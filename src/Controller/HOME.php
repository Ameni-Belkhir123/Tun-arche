<?php

namespace App\Controller;
use App\Repository\UserRepository;
use App\Entity\User;
use App\Form\UserType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;






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
        return $this->render('security/login.html.twig', [
            'test' => 'test',
        ]);
    }
    #[Route('/home01', name: 'home01')]
    public function index5(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('home0');
        }

        return $this->render('user/register.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }


}
