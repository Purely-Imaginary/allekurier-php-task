<?php

namespace App\Core\User\Application\Command\CreateUser;

use App\Core\User\Domain\Event\UserCreatedEvent;
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
        $user = new User($command->email);

        $this->userRepository->save($user);
        $this->userRepository->flush();

        $this->messageBus->dispatch(new UserCreatedEvent($user));
    }
}
