<?php

namespace App\Tests\Controller;

use App\Repository\PostRepository;
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

//        $repository = $this->getMockBuilder(PostRepository::class)
//            ->disableOriginalConstructor()
//            ->onlyMethods(['getPostListQuery'])
//            ->getMock();
//
//        $repository->method('getPostListQuery')
//            ->willThrowException(new \Exception('phpunit hello Alex!'));
//
//        self::getContainer()->set(PostRepository::class, $repository);

        // послать GET запрос на главную страницу
        $crawler = $client->request('GET', '/');

        // проверяем что страница отдала успешный код ответа
        $this->assertResponseIsSuccessful();

        // проверяем на текст в заголовке
        $this->assertSelectorTextContains('h2', 'Welcome, Symfony community!');

        $client->xmlHttpRequest('GET', '/');
        $this->assertResponseIsSuccessful();
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

    public function testException()
    {
        $client = static::createClient();
        $client->catchExceptions(false);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Something went wrong');
        $this->expectExceptionCode(999);
        $client->request('GET', '/test-exception');
    }

    public function testFakeExport()
    {
        $client = static::createClient();

        $exporter = $this->getMockBuilder(ExportSerialize::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['run'])
            ->getMock();

        $file = '/tmp/1.txt';
        file_put_contents($file, 'hello Alex!');

        $exporter->method('run')
            ->willReturn($file);

        self::getContainer()->set(ExportInterface::class, $exporter);

        $client->request('GET', '/export');
        $this->assertResponseIsSuccessful();

        $this->expectOutputString('hello Alex!');
        $client->getResponse()->sendContent();
    }
}
