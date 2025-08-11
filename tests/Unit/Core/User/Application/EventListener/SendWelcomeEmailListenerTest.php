<?php

namespace App\Tests\Unit\Core\User\Application\EventListener;

use App\Common\Mailer\MailerInterface;
use App\Core\User\Application\EventListener\SendWelcomeEmailListener;
use App\Core\User\Domain\Event\UserCreatedEvent;
use App\Core\User\Domain\User;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SendWelcomeEmailListenerTest extends TestCase
{
    private MailerInterface|MockObject $mailer;
    private SendWelcomeEmailListener $listener;

    protected function setUp(): void
    {
        parent::setUp();

        $this->listener = new SendWelcomeEmailListener(
            $this->mailer = $this->createMock(MailerInterface::class)
        );
    }

    public function test_it_sends_welcome_email_when_user_created(): void
    {
        $email = 'test@example.com';
        $user = $this->createMock(User::class);
        $user->method('getEmail')->willReturn($email);
        
        $event = new UserCreatedEvent($user);

        $this->mailer->expects(self::once())
            ->method('send')
            ->with(
                $email,
                'Rejestracja konta',
                'Zarejestrowano konto w systemie. Aktywacja konta trwa do 24h'
            );

        $this->listener->__invoke($event);
    }
}