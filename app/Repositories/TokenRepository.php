<?php

namespace App\Repositories;

use App\Models\RefreshToken;
use Carbon\Carbon;

class TokenRepository
{
    /**
     * Create a new refresh token.
     *
     * @param array $tokenData
     * @return RefreshToken
     */
    public function create(array $tokenData): RefreshToken
    {
        return RefreshToken::create($tokenData);
    }

    /**
     * Find a refresh token by token string.
     *
     * @param string $token
     * @return RefreshToken|null
     */
    public function findByToken(string $token): ?RefreshToken
    {
        return RefreshToken::where('token', $token)->first();
    }

    /**
     * Revoke a refresh token.
     *
     * @param string $token
     * @return bool
     */
    public function revokeToken(string $token): bool
    {
        $refreshToken = $this->findByToken($token);

        if (!$refreshToken) {
            return false;
        }

        $refreshToken->is_revoked = true;
        return $refreshToken->save();
    }

    /**
     * Delete all refresh tokens for a specific user.
     *
     * @param int $userId
     * @return int Number of tokens deleted
     */
    public function deleteByUserId(int $userId): int
    {
        return RefreshToken::where('user_id', $userId)->delete();
    }
}
