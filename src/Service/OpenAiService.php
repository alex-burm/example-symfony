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
        $response = $this->client->completions()->create([
            'model' => 'gpt-3.5-turbo-instruct',
            'prompt' => $prompt,
        ]);

        $result = '';
        foreach ($response->choices as $choice) {
            $result .= $choice->text;
        }
        return $result;
    }
}
