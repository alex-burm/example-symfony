<?php

namespace App\Dto;

use App\Entity\User;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class UserTelegramDto
{
    public function __construct(
        #[NotBlank]
        private string $username,

        #[NotBlank]
        #[Length(min: 3, max: 50)]
        private string $first_name,

        #[Length(min: 3, max: 50)]
        private ?string $last_name = null,
    ) {
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getUser(): User
    {
        $user = new User();
        $user->setLogin($this->username);
        $user->setFirstName($this->first_name);
        $user->setLastName($this->last_name);
        return $user;
    }
}
