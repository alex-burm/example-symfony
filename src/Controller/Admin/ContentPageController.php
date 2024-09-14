<?php

namespace App\Controller\Admin;

use App\Entity\ContentPage;
use App\Form\ContentPageType;
use App\Repository\ContentPageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/content')]
final class ContentPageController extends AbstractController
{
    #[Route(name: 'app_admin_content_index', methods: ['GET'])]
    public function index(ContentPageRepository $contentPageRepository): Response
    {
        return $this->render('admin/content_page/index.html.twig', [
            'content_pages' => $contentPageRepository->findAll(),
        ]);
    }

    #[Route('/{id}/edit', name: 'app_admin_content_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, ContentPage $contentPage, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ContentPageType::class, $contentPage);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_admin_content_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/content_page/edit.html.twig', [
            'content_page' => $contentPage,
            'form' => $form,
        ]);
    }
}
