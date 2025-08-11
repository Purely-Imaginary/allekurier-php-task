<?php

namespace App\Tests\Unit\Core\User\Application\Command\CreateUser;

use App\Core\User\Application\Command\CreateUser\CreateUserCommand;
use App\Core\User\Application\Command\CreateUser\CreateUserHandler;
use App\Core\User\Domain\Event\UserCreatedEvent;
use App\Core\User\Domain\Exception\UserAlreadyExistsException;
use App\Core\User\Domain\Exception\UserNotFoundException;
use App\Core\User\Domain\Repository\UserRepositoryInterface;
use App\Core\User\Domain\User;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

class CreateUserHandlerTest extends TestCase
{
    private UserRepositoryInterface|MockObject $userRepository;
    private MessageBusInterface|MockObject $messageBus;
    private CreateUserHandler $handler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->handler = new CreateUserHandler(
            $this->userRepository = $this->createMock(UserRepositoryInterface::class),
            $this->messageBus = $this->createMock(MessageBusInterface::class)
        );
    }

    public function test_handle_creates_inactive_user(): void
    {
        $email = 'test@example.com';
        $command = new CreateUserCommand($email);

        // Setup the repository to throw UserNotFoundException when getByEmail is called
        $this->userRepository->expects(self::once())
            ->method('getByEmail')
            ->with($email)
            ->willThrowException(new UserNotFoundException());

        $this->userRepository->expects(self::once())
            ->method('save')
            ->with(self::callback(function (User $user) use ($email) {
                return $user->getEmail() === $email && !$user->isActive();
            }));

        $this->userRepository->expects(self::once())
            ->method('flush');

        $this->handler->__invoke($command);
    }

    public function test_handle_dispatches_user_created_event(): void
    {
        $email = 'test@example.com';
        $command = new CreateUserCommand($email);

        // Setup the repository to throw UserNotFoundException when getByEmail is called
        $this->userRepository->expects(self::once())
            ->method('getByEmail')
            ->with($email)
            ->willThrowException(new UserNotFoundException());

        $this->messageBus->expects(self::once())
            ->method('dispatch')
            ->with(self::callback(function ($event) use ($email) {
                return $event instanceof UserCreatedEvent && $event->user->getEmail() === $email;
            }))
            ->willReturn(new Envelope(new \stdClass()));

        $this->handler->__invoke($command);
    }

    public function test_handle_throws_exception_when_user_already_exists(): void
    {
        $email = 'existing@example.com';
        $command = new CreateUserCommand($email);
        $existingUser = new User($email);

        // Setup the repository to return a user when getByEmail is called
        $this->userRepository->expects(self::once())
            ->method('getByEmail')
            ->with($email)
            ->willReturn($existingUser);

        // The repository should not be called to save or flush
        $this->userRepository->expects(self::never())
            ->method('save');
        $this->userRepository->expects(self::never())
            ->method('flush');

        // The message bus should not be called to dispatch
        $this->messageBus->expects(self::never())
            ->method('dispatch');

        // Expect UserAlreadyExistsException to be thrown
        $this->expectException(UserAlreadyExistsException::class);
        $this->expectExceptionMessage('User with this email already exists.');

        $this->handler->__invoke($command);
    }
}
