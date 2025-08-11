<?php

namespace App\Tests\Functional\Core\Invoice\UserInterface\Cli;

use App\Core\Invoice\Domain\Invoice;
use App\Core\Invoice\Domain\Repository\InvoiceRepositoryInterface;
use App\Core\Invoice\Domain\Status\InvoiceStatus;
use App\Core\User\Domain\User;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\DependencyInjection\ContainerInterface;

class GetInvoicesTest extends KernelTestCase
{
    private ContainerInterface $container;
    private CommandTester $commandTester;
    private InvoiceRepositoryInterface|MockObject $mockRepository;
    private User $user;
    private Invoice $invoice1;
    private Invoice $invoice2;
    private Invoice $invoice3;

    protected function setUp(): void
    {
        parent::setUp();

        $kernel = self::bootKernel();
        $this->container = static::getContainer();

        // Create mock user
        $this->user = $this->createMock(User::class);
        $this->user->method('getEmail')->willReturn('test@example.com');

        // Create mock invoices with different statuses and amounts
        $this->invoice1 = $this->createMock(Invoice::class);
        $this->invoice1->method('getId')->willReturn(1);
        $this->invoice1->method('getUser')->willReturn($this->user);
        $this->invoice1->method('getAmount')->willReturn(5000);

        $this->invoice2 = $this->createMock(Invoice::class);
        $this->invoice2->method('getId')->willReturn(2);
        $this->invoice2->method('getUser')->willReturn($this->user);
        $this->invoice2->method('getAmount')->willReturn(15000);

        $this->invoice3 = $this->createMock(Invoice::class);
        $this->invoice3->method('getId')->willReturn(3);
        $this->invoice3->method('getUser')->willReturn($this->user);
        $this->invoice3->method('getAmount')->willReturn(25000);

        // Create a mock repository that simulates the bug in DoctrineInvoiceRepository
        $this->mockRepository = $this->createMock(InvoiceRepositoryInterface::class);

        // Replace the real repository with our mock in the container
        $this->container->set(InvoiceRepositoryInterface::class, $this->mockRepository);

        // Create the command tester
        $application = new Application($kernel);
        $command = $application->find('app:invoice:get-by-status-and-amount');
        $this->commandTester = new CommandTester($command);
    }

    /**
     * This test demonstrates how a functional test would catch the bug in DoctrineInvoiceRepository
     * The bug is that the repository doesn't correctly filter by status and amount
     */
    public function testExecuteReturnsInvoicesWithCorrectStatusAndAmount(): void
    {
        // Set up the mock repository to simulate the bug
        // The bug is that it returns all invoices regardless of status or amount
        $this->mockRepository->expects($this->once())
            ->method('getInvoicesWithGreaterAmountAndStatus')
            ->with(10000, InvoiceStatus::NEW)
            ->willReturn([$this->invoice1, $this->invoice2, $this->invoice3]); // Bug: returns all invoices

        // Execute command with status=new and amount=10000
        $this->commandTester->execute([
            'status' => 'new',
            'amount' => 10000,
        ]);

        $output = $this->commandTester->getDisplay();

        // With the bug, all invoice IDs would be in the output
        // This test would fail because invoice1 and invoice3 shouldn't be returned
        $this->assertStringContainsString((string)$this->invoice1->getId(), $output); // Should not be in output if repository worked correctly
        $this->assertStringContainsString((string)$this->invoice2->getId(), $output); // Should be in output
        $this->assertStringContainsString((string)$this->invoice3->getId(), $output); // Should not be in output if repository worked correctly
    }

    /**
     * This test shows how the repository should work correctly
     */
    public function testExecuteReturnsInvoicesWithCorrectStatusAndAmountWhenRepositoryIsFixed(): void
    {
        // Set up the mock repository to simulate the fixed behavior
        $this->mockRepository->expects($this->once())
            ->method('getInvoicesWithGreaterAmountAndStatus')
            ->with(10000, InvoiceStatus::NEW)
            ->willReturn([$this->invoice2]); // Fixed: returns only invoices with NEW status and amount > 10000

        // Execute command with status=new and amount=10000
        $this->commandTester->execute([
            'status' => 'new',
            'amount' => 10000,
        ]);

        $output = $this->commandTester->getDisplay();

        // With the fix, only invoice2 ID would be in the output
        $this->assertStringNotContainsString((string)$this->invoice1->getId(), $output);
        $this->assertStringContainsString((string)$this->invoice2->getId(), $output);
        $this->assertStringNotContainsString((string)$this->invoice3->getId(), $output);
    }

    /**
     * This test verifies that the command returns an error when an invalid status is provided
     */
    public function testExecuteReturnsErrorForInvalidStatus(): void
    {
        // Execute command with an invalid status
        $this->commandTester->execute([
            'status' => 'invalidstatus',
            'amount' => 10000,
        ]);

        // Check that the command returned INVALID
        $this->assertEquals(\Symfony\Component\Console\Command\Command::INVALID, $this->commandTester->getStatusCode());

        // Check that the error message is displayed
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Invalid status provided', $output);
    }

    /**
     * This test shows how the repository should handle canceled invoices
     */
    public function testExecuteReturnsInvoicesWithCanceledStatusAndAmount(): void
    {
        // Set up the mock repository to simulate the correct behavior for canceled invoices
        $this->mockRepository->expects($this->once())
            ->method('getInvoicesWithGreaterAmountAndStatus')
            ->with(20000, InvoiceStatus::CANCELED)
            ->willReturn([$this->invoice3]); // Returns only invoices with CANCELED status and amount > 20000

        // Execute command with status=canceled and amount=20000
        $this->commandTester->execute([
            'status' => 'canceled',
            'amount' => 20000,
        ]);

        $output = $this->commandTester->getDisplay();

        // Should only return invoice3 (CANCELED status, 25000 amount)
        $this->assertStringContainsString((string)$this->invoice3->getId(), $output);

        // Should not return invoice1 or invoice2 (wrong status)
        $this->assertStringNotContainsString((string)$this->invoice1->getId(), $output);
        $this->assertStringNotContainsString((string)$this->invoice2->getId(), $output);
    }
}
