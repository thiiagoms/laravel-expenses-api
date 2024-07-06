<?php

use App\Models\User;

beforeEach(function (): void {

    $this->user = User::factory()->createOne(['email' => fake()->freeEmail(), 'password' => '@p5sSw0rd!']);

    auth('api')->attempt(['email' => $this->user->email, 'password' => '@p5sSw0rd!']);
});

test('should destroy authenticated user and return empty content on response', function (): void {

    $this->deleteJson(USER_ENDPOINT)
        ->assertNoContent();
});
