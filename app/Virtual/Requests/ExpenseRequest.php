<?php

namespace App\Virtual\Requests;

use OpenApi\Attributes as OA;

#[OA\Schema(
    description: 'Base request for CRUD operations with expenses',
    type: 'object',
    title: 'Expense base request',
)]
class ExpenseRequest
{
    #[OA\Property(
        property: 'description',
        type: 'string',
        description: 'The description of the expense.',
        example: 'My first expense'
    )]
    public string $description;

    #[OA\Property(
        property: 'price',
        type: 'number',
        description: 'The price of the expense.',
        example: 100
    )]
    public float|int $price;

    #[OA\Property(
        property: 'date',
        type: 'string',
        description: 'The date of the expense.',
        format: 'date',
        example: '2024-01-01 23:19:39'
    )]
    public string $date;
}
