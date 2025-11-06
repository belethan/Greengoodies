<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class LoginController extends AbstractController
{
    #[Route(path: '/login', name: 'app.login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // Récupère la dernière erreur de connexion (si existante)
        $error = $authenticationUtils->getLastAuthenticationError();
        // Récupère la dernière adresse e-mail saisie
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('pages/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route(path: '/logout', name: 'app.logout')]
    public function logout(): void
    {
        // Géré automatiquement par Symfony
    }
}
