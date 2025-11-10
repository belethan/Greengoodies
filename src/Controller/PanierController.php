<?php

namespace App\Controller;

use App\Entity\LignePanier;
use App\Entity\Panier;
use App\Enum\StatutCommande;
use App\Repository\PanierRepository;
use App\Repository\ProduitRepository;
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
     * 🛒 Affiche le panier de l’utilisateur connecté
     */
    #[Route('/', name: 'visualiser', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function index(PanierRepository $panierRepository): Response
    {
        $user = $this->getUser();
        if (!$user) {
            $this->addFlash('warning', 'Veuillez vous connecter avant de pouvoir consulter votre panier.');
            return $this->redirectToRoute('app.login');
        }

        // Récupération du panier actif (modePanier = 0)
        $panier = $panierRepository->findPanierActifByUser($user);

        // Si aucun panier ou panier vide
        if (!$panier || $panier->getLignePaniers()->isEmpty()) {
            $this->addFlash('warning', 'Votre panier est vide.');
            return $this->redirectToRoute('app.principale');
        }

        // Calcul du total
        $total = 0;
        foreach ($panier->getLignePaniers() as $ligne) {
            $produit = $ligne->getProduit();
            if ($produit) {
                $total += $produit->getPrix() * $ligne->getQuantite();
            }
        }

        return $this->render('pages/panier.html.twig', [
            'panier' => $panier,
            'lignesPanier' => $panier->getLignePaniers(),
            'total' => $total,
        ]);
    }

    /**
     * ➕ Ajoute un produit au panier (ou incrémente la quantité)
     */
    #[Route('/ajouter/{id}', name: 'ajouter', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function ajouter(
        int $id,
        ProduitRepository $produitRepository,
        PanierRepository $panierRepository,
        EntityManagerInterface $em
    ): RedirectResponse {
        $user = $this->getUser();

        $produit = $produitRepository->find($id);
        if (!$produit) {
            $this->addFlash('danger', 'Produit introuvable.');
            return $this->redirectToRoute('app.principale');
        }

        // Récupère le panier actif ou en crée un
        $panier = $panierRepository->findPanierActifByUser($user);
        if (!$panier) {
            $panier = new Panier();
            $panier->setUser($user);
            $panier->setModePanier(false); // panier actif
            $em->persist($panier);
        }

        // Vérifie si le produit existe déjà dans le panier
        $ligneExistante = null;
        foreach ($panier->getLignePaniers() as $ligne) {
            if ($ligne->getProduit()->getId() === $produit->getId()) {
                $ligneExistante = $ligne;
                break;
            }
        }

        if ($ligneExistante) {
            $ligneExistante->setQuantite($ligneExistante->getQuantite() + 1);
        } else {
            $ligne = new LignePanier();
            $ligne->setProduit($produit);
            $ligne->setQuantite(1);
            $ligne->setPanier($panier);
            $em->persist($ligne);
        }

        $em->flush();

        $this->addFlash('success', 'Le produit a été ajouté à votre panier.');
        return $this->redirectToRoute('app.principale');
    }

    /**
     * ➖ Supprime une ligne précise du panier
     */
    #[Route('/supprimer-ligne/{id}', name: 'supprimer_ligne', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function supprimerLigne(
        int $id,
        EntityManagerInterface $em,
        PanierRepository $panierRepository
    ): RedirectResponse {
        $user = $this->getUser();
        $panier = $panierRepository->findPanierActifByUser($user);

        if (!$panier) {
            $this->addFlash('warning', 'Aucun panier actif trouvé.');
            return $this->redirectToRoute('app.principale');
        }

        $ligne = $em->getRepository(LignePanier::class)->find($id);
        if (!$ligne || $ligne->getPanier() !== $panier) {
            $this->addFlash('warning', 'Article introuvable dans votre panier.');
            return $this->redirectToRoute('app.principale');
        }

        $em->remove($ligne);
        $em->flush();

        $this->addFlash('success', 'L’article a été supprimé du panier.');
        return $this->redirectToRoute('app.principale');
    }

    /**
     * 🗑️ Vide complètement le panier actif
     */
    #[Route('/vider', name: 'vider', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function vider(
        EntityManagerInterface $em,
        PanierRepository $panierRepository
    ): RedirectResponse {
        $user = $this->getUser();
        $panier = $panierRepository->findPanierActifByUser($user);

        if (!$panier) {
            $this->addFlash('warning', 'Aucun panier actif à vider.');
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
     *  Valide le panier et le transforme en commande (modePanier = 1)
     */
    #[Route('/valider', name: 'valider', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function valider(
        EntityManagerInterface $em,
        PanierRepository $panierRepository
    ): RedirectResponse {
        $user = $this->getUser();
        $panier = $panierRepository->findPanierActifByUser($user);

        if (!$panier || $panier->getLignePaniers()->isEmpty()) {
            $this->addFlash('warning', 'Votre panier est vide, impossible de valider la commande.');
            return $this->redirectToRoute('app.principale');
        }

        $panier->setModePanier(true); // devient une commande
        $panier->setDateCmde(new \DateTimeImmutable());
        $panier->setStatutCmde(StatutCommande::EN_CONCEPTION);

        $em->flush();

        $this->addFlash('success', 'Votre commande a été validée avec succès.');
        return $this->redirectToRoute('app.commandes');
    }
}
