<?php

namespace App\Services\Auth;

use App\DTO\Auth\AuthDTO;
use App\Exceptions\BusinessException;
use App\Messages\Auth\AuthMessage;
use App\Models\User;
use App\Services\User\UserService;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    /**
     * Init constructor
     */
    public function __construct(private readonly UserService $userService) {}

    private function responseWithToken(string $token): array
    {
        return [
            'token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => auth('api')->factory()->getTTL() * config('jwt.ttl'),
        ];
    }

    public function login(AuthDTO $authDTO): array
    {
        /** @var User|bool $user */
        $user = $this->userService->findBy('email', $authDTO->email);

        if (! $user || ! Hash::check($authDTO->password, $user->password)) {
            throw new BusinessException(AuthMessage::invalidCredentials());
        }

        if (! $token = auth('api')->attempt($authDTO->toArray())) {
            throw new AuthenticationException(AuthMessage::unauthorized());
        }

        return $this->responseWithToken($token);
    }
}
