<?php

namespace App\EventListener;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;

class AuthSuccessHandler implements AuthenticationSuccessHandlerInterface
{
    public function __construct(
        protected EntityManagerInterface $entityManager,
        protected Security $security,
        protected RouterInterface $router,
    ) {
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token): ?Response
    {
        $user = $token->getUser();
        $user->setLoginAt(new \DateTimeImmutable());
        $user->setLoginCnt($user->getLoginCnt() + 1);

        $this->entityManager->flush();

        $redirectUrl = $this->router->generate('homepage');
        if ($this->security->isGranted('ROLE_ADMIN')) {
            $redirectUrl = $this->router->generate('admin_dashboard');
        }

        return new RedirectResponse($redirectUrl);
    }
}
