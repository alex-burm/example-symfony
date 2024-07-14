<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Post;
use App\Form\FeedbackForm;
use App\Repository\PostRepository;
use App\Service\ExportInterface;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Attribute\Route;

class DefaultController extends AbstractController
{
    #[Route('/', name: 'homepage')]
    public function homepage(
        Request $request,
        EntityManagerInterface $em,
        PaginatorInterface $paginator
    ): Response {
        $query = $em->getRepository(Post::class)
            ->getPostListQuery(
                $request->query->get('keyword')
            );

        $posts = $paginator->paginate($query, max(0, $request->get('page', 1)), 3);
        if ($request->isXmlHttpRequest()) {
            return $this->render('default/_posts.html.twig', [
                'posts' => $posts,
            ]);
        }

        return $this->render('default/homepage.html.twig', [
            'posts' => $posts,
        ]);
    }

    #[Route('/about', name: 'about')]
    public function about(): Response
    {
        return $this->render('default/about.html.twig');
    }

    #[Route('/contact', name: 'contact')]
    public function contact(
        Request $request,
        EntityManagerInterface $em,
        MailerInterface $mailer,
    ): Response {
        $form = $this->createForm(FeedbackForm::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $feedback = $form->getData();

            $em->persist($feedback);
            $em->flush();

            $message = new Email();
            $message->from('ask@drivedcrm.com');
            $message->to('burmistrov.alexander@gmail.com');
            $message->text('New feedback!');
            $message->html($this->renderView('mail/feedback.html.twig', [
                'name' => $feedback->getName(),
                'message' => $feedback->getMessage(),
                'contact' => $feedback->getEmail(),
            ]));
            $message->subject('Feedback form: ['.$feedback->getSubject().']');

            $mailer->send($message);

            $this->addFlash('success', 'Thanks for your feedback!');

            return $this->redirectToRoute('contact');
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

    #[Route('/export', name: 'export')]
    public function exportAction(ExportInterface $exporter, PostRepository $postRepository): Response
    {
        $list = $postRepository->getAllItems();
        $file = $exporter->run($list);

        $response = new BinaryFileResponse($file);
        $response->headers->set('Content-type', $exporter->getFileType());
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $exporter->getFriendlyFileName()
        );

        return $response;
    }

    #[Route('/login', name: 'login')]
    public function login(): Response
    {
        return $this->render('default/login.html.twig');
    }

    #[Route('/get-posts', name: 'get-posts')]
    public function getPosts(Request $request, EntityManagerInterface $entityManager): Response
    {
        if (0 === \strlen($request->query->get('id', ''))
            || 0 === \strlen($request->query->get('date', ''))
        ) {
            throw $this->createNotFoundException('Invalid request');
        }

        $posts = $entityManager->getRepository(Post::class)
            ->getNextPosts(
                $request->query->get('id'),
                $request->query->get('date')
            );

        $posts = \array_map(function ($x) {
            $url = $this->generateUrl('post_show', [
                'id' => $x['id'],
            ]);

            $x['url'] = $url;
            return $x;
        }, $posts);

        return new JsonResponse($posts);
    }
}
