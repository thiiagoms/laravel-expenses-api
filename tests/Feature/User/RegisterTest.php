<?php

use App\Enums\User\NameEnum;
use App\Messages\User\UserMessage;
use App\Models\User;
use Illuminate\Http\Response;
use Illuminate\Testing\Fluent\AssertableJson;

const REGISTER_ENDPOINT = '/api/register';

dataset('validate name provider', fn (): array => [
    'should return name is required message when name value doest not exists' => [
        'name' => '',
        'expectedStatus' => Response::HTTP_BAD_REQUEST,
        'expectedJson' => fn (AssertableJson $json): AssertableJson => $json
            ->hasAll([
                'name',
                'name.0',
            ])
            ->whereAllType([
                'name' => 'array',
                'name.0' => 'string',
            ])
            ->where('name.0', UserMessage::nameIsRequired()),
    ],
    'should return name min length message when name is lower than min length' => [
        'name' => str_repeat('#', (NameEnum::MIN_LENGTH->value - 1)),
        'expectedStatus' => Response::HTTP_BAD_REQUEST,
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
        'expectedStatus' => Response::HTTP_BAD_REQUEST,
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
        'expectedStatus' => Response::HTTP_BAD_REQUEST,
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

test(
    'validate name',
    fn (string|float $name, int $expectedStatus, Closure $expectedJson) => $this
        ->postJson(
            REGISTER_ENDPOINT,
            ['name' => $name, 'email' => fake()->freeEmail(), 'password' => '@p5sSw0rd!']
        )
        ->assertStatus($expectedStatus)
        ->assertJson($expectedJson)
)->with('validate name provider');

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
    'should return email already exists message when email already exists' => [
        'email' => 'ilovelaravel@gmail.com',
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
            ->where('email.0', UserMessage::emailAlreadyExists()),
    ],
]);

test('validate email', function (string $email, int $expectedStatus, Closure $expectedJson): void {

    User::factory()->createOne(['email' => 'ilovelaravel@gmail.com']);

    $this
        ->postJson(
            REGISTER_ENDPOINT,
            ['name' => fake()->name(), 'email' => $email, 'password' => '@p5sSw0rd!']
        )
        ->assertStatus($expectedStatus)
        ->assertJson($expectedJson);
})->with('validate email provider');

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
    fn (string $password, int $expectedStatus, Closure $expectedJson) => $this
        ->postJson(
            REGISTER_ENDPOINT,
            ['name' => fake()->name(), 'email' => fake()->freeEmail(), 'password' => $password]
        )
        ->assertStatus($expectedStatus)
        ->assertJson($expectedJson)
)->with('validate password provider');

test('should create user and return created user data', function (): void {

    $data = [
        'name' => fake()->name(),
        'email' => fake()->freeEmail(),
        'password' => '@p5sSw0rd!',
    ];

    $this->postJson(REGISTER_ENDPOINT, $data)
        ->assertCreated()
        ->assertJson(
            fn (AssertableJson $json): AssertableJson => $json
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
