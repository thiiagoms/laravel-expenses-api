<?php

use App\Messages\Auth\AuthMessage;
use App\Messages\System\SystemMessage;
use App\Models\Expense;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Testing\Fluent\AssertableJson;

beforeEach(function (): void {

    $this->user = User::factory()->createOne(['email' => fake()->freeEmail(), 'password' => '@p5sSw0rd!']);

    auth('api')->attempt(['email' => $this->user->email, 'password' => '@p5sSw0rd!']);
});

test('should return resource not found when expense id does not exists', function (): void {

    $id = fake()->uuid();

    $this->getJson(EXPENSE_ENDPOINT.DIRECTORY_SEPARATOR.$id)
        ->assertNotFound()
        ->assertJson(fn (AssertableJson $json): AssertableJson => $json
            ->has('message')
            ->whereType('message', 'string')
            ->where('message', SystemMessage::RESOURCE_NOT_FOUND)
        );
});

test('should return unauthorized when user tries to find an expense of another user', function (): void {

    $expense = Expense::factory()->createOne();

    $this->getJson(EXPENSE_ENDPOINT.DIRECTORY_SEPARATOR.$expense->id)
        ->assertForbidden()
        ->assertJson(fn (AssertableJson $json): AssertableJson => $json
            ->has('message')
            ->whereType('message', 'string')
            ->where('message', AuthMessage::unauthorized())
        );
});

test('should return user expense when expense id exists and belongs to authenticated user', function (): void {

    $expense = Expense::factory()->createOne(['user_id' => $this->user->id]);

    $this
        ->getJson(EXPENSE_ENDPOINT.DIRECTORY_SEPARATOR.$expense->id)
        ->assertOk()
        ->assertJson(fn (AssertableJson $json) => $json
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
                'data.price' => 'string',
                'data.date' => 'string',
                'data.created_at' => 'string',
                'data.updated_at' => 'string',
                'data.user' => 'array',
                'data.user.id' => 'string',
                'data.user.name' => 'string',
                'data.user.email' => 'string',
            ])
            ->whereAll([
                'data.id' => $expense->id,
                'data.description' => $expense->description,
                'data.date' => Carbon::parse($expense->date)->format('Y-m-d H:i:s'),
                'data.user.id' => $expense->user->id,
                'data.user.name' => $expense->user->name,
                'data.user.email' => $expense->user->email,
            ])
        );
});
