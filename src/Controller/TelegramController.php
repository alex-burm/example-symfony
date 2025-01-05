<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\ContentPage;
use App\Entity\Feedback;
use App\Entity\Post;
use App\Entity\User;
use App\EventListener\AuthSuccessHandler;
use App\Form\FeedbackForm;
use App\Message\FeedbackMessage;
use App\Repository\PostRepository;
use App\Service\ExportInterface;
use BotMan\BotMan\BotMan;
use BotMan\BotMan\Messages\Attachments\File;
use BotMan\BotMan\BotManFactory;
use BotMan\BotMan\Drivers\DriverManager;
use BotMan\BotMan\Messages\Attachments\Image;
use BotMan\BotMan\Messages\Outgoing\OutgoingMessage;
use BotMan\Drivers\Telegram\TelegramDriver;
use BotMan\Drivers\Telegram\TelegramFileDriver;
use BotMan\Drivers\Telegram\TelegramPhotoDriver;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Symfony\Contracts\Cache\CacheInterface;

class TelegramController extends AbstractController
{
    public function __construct(
        protected EntityManagerInterface $entityManager
    ) {
    }

    #[Route('/telegram/hook', name: 'telegram')]
    public function hook(
        Request $request,
        LoggerInterface $logger,
        KernelInterface $kernel,
        RouterInterface $router,
    )
    {
        $logger->info('Got msg:' . print_r($request->getPayload()->all(), true));

        DriverManager::loadDriver(TelegramDriver::class);
        DriverManager::loadDriver(TelegramPhotoDriver::class);
        DriverManager::loadDriver(TelegramFileDriver::class);

        $botman = BotManFactory::create([
            'telegram' => [
                'token' => $_ENV['TELEGRAM_TOKEN'],
            ]
        ]);

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
        });

        $botman->hears('/start{action}', function ($bot, $action) use ($router) {
            try {
                $action = \trim($action);
                if ('login' === $action) {
                    $params = [
                        'username' => $bot->getUser()->getUsername(),
                        'first_name' => $bot->getUser()->getFirstName(),
                        'last_name' => $bot->getUser()->getLastName(),
                    ];
                    $url = $router->generate('telegram_callback', $params, RouterInterface::ABSOLUTE_URL);
                    $bot->reply('Welcome! Click to continue ' . $url);
                }
            } catch (\Exception $e) {
                $bot->reply('Exception: ' . $e->getMessage());
            }
        });

        $botman->receivesImages(function($bot, $images) use ($logger) {
            $logger->info('receivesImages');
            foreach ($images as $image) {
                $url = $image->getUrl(); // The direct url
                $payload = $image->getPayload(); // The original payload

                $bot->reply('URL: ' . $url);
                $bot->reply('Payload: ' . print_r($payload, true));
            }
        });

        $botman->receivesFiles(function($bot, $files) use ($logger, $kernel) {
            $logger->info('receivesFiles');
            foreach ($files as $file) {
                $url = $file->getUrl(); // The direct url
                $payload = $file->getPayload(); // The original payload

                file_put_contents($kernel->getProjectDir() . '/var/telegram/' . $payload['file_name'], file_get_contents($url));
                $bot->reply('URL: ' . $url);
                $bot->reply('Payload: ' . print_r($payload, true));
            }
        });

        $botman->listen();
        $logger->info("Hook received");
        return new Response();
    }

    #[Route('telegram/callback', name: 'telegram_callback')]
    public function callback(
        Request $request,
        TokenStorageInterface $storage,
        AuthSuccessHandler $successHandler,
    ) {
        $user = $this->getOrCreate($request->query->all());

        $token = new UsernamePasswordToken($user, 'main', $user->getRoles());
        $storage->setToken($token);

        $successHandler->onAuthenticationSuccess($request, $token);

        return $this->redirectToRoute('admin_dashboard');
    }

    protected function getOrCreate(array $data)
    {
        if (false === \array_key_exists('username', $data)
            || \is_null($data['username'])
        ) {
            throw $this->createAccessDeniedException();
        }

        $user = $this->entityManager->getRepository(User::class)
            ->findOneBy([
                'login' => $data['username'],
            ]);

//        $user = $this->entityManager->getRepository(User::class)
//            ->findOneByLogin($data['login']);

        if (\is_null($user)) {
            $user = new User();
            $user->setLogin($data['username']);
            $user->setFirstName($data['first_name'] ?? 'Guest');
            $user->setLastName($data['last_name'] ?? '');

            $this->entityManager->persist($user);
            $this->entityManager->flush();
        }

        return $user;
    }
}
