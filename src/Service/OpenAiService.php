<?php

namespace App\Service;

use OpenAI\Client;

class OpenAiService
{
    protected Client $client;

    public function __construct(
        string $apiKey,
    ) {
        $this->client = \OpenAI::client($apiKey);
    }

    public function completions(string $prompt): string
    {
        $response = $this->client->chat()->create([
            'model' => 'gpt-4.1',
            'messages' => [
                ['role' => 'user', 'content' => $prompt],
            ],
            'temperature' => 0.7,
            'top_p' => 1,
            'max_tokens' => 1000,
        ]);

        $result = '';
        foreach ($response->choices as $choice) {
            $result .= $choice->message->content;
        }
        return $result;
    }

    public function getEmbedding(string $prompt): array
    {
        $response = $this->client->embeddings()->create([
            'model' => 'text-embedding-3-small',
            'input' => $prompt,
        ]);

        return $response->embeddings[0]->embedding;
    }

    public function moderateComment(string $message): int
    {
        $moderateFunctionSchema = [
            'name' => 'moderate_comment',
            'description' => 'Analyze the user comment and assign a rating from 1 (completely safe) to 10 (extremely offensive or spammy).',
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'rating' => [
                        'type' => 'integer',
                        'description' => 'Rating from 1 to 10: 1-3 = safe and meaningful content, 4-6 = borderline (irrelevant but readable), 7-8 = offensive language, 9-10 = clear spam, abusive content, nonsensical or machine-generated text (e.g., Lorem Ipsum, random characters like "asdasd qweqwe").',
                        'minimum' => 1,
                        'maximum' => 10,
                    ]
                ],
                'required' => ['rating']
            ]
        ];

        $response = $this->client->chat()->create([
            'model' => 'gpt-4.1',
            'messages' => [
                [
                    'role' => 'user',
                    'content' => \sprintf('Evaluate this comment and assign a rating from 1 to 10: %s', $message),
                ],
            ],
            'functions' => [$moderateFunctionSchema],
            'function_call' => 'auto', // none // ["name" => "moderate_comment"]
            'temperature' => 0.7,
            'top_p' => 1,
            'max_tokens' => 1000,
        ]);

        $functionCall = $response->choices[0]->message->functionCall ?? null;
        if ('moderate_comment' !== $functionCall?->name) {
            throw new \RuntimeException('Function call not triggered by GPT.');
        }

        $arguments = json_decode($functionCall->arguments, true);
        return (int) ($arguments['rating'] ?? 0);
    }
}
