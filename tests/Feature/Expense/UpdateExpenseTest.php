<?php

use App\Enums\Expense\DescriptionEnum;
use App\Messages\Auth\AuthMessage;
use App\Messages\Expense\ExpenseMessage;
use App\Messages\System\SystemMessage;
use App\Models\Expense;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Testing\Fluent\AssertableJson;

beforeEach(function (): void {

    $this->user = User::factory()->createOne(['email' => fake()->freeEmail(), 'password' => '@p5sSw0rd!']);

    $this->expense = Expense::factory()->createOne(['user_id' => $this->user->id]);

    auth('api')->attempt(['email' => $this->user->email, 'password' => '@p5sSw0rd!']);
});

test('should return resource not found when expense id does not exists', function (): void {

    $id = fake()->uuid();

    $this->patchJson(EXPENSE_ENDPOINT.DIRECTORY_SEPARATOR.$id)
        ->assertNotFound()
        ->assertJson(
            fn (AssertableJson $json): AssertableJson => $json
                ->has('message')
                ->whereType('message', 'string')
                ->where('message', SystemMessage::RESOURCE_NOT_FOUND)
        );
});

test('should return unauthorized when user tries to update an expense of another user', function (): void {

    $expense = Expense::factory()->createOne();

    $this->patchJson(EXPENSE_ENDPOINT.DIRECTORY_SEPARATOR.$expense->id)
        ->assertForbidden()
        ->assertJson(
            fn (AssertableJson $json): AssertableJson => $json
                ->has('message')
                ->whereType('message', 'string')
                ->where('message', AuthMessage::unauthorized())
        );
});

dataset('validate description provider', fn (): array => [
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
        ->patchJson(EXPENSE_ENDPOINT.DIRECTORY_SEPARATOR.$this->expense->id, [
            'description' => $description,
        ])
        ->assertBadRequest()
        ->assertJson($expectedJson)

)->with('validate description provider');

dataset('validate price provider', fn (): array => [
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
        ->patchJson(EXPENSE_ENDPOINT.DIRECTORY_SEPARATOR.$this->expense->id, [
            'price' => $price,
        ])
        ->assertBadRequest()
        ->assertJson($expectedJson)
)->with('validate price provider');

dataset('validate date provider', fn (): array => [
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
        ->patchJson(EXPENSE_ENDPOINT.DIRECTORY_SEPARATOR.$this->expense->id, [
            'date' => $date,
        ])
        ->assertBadRequest()
        ->assertJson($expectedJson)
)->with('validate date provider');

test(
    'should update only expense description when description is provided',
    fn () => $this
        ->patchJson(EXPENSE_ENDPOINT.DIRECTORY_SEPARATOR.$this->expense->id, [
            'description' => 'New description',
        ])
        ->assertOk()
        ->assertJson(
            fn (AssertableJson $json): AssertableJson => $json
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
                    'data.description' => 'New description',
                    'data.date' => Carbon::parse($this->expense->date)->format('Y-m-d H:i:s'),
                    'data.user.id' => $this->user->id,
                    'data.user.name' => $this->user->name,
                    'data.user.email' => $this->user->email,
                ])
        )
);

test(
    'should update only expense price when price is provided',
    fn () => $this
        ->patchJson(EXPENSE_ENDPOINT.DIRECTORY_SEPARATOR.$this->expense->id, [
            'price' => 100,
        ])
        ->assertOk()
        ->assertJson(
            fn (AssertableJson $json): AssertableJson => $json
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
                    'data.description' => $this->expense->description,
                    'data.price' => 100,
                    'data.date' => Carbon::parse($this->expense->date)->format('Y-m-d H:i:s'),
                    'data.user.id' => $this->user->id,
                    'data.user.name' => $this->user->name,
                    'data.user.email' => $this->user->email,
                ])
        )
);

test(
    'should update only expense date when date is provided',
    fn () => $this
        ->patchJson(EXPENSE_ENDPOINT.DIRECTORY_SEPARATOR.$this->expense->id, [
            'date' => '2024-01-02 00:40:00',
        ])
        ->assertOk()
        ->assertJson(
            fn (AssertableJson $json): AssertableJson => $json
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
                    'data.description' => $this->expense->description,
                    'data.price' => $this->expense->price,
                    'data.date' => '2024-01-02 00:40:00',
                    'data.user.id' => $this->user->id,
                    'data.user.name' => $this->user->name,
                    'data.user.email' => $this->user->email,
                ])
        )
);

test(
    'should update entire expense data when all fields are provided',
    fn () => $this
        ->putJson(EXPENSE_ENDPOINT.DIRECTORY_SEPARATOR.$this->expense->id, [
            'description' => 'Renew description',
            'date' => '2024-02-02 00:40:00',
            'price' => 200,
        ])
        ->assertOk()
        ->assertJson(
            fn (AssertableJson $json): AssertableJson => $json
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
                    'data.description' => 'Renew description',
                    'data.price' => 200,
                    'data.date' => '2024-02-02 00:40:00',
                    'data.user.id' => $this->user->id,
                    'data.user.name' => $this->user->name,
                    'data.user.email' => $this->user->email,
                ])
        )
);
