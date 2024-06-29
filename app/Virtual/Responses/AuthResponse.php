<?php

namespace App\Virtual\Responses;

use OpenApi\Attributes as OA;

#[OA\Schema(
    description: 'Basic response for user authentication',
    type: 'object',
    title: 'User authentication response',
)]
class AuthResponse
{
    #[OA\Property(
        property: 'token',
        type: 'string',
        description: 'The token for the user.',
        example: 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJzdWIiOiIxMjM0NTY3ODkwIiwibmFtZSI6IkpvaG4gRG9lIiwiaWF0IjoxNTE2MjM5MDIyfQ.SflKxwRJSMeKKF2QT4fwpMeJf36POk6yJV_adQssw5c'
    )]
    public string $token;

    #[OA\Property(
        property: 'token_type',
        type: 'string',
        description: 'The type of the token.',
        example: 'Bearer'
    )]
    public string $token_type;

    #[OA\Property(
        property: 'expires_in',
        type: 'integer',
        description: 'The expiration time of the token.',
        example: 3600
    )]
    public int $expires_in;
}
