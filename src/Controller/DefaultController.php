<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Post;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DefaultController extends AbstractController
{
    #[Route("/", name: "homepage")]
    public function homepage(EntityManagerInterface $em): Response
    {
        $posts = $em->getRepository(Post::class)->findAll();

        return $this->render('default/homepage.html.twig', [
            'posts' => $posts,
        ]);
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

    public function categoriesWidget(EntityManagerInterface $em): Response
    {
        $list = $em->getRepository(Category::class)->getPopularList();

        return $this->render('default/widget/categories.html.twig', [
            'list' => $list,
        ]);
    }

    public function popularPostsWidget(): Response
    {
        return $this->render('default/widget/popularPosts.html.twig');
    }
}
