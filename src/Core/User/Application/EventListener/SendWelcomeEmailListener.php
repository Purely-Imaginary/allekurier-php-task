<?php

namespace App\Core\User\Application\EventListener;

use App\Common\Mailer\MailerInterface;
use App\Core\User\Domain\Event\UserCreatedEvent;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class SendWelcomeEmailListener
{
    public function __construct(private readonly MailerInterface $mailer)
    {
    }

    public function __invoke(UserCreatedEvent $event): void
    {
        $this->mailer->send(
            $event->user->getEmail(),
            'Rejestracja konta',
            'Zarejestrowano konto w systemie. Aktywacja konta trwa do 24h'
        );
    }
}