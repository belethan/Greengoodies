<?php

namespace translations\DataFixtures;

use App\Entity\Produit;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class ProduitFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        // Quelques catégories de produits écologiques
        $categories = [
            'Savon bio à l’huile d’argan',
            'Shampoing solide zéro déchet',
            'Gourde en inox isotherme',
            'Tote bag en coton recyclé',
            'Crème hydratante naturelle',
            'Brosse à dents en bambou',
            'Coffret bien-être éthique',
            'Déodorant naturel sans aluminium',
            'Huile essentielle bio relaxante'
        ];

        $id = 1;
        foreach ($categories as $titre) {
            $produit = new Produit();

            // Le champ nom = titre
            $produit->setNom($titre);
            $produit->setTitre($titre);

            // Sous-titre aléatoire lié à l’univers écologique
            $produit->setSousTitre($faker->randomElement([
                'Un geste simple pour la planète 🌿',
                'Revêtement Bio en olivier & sac de transport',
                'Pour une salle de bain éco-friendly',
                'Beauté éthique et naturelle 💚',
                'Un produit zéro déchet à adopter ♻️',
                'Respectueux de votre peau et de la nature 🌸',
                'Fabriqué en France avec amour 🇫🇷'
            ]));

            // Description : 3 lignes, 50 caractères mini chacune
            $description = implode("\n", [
                $faker->text(80),
                $faker->text(75),
                $faker->text(70)
            ]);
            $produit->setDescription($description);

            // Prix entre 6.90 et 39.90 €
            $produit->setPrix($faker->randomFloat(2, 6.90, 39.90));

            // ImageProduit = nom du fichier basé sur l'ID
            $produit->setImageProduit("ImgProduit_{$id}.jpg");

            $manager->persist($produit);
            $id++;
        }

        $manager->flush();
    }
}
