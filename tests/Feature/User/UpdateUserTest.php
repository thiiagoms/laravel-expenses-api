<?php

use App\Enums\User\NameEnum;
use App\Messages\User\UserMessage;
use App\Models\User;
use Illuminate\Testing\Fluent\AssertableJson;

beforeEach(function (): void {

    $this->user = User::factory()->createOne(['email' => fake()->freeEmail(), 'password' => '@p5sSw0rd!']);

    auth('api')->attempt(['email' => $this->user->email, 'password' => '@p5sSw0rd!']);
});

dataset('validate name provider', fn (): array => [
    'should return name min length message when name is lower than min length' => [
        'name' => str_repeat('#', (NameEnum::MIN_LENGTH->value - 1)),
        'expectedJson' => fn (AssertableJson $json): AssertableJson => $json
            ->hasAll([
                'name',
                'name.0',
            ])
            ->whereAllType([
                'name' => 'array',
                'name.0' => 'string',
            ])
            ->where('name.0', UserMessage::nameMinLength()),
    ],
    'should return name max length message when name length is higher than max length' => [
        'name' => implode(',', fake()->paragraphs(NameEnum::MAX_LENGTH->value)),
        'expectedJson' => fn (AssertableJson $json): AssertableJson => $json
            ->hasAll([
                'name',
                'name.0',
            ])
            ->whereAllType([
                'name' => 'array',
                'name.0' => 'string',
            ])
            ->where('name.0', UserMessage::nameMaxLength()),
    ],
    'should return name type message when name is not a string' => [
        'name' => (float) fake()->randomFloat(),
        'expectedJson' => fn (AssertableJson $json): AssertableJson => $json
            ->hasAll([
                'name',
                'name.0',
            ])
            ->whereAllType([
                'name' => 'array',
                'name.0' => 'string',
            ])
            ->where('name.0', UserMessage::nameType()),
    ],
]);

test('validate name', fn (string|float $name, Closure $expectedJson) => $this
    ->patchJson(USER_ENDPOINT, ['name' => $name])
    ->assertBadRequest()
    ->assertJson($expectedJson)
)->with('validate name provider');

dataset('validate email provider', fn (): array => [
    'should return email is invalid message when email is not a valid email' => [
        'email' => fake()->name(),
        'expectedJson' => fn (AssertableJson $json): AssertableJson => $json
            ->hasAll([
                'email',
                'email.0',
            ])
            ->whereAllType([
                'email' => 'array',
                'email.0' => 'string',
            ])
            ->where('email.0', UserMessage::emailInvalid()),
    ],
    'should return email already exists message when email already exists' => [
        'email' => 'ilovelaravel@gmail.com',
        'expectedJson' => fn (AssertableJson $json): AssertableJson => $json
            ->hasAll([
                'email',
                'email.0',
            ])
            ->whereAllType([
                'email' => 'array',
                'email.0' => 'string',
            ])
            ->where('email.0', UserMessage::emailAlreadyExists()),
    ],
]);

dataset('validate password provider', fn (): array => [
    'should return password min length message when password is less than 8 characters' => [
        'password' => 'p4sS!',
        'expectedJson' => fn (AssertableJson $json): AssertableJson => $json
            ->hasAll([
                'password',
                'password.0',
            ])
            ->whereAllType([
                'password' => 'array',
                'password.0' => 'string',
            ])
            ->where('password.0', UserMessage::passwordMinLength()),
    ],
    'should return password numbers message when password does not contain at least one number' => [
        'password' => 'pAssssssssS!',
        'expectedJson' => fn (AssertableJson $json): AssertableJson => $json
            ->hasAll([
                'password',
                'password.0',
            ])
            ->whereAllType([
                'password' => 'array',
                'password.0' => 'string',
            ])
            ->where('password.0', UserMessage::passwordNumbers()),
    ],
    'should return password symbols message when password does not contain at least one symbol' => [
        'password' => 'pAsssssss12sSD',
        'expectedJson' => fn (AssertableJson $json): AssertableJson => $json
            ->hasAll([
                'password',
                'password.0',
            ])
            ->whereAllType([
                'password' => 'array',
                'password.0' => 'string',
            ])
            ->where('password.0', UserMessage::passwordSymbols()),
    ],
    'should return password mixed case message when password does not contain at least one lower and upper case letter' => [
        'password' => 'p4sssssss12s@ad',
        'expectedJson' => fn (AssertableJson $json): AssertableJson => $json
            ->hasAll([
                'password',
                'password.0',
            ])
            ->whereAllType([
                'password' => 'array',
                'password.0' => 'string',
            ])
            ->where('password.0', UserMessage::passwordMixedCase()),
    ],
]);

test('validate email', function (string $email, Closure $expectedJson): void {

    User::factory()->createOne(['email' => 'ilovelaravel@gmail.com']);

    $this
        ->patchJson(USER_ENDPOINT, ['email' => $email])
        ->assertBadRequest()
        ->assertJson($expectedJson);
})->with('validate email provider');

test('validate password', fn (string $password, Closure $expectedJson) => $this
    ->patchJson(USER_ENDPOINT, ['password' => $password])
    ->assertBadRequest()
    ->assertJson($expectedJson)
)->with('validate password provider');

test('should update only user name and return updated user data', function (): void {

    $name = fake()->name();

    $this
        ->patchJson(USER_ENDPOINT, ['name' => $name])
        ->assertOk()
        ->assertJson(fn (AssertableJson $json): AssertableJson => $json
            ->hasAll([
                'data',
                'data.id',
                'data.name',
                'data.email',
                'data.created_at',
                'data.updated_at',
            ])
            ->whereAllType([
                'data' => 'array',
                'data.id' => 'string',
                'data.name' => 'string',
                'data.email' => 'string',
                'data.created_at' => 'string',
                'data.updated_at' => 'string',
            ])
            ->whereAll([
                'data.name' => $name,
                'data.email' => $this->user->email,
            ])
        );
});

test('should update only user email and return updated user data', function (): void {

    $email = fake()->freeEmail();

    $this
        ->patchJson(USER_ENDPOINT, ['email' => $email])
        ->assertOk()
        ->assertJson(fn (AssertableJson $json): AssertableJson => $json
            ->hasAll([
                'data',
                'data.id',
                'data.name',
                'data.email',
                'data.created_at',
                'data.updated_at',
            ])
            ->whereAllType([
                'data' => 'array',
                'data.id' => 'string',
                'data.name' => 'string',
                'data.email' => 'string',
                'data.created_at' => 'string',
                'data.updated_at' => 'string',
            ])
            ->whereAll([
                'data.name' => $this->user->name,
                'data.email' => $email,
            ])
        );
});

test('should update entire user data and return updated user data', function (): void {

    $data = [
        'name' => fake()->name(),
        'email' => fake()->freeEmail(),
        'password' => '@p5sSw0rd!',
    ];

    $this
        ->patchJson(USER_ENDPOINT, $data)
        ->assertOk()
        ->assertJson(fn (AssertableJson $json): AssertableJson => $json
            ->hasAll([
                'data',
                'data.id',
                'data.name',
                'data.email',
                'data.created_at',
                'data.updated_at',
            ])
            ->whereAllType([
                'data' => 'array',
                'data.id' => 'string',
                'data.name' => 'string',
                'data.email' => 'string',
                'data.created_at' => 'string',
                'data.updated_at' => 'string',
            ])
            ->whereAll([
                'data.name' => $data['name'],
                'data.email' => $data['email'],
            ])
        );
});
