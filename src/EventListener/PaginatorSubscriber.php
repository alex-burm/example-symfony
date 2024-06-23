<?php

namespace App\EventListener;

use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\Event\ItemsEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class PaginatorSubscriber implements EventSubscriberInterface
{
    public function __construct(
        protected EntityManagerInterface $entityManager
    ) {
    }

    public function items(ItemsEvent $event): void
    {
        if (false === \is_string($event->target)) {
            return;
        }

        $sql = $event->target.' LIMIT '.$event->getOffset().', '.$event->getLimit();
        $stm = $this->entityManager->getConnection()->prepare($sql);
        $list = $stm->executeQuery()->fetchAllAssociative();

        $event->items = $list;
        $event->count = $this->getCount();

        $event->stopPropagation();
    }

    protected function getCount()
    {
        return $this
            ->entityManager
            ->getConnection()
            ->executeQuery('SELECT FOUND_ROWS()')
            ->fetchOne();
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'knp_pager.items' => ['items', 1],
        ];
    }
}
