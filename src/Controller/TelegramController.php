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
use BotMan\BotMan\BotManFactory;
use BotMan\BotMan\Drivers\DriverManager;
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
        DriverManager::loadDriver(TelegramDriver::class);
        $botman = BotManFactory::create([
            'telegram' => [
                'token' => $_ENV['TELEGRAM_TOKEN'],
            ]
        ]);
        $botman->loadDriver(TelegramDriver::class);

        $botman->say('Hello!', [
            6690637283,
        ]);

        $logger->info("Hook received");
        return new Response();
    }
}
