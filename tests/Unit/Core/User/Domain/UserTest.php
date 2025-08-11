<?php

namespace App\Tests\Unit\Core\User\Domain;

use App\Core\User\Domain\User;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    public function test_user_is_inactive_by_default(): void
    {
        $user = new User('test@example.com');
        
        $this->assertFalse($user->isActive());
    }
    
    public function test_user_can_be_created_as_active(): void
    {
        $user = new User('test@example.com', true);
        
        $this->assertTrue($user->isActive());
    }
    
    public function test_user_can_be_activated(): void
    {
        $user = new User('test@example.com');
        $user->activate();
        
        $this->assertTrue($user->isActive());
    }
    
    public function test_user_can_be_deactivated(): void
    {
        $user = new User('test@example.com', true);
        $user->deactivate();
        
        $this->assertFalse($user->isActive());
    }
    
    public function test_user_email_is_accessible(): void
    {
        $email = 'test@example.com';
        $user = new User($email);
        
        $this->assertSame($email, $user->getEmail());
    }
}