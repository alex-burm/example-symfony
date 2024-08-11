<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class MathExtension extends AbstractExtension
{
    public function getFunctions()
    {
        return [
            // new TwigFunction('round', [$this, 'myRound']),
            new TwigFunction(
                'round',
                function ($value) {
                    return round($value);
                }
            ),
        ];
    }

    public function getFilters()
    {
        return [
            new TwigFilter('example', [$this, 'myExample']),
        ];
    }

    public function myExample($value)
    {
        return 'This is example filter ('.$value.')';
    }

    public function myRound($value)
    {
        return round($value);
    }
}
