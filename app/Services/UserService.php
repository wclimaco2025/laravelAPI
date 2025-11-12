<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\UserRepository;
use App\Repositories\TokenRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class UserService
{
    protected $userRepository;
    protected $tokenRepository;

    public function __construct(UserRepository $userRepository, TokenRepository $tokenRepository)
    {
        $this->userRepository = $userRepository;
        $this->tokenRepository = $tokenRepository;
    }

    /**
     * Create a new user.
     * Validates that email is unique before creating.
     *
     * @param array $userData
     * @return User
     * @throws \Exception
     */
    public function createUser(array $userData): User
    {
        // Validate email uniqueness
        $existingUser = $this->userRepository->findByEmail($userData['email']);

        if ($existingUser) {
            throw new \Exception('USER_ALREADY_EXISTS: El email ya está registrado', 409);
        }

        return $this->userRepository->create($userData);
    }

    /**
     * Get all users without passwords.
     *
     * @return Collection
     */
    public function getAllUsers(): Collection
    {
        return $this->userRepository->findAll();
    }

    /**
     * Get a user by ID.
     * Throws ModelNotFoundException if user not found.
     *
     * @param int $userId
     * @return User
     * @throws ModelNotFoundException
     */
    public function getUserById(int $userId): User
    {
        $user = $this->userRepository->findById($userId);

        if (!$user) {
            throw new ModelNotFoundException('USER_NOT_FOUND: Usuario no encontrado');
        }

        return $user;
    }

    /**
     * Update a user.
     * Validates email uniqueness if email is being changed.
     *
     * @param int $userId
     * @param array $userData
     * @return User
     * @throws ModelNotFoundException
     * @throws \Exception
     */
    public function updateUser(int $userId, array $userData): User
    {
        // Check if user exists
        $user = $this->getUserById($userId);

        // Validate email uniqueness if email is being changed
        if (isset($userData['email']) && $userData['email'] !== $user->email) {
            $existingUser = $this->userRepository->findByEmail($userData['email']);

            if ($existingUser) {
                throw new \Exception('USER_ALREADY_EXISTS: El email ya está registrado', 409);
            }
        }

        $updatedUser = $this->userRepository->update($userId, $userData);

        if (!$updatedUser) {
            throw new ModelNotFoundException('USER_NOT_FOUND: Usuario no encontrado');
        }

        return $updatedUser;
    }

    /**
     * Delete a user and all associated tokens.
     *
     * @param int $userId
     * @return bool
     * @throws ModelNotFoundException
     */
    public function deleteUser(int $userId): bool
    {
        // Check if user exists
        $this->getUserById($userId);

        // Delete all associated refresh tokens
        $this->tokenRepository->deleteByUserId($userId);

        // Delete the user
        return $this->userRepository->delete($userId);
    }
}
