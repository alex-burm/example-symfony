<?php

namespace App\Controller;

use App\Entity\BlogComment;
use App\Entity\Post;
use App\Form\BlogCommentForm;
use BotMan\BotMan\BotManFactory;
use BotMan\BotMan\Drivers\DriverManager;
use BotMan\Drivers\Telegram\TelegramDriver;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class PostController extends AbstractController
{
    #[Route('/post/{slug}', name: 'post_show')]
    public function show(Post $post, EntityManagerInterface $entityManager): Response
    {
        $comments = $entityManager->getRepository(BlogComment::class)
            ->findBy([
                'post' => $post,
                'isModerated' => 1,
            ]);

        return $this->render('post/show.html.twig', [
            'post' => $post,
            'comments' => $comments,
        ]);
    }

    #[Route('/post/{slug}/comment', name: 'post_comment')]
    public function postComment(
        Request $request,
        Post $post,
        EntityManagerInterface $entityManager
    ): Response {
        $form = $this->createForm(BlogCommentForm::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $comment = $form->getData();
            $comment->setPost($post);

            $entityManager->persist($comment);
            $entityManager->flush();

            //
            $config = [
                'telegram' => [
                    'token' => $_ENV['TELEGRAM_TOKEN'],
                ]
            ];

            $message = <<<EOT
Привет, друг!
Мы получили новое сообщение для поста на твоем блоге.

Текст сообщения ниже:
Name: {$comment->getName()}
Email: {$comment->getEmail()}
Message: {$comment->getMessage()}
EOT;

            DriverManager::loadDriver(TelegramDriver::class);
            $botman = BotManFactory::create($config);
            $botman->loadDriver(TelegramDriver::class);
            $botman->say($message, [
                6690637283,
                '-1002482545581'
            ]);

            return $this->render('post/partial/success.html.twig');
        }

        return $this->render('post/partial/comment.html.twig', [
            'post' => $post,
            'form' => $form->createView(),
        ]);
    }
}
