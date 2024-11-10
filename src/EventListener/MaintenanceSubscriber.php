<?php

namespace App\EventListener;

use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Twig\Environment;

class MaintenanceSubscriber implements EventSubscriberInterface
{
    public function __construct(
        protected Environment $twig,
        protected Security $security,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => 'onKernelRequest',
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $isEnabled = (bool)$_ENV['MAINTENANCE_MODE'];

        if ($isEnabled && false === $this->security->isGranted('ROLE_ADMIN')) {
            $response = new Response(
                $this->twig->render('default/maintenance.html.twig')
            );
            $event->setResponse($response);
            $event->stopPropagation();
        }
    }
}
