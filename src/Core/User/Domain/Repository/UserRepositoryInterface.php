<?php

namespace App\Core\User\Domain\Repository;

use App\Core\User\Domain\Exception\UserNotFoundException;
use App\Core\User\Domain\User;

interface UserRepositoryInterface
{
    /**
     * @throws UserNotFoundException
     */
    public function getByEmail(string $email): User;

    /**
     * Save a user entity
     */
    public function save(User $user): void;

    /**
     * Flush changes to the database
     */
    public function flush(): void;

    /**
     * Get all inactive users
     * @return User[]
     */
    public function getInactiveUsers(): array;
}
