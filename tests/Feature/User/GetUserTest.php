<?php

use App\Messages\Auth\AuthMessage;
use App\Models\User;
use Illuminate\Testing\Fluent\AssertableJson;

test('should return unauthenticated when user not authenticated', function (): void {

    $this
        ->getJson(USER_ENDPOINT)
        ->assertUnauthorized()
        ->assertJson(
            fn (AssertableJson $json): AssertableJson =>
            $json
                ->has('message')
                ->whereType('message', 'string')
                ->where('message', AuthMessage::unauthenticated())
        );
});

test('should return authenticated user data', function (): void {

    $user = User::factory()->createOne();

    auth('api')->attempt(['email' => $user->email, 'password' => '@p5sSw0rd!']);

    $this
        ->actingAs($user)
        ->getJson(USER_ENDPOINT)
        ->assertOk()
        ->assertJson(
            fn (AssertableJson $json) => $json
                ->hasAll([
                    'data',
                    'data.id',
                    'data.name',
                    'data.email',
                ])
                ->whereAllType([
                    'data' => 'array',
                    'data.id' => 'string',
                    'data.name' => 'string',
                    'data.email' => 'string',
                ])
                ->whereAll([
                    'data.id' => $user->id,
                    'data.name' => $user->name,
                    'data.email' => $user->email,
                ])
        );
});
