<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DefaultControllerTest extends WebTestCase
{
    public function testMainPage(): void
    {
        // "client" клиент это наш тестовый браузер
        // через который мы будем делать запросы
        $client = static::createClient();

        // послать GET запрос на главную страницу
        $crawler = $client->request('GET', '/');

        // проверяем что страница отдала успешный код ответа
        $this->assertResponseIsSuccessful();

        // проверяем на текст в заголовке
        $this->assertSelectorTextContains('h2', 'Welcome, Symfony community!');
    }
}
