<?php

use App\Enums\Expense\DescriptionEnum;
use App\Messages\Expense\ExpenseMessage;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Response;
use Illuminate\Testing\Fluent\AssertableJson;

beforeEach(function (): void {

    $this->user = User::factory()->createOne(['email' => fake()->freeEmail(), 'password' => '@p5sSw0rd!']);

    auth('api')->attempt(['email' => $this->user->email, 'password' => '@p5sSw0rd!']);
});

dataset('validate description provider', fn (): array => [
    'should return description is required message when description is empty' => [
        'description' => '',
        'expectedJson' => fn (AssertableJson $json): AssertableJson => $json
            ->hasAll([
                'description',
                'description.0',
            ])
            ->whereAllType([
                'description' => 'array',
                'description.0' => 'string',
            ])
            ->where('description.0', ExpenseMessage::descriptionIsRequired()),
    ],
    'should return description max length message when description is higher than maxlength' => [
        'description' => str_repeat('#', (DescriptionEnum::MAX_LENGTH->value + 1)),
        'expectedJson' => fn (AssertableJson $json): AssertableJson => $json
            ->hasAll([
                'description',
                'description.0',
            ])
            ->whereAllType([
                'description' => 'array',
                'description.0' => 'string',
            ])
            ->where('description.0', ExpenseMessage::descriptionMaxLength()),
    ],
    'should return description type message when description is not a string' => [
        'description' => 123,
        'expectedJson' => fn (AssertableJson $json): AssertableJson => $json
            ->hasAll([
                'description',
                'description.0',
            ])
            ->whereAllType([
                'description' => 'array',
                'description.0' => 'string',
            ])
            ->where('description.0', ExpenseMessage::descriptionType()),
    ],
]);

test(
    'validate description',
    fn (string|int $description, Closure $expectedJson) => $this
        ->postJson(EXPENSE_ENDPOINT, [
            'description' => $description,
            'price' => fake()->randomFloat(),
            'date' => now()->format('Y-m-d H:i:s'),
        ])
        ->assertStatus(Response::HTTP_BAD_REQUEST)
        ->assertJson($expectedJson)

)->with('validate description provider');

dataset('validate price provider', fn (): array => [
    'should return price is required message when price is empty' => [
        'price' => '',
        'expectedJson' => fn (AssertableJson $json): AssertableJson => $json
            ->hasAll([
                'price',
                'price.0',
            ])
            ->whereAllType([
                'price' => 'array',
                'price.0' => 'string',
            ])
            ->where('price.0', ExpenseMessage::priceIsRequired()),
    ],
    'should return price type message when price is not a number' => [
        'price' => 'abc',
        'expectedJson' => fn (AssertableJson $json): AssertableJson => $json
            ->hasAll([
                'price',
                'price.0',
            ])
            ->whereAllType([
                'price' => 'array',
                'price.0' => 'string',
            ])
            ->where('price.0', ExpenseMessage::priceType()),
    ],
    'should return price is not valid message when price is a negative number' => [
        'price' => -1,
        'expectedJson' => fn (AssertableJson $json): AssertableJson => $json
            ->hasAll([
                'price',
                'price.0',
            ])
            ->whereAllType([
                'price' => 'array',
                'price.0' => 'string',
            ])
            ->where('price.0', ExpenseMessage::priceIsNotValid()),
    ],
]);

test(
    'validate price provider',
    fn (string|int $price, Closure $expectedJson) => $this
        ->postJson(EXPENSE_ENDPOINT, [
            'description' => fake()->sentence(),
            'price' => $price,
            'date' => now()->format('Y-m-d H:i:s'),
        ])
        ->assertStatus(Response::HTTP_BAD_REQUEST)
        ->assertJson($expectedJson)

)->with('validate price provider');

dataset('validate date provider', fn (): array => [
    'should return date is required message when date is empty' => [
        'date' => '',
        'expectedJson' => fn (AssertableJson $json): AssertableJson => $json
            ->hasAll([
                'date',
                'date.0',
            ])
            ->whereAllType([
                'date' => 'array',
                'date.0' => 'string',
            ])
            ->where('date.0', ExpenseMessage::dateIsRequired()),
    ],
    'should return date type message when date is not a date' => [
        'date' => 'abc',

        'expectedJson' => fn (AssertableJson $json): AssertableJson => $json
            ->hasAll([
                'date',
                'date.0',
            ])
            ->whereAllType([
                'date' => 'array',
                'date.0' => 'string',
            ])
            ->where('date.0', ExpenseMessage::dateIsInvalid()),
    ],
    'should return date is not valid message when date is in the future' => [
        'date' => Carbon::now()->addDay()->format('Y-m-d H:i:s'),
        'expectedJson' => fn (AssertableJson $json): AssertableJson => $json
            ->hasAll([
                'date',
                'date.0',
            ])
            ->whereAllType([
                'date' => 'array',
                'date.0' => 'string',
            ])
            ->where('date.0', ExpenseMessage::dateIsInvalid()),
    ],
    'should return date is not valid message when date is in the wrong format' => [
        'date' => Carbon::now()->format('d-m-y H:i:s'),
        'expectedJson' => fn (AssertableJson $json): AssertableJson => $json
            ->hasAll([
                'date',
                'date.0',
            ])
            ->whereAllType([
                'date' => 'array',
                'date.0' => 'string',
            ])
            ->where('date.0', ExpenseMessage::dateIsInvalid()),
    ],
]);

test(
    'validate date',
    fn (string $date, Closure $expectedJson) => $this
        ->postJson(EXPENSE_ENDPOINT, [
            'description' => fake()->sentence(),
            'price' => fake()->randomNumber(),
            'date' => $date,
        ])
        ->assertStatus(Response::HTTP_BAD_REQUEST)
        ->assertJson($expectedJson)

)->with('validate date provider');

test('should return created expense', function (): void {

    $data = [
        'description' => fake()->sentence(),
        'price' => fake()->numberBetween(1, 10),
        'date' => Carbon::now()->format('Y-m-d H:i:s'),
    ];

    $this->postJson(EXPENSE_ENDPOINT, $data)
        ->assertCreated()
        ->assertJson(fn (AssertableJson $json): AssertableJson => $json
            ->hasAll([
                'data',
                'data.id',
                'data.description',
                'data.price',
                'data.date',
                'data.created_at',
                'data.updated_at',
                'data.user',
                'data.user.id',
                'data.user.name',
                'data.user.email',
            ])
            ->whereAllType([
                'data' => 'array',
                'data.id' => 'string',
                'data.description' => 'string',
                'data.price' => 'float|double|integer|string',
                'data.date' => 'string',
                'data.created_at' => 'string',
                'data.updated_at' => 'string',
                'data.user' => 'array',
                'data.user.id' => 'string',
                'data.user.name' => 'string',
                'data.user.email' => 'string',
            ])
            ->whereAll([
                'data.description' => $data['description'],
                'data.price' => $data['price'],
                'data.date' => $data['date'],
                'data.user.id' => $this->user->id,
                'data.user.name' => $this->user->name,
                'data.user.email' => $this->user->email,
            ])
        );
});
