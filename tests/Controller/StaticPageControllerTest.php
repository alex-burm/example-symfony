<?php

namespace App\Tests\Controller;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class StaticPageControllerTest extends WebTestCase
{
    /**
     * @dataProvider staticUrlProvider
     */
    public function testStaticPage(string $url, string $name): void
    {
        $client = static::createClient();
        $client->request('GET', $url);
        $this->assertResponseIsSuccessful();
    }

    public static function staticUrlProvider(): array
    {
        return [
            ['/', 'Homepage'],
            ['/about', 'About'],
            ['/contact', 'Contact'],
            ['/login', 'Login'],
        ];
    }
}
