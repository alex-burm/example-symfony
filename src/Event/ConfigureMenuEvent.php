<?php

namespace App\Event;

use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;

class ConfigureMenuEvent
{
    public function __construct(
        protected FactoryInterface $factory,
        protected ItemInterface $menu,
    ) {
    }

    public function getFactory(): FactoryInterface
    {
        return $this->factory;
    }

    public function getMenu(): ItemInterface
    {
        return $this->menu;
    }
}
