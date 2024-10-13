<?php

namespace App\Tests\Controller;

use App\Service\ExportInterface;
use App\Service\ExportSerialize;
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

    public function testExportService()
    {
        $container = self::getContainer();
        $exportService = $container->get(ExportInterface::class);

        $data = [
            'name' => 'Alex',
            'age' => 16,
        ];

        $expected = serialize($data);

        $filename = $exportService->run($data);
        $actual = file_get_contents($filename);

        $this->assertEquals($expected, $actual);
    }

    public function testExportServiceUnit()
    {
        $kernel = self::bootKernel();

        $export = new ExportSerialize($kernel);
        $data = [
            'name' => 'Alex',
            'age' => 16,
        ];

        $expected = serialize($data);

        $filename = $export->run($data);
        $actual = file_get_contents($filename);

        $this->assertEquals($expected, $actual);
    }
}
