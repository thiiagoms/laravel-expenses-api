<?php

namespace App\Virtual\Responses;

use OpenApi\Attributes as OA;

#[OA\Schema(
    description: 'Base response for user crud operations',
    type: 'object',
    title: 'User response',
)]
class UserResponse
{
    #[OA\Property(
        title: 'Id',
        description: 'The unique identifier of the user.',
        type: 'string',
        format: 'uuid',
    )]
    public string $id;

    #[OA\Property(
        title: 'Name',
        description: 'The name of the user.',
        format: 'string',
        example: 'John'
    )]
    public string $name;

    #[OA\Property(
        title: 'Email',
        description: 'The email address of the user.',
        format: 'email',
        example: 'john.guard123@gmail.com'
    )]
    public string $email;

    #[OA\Property(
        title: 'Created at',
        description: 'The date and time when the user was created',
        type: 'string',
        format: 'date-time',
        example: '2024-06-02 22:19:39',
    )]
    public string $created_at;

    #[OA\Property(
        title: 'Updated at',
        description: 'The date and time when the user was updated',
        type: 'string',
        format: 'date-time',
        example: '2024-06-02 23:19:39',
    )]
    public string $updated_at;
}
