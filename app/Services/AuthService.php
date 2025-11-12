<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\UserRepository;
use App\Repositories\TokenRepository;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenExpiredException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenInvalidException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException;

class AuthService
{
    protected $userRepository;
    protected $tokenRepository;

    public function __construct(UserRepository $userRepository, TokenRepository $tokenRepository)
    {
        $this->userRepository = $userRepository;
        $this->tokenRepository = $tokenRepository;
    }

    /**
     * Register a new user and generate tokens.
     *
     * @param array $userData
     * @return array
     * @throws \Exception
     */
    public function register(array $userData): array
    {
        // Check if email already exists
        $existingUser = $this->userRepository->findByEmail($userData['email']);

        if ($existingUser) {
            throw new \Exception('USER_ALREADY_EXISTS: El email ya está registrado', 409);
        }

        // Create user (password will be hashed in repository)
        $user = $this->userRepository->create($userData);

        // Generate tokens
        $tokens = $this->generateTokens($user->id);

        return [
            'user' => $user,
            'access_token' => $tokens['access_token'],
            'refresh_token' => $tokens['refresh_token']
        ];
    }

    /**
     * Login user and generate tokens.
     *
     * @param string $email
     * @param string $password
     * @return array
     * @throws \Exception
     */
    public function login(string $email, string $password): array
    {
        // Find user by email
        $user = $this->userRepository->findByEmail($email);

        if (!$user) {
            throw new \Exception('INVALID_CREDENTIALS: Credenciales inválidas', 401);
        }

        // Verify password
        if (!Hash::check($password, $user->password)) {
            throw new \Exception('INVALID_CREDENTIALS: Credenciales inválidas', 401);
        }

        // Generate tokens
        $tokens = $this->generateTokens($user->id);

        return [
            'user' => $user,
            'access_token' => $tokens['access_token'],
            'refresh_token' => $tokens['refresh_token']
        ];
    }

    /**
     * Generate access and refresh tokens for a user.
     *
     * @param int $userId
     * @return array
     */
    public function generateTokens(int $userId): array
    {
        $user = $this->userRepository->findById($userId);

        // Generate access token (5 minutes)
        $accessToken = JWTAuth::fromUser($user);

        // Generate refresh token (7 days)
        $refreshToken = Str::random(64);
        $expiresAt = Carbon::now()->addDays(7);

        // Store refresh token in database
        $this->tokenRepository->create([
            'token' => $refreshToken,
            'user_id' => $userId,
            'expires_at' => $expiresAt,
            'is_revoked' => false
        ]);

        return [
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken
        ];
    }

    /**
     * Refresh access token using refresh token.
     *
     * @param string $refreshToken
     * @return string
     * @throws \Exception
     */
    public function refreshAccessToken(string $refreshToken): string
    {
        // Find refresh token in database
        $token = $this->tokenRepository->findByToken($refreshToken);

        if (!$token) {
            throw new \Exception('TOKEN_INVALID: Token de refresco inválido', 403);
        }

        // Check if token is revoked
        if ($token->is_revoked) {
            throw new \Exception('TOKEN_INVALID: Token de refresco revocado', 403);
        }

        // Check if token is expired
        if (Carbon::now()->greaterThan($token->expires_at)) {
            throw new \Exception('TOKEN_EXPIRED: Token de refresco expirado', 401);
        }

        // Generate new access token
        $user = $this->userRepository->findById($token->user_id);
        $accessToken = JWTAuth::fromUser($user);

        return $accessToken;
    }

    /**
     * Logout user by revoking refresh token.
     *
     * @param string $refreshToken
     * @return bool
     * @throws \Exception
     */
    public function logout(string $refreshToken): bool
    {
        $result = $this->tokenRepository->revokeToken($refreshToken);

        if (!$result) {
            throw new \Exception('TOKEN_INVALID: Token de refresco no encontrado', 403);
        }

        return true;
    }

    /**
     * Verify access token.
     *
     * @param string $token
     * @return object|null
     */
    public function verifyAccessToken(string $token): ?object
    {
        try {
            JWTAuth::setToken($token);
            $payload = JWTAuth::getPayload();
            return $payload;
        } catch (TokenExpiredException $e) {
            throw new \Exception('TOKEN_EXPIRED: Token de acceso expirado', 401);
        } catch (TokenInvalidException $e) {
            throw new \Exception('TOKEN_INVALID: Token de acceso inválido', 403);
        } catch (JWTException $e) {
            throw new \Exception('UNAUTHORIZED: Error al verificar token', 401);
        }
    }
}
