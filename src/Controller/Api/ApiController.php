<?php

namespace App\Controller\Api;

use App\Repository\ProduitRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;

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
        $data = json_decode($request->getContent(), true);

        if (!isset($data['username'], $data['password'])) {
            return new JsonResponse(['error' => 'Champs manquants (username, password).'], 400);
        }

        $username = $data['username'];
        $password = $data['password'];

        try {
            $user = $userProvider->loadUserByIdentifier($username);
        } catch (\Throwable) {
            return new JsonResponse(['error' => 'Utilisateur introuvable.'], 401);
        }

        if (!$passwordHasher->isPasswordValid($user, $password)) {
            return new JsonResponse(['error' => 'Identifiants incorrects.'], 401);
        }

        if (method_exists($user, 'isApiActive') && !$user->isApiActive()) {
            return new JsonResponse(['error' => 'Accès API non activé.'], 403);
        }

        $token = $jwtManager->create($user);

        return new JsonResponse(['token' => $token], 200);
    }

    /**
     * GET /api/products — Liste des produits (protégée par JWT)
     */
    #[Route('/products', name: 'products', methods: ['GET'])]
    public function getProducts(ProduitRepository $produitRepository): JsonResponse
    {
        if (!$this->getUser()) {
            return new JsonResponse(['error' => 'Token invalide ou manquant.'], 401);
        }

        $produits = $produitRepository->findAll();

        $data = array_map(static function ($produit) {
            return [
                'id' => $produit->getId(),
                'name' => $produit->getNom(),
                'shortDescription' => $produit->getSoustitre(),
                'fullDescription' => $produit->getDescription(),
                'price' => (float) $produit->getPrix(),
                'picture' => $produit->getImageProduit(),
            ];
        }, $produits);

        return new JsonResponse($data, JsonResponse::HTTP_OK);
    }
}

