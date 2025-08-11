<?php

namespace App\Tests\Unit\Core\User\UserInterface\Cli;

use App\Core\User\Domain\Repository\UserRepositoryInterface;
use App\Core\User\Domain\User;
use App\Core\User\UserInterface\Cli\GetInactiveUsers;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

class GetInactiveUsersTest extends TestCase
{
    private UserRepositoryInterface|MockObject $userRepository;
    private GetInactiveUsers $command;
    private CommandTester $commandTester;

    protected function setUp(): void
    {
        parent::setUp();

        $this->command = new GetInactiveUsers(
            $this->userRepository = $this->createMock(UserRepositoryInterface::class)
        );

        $this->commandTester = new CommandTester($this->command);
    }

    public function test_execute_displays_inactive_users_emails(): void
    {
        $user1 = new User('user1@example.com', false);
        $user2 = new User('user2@example.com', false);

        $this->userRepository->expects(self::once())
            ->method('getInactiveUsers')
            ->willReturn([$user1, $user2]);

        $this->commandTester->execute([]);

        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('user1@example.com', $output);
        $this->assertStringContainsString('user2@example.com', $output);
    }

    public function test_execute_displays_message_when_no_inactive_users(): void
    {
        $this->userRepository->expects(self::once())
            ->method('getInactiveUsers')
            ->willReturn([]);

        $this->commandTester->execute([]);

        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Brak nieaktywnych użytkowników.', $output);
    }
}