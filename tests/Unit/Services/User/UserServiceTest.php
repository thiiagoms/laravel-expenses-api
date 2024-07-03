<?php

use App\DTO\User\StoreUserDTO;
use App\Exceptions\BusinessException;
use App\Exceptions\LogicalException;
use App\Messages\System\SystemMessage;
use App\Messages\User\UserMessage;
use App\Models\User;
use App\Repositories\Contracts\User\UserContract;
use App\Services\User\UserService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

dataset('find provider', fn (): array => [
    'should throw exception when user id is not valid uuid' => [
        'input' => fake()->name(),
        'exception' => LogicalException::class,
        'exceptionMessage' => SystemMessage::INVALID_PARAMETER,
        'result' => null,
    ],
    'should return false when user id is valid uuid but user does not exists' => [
        'input' => fake()->uuid(),
        'exception' => false,
        'exceptionMessage' => false,
        'result' => false,
    ],
    'should return user when user id is valid uuid and user exists' => [
        'input' => fake()->uuid(),
        'exception' => false,
        'exceptionMessage' => false,
        'result' => new User(),
    ],
]);

test('find method', function (string $input, mixed $exception, string|bool $exceptionMessage, User|bool|null $result): void {

    $userRepositoryMock = Mockery::mock(UserContract::class);

    if ($exception) {
        $this->expectException($exception);
        $this->expectExceptionMessage($exceptionMessage);
    } else {
        $userRepositoryMock->shouldReceive('find')
            ->once()
            ->with($input)
            ->andReturn($result);
    }

    /** @var UserService $userService */
    $userService = resolve(UserService::class, ['userRepository' => $userRepositoryMock]);

    /** @var User|bool $user */
    $user = $userService->find($input);

    expect($user)->toBe($result);

})->with('find provider');

dataset('emailExists provider', fn (): array => [
    'should throw exception when user email is not valid email' => [
        'input' => fake()->name(),
        'exception' => LogicalException::class,
        'exceptionMessage' => SystemMessage::INVALID_PARAMETER,
        'result' => false,
    ],
    'should return false when user email is valid but does not exists' => [
        'input' => fake()->freeEmail(),
        'exception' => false,
        'exceptionMessage' => false,
        'result' => false,
    ],
    'should return true when user email is valid and exists' => [
        'input' => fake()->freeEmail(),
        'exception' => false,
        'exceptionMessage' => false,
        'result' => true,
    ],
]);

test('emailExists method', function (
    string $input,
    string|bool $exception,
    string|bool $exceptionMessage,
    bool $result
): void {

    $userRepositoryMock = Mockery::mock(UserContract::class);

    if ($exception) {
        $this->expectException($exception);
        $this->expectExceptionMessage($exceptionMessage);
    } else {

        $userExists = $result ? new User() : $result;

        $userRepositoryMock->shouldReceive('findBy')
            ->once()
            ->with('email', $input, ['email'], false)
            ->andReturn($userExists);
    }

    /** @var UserService $userService */
    $userService = resolve(UserService::class, ['userRepository' => $userRepositoryMock]);

    $emailExistsMethod = new ReflectionMethod($userService, 'emailExists');
    $emailExistsMethod->setAccessible(true);

    $emailExistsResult = $emailExistsMethod->invoke($userService, $input);

    expect($emailExistsResult)
        ->toBeBool()
        ->toBe($result);
})->with('emailExists provider');

dataset('create provider', fn (): array => [

    'should throw exception when user email already exists' => [
        'userDTO' => StoreUserDTO::from([
            'name' => fake()->name(),
            'email' => fake()->freeEmail(),
            'password' => '@p5sSw0rd!',
        ]),
        'emailExists' => true,
        'exception' => BusinessException::class,
        'exceptionMessage' => UserMessage::emailAlreadyExists(),
    ],
    'should create user and return created user data when data is valid' => [
        'userDTO' => StoreUserDTO::from([
            'name' => fake()->name(),
            'email' => fake()->freeEmail(),
            'password' => '@p5sSw0rd!',
        ]),
        'emailExists' => false,
        'exception' => false,
        'exceptionMessage' => false,
    ],
]);

test('create method', function (
    StoreUserDTO $userDTO,
    bool $emailExists,
    string|bool $exception,
    string|bool $exceptionMessage
): void {

    $userRepositoryMock = Mockery::mock(UserContract::class);

    $userModelMock = new User($userDTO->toArray());

    $emailExists = $emailExists ? $userModelMock : $emailExists;

    $userRepositoryMock->shouldReceive('findBy')
        ->once()
        ->with('email', $userDTO->email, ['email'], false)
        ->andReturn($emailExists);

    if ($emailExists) {
        $this->expectException($exception);
        $this->expectExceptionMessage($exceptionMessage);
    } else {

        $userRepositoryMock->shouldReceive('create')
            ->once()
            ->with($userDTO->toArray())
            ->andReturn($userModelMock);

        DB::shouldReceive('transaction')
            ->once()
            ->andReturnUsing(fn (Closure $closure) => $closure());
    }

    /** @var UserService $userService */
    $userService = resolve(UserService::class, ['userRepository' => $userRepositoryMock]);

    /** @var User $user */
    $result = $userService->create($userDTO);

    expect($result)
        ->toBeInstanceOf(User::class)
        ->and($userDTO->name)->toBe($result->name)
        ->and($userDTO->email)->toBe($result->email)
        ->and(
            Hash::check($userDTO->password, $result->password)
        )->toBeTrue();
})->with('create provider');
