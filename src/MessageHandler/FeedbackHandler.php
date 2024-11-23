<?php

namespace App\MessageHandler;

use App\Entity\Feedback;
use App\Message\FeedbackMessage;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Mime\Email;
use Twig\Environment;

#[AsMessageHandler]
class FeedbackHandler
{
    public function __construct(
        protected MailerInterface $mailer,
        protected Environment $twig,
        protected LoggerInterface $logger,
    ) {
    }

    public function __invoke(FeedbackMessage $message)
    {
        $this->logger->debug('Wait 5 sec');
        sleep(1);

        $this->logger->debug('Wait 4 sec');
        sleep(1);

        $this->logger->debug('Wait 3 sec');
        sleep(1);

        $this->logger->debug('Wait 2 sec');
        sleep(1);

        $this->logger->debug('Wait 1 sec');
        sleep(1);

        $mail = new Email();
        $mail->from('ask@drivedcrm.com');
        $mail->to('burmistrov.alexander@gmail.com');
        $mail->text('New feedback!');
        $mail->html($this->twig->render('mail/feedback.html.twig', [
            'name' => $message->getName(),
            'message' => $message->getMessage(),
            'contact' => $message->getEmail(),
        ]));
        $mail->subject('Feedback form: ['.$message->getSubject().']');

        $this->mailer->send($mail);

        $this->logger->debug('1 sec for stop');
        sleep(1);
    }
}
