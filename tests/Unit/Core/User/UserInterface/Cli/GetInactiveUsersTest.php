<?php

namespace App\Tests\Unit\Core\User\UserInterface\Cli;

use App\Common\Bus\QueryBusInterface;
use App\Core\User\Application\DTO\UserDTO;
use App\Core\User\Application\Query\GetInactiveUsers\GetInactiveUsersQuery;
use App\Core\User\UserInterface\Cli\GetInactiveUsers;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

class GetInactiveUsersTest extends TestCase
{
    private QueryBusInterface|MockObject $queryBus;
    private GetInactiveUsers $command;
    private CommandTester $commandTester;

    protected function setUp(): void
    {
        parent::setUp();

        $this->command = new GetInactiveUsers(
            $this->queryBus = $this->createMock(QueryBusInterface::class)
        );

        $this->commandTester = new CommandTester($this->command);
    }

    public function test_execute_displays_inactive_users_emails(): void
    {
        $user1 = new UserDTO(1, 'user1@example.com', false);
        $user2 = new UserDTO(2, 'user2@example.com', false);

        $this->queryBus->expects(self::once())
            ->method('dispatch')
            ->with(self::isInstanceOf(GetInactiveUsersQuery::class))
            ->willReturn([$user1, $user2]);

        $this->commandTester->execute([]);

        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('user1@example.com', $output);
        $this->assertStringContainsString('user2@example.com', $output);
    }

    public function test_execute_displays_message_when_no_inactive_users(): void
    {
        $this->queryBus->expects(self::once())
            ->method('dispatch')
            ->with(self::isInstanceOf(GetInactiveUsersQuery::class))
            ->willReturn([]);

        $this->commandTester->execute([]);

        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Brak nieaktywnych użytkowników.', $output);
    }
}
