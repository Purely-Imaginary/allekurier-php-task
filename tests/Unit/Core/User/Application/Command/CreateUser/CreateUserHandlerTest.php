<?php

namespace App\Tests\Unit\Core\User\Application\Command\CreateUser;

use App\Common\Mailer\MailerInterface;
use App\Core\User\Application\Command\CreateUser\CreateUserCommand;
use App\Core\User\Application\Command\CreateUser\CreateUserHandler;
use App\Core\User\Domain\Repository\UserRepositoryInterface;
use App\Core\User\Domain\User;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CreateUserHandlerTest extends TestCase
{
    private UserRepositoryInterface|MockObject $userRepository;
    private MailerInterface|MockObject $mailer;
    private CreateUserHandler $handler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->handler = new CreateUserHandler(
            $this->userRepository = $this->createMock(UserRepositoryInterface::class),
            $this->mailer = $this->createMock(MailerInterface::class)
        );
    }

    public function test_handle_creates_inactive_user(): void
    {
        $email = 'test@example.com';
        $command = new CreateUserCommand($email);

        $this->userRepository->expects(self::once())
            ->method('save')
            ->with(self::callback(function (User $user) use ($email) {
                return $user->getEmail() === $email && !$user->isActive();
            }));

        $this->userRepository->expects(self::once())
            ->method('flush');

        $this->handler->__invoke($command);
    }

    public function test_handle_sends_email_notification(): void
    {
        $email = 'test@example.com';
        $command = new CreateUserCommand($email);

        $this->mailer->expects(self::once())
            ->method('send')
            ->with(
                $email,
                'Rejestracja konta',
                'Zarejestrowano konto w systemie. Aktywacja konta trwa do 24h'
            );

        $this->handler->__invoke($command);
    }
}