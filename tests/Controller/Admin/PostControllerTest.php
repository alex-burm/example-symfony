<?php

namespace App\Tests\Controller\Admin;

use App\Repository\UserRepository;
use App\Service\ExportInterface;
use App\Service\ExportSerialize;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class PostControllerTest extends WebTestCase
{
    public function testEditContent(): void
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

        $btnNode = $crawler->selectButton('Update');
        $form = $btnNode->form([
            'post[name]' => 'PhpUnit post name',
            'post[content]' => 'PhpUnit post content',
            'post[publishedAt]' => '2000-01-01',
        ]);

        $client->submit($form);
    }
}
