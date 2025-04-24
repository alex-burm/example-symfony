<?php

namespace App\Command;

use App\Entity\BlogComment;
use App\Service\OpenAiService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'moderate')]
class ModerateCommand extends Command
{
    public function __construct(
        protected EntityManagerInterface $entityManager,
        protected OpenAiService $openAi,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Moderating messages...');
        $comments = $this->entityManager->getRepository(BlogComment::class)->findBy([
            'isModerated' => false,
        ]);

        foreach ($comments as $comment) {
            $output->writeln('Moderating comment: ' . $comment->getMessage());
            $this->moderate($comment);
        }

        return Command::SUCCESS;
    }

    protected function moderate(BlogComment $comment): void
    {
        $result = $this->openAi->moderateComment($comment->getMessage());

        $value = (int) \trim($result);
        if ($value <= 0) {
            return;
        }

        if ($value >= 7) {
            $this->entityManager->remove($comment);
        } else {
            $comment->setIsModerated(true);
        }

        $this->entityManager->flush();
    }
}
