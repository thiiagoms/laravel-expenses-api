<?php

namespace App\Virtual\Responses;

use OpenApi\Attributes as OA;

#[OA\Schema(
    description: 'Expense response',
    type: 'object',
    title: "Example expenses's model response",
)]
class ExpenseResponse
{
    #[OA\Property(
        title: 'Id',
        description: 'The unique identifier of the expense.',
        type: 'string',
        format: 'uuid',
    )]
    public string $id;

    #[OA\Property(
        title: 'Description',
        description: 'The description of the expense.',
        format: 'string',
        example: 'My first expense'
    )]
    public string $description;

    #[OA\Property(
        title: 'Price',
        description: 'The price of the expense.',
        format: 'number',
        example: 100
    )]
    public float|int $price;

    #[OA\Property(
        title: 'Date',
        description: 'The date of the expense.',
        format: 'date',
        example: '2024-01-01'
    )]
    public string $date;

    #[OA\Property(
        title: 'Created at',
        description: 'The date and time when the expense was created',
        type: 'string',
        format: 'date-time',
        example: '2024-01-01 23:19:39',
    )]
    public string $created_at;

    #[OA\Property(
        title: 'Updated at',
        description: 'The date and time when the expense was updated',
        type: 'string',
        format: 'date-time',
        example: '2024-01-01 23:19:39',
    )]
    public string $updated_at;

    #[OA\Property(
        title: 'User',
        description: 'The user who created the expense.',
        type: 'object',
        ref: '#/components/schemas/UserResponse'
    )]
    public UserResponse $user;
}
