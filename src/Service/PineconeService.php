<?php

namespace App\Service;

use GuzzleHttp\Client;

class PineconeService
{
    protected Client $client;

    public function __construct(
        protected string $apiKey,
        protected string $host,
    ) {

        $this->client = new Client([
            'base_uri' => $this->host,
            'headers' => [
                'Api-Key' => $this->apiKey,
                'Content-Type' => 'application/json'
            ]
        ]);
    }

    public function upsert(array $vectors): array
    {
        $response = $this->client->post('/vectors/upsert', [
            'json' => ['vectors' => $vectors]
        ]);
        return \json_decode($response->getBody()->getContents(), true);
    }

    public function query(array $vector, int $topK = 5): array
    {
        $response = $this->client->post('/query', [
            'json' => [
                'vector' => $vector,
                'topK' => $topK,
                'includeMetadata' => true
            ]
        ]);
        return \json_decode($response->getBody()->getContents(), true);
    }
}
