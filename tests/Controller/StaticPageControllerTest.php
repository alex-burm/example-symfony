<?php

namespace App\Tests\Controller;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Tests\DataProvider\ControllerProvider;

class StaticPageControllerTest extends WebTestCase
{
    #[DataProviderExternal(ControllerProvider::class, 'staticUrl')]
    public function testStaticPage(string $url, string $name): void
    {
        $client = static::createClient();
        $client->request('GET', $url);
        $this->assertResponseIsSuccessful();
    }


}
