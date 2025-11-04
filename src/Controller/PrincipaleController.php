<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PrincipaleController extends AbstractController
{
    #[Route('/', name: 'app.principale')]
    public function index(): Response
    {
        return $this->render('pages/principale.html.twig');
    }
}

