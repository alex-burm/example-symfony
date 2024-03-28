<?php

namespace App\EventListener;

use App\Entity\Post;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PostChangesListener implements EventSubscriberInterface
{
    public function __construct(
        protected EntityManagerInterface $entityManager,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            PostChangesEvent::class => 'onPostChangesEvent',
        ];
    }

    public function onPostChangesEvent(PostChangesEvent $event)
    {
        $category = $event->getCategory();

        $postCnt = $this->entityManager->getRepository(Post::class)->count([
            'category' => $category,
        ]);

        $category->setPostCnt($postCnt);
        $this->entityManager->flush();
    }
}
