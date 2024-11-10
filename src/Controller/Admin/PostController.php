<?php

namespace App\Controller\Admin;

use App\Entity\Post;
use App\EventListener\PostChangesEvent;
use App\Form\PostType;
use App\Repository\PostRepository;
use App\Service\UploadService;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

#[Route('/post')]
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
        EntityManagerInterface $entityManager,
    ): Response {
        $post = new Post();
        $post->setName('Untitled post');

        $entityManager->persist($post);
        $entityManager->flush();

        return $this->redirectToRoute('app_admin_post_edit', [
            'id' => $post->getId(),
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
    public function edit(
        Request $request,
        Post $post,
        EntityManagerInterface $entityManager,
        TagAwareCacheInterface $cache,
        UploadService $uploadService,
    ): Response {
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $uploadService->saveFile(
                $post,
                $form,
                'image',
            );

            $entityManager->flush();
            $cache->invalidateTags(['posts']);

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
        EventDispatcherInterface $dispatcher,
        TagAwareCacheInterface $cache,
    ): Response {
        if ($this->isCsrfTokenValid('delete'.$post->getId(), $request->request->get('_token'))) {
            $category = $post->getCategory();

            $entityManager->remove($post);
            $entityManager->flush();

            $cache->invalidateTags(['posts']);

            if (false === \is_null($category)) {
                $dispatcher->dispatch(new PostChangesEvent($category));
            }
        }

        return $this->redirectToRoute('app_admin_post_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/delete-selected', name: 'app_admin_post_delete_selected', methods: ['POST'], priority: 99)]
    public function deleteSelected(
        Request $request,
        EntityManagerInterface $entityManager,
        TagAwareCacheInterface $cache,
        KernelInterface $kernel,
    ): Response {
        foreach ($request->request->all('id') as $id) {
            $post = $entityManager->find(Post::class, $id);
            if (false === \is_null($post)) {
                $entityManager->remove($post);
            }

            $this->deleteImages($post, $kernel->getProjectDir() . '/public');
        }

        $cache->invalidateTags(['posts']);

        $message = '<a href="'.$this->generateUrl('homepage').'">Object successfully deleted!</a>';
        $this->addFlash('success', $message);

        $entityManager->flush();

        return $this->redirectToRoute('app_admin_post_index', [], Response::HTTP_SEE_OTHER);
    }

    /**
     * @param Post   $post
     * @param string $publicDir
     * @return void
     */
    protected function deleteImages(Post $post, string $publicDir): void
    {
        $postFolder = $publicDir . '/uploads/editor/' . $post->getId();
        if (file_exists($postFolder)) {
            $filesystem = new Filesystem();
            $filesystem->remove($postFolder);
        }

//        $content = $post->getContent();
//        preg_match_all('/img\s+src="([\s\S]*?)"/', $content, $matches);
//        foreach ($matches[1] as $imageUrl) {
//            $filepath = $publicDir . $imageUrl;
//            if (file_exists($filepath)) {
//                unlink($filepath);
//            }
//        }
    }
}
