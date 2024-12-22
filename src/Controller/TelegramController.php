<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\ContentPage;
use App\Entity\Feedback;
use App\Entity\Post;
use App\Form\FeedbackForm;
use App\Message\FeedbackMessage;
use App\Repository\PostRepository;
use App\Service\ExportInterface;
use BotMan\BotMan\BotMan;
use BotMan\BotMan\BotManFactory;
use BotMan\BotMan\Drivers\DriverManager;
use BotMan\BotMan\Messages\Attachments\Image;
use BotMan\BotMan\Messages\Outgoing\OutgoingMessage;
use BotMan\Drivers\Telegram\TelegramDriver;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Symfony\Contracts\Cache\CacheInterface;

class TelegramController extends AbstractController
{
    #[Route('/telegram/hook', name: 'telegram')]
    public function hook(Request $request, LoggerInterface $logger)
    {
        $logger->info('Got msg:' . print_r($request->getPayload()->all(), true));

        DriverManager::loadDriver(TelegramDriver::class);
        $botman = BotManFactory::create([
            'telegram' => [
                'token' => $_ENV['TELEGRAM_TOKEN'],
            ]
        ]);
        $botman->loadDriver(TelegramDriver::class);
        $botman->hears('hello', function (BotMan $bot) {
            $image = new Image('https://burm.me/img/hero.jpg');
            $message = OutgoingMessage::create('Got "hello"!');
            $message->withAttachment($image);

            $bot->reply($message);
        });
        $botman->hears('/start', function ($bot) {
            try {
                $bot->reply('Welcome! UserID:' . $bot->getUser()->getId());
            } catch (\Exception $e) {
                $bot->reply('Exception: ' . $e->getMessage());
            }
//            $bot->reply('Welcome!');
        });

        $botman->listen();
        $logger->info("Hook received");
        return new Response();
    }
}
