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
    name: 'notification',
    description: 'Add a short description for your command',
)]
class NotificationCommand extends Command
{
    public function __construct(
        protected EntityManagerInterface $entityManager,
        protected LoggerInterface $logger,
        protected MailerInterface $mailer,
        protected RouterInterface $router,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $emails = [
            'user1@example.com',
            'user2@example.com',
            'user3@example.com',
            'user4@example.com',
            // ...
        ];

        $io->note('Start working...');
        foreach ($emails as $email) {
            $output->write('Generate email for ' . $email . '...');
            $message = new TemplatedEmail();
            $message->from('ask@drivedcrm.com');
            $message->to($email);
            $message->htmlTemplate('mail/notification.html.twig');
            $message->subject('A new post!');

            $this->mailer->send($message);
            sleep(1);
            $output->writeln(' and sent');
        }

        $io->success("End working.");
        return Command::SUCCESS;
    }
}
