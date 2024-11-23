<?php

declare(strict_types=1);

namespace App\Message;

class FeedbackMessage
{
    public function __construct(
        protected string $name,
        protected string $email,
        protected string $subject,
        protected string $message,
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getSubject(): string
    {
        return $this->subject;
    }
}
