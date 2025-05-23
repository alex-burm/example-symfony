<?php

namespace App\Tests\Controller\Admin;

use App\Repository\UserRepository;
use App\Service\ExportInterface;
use App\Service\ExportSerialize;
use PHPUnit\Framework\Attributes\Depends;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpKernel\KernelInterface;

class PostControllerTest extends WebTestCase
{
    public function testEditContent(): int
    {
        $client = static::createClient();

        $user = self::getContainer()->get(UserRepository::class)
            ->findOneByLogin('admin');

        $client->loginUser($user);

        $client->request('GET', '/admin/post/');
        $this->assertResponseIsSuccessful();

        $client->request('GET', '/admin/post/new');
        $this->assertResponseRedirects();

        $crawler = $client->followRedirect();
        $this->assertResponseIsSuccessful();

        // http://localhost:9090/admin/post/1063/edit
        $uri = $client->getRequest()->getUri();
        $params = \explode('/', $uri);
        $postId = (int)$params[\count($params) - 2];

        $btnNode = $crawler->selectButton('Update');
        $form = $btnNode->form([
            'post[name]' => 'PhpUnit post name 2',
            'post[content]' => 'PhpUnit post content 2',
            'post[publishedAt]' => '2010-01-01',
        ]);

        $dir = self::getContainer()->get(KernelInterface::class)->getProjectDir() . '/var';
        $form['post[image]']->upload($dir . '/test-photo.jpg');

        $client->submit($form);

        return $postId;
    }

    public function testFileUploader()
    {
        $client = static::createClient();

        $user = self::getContainer()->get(UserRepository::class)
            ->findOneByLogin('admin');

        $client->loginUser($user);
        $dir = self::getContainer()->get(KernelInterface::class)->getProjectDir() . '/var';

        copy($dir . '/test-photo.jpg', $dir . '/test-photo-99.jpg');

        $uploadedFile = new UploadedFile(
            $dir . '/test-photo-99.jpg',
            'test-photo.jpg',
        );
        $client->request('POST', '/admin/file-uploader', [], [
            'file' => $uploadedFile,
        ]);
        $this->assertResponseIsSuccessful();

        $content = json_decode($client->getResponse()->getContent(), true);
        $this->assertTrue($content['status']);
    }

    #[Depends('testEditContent')]
    public function testDelete(int $postId): void
    {
        $client = static::createClient();

        $user = self::getContainer()->get(UserRepository::class)
            ->findOneByLogin('admin');

        $client->loginUser($user);

        $uri = '/admin/post/' . $postId . '/edit';
        $crawler = $client->request('GET', $uri);
        $btn = $crawler->selectButton('Delete');
        $client->submit($btn->form([]));

        $this->assertResponseRedirects('/admin/post/');
    }

}
