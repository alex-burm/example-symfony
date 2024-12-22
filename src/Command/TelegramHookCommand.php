<?php

namespace App\Command;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\RouterInterface;

#[AsCommand(
    name: 'telegram:hook',
)]
class TelegramHookCommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $url = sprintf('https://api.telegram.org/bot%s/setWebhook', $_ENV['TELEGRAM_TOKEN']);

        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => "Content-Type: application/x-www-form-urlencoded\r\n",
                'content' => http_build_query([
                    'url' => 'https://9bb4-194-53-114-1.ngrok-free.app/telegram/hook',
                ])
            ]
        ]);
        $result = file_get_contents($url, false, $context);
        dd($result);
        return Command::SUCCESS;
    }
}
