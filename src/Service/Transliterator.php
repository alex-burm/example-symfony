<?php

declare(strict_types=1);

namespace App\Service;
use Symfony\Component\String\Slugger\AsciiSlugger;

class Transliterator
{
    public function __invoke($text, $separatorUsed, $objectBeingSlugged)
    {
        // privet-mir-eto-ia-sadfdsfasd
        // privet-mir-eto-a-2
        // privet-mir-eto-ya-3
        $slugger = new AsciiSlugger();
        return $slugger->slug(
            str_replace('я', 'ya', $text),
            $separatorUsed
        );
    }
}
