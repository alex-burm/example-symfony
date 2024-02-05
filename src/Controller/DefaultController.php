<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Post;
use App\Form\FeedbackForm;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
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
    public function contact(Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(FeedbackForm::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $feedback = $form->getData();

            $em->persist($feedback);
            $em->flush();

            return $this->render('default/thanks.html.twig');
        }

        return $this->render('default/contact.html.twig', [
            'form' => $form->createView(),
        ]);
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
