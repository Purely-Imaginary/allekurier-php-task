<?php

namespace App\Core\User\Application\Command\CreateUser;

use App\Core\User\Domain\Event\UserCreatedEvent;
use App\Core\User\Domain\Exception\UserAlreadyExistsException;
use App\Core\User\Domain\Exception\UserNotFoundException;
use App\Core\User\Domain\Repository\UserRepositoryInterface;
use App\Core\User\Domain\User;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
class CreateUserHandler
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
        private readonly MessageBusInterface $messageBus
    ) {}

    public function __invoke(CreateUserCommand $command): void
    {
        try {
            $this->userRepository->getByEmail($command->email);
            throw new UserAlreadyExistsException('Użytkownik z tym adresem email już istnieje!');
        } catch (UserNotFoundException $e) {
            // This is the expected "good" path, so we continue.
        }

        $user = new User($command->email);

        $this->userRepository->save($user);
        $this->userRepository->flush();

        $this->messageBus->dispatch(new UserCreatedEvent($user));
    }
}
