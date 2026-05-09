<?php

namespace App\Tests\Api;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;

class ProjetApiTest extends ApiTestCase
{
    public function testGetProjets(): void
    {
        $response = static::createClient()->request('GET', '/api/projets');

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');
    }

    

    public function testPostProjetValid(): void
{
    $client = static::createClient();

    $client->request('POST', '/api/projets', [
    'headers' => [
        'Content-Type' => 'application/ld+json',
        'Accept' => 'application/ld+json',
    ],
    'json' => [
        'nom' => 'Projet Test',
        'description' => 'Description test',
        'statut' => 'en_cours',
        'dateLimite' => (new \DateTime('2026-12-31'))->format('Y-m-d'),
    ],
]);

    $this->assertResponseStatusCodeSame(201);
}

public function testPostProjetInvalid(): void
{
    $client = static::createClient();

    $client->request('POST', '/api/projets', [
        'headers' => [
            'Content-Type' => 'application/ld+json',
            'Accept' => 'application/ld+json',
        ],
        'json' => [
            'nom' => '',
            'dateLimite' => null,
        ],
    ]);

    $this->assertResponseStatusCodeSame(422);
}
}