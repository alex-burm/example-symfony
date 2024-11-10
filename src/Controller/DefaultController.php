<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\ContentPage;
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
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Symfony\Contracts\Cache\CacheInterface;

class DefaultController extends AbstractController
{
    public const LIMIT = 5;

    #[Route('/', name: 'homepage')]
    public function homepage(
        Request $request,
        EntityManagerInterface $em,
        PaginatorInterface $paginator,
        TagAwareCacheInterface $cache,
    ): Response {
        if ($request->query->has('keyword')) {
            $query = $em->getRepository(Post::class)
                ->getPostListQuery($request->query->get('keyword'));

            $posts = $paginator->paginate(
                $query,
                max(0, $request->get('page', 1)),
                self::LIMIT
            );
        } else {
            $posts = $cache->get('post_list', function (ItemInterface $item) use ($em, $paginator) {
                $query = $em->getRepository(Post::class)
                    ->getPostListQuery();

                $posts = $paginator->paginate(
                    $query,
                    1,
                    self::LIMIT
                );

                $item->tag('posts');

                return $posts;
            });
        }

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

    /**
     * @param Request                $request
     * @param EntityManagerInterface $em
     * @param MailerInterface        $mailer
     * @return Response
     */
    #[Route('/contact', name: 'contact')]
    public function contact(
        Request $request,
        EntityManagerInterface $em,
        MailerInterface $mailer,
    ): Response {
        $form = $this->createForm(FeedbackForm::class);
        $form->handleRequest($request);
        if ($form->isSubmitted()
            && $form->isValid()
            && $this->captchaVerify($request->request->get('token'))
        ) {
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

    protected function captchaVerify(string $token): bool
    {
        /**
         * RL: https://www.google.com/recaptcha/api/siteverify METHOD: POST
         *
         * POST Parameter    Description
         * secret    Required. The shared key between your site and reCAPTCHA.
         * response    Required. The user response token provided by the reCAPTCHA client-side integration on your site.
         * remoteip    Optional. The user's IP address
         */

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://www.google.com/recaptcha/api/siteverify');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, [
            'secret' => $_ENV['CAPTCHA_SECRET'],
            'response' => $token,
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $response = curl_exec($ch);
        curl_close($ch);
        $data = json_decode($response, true);
        return $data['success'];
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
    public function getPosts(
        Request $request,
        EntityManagerInterface $em,
        TagAwareCacheInterface $cache,
    ): Response {
        if (0 === \strlen($request->query->get('id', ''))
            || 0 === \strlen($request->query->get('date', ''))
        ) {
            throw $this->createNotFoundException('Invalid request');
        }

        $id = $request->query->get('id');
        $date = $request->query->get('date');

        $key = 'post_list_'.\md5($id.$date);
        $posts = $cache->get($key, function (ItemInterface $item) use ($em, $id, $date) {
            $posts = $em->getRepository(Post::class)
                ->getNextPosts(
                    $id,
                    $date,
                    self::LIMIT
                );

            $item->tag('posts');

            return $posts;
        });

        $posts = \array_map(function ($x) {
            $url = $this->generateUrl('post_show', [
                'slug' => $x['slug'],
            ]);

            $x['url'] = $url;

            return $x;
        }, $posts);

        return new JsonResponse($posts);
    }

    public function text(string $name, EntityManagerInterface $entityManager): Response
    {
        static $list = null;

        if (\is_null($list)) {
            $list = $entityManager->getRepository(ContentPage::class)->findAll();
        }

        $records = \array_filter(
            $list,
            static fn (ContentPage $x) => $x->getName() === $name
        );

        if (\count($records) === 0) {
            return new Response;
        }

        $record = \current($records);
        return new Response($record->getValue());
    }

    #[Route('/test-exception')]
    public function testException()
    {
        throw new \Exception('Something went wrong', 999);
    }
}
