<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    public function __construct(
        protected UserPasswordHasherInterface $hasher
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        $user = new User();
        $user->setLogin('admin');
        $user->setFirstName('Admin');
        $user->setLastName('');
        $user->setSex('MALE');
        $user->setAvatar('');
        $user->setPassword(
            $this
                ->hasher
                ->hashPassword($user, '123123')
        );

        $manager->persist($user);
        $manager->flush();
    }
}
