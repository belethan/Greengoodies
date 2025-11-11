<?php


namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class UserController extends AbstractController
{
    #[Route('api/activer', name: 'api.activer')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function toggleApi(EntityManagerInterface $em): RedirectResponse
    {
        $user = $this->getUser();

        if (!$user) {
            $this->addFlash('danger', 'Vous devez être connecté.');
            return $this->redirectToRoute('app.login');
        }

        $user->setApiActive(!$user->isApiActive());
        $em->persist($user);
        $em->flush();

        $message = $user->isApiActive()
            ? 'Accès API activé avec succès.'
            : 'Accès API désactivé.';
        $this->addFlash('success', $message);

        return $this->redirectToRoute('app.panier.commandes');
    }

    #[Route('/user/delete', name: 'user.delete', methods: ['POST', 'GET'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function delete(EntityManagerInterface $em, LogoutUrlGenerator $logoutUrlGenerator): RedirectResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        if (!$user) {
            $this->addFlash('danger', 'Vous devez être connecté.');
            return $this->redirectToRoute('app_login');
        }

        // Supprimer le user
        $em->remove($user);
        $em->flush();

        $this->addFlash('success', 'Votre compte a été supprimé avec succès.');

        // Redirection vers logout automatique
        $logoutUrl = $logoutUrlGenerator->getLogoutPath();
        return $this->redirect($logoutUrl);
    }
}
