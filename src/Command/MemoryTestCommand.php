<?php

namespace App\Command;

use App\Entity\BlogComment;
use App\Service\OpenAiService;
use App\Service\PineconeService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'memory-test')]
class MemoryTestCommand extends Command
{
    public function __construct(
        protected EntityManagerInterface $entityManager,
        protected OpenAiService $openAi,
        protected PineconeService $pinecone,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /*
        $contextMessage = '
            Я, Александр айти наставник, метор по программированию, преподаватель.
            Родился в 1986 г, закончил университет магистратуру в 2009 году,
            по специальности "Компьютерные системы и сети".
        ';
        $this->indexing($contextMessage);
        */
        $questionMessage = 'Александр, что он закончил и в каком году?';
        $this->quering($questionMessage);

        return Command::SUCCESS;
    }

    protected function quering(string $message): string
    {
        $embedding = $this->openAi->getEmbedding($message);

        $result = $this->pinecone->query($embedding);
        $text = $result['matches'][0]['metadata']['text'];

        $prompt = '
            У тебя есть контекст, используй его:
            
            ' . $text . '
            --------------------------------
            
        ' . $message;

        return $this->openAi->completions($prompt);
    }

    protected function indexing(string $message): void
    {
        $embedding = $this->openAi->getEmbedding($message);
        $this->pinecone->upsert([
            [
                'id' => uniqid('vector'),
                'values' => $embedding,
                'metadata' => [
                    'text' => $message,
                ],
            ],
        ]);
    }
}
