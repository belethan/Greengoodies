<?php

namespace App\Controller;

use App\Entity\Produit;
use App\Repository\ProduitRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProduitController extends AbstractController
{
    #[Route('/produit/{id}', name: 'app.produit', requirements: ['id' => '\d+'])]
    public function show(ProduitRepository $produitRepository, int $id): Response
    {
        $produit = $produitRepository->find($id);

        if (!$produit) {
            throw $this->createNotFoundException('Produit non trouvé.');
        }

        return $this->render('pages/produit.html.twig', [
            'produit' => $produit,
        ]);
    }
}
