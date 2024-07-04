<?php

use App\Messages\Auth\AuthMessage;
use App\Messages\System\SystemMessage;
use App\Models\Expense;
use App\Models\User;
use Illuminate\Testing\Fluent\AssertableJson;

beforeEach(function (): void {

    $this->user = User::factory()->createOne(['email' => fake()->freeEmail(), 'password' => '@p5sSw0rd!']);
    $this->expense = Expense::factory()->createOne(['user_id' => $this->user->id]);

    auth('api')->attempt(['email' => $this->user->email, 'password' => '@p5sSw0rd!']);
});

test('should return resource not found when expense id does not exists', function (): void {

    $id = fake()->uuid();

    $this->deleteJson(EXPENSE_ENDPOINT.DIRECTORY_SEPARATOR.$id)
        ->assertNotFound()
        ->assertJson(
            fn (AssertableJson $json): AssertableJson => $json
                ->has('message')
                ->whereType('message', 'string')
                ->where('message', SystemMessage::RESOURCE_NOT_FOUND)
        );
});

test('should return unauthorized when user tries to delete an expense of another user', function (): void {

    $expense = Expense::factory()->createOne();

    $this->deleteJson(EXPENSE_ENDPOINT.DIRECTORY_SEPARATOR.$expense->id)
        ->assertForbidden()
        ->assertJson(
            fn (AssertableJson $json): AssertableJson => $json
                ->has('message')
                ->whereType('message', 'string')
                ->where('message', AuthMessage::unauthorized())
        );
});

test(
    'should return no content when user expense is successfully deleted',
    fn () => $this->deleteJson(EXPENSE_ENDPOINT.DIRECTORY_SEPARATOR.$this->expense->id)
        ->assertNoContent()
);
