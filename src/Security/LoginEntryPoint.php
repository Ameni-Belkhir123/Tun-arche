<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;
use Twig\Environment;

class LoginEntryPoint implements AuthenticationEntryPointInterface
{
    private Environment $twig;

    public function __construct(Environment $twig)
    {
        $this->twig = $twig;
    }

    public function start(Request $request, AuthenticationException $authException = null): Response
    {
        return new Response($this->twig->render('security/login.html.twig', [
            'error' => $authException,
        ]));
    }
}
