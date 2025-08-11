<?php

namespace App\Tests\Unit\Core\Invoice\Application\Query\GetInvoicesByStatusAndAmountGreater;

use App\Core\Invoice\Application\DTO\InvoiceDTO;
use App\Core\Invoice\Application\Query\GetInvoicesByStatusAndAmountGreater\GetInvoicesByStatusAndAmountGreaterHandler;
use App\Core\Invoice\Application\Query\GetInvoicesByStatusAndAmountGreater\GetInvoicesByStatusAndAmountGreaterQuery;
use App\Core\Invoice\Domain\Invoice;
use App\Core\Invoice\Domain\Repository\InvoiceRepositoryInterface;
use App\Core\Invoice\Domain\Status\InvoiceStatus;
use App\Core\User\Domain\User;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class GetInvoicesByStatusAndAmountGreaterHandlerTest extends TestCase
{
    private InvoiceRepositoryInterface|MockObject $invoiceRepository;
    private GetInvoicesByStatusAndAmountGreaterHandler $handler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->handler = new GetInvoicesByStatusAndAmountGreaterHandler(
            $this->invoiceRepository = $this->createMock(InvoiceRepositoryInterface::class)
        );
    }

    public function test_invoke_returns_invoices_with_correct_status_and_amount(): void
    {
        $status = 'new';
        $amount = 10000;
        $query = new GetInvoicesByStatusAndAmountGreaterQuery($status, $amount);

        $user = $this->createMock(User::class);
        $user->method('getEmail')->willReturn('test@example.com');

        $invoice = $this->createMock(Invoice::class);
        $invoice->method('getId')->willReturn(1);
        $invoice->method('getUser')->willReturn($user);
        $invoice->method('getAmount')->willReturn(12500);

        $this->invoiceRepository->expects(self::once())
            ->method('getInvoicesWithGreaterAmountAndStatus')
            ->with($amount, InvoiceStatus::from($status))
            ->willReturn([$invoice]);

        $result = $this->handler->__invoke($query);

        $this->assertCount(1, $result);
        $this->assertInstanceOf(InvoiceDTO::class, $result[0]);
        $this->assertEquals(1, $result[0]->id);
        $this->assertEquals('test@example.com', $result[0]->email);
        $this->assertEquals(12500, $result[0]->amount);
    }

    public function test_invoke_returns_empty_array_when_no_invoices_match(): void
    {
        $status = 'new';
        $amount = 10000;
        $query = new GetInvoicesByStatusAndAmountGreaterQuery($status, $amount);

        $this->invoiceRepository->expects(self::once())
            ->method('getInvoicesWithGreaterAmountAndStatus')
            ->with($amount, InvoiceStatus::from($status))
            ->willReturn([]);

        $result = $this->handler->__invoke($query);

        $this->assertEmpty($result);
    }
}