<?php
namespace App\Enum;

enum StatutCommande: string
{
    case EN_CONCEPTION = 'En conception';
    case EN_PREPARATION = 'En préparation';
    case EN_LIVRAISON = 'En livraison';
    case LIVREE = 'Livrée';
}

