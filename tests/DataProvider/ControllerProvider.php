<?php

namespace App\Tests\DataProvider;

class ControllerProvider
{
    public static function staticUrl(): array
    {
        return [
            ['/', 'Homepage'],
            ['/about', 'About'],
            ['/contact', 'Contact'],
            ['/login', 'Login'],
        ];
    }
}
