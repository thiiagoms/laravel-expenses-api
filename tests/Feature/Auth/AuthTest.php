<?php

use App\Messages\Auth\AuthMessage;
use App\Messages\User\UserMessage;
use App\Models\User;
use Illuminate\Http\Response;
use Illuminate\Testing\Fluent\AssertableJson;

const AUTH_ENDPOINT = '/api/auth/login';

dataset('validate email provider', fn (): array => [
    'should return email is required message when email value does not exists' => [
        'email' => '',
        'expectedStatus' => Response::HTTP_BAD_REQUEST,
        'expectedJson' => fn (AssertableJson $json): AssertableJson => $json
            ->hasAll([
                'email',
                'email.0',
            ])
            ->whereAllType([
                'email' => 'array',
                'email.0' => 'string',
            ])
            ->where('email.0', UserMessage::emailIsRequired()),
    ],
    'should return email is invalid message when email is not a valid email' => [
        'email' => fake()->name(),
        'expectedStatus' => Response::HTTP_BAD_REQUEST,
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
]);

test(
    'validate email',
    fn (string $email, int $expectedStatus, Closure $expectedJson) =>
    $this
        ->postJson(AUTH_ENDPOINT, ['email' => $email, 'password' => '@p5sSw0rd!'])
        ->assertStatus($expectedStatus)
        ->assertJson($expectedJson)
)->with('validate email provider');

dataset('validate password provider', fn (): array => [
    'should return password is required message when password value does not exists' => [
        'password' => '',
        'expectedStatus' => Response::HTTP_BAD_REQUEST,
        'expectedJson' => fn (AssertableJson $json): AssertableJson => $json
            ->hasAll([
                'password',
                'password.0',
            ])
            ->whereAllType([
                'password' => 'array',
                'password.0' => 'string',
            ])
            ->where('password.0', UserMessage::passwordIsRequired()),
    ],
    'should return password min length message when password is less than 8 characters' => [
        'password' => 'p4sS!',
        'expectedStatus' => Response::HTTP_BAD_REQUEST,
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
        'expectedStatus' => Response::HTTP_BAD_REQUEST,
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
        'expectedStatus' => Response::HTTP_BAD_REQUEST,
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
        'expectedStatus' => Response::HTTP_BAD_REQUEST,
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

test(
    'validate password',
    fn (string $password, int $expectedStatus, Closure $expectedJson) =>
    $this
        ->postJson(AUTH_ENDPOINT, ['email' => fake()->freeEmail(), 'password' => $password])
        ->assertStatus($expectedStatus)
        ->assertJson($expectedJson)
)->with('validate password provider');

dataset('auth provider', fn (): array => [
    'should return invalid credentials message when credentials are invalid' => [
        'expectedData' => ['email' => fake()->freeEmail(), 'password' => '@p5sSw0rd!'],
        'expectedStatus' => Response::HTTP_BAD_REQUEST,
        'expectedJson' => fn (AssertableJson $json): AssertableJson => $json
            ->has('message')
            ->whereType('message', 'string')
            ->where('message', AuthMessage::invalidCredentials()),
    ],
    'should return user token when credentials are valid' => [
        'expectedData' => ['email' => 'ilovelaravel@gmail.com', 'password' => '@p5sSw0rd!'],
        'expectedStatus' => Response::HTTP_OK,
        'expectedJson' => fn (AssertableJson $json): AssertableJson => $json
            ->hasAll([
                'data',
                'data.token',
                'data.token_type',
                'data.expires_in',
            ])
            ->whereAllType([
                'data' => 'array',
                'data.token' => 'string',
                'data.token_type' => 'string',
                'data.expires_in' => 'integer',
            ]),
    ],
]);

test('auth', function (array $expectedData,int $expectedStatus, Closure $expectedJson): void {

    User::factory()->createOne(['email' => 'ilovelaravel@gmail.com']);

    $this->postJson(AUTH_ENDPOINT, $expectedData)
        ->assertStatus($expectedStatus)
        ->assertJson($expectedJson);
})->with('auth provider');

