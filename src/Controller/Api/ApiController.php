<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Repository\ProduitRepository;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\User\UserProviderInterface;

#[Route('/api', name: 'api.')]
class ApiController extends AbstractController
{
    /**
     * POST /api/login — Génération du token JWT
     */
    #[Route('/login', name: 'login', methods: ['POST'])]
    public function login(
        Request $request,
        UserProviderInterface $userProvider,
        UserPasswordHasherInterface $passwordHasher,
        JWTTokenManagerInterface $jwtManager
    ): JsonResponse {

        // Décodage + validation des champs requis
        $data = json_decode($request->getContent(), true);
        if (!is_array($data) || empty($data['username']) || empty($data['password'])) {
            return new JsonResponse(
                ['error' => 'Champs manquants : username et password sont requis.'],
                Response::HTTP_BAD_REQUEST
            );
        }

        $username = trim($data['username']);
        $password = $data['password'];

        // Récupération de l’utilisateur
        try {
            $user = $userProvider->loadUserByIdentifier($username);
        } catch (\Throwable) {
            return new JsonResponse(
                ['error' => 'Identifiant ou mot de passe incorrect.'],
                Response::HTTP_UNAUTHORIZED
            );
        }

        // Vérification du mot de passe
        if (!$passwordHasher->isPasswordValid($user, $password)) {
            return new JsonResponse(
                ['error' => 'Identifiant ou mot de passe incorrect.'],
                Response::HTTP_UNAUTHORIZED
            );
        }

        // Vérification si l’API est activée pour cet utilisateur
        if (method_exists($user, 'isApiActive') && !$user->isApiActive()) {
            return new JsonResponse(
                ['error' => 'Accès API non activé pour cet utilisateur.'],
                Response::HTTP_FORBIDDEN
            );
        }

        // Génération du token JWT
        $token = $jwtManager->create($user);

        return new JsonResponse(
            ['token' => $token],
            Response::HTTP_OK
        );
    }


    /**
     * GET /api/products — Liste des produits (protégée par JWT)
     */
    #[Route('/products', name: 'products', methods: ['GET'])]
    public function getProducts(
        ProduitRepository $produitRepository
    ): JsonResponse {

        // Vérifie que le user est authentifié par JWT
        if (!$this->getUser()) {
            return new JsonResponse(
                ['error' => 'Token invalide ou manquant.'],
                Response::HTTP_UNAUTHORIZED
            );
        }

        // Récupération des produits
        $produits = $produitRepository->findAll();

        // Transformation en tableau API-ready
        $data = array_map(
            static function ($produit): array {
                return [
                    'id'                => $produit->getId(),
                    'name'              => $produit->getNom(),
                    'shortDescription'  => $produit->getSoustitre(),
                    'fullDescription'   => $produit->getDescription(),
                    'price'             => (float) $produit->getPrix(),
                    'picture'           => $produit->getImageProduit(),
                ];
            },
            $produits
        );

        return new JsonResponse(
            $data,
            Response::HTTP_OK
        );
    }
}
