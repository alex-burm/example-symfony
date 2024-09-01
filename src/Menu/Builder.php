<?php

namespace App\Menu;

use Knp\Menu\ItemInterface;
use Knp\Menu\FactoryInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use App\Event\ConfigureMenuEvent;

class Builder
{
    public function __construct(
        protected FactoryInterface $factory,
        protected EventDispatcherInterface $eventDispatcher
    ) {
    }

    public function menu(): ItemInterface
    {
        $menu = $this->factory->createItem('root');
        $menu->addChild('Users', [
            'route' => 'app_admin_user_index',
            'linkAttributes' => [
                'class' => 'test',
            ],
            'extras' => [
                'icon' => 'fa fa-users',
            ]
        ]);
        $menu->addChild('Posts', [
            'route' => 'app_admin_post_index',
        ]);
        $menu->addChild('Categories', [
            'route' => 'app_admin_category_index',
        ]);

        $this->eventDispatcher->dispatch(
            new ConfigureMenuEvent($this->factory, $menu)
        );
        return $menu;
    }
}
