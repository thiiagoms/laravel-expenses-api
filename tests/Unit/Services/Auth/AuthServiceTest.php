<?php

use App\DTO\Auth\AuthDTO;
use App\Exceptions\BusinessException;
use App\Messages\Auth\AuthMessage;
use App\Models\User;
use App\Services\Auth\AuthService;
use App\Services\User\UserService;

test('response with token should return token with ttl', function () {

    $token = 'user_token';
    $expectedTTL = 60;

    $expectedOutput = [
        'token' => $token,
        'token_type' => 'Bearer',
        'expires_in' => $expectedTTL * 60,
    ];

    $authFactoryMock = Mockery::mock();
    $guardMock = Mockery::mock();
    $authMock = Mockery::mock();

    $authFactoryMock->shouldReceive('getTTL')->andReturn($expectedTTL);
    $guardMock->shouldReceive('factory')->andReturn($authFactoryMock);
    $authMock->shouldReceive('guard')->with('api')->andReturn($guardMock);

    $this->app->instance('auth', $authMock);

    /** @var AuthService $authService */
    $authService = resolve(AuthService::class);

    /** @var ReflectionMethod $responseWithTokenMethod */
    $responseWithTokenMethod = new ReflectionMethod($authService, 'responseWithToken');
    $responseWithTokenMethod->setAccessible(true);

    $result = $responseWithTokenMethod->invoke($authService, $token);

    expect($expectedOutput)->toBe($result);
});

dataset('login provider', fn (): array => [
    'should throw invalid credentials when user does not exists' => [
        'expectedInput' => AuthDTO::from(['email' => 'email', 'password' => 'password']),
        'expectedUser' => false,
        'expectedException' => true,
    ],
    'should throw invalid credentials when user is not active' => [
        'expectedInput' => AuthDTO::from(['email' => 'ilovelaravel@gmail.com', 'password' => 'password']),
        'expectedUser' => true,
        'expectedException' => true,
    ],
    'should login when user exists and has correct password and return token' => [
        'expectedInput' => AuthDTO::from(['email' => 'ilovelaravel@gmail.com', 'password' => '@p5sSw0rd!']),
        'expectedUser' => true,
        'expectedException' => false,
    ],
]);

test('login method', function (AuthDTO $expectedInput, User|bool $expectedUser, bool $expectedException): void {

    $userServiceMock = Mockery::mock(UserService::class);

    if ($expectedUser) {

        $userModelMock = new User(['name' => 'name', 'email' => 'email', 'password' => '@p5sSw0rd!']);

        $userServiceMock->shouldReceive('findBy')
            ->once()
            ->with('email', $expectedInput->email)
            ->andReturn($userModelMock);

        $authFactoryMock = Mockery::mock();
        $guardMock = Mockery::mock();
        $authMock = Mockery::mock();

        $authFactoryMock->shouldReceive('getTTL')->andReturn(60);
        $guardMock->shouldReceive('factory')->andReturn($authFactoryMock);

        $guardMock->shouldReceive('attempt')
            ->with($expectedInput->toArray())
            ->andReturn('mocked_token');

        $authMock->shouldReceive('guard')->with('api')->andReturn($guardMock);

        $this->app->instance('auth', $authMock);
    } else {

        $userServiceMock->shouldReceive('findBy')
            ->once()
            ->with('email', $expectedInput->email)
            ->andReturn($expectedUser);
    }

    /** @var AuthService $authService */
    $authService = resolve(AuthService::class, ['userService' => $userServiceMock]);

    if ($expectedException) {
        $this->expectException(BusinessException::class);
        $this->expectExceptionMessage(AuthMessage::invalidCredentials());
    }

    $tokenData = $authService->login($expectedInput);

    $this->assertArrayHasKey('token', $tokenData);
    $this->assertArrayHasKey('token_type', $tokenData);
    $this->assertArrayHasKey('expires_in', $tokenData);
})->with('login provider');
