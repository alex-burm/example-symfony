<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegisterForm;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class RegisterController extends AbstractController
{
    #[Route('/register', name: 'register')]
    public function form(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
        MailerInterface $mailer
    ): Response {
        $form = $this->createForm(RegisterForm::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $user = $form->getData();

            $newPassword = $passwordHasher
                ->hashPassword(
                    $user,
                    $form->get('password')->getData()
                );

            $user->setPassword($newPassword);

            $value = microtime(true).$user->getEmail();
            $user->setConfirmationCode(\md5($value));

            $this->sendConfirmationEmail($user, $mailer);

            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('confirmation');
        }

        return $this->render('register/form.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/confirmation', name: 'confirmation')]
    public function confirmation(Request $request, EntityManagerInterface $entityManager): Response
    {
        $code = $request->query->get('code');
        if (false === empty($code)) {
            $user = $entityManager->getRepository(User::class)->findOneBy([
                'confirmationCode' => $code,
            ]);
            if (false === \is_null($user)) {
                $user->setConfirmationCode(null);

                $entityManager->flush();
                return $this->redirectToRoute('homepage');
            }
        }
        return $this->render('register/confirmation.html.twig');
    }

    public function sendConfirmationEmail(User $user, MailerInterface $mailer)
    {
        $message = new Email();
        $message->from('ask@drivedcrm.com');
        $message->to($user->getEmail());
        $message->html($this->renderView('mail/register.html.twig', [
            'user' => $user,
        ]));
        $message->subject('Welcome to Symfony Blog!');

        $mailer->send($message);
    }
}
