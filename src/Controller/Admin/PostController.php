<?php

namespace App\Controller\Admin;

use App\Entity\Post;
use App\EventListener\PostChangesEvent;
use App\Form\PostType;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/post')]
class PostController extends AbstractController
{
    #[Route('/', name: 'app_admin_post_index', methods: ['GET'])]
    public function index(
        Request $request,
        PostRepository $postRepository,
        PaginatorInterface $paginator,
        EntityManagerInterface $entityManager,
    ): Response {
        $page = max($request->query->get('page', 1), 1);
        $limit = 3;

        $dql = 'SELECT a FROM App\Entity\Post a ORDER BY a.id DESC';
        $query = $entityManager->createQuery($dql);
        $posts = $paginator->paginate(
            $query,
            $page,
            $limit,
        );

        return $this->render('admin/post/index.html.twig', [
            'posts' => $posts,
        ]);
    }

    #[Route('/new', name: 'app_admin_post_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager,
        EventDispatcherInterface $dispatcher,
    ): Response {
        $post = new Post();
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($post);
            $entityManager->flush();

            $category = $post->getCategory();
            $dispatcher->dispatch(new PostChangesEvent($category));

            return $this->redirectToRoute('app_admin_post_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/post/new.html.twig', [
            'post' => $post,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_admin_post_show', methods: ['GET'])]
    public function show(Post $post): Response
    {
        return $this->render('admin/post/show.html.twig', [
            'post' => $post,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_admin_post_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Post $post, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_admin_post_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/post/edit.html.twig', [
            'post' => $post,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_admin_post_delete', methods: ['POST'])]
    public function delete(
        Request $request,
        Post $post,
        EntityManagerInterface $entityManager,
        EventDispatcherInterface $dispatcher
    ): Response {
        if ($this->isCsrfTokenValid('delete'.$post->getId(), $request->request->get('_token'))) {
            $category = $post->getCategory();

            $entityManager->remove($post);
            $entityManager->flush();

            $dispatcher->dispatch(new PostChangesEvent($category));
        }

        return $this->redirectToRoute('app_admin_post_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/delete-selected', name: 'app_admin_post_delete_selected', methods: ['POST'], priority: 99)]
    public function deleteSelected(
        Request $request,
        EntityManagerInterface $entityManager,
    ): Response {
        foreach ($request->request->all('id') as $id) {
            $post = $entityManager->find(Post::class, $id);
            if (false === \is_null($post)) {
                $entityManager->remove($post);
            }
        }

        $entityManager->flush();
        return $this->redirectToRoute('app_admin_post_index', [], Response::HTTP_SEE_OTHER);
    }
}
