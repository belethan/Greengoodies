<?php

namespace App\Security;

use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserChecker implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user): void
    {
        if (method_exists($user, 'isApiActive') && !$user->isApiActive()) {
            throw new CustomUserMessageAccountStatusException(
                'L’accès API est désactivé pour cet utilisateur.'
            );
        }
    }

    public function checkPostAuth(UserInterface $user): void {}
}

