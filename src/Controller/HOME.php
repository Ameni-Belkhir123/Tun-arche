<?php

namespace App\Controller;
use App\Repository\UserRepository;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

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

   


}
