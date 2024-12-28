<?php

namespace App\EventListener;

use App\Entity\Activity;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Event\RequestEvent;

class AppListener
{
    /**
     * @param Security               $security
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(
        protected Security $security,
        protected EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * @param RequestEvent $event
     * @return void
     */
    public function onKernelRequest(RequestEvent $event): void
    {
        if (false === $event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();

        $activity = new Activity();
        $activity->setUrl($request->getRequestUri());
        $activity->setAgent($request->headers->get('User-Agent', '') ?? '');
        $activity->setIpAddr($request->getClientIp());
        $activity->setQuery(\substr(json_encode($request->query->all()), 0, 1024));
        $activity->setUserId($this->security->getUser()?->getId());

        $this->entityManager->persist($activity);
        $this->entityManager->flush();
    }
}
