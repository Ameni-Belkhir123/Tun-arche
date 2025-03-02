<?php

namespace App\Security;

use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;

class UserChecker implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user): void
    {
        if (method_exists($user, 'isVerified') && !$user->isVerified()) {
            throw new CustomUserMessageAccountStatusException('Your account is not verified. Please check your email.');
        }
    }

    public function checkPostAuth(UserInterface $user): void
    {
    }
}
