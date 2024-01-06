<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DefaultController extends AbstractController
{
    #[Route("/", name: "homepage")]
    public function homepage(): Response
    {
        return $this->render('default/homepage.html.twig');
    }

    #[Route("/about", name: "about")]
    public function about(): Response
    {
        return $this->render('default/about.html.twig');
    }

    #[Route("/contact", name: "contact")]
    public function contact(): Response
    {
        return $this->render('default/contact.html.twig');
    }
}
