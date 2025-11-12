<?php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Hash;

class UserRepository
{
    /**
     * Create a new user.
     *
     * @param array $userData
     * @return User
     */
    public function create(array $userData): User
    {
        // Hash the password before creating the user
        if (isset($userData['password'])) {
            $userData['password'] = Hash::make($userData['password']);
        }

        return User::create($userData);
    }

    /**
     * Get all users.
     *
     * @return Collection
     */
    public function findAll(): Collection
    {
        return User::all();
    }

    /**
     * Find a user by ID.
     *
     * @param int $userId
     * @return User|null
     */
    public function findById(int $userId): ?User
    {
        return User::find($userId);
    }

    /**
     * Find a user by email.
     *
     * @param string $email
     * @return User|null
     */
    public function findByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }

    /**
     * Update a user.
     *
     * @param int $userId
     * @param array $userData
     * @return User|null
     */
    public function update(int $userId, array $userData): ?User
    {
        $user = $this->findById($userId);

        if (!$user) {
            return null;
        }

        // Hash the password if it's being updated
        if (isset($userData['password'])) {
            $userData['password'] = Hash::make($userData['password']);
        }

        $user->update($userData);

        return $user->fresh();
    }

    /**
     * Delete a user.
     *
     * @param int $userId
     * @return bool
     */
    public function delete(int $userId): bool
    {
        $user = $this->findById($userId);

        if (!$user) {
            return false;
        }

        return $user->delete();
    }
}
