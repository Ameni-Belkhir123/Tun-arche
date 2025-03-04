<?php

namespace App\Security;

use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\RememberMeBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;

class AuthAuthenticator extends AbstractLoginFormAuthenticator
{
    public const LOGIN_ROUTE = 'login';

    private string $recaptchaSecret;

    public function __construct(private UrlGeneratorInterface $urlGenerator, string $recaptchaSecret)
    {
        $this->recaptchaSecret = $recaptchaSecret;
    }

    public function supports(Request $request): bool
    {
        return $request->isMethod('POST') && $request->getPathInfo() === '/login';
    }

    public function authenticate(Request $request): Passport
    {
        $email     = $request->request->get('_email');
        $password  = $request->request->get('_password');
        $csrfToken = $request->request->get('_csrf_token');
        if (!$email || !$password) {
            throw new \InvalidArgumentException('Email and password are required.');
        }
        $recaptchaResponse = $request->request->get('g-recaptcha-response');
        if (!$recaptchaResponse) {
            throw new AuthenticationException('Please complete the reCAPTCHA.');
        }
        $client = HttpClient::create();
        $response = $client->request('POST', 'https://www.google.com/recaptcha/api/siteverify', [
            'body' => [
                'secret'   => $this->recaptchaSecret,
                'response' => $recaptchaResponse,
                'remoteip' => $request->getClientIp(),
            ],
        ]);
        $data = $response->toArray();
        if (!isset($data['success']) || !$data['success']) {
            throw new AuthenticationException('reCAPTCHA verification failed. Please try again.');
        }
        return new Passport(
            new UserBadge($email),
            new PasswordCredentials($password),
            [
                new CsrfTokenBadge('authenticate', $csrfToken),
                new RememberMeBadge(),
            ]
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        $user = $token->getUser();
        $roles = $user->getRoles();
        if (in_array('ROLE_ADMIN', $roles))  {
            return new RedirectResponse($this->urlGenerator->generate('app_user_index'));
        } elseif (in_array('ROLE_USER', $roles) || in_array('ROLE_ARTIST', $roles)) {
            // NEW: Redirect non-admin users to the event front page
            return new RedirectResponse($this->urlGenerator->generate('app_front_home'));
        }
        return new RedirectResponse($this->urlGenerator->generate('app_front_home'));
    }

    protected function getLoginUrl(Request $request): string
    {
        return $this->urlGenerator->generate(self::LOGIN_ROUTE);
    }
}
