<?php

namespace App\Tests\Unit\Core\Invoice\Domain;

use App\Core\Invoice\Domain\Exception\InvoiceException;
use App\Core\Invoice\Domain\Invoice;
use App\Core\User\Domain\User;
use PHPUnit\Framework\TestCase;

class InvoiceTest extends TestCase
{
    public function test_create_invoice_with_active_user_success(): void
    {
        $user = new User('test@example.com', true);
        
        $invoice = new Invoice($user, 12500);
        
        $this->assertSame($user, $invoice->getUser());
        $this->assertSame(12500, $invoice->getAmount());
    }
    
    public function test_create_invoice_with_inactive_user_throws_exception(): void
    {
        $this->expectException(InvoiceException::class);
        $this->expectExceptionMessage('Faktura może być utworzona tylko dla aktywnego użytkownika');
        
        $user = new User('test@example.com', false);
        
        new Invoice($user, 12500);
    }
    
    public function test_create_invoice_with_invalid_amount_throws_exception(): void
    {
        $this->expectException(InvoiceException::class);
        $this->expectExceptionMessage('Kwota faktury musi być większa od 0');
        
        $user = new User('test@example.com', true);
        
        new Invoice($user, 0);
    }
}