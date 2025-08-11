<?php

namespace App\Tests\Unit\Core\User\Application\Query\GetInactiveUsers;

use App\Core\User\Application\DTO\UserDTO;
use App\Core\User\Application\Query\GetInactiveUsers\GetInactiveUsersHandler;
use App\Core\User\Application\Query\GetInactiveUsers\GetInactiveUsersQuery;
use App\Core\User\Domain\Repository\UserRepositoryInterface;
use App\Core\User\Domain\User;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class GetInactiveUsersHandlerTest extends TestCase
{
    private UserRepositoryInterface|MockObject $userRepository;
    private GetInactiveUsersHandler $handler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->handler = new GetInactiveUsersHandler(
            $this->userRepository = $this->createMock(UserRepositoryInterface::class)
        );
    }

    public function test_invoke_returns_user_dtos(): void
    {
        $user1 = $this->createMock(User::class);
        $user1->method('getId')->willReturn(1);
        $user1->method('getEmail')->willReturn('user1@example.com');
        $user1->method('isActive')->willReturn(false);

        $user2 = $this->createMock(User::class);
        $user2->method('getId')->willReturn(2);
        $user2->method('getEmail')->willReturn('user2@example.com');
        $user2->method('isActive')->willReturn(false);

        $this->userRepository->expects(self::once())
            ->method('getInactiveUsers')
            ->willReturn([$user1, $user2]);

        $result = $this->handler->__invoke(new GetInactiveUsersQuery());

        $this->assertCount(2, $result);
        $this->assertInstanceOf(UserDTO::class, $result[0]);
        $this->assertEquals(1, $result[0]->id);
        $this->assertEquals('user1@example.com', $result[0]->email);
        $this->assertFalse($result[0]->active);

        $this->assertInstanceOf(UserDTO::class, $result[1]);
        $this->assertEquals(2, $result[1]->id);
        $this->assertEquals('user2@example.com', $result[1]->email);
        $this->assertFalse($result[1]->active);
    }

    public function test_invoke_returns_empty_array_when_no_inactive_users(): void
    {
        $this->userRepository->expects(self::once())
            ->method('getInactiveUsers')
            ->willReturn([]);

        $result = $this->handler->__invoke(new GetInactiveUsersQuery());

        $this->assertEmpty($result);
    }
}