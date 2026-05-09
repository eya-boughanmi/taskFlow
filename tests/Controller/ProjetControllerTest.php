<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ProjetControllerTest extends WebTestCase
{
    public function testProjetIndexReturns200(): void
    {
        $client = static::createClient();

        $client->request('GET', '/projets');

        $this->assertResponseIsSuccessful();
    }

    public function testProjetIndexContainsTable(): void
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/projets');

        $this->assertSelectorExists('table');
    }

    public function testProjetNewRequiresLogin(): void
    {
        $client = static::createClient();

        $client->request('GET', '/projets/nouveau');

        $this->assertResponseStatusCodeSame(302);
    }
}