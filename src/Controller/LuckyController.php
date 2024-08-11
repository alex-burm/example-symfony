<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class LuckyController extends AbstractController
{
    #[Route('/lucky/number/{page}', name: 'lucky_number', defaults: ['page' => 0])]
    public function number(int $page): Response
    {
        $number = 99; // rand(0, 100);
        $list = [1, 2, 534, 4564, 'Alex', 'test', 'ABC'];

        $latestPosts = [];

        return $this->render('lucky/number.html.twig', [
            'number' => $number,
            'list' => $list,
            'action' => $this,
            'latestPosts' => $latestPosts,
        ]);
    }

    public function latestPosts($a, $b, $c): Response
    {
        $list = [[
                'img' => 'https://uigator.com/bootstrap5/boland-v2.1/assets/img/img-2.jpg',
                'name' => 'Lorem ipsum dolor sit amet ipsum dolor 1',
                'date' => '12 Apr 2000',
            ], [
                'img' => 'https://uigator.com/bootstrap5/boland-v2.1/assets/img/img-2.jpg',
                'name' => 'Lorem ipsum dolor sit amet ipsum dolor 2 ',
                'date' => '12 Apr 2001',
            ],
            [
                'img' => 'https://uigator.com/bootstrap5/boland-v2.1/assets/img/img-2.jpg',
                'name' => 'Lorem ipsum dolor sit amet ipsum dolor 3',
                'date' => '12 Apr 2002',
            ],
            [
                'img' => 'https://uigator.com/bootstrap5/boland-v2.1/assets/img/img-2.jpg',
                'name' => 'Lorem ipsum dolor sit amet ipsum dolor 4',
                'date' => '12 Apr 2003',
            ],
        ];

        return $this->render('lucky/latestPosts.html.twig', [
            'list' => $list,
            'a' => $a,
            'b' => $b,
            'c' => $c,
        ]);
    }
}
