<?php

namespace App\Controller;

use App\Entity\LignePanier;
use App\Entity\Panier;
use App\Repository\ProduitRepository;
use App\Repository\PanierRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/panier', name: 'app.panier.')]
class PanierController extends AbstractController
{
    /**
     * Permet de visualiser le panier en cours
     */
    #[Route('/visualiser', name: 'visualiser', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function visualiser(PanierRepository $panierRepository): Response
    {
        $user = $this->getUser();
        if (!$user) {
            $this->addFlash('warning', 'Veuillez vous connecter avant de pouvoir consulter le panier.');
            return $this->redirectToRoute('app.login');
        }
        $panier = method_exists($user, 'getPanier')
            ? $user->getPanier()
            : $panierRepository->findOneBy(['user' => $user]);

        // Si aucun panier ou panier vide → redirection vers app.principale
        if (!$panier || $panier->getLignePaniers()->isEmpty()) {
            $this->addFlash('warning', 'Votre panier est vide.');
            return $this->redirectToRoute('app.principale');
        }

        // Calcul du total
        $total = 0;
        foreach ($panier->getLignePaniers() as $ligne) {
            $total += $ligne->getProduit()->getPrix() * $ligne->getQuantite();
        }

        // Affichage du même template que index()
        return $this->render('pages/panier.html.twig', [
            'panier' => $panier,
            'lignesPanier' => $panier->getLignePaniers(),
            'total' => $total,
        ]);
    }

    /**
     * Ajoute 1 exemplaire d’un produit au panier depuis sa fiche.
     */
    #[Route('/ajouter/{id}', name: 'ajouter', methods: ['POST','GET'])]
    public function ajouter(
        int $id,
        ProduitRepository $produitRepository,
        PanierRepository $panierRepository,
        EntityManagerInterface $em
    ): RedirectResponse {
        $user = $this->getUser();
        if (!$user) {
            $this->addFlash('warning', 'Veuillez vous connecter avant d’ajouter ce produit au panier.');
            return $this->redirectToRoute('app.login');
        }

        $produit = $produitRepository->find($id);
        if (!$produit) {
            $this->addFlash('danger', 'Produit introuvable.');
            return $this->redirectToRoute('app.principale');
        }

        // Récupère ou crée le panier du user
        $panier = method_exists($user, 'getPanier') ? $user->getPanier() : $panierRepository->findOneBy(['user' => $user]);
        if (!$panier) {
            $panier = new Panier();
            // si ton entité Panier possède un setUser()
            if (method_exists($panier, 'setUser')) {
                $panier->setUser($user);
            }
            $em->persist($panier);
        }

        // Cherche une ligne existante pour ce produit
        $ligneExistante = null;
        foreach ($panier->getLignePaniers() as $l) {
            if ($l->getProduit()->getId() === $produit->getId()) {
                $ligneExistante = $l;
                break;
            }
        }

        if ($ligneExistante) {
            $ligneExistante->setQuantite($ligneExistante->getQuantite() + 1);
        } else {
            $ligne = new LignePanier();
            $ligne->setProduit($produit);
            $ligne->setQuantite(1);
            // si ton entité LignePanier possède setPanier()
            if (method_exists($ligne, 'setPanier')) {
                $ligne->setPanier($panier);
            }
            // si Panier gère l’ajout côté inverse (addLignePanier / addLigne)
            if (method_exists($panier, 'addLignePanier')) {
                $panier->addLignePanier($ligne);
            } elseif (method_exists($panier, 'addLigne')) {
                $panier->addLigne($ligne);
            }
            $em->persist($ligne);
        }

        $em->flush();

        $this->addFlash('success', 'Produit ajouté au panier !');
        // on renvoie vers la fiche produit si tu as une route app.produit
        return $this->redirectToRoute('app.principale');
    }

    /**
     * Supprime une ligne précise du panier.
     */
    #[Route('/ligne/{id}/supprimer', name: 'supprimer_ligne', methods: ['POST','GET'])]
    #[IsGranted('ROLE_USER')]
    public function supprimerLigne(int $id, EntityManagerInterface $em): RedirectResponse
    {
        $repo = $em->getRepository(LignePanier::class);
        $ligne = $repo->find($id);

        if (!$ligne) {
            $this->addFlash('warning', 'Ligne introuvable.');
            return $this->redirectToRoute('app.panier.visualiser');
        }

        $em->remove($ligne);
        $em->flush();

        $this->addFlash('success', 'Article retiré du panier.');
        return $this->redirectToRoute('app.panier.visualiser');
    }

    /**
     * Vide entièrement le panier de l’utilisateur.
     */
    #[Route('/vider', name: 'vider', methods: ['POST','GET'])]
    #[IsGranted('ROLE_USER')]
    public function vider(EntityManagerInterface $em, PanierRepository $panierRepository): RedirectResponse
    {
        $user = $this->getUser();
        $panier = method_exists($user, 'getPanier') ? $user->getPanier() : $panierRepository->findOneBy(['user' => $user]);

        if (!$panier) {
            $this->addFlash('warning', 'Aucun panier à vider.');
            return $this->redirectToRoute('app.principale');
        }

        foreach ($panier->getLignePaniers() as $ligne) {
            $em->remove($ligne);
        }
        $em->flush();

        $this->addFlash('success', 'Votre panier a été vidé.');
        return $this->redirectToRoute('app.principale');
    }

    /**
     * Démarre la validation (redirige vers ton flux de commande).
     */
    #[Route('/valider', name: 'valider', methods: ['POST','GET'])]
    #[IsGranted('ROLE_USER')]
    public function valider(): RedirectResponse
    {
        // Ici tu démarreras la création de Commande (Panier -> Commande)
        $this->addFlash('success', 'Commande validée avec succès !');
        return $this->redirectToRoute('app.principale'); // adapte s’il le faut
    }
}
