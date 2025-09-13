<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\UserRepository;

class UserService
{
    public function __construct(private UserRepository $userRepository) {}

    public function findById(int $id): User
    {
        return $this->userRepository->findById($id);
    }

    public function update(int $id, array $data): User
    {
        return $this->userRepository->update($id, $data);
    }
}
