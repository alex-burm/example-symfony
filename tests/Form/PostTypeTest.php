<?php

namespace App\Tests\Form;

use App\Entity\Post;
use App\Form\PostType;
use Symfony\Component\Form\Test\TypeTestCase;

class PostTypeTest extends TypeTestCase
{
    public function testSubmitValidData()
    {
        $publishedAt = new \DateTimeImmutable();

        $formData = [
            'name' => 'PhpUnit name test',
            'content' => 'PhpUnit test content',
//            'publishedAt' => $publishedAt,
        ];

        $actual = new Post();
        $form = $this->factory->create(PostType::class, $actual);


        $form->submit($formData);

        $expected = new Post();
        $expected->setName('PhpUnit name test');
        $expected->setContent('PhpUnit test content');
        $expected->setPublishedAt($publishedAt);

        $this->assertTrue($form->isSynchronized());
        $this->assertEquals($expected, $actual);
    }
}
