<?php

namespace App\Core\User\UserInterface\Cli;

use App\Core\User\Domain\Repository\UserRepositoryInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:user:get-inactive',
    description: 'Pobieranie e-maili nieaktywnych użytkowników'
)]
class GetInactiveUsers extends Command
{
    public function __construct(private readonly UserRepositoryInterface $userRepository)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $users = $this->userRepository->getInactiveUsers();

        if (empty($users)) {
            $output->writeln('Brak nieaktywnych użytkowników.');
            return Command::SUCCESS;
        }

        foreach ($users as $user) {
            $output->writeln($user->getEmail());
        }

        return Command::SUCCESS;
    }
}