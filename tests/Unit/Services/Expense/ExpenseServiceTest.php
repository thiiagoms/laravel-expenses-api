<?php

use App\DTO\Expense\StoreExpenseDTO;
use App\DTO\Expense\UpdateExpenseDTO;
use App\Enums\Expense\DescriptionEnum;
use App\Events\Expense\CreateExpenseEvent;
use App\Exceptions\BusinessException;
use App\Exceptions\LogicalException;
use App\Messages\Expense\ExpenseMessage;
use App\Messages\System\SystemMessage;
use App\Models\Expense;
use App\Models\User;
use App\Repositories\Contracts\Expense\ExpenseContract;
use App\Services\Expense\ExpenseService;
use App\Services\User\UserService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;

dataset('find provider', fn (): array => [
    'should throw exception when id is not a valid uuid' => [
        'input' => fake()->name(),
        'exception' => LogicalException::class,
        'exceptionMessage' => SystemMessage::INVALID_PARAMETER,
        'result' => null,
    ],
    'should return false when id is a valid uuid but expense does not exists' => [
        'input' => fake()->uuid(),
        'exception' => false,
        'exceptionMessage' => false,
        'result' => false,
    ],
    'should return expense when id is a valid uuid and expense exists' => [
        'input' => fake()->uuid(),
        'exception' => false,
        'exceptionMessage' => false,
        'result' => new Expense(),
    ],
]);

test('find method', function (
    string $input,
    mixed $exception,
    string|bool $exceptionMessage,
    Expense|bool|null $result
): void {

    $expenseRepositoryMock = Mockery::mock(ExpenseContract::class);

    if ($exception) {
        $this->expectException($exception);
        $this->expectExceptionMessage($exceptionMessage);
    } else {

        $expenseRepositoryMock
            ->shouldReceive('find')
            ->with($input)
            ->once()
            ->andReturn($result);
    }

    /** @var ExpenseService $expenseService */
    $expenseService = resolve(ExpenseService::class, ['expenseRepository' => $expenseRepositoryMock]);

    $output = $expenseService->find($input);

    expect($output)->toBe($result);
})->with('find provider');

dataset('validateDescription provider', fn (): array => [
    'should throw exception when description is higher than max length' => [
        'input' => str_repeat('#', (DescriptionEnum::MAX_LENGTH->value + 1)),
        'exception' => BusinessException::class,
        'exceptionMessage' => ExpenseMessage::descriptionMaxLength(),
        'result' => null,
    ],
    'should return null when description is valid' => [
        'input' => str_repeat('#', DescriptionEnum::MAX_LENGTH->value),
        'exception' => false,
        'exceptionMessage' => false,
        'result' => null,
    ],
]);

test('validateDescription method', function (
    string $input,
    string|bool $exception,
    string|bool $exceptionMessage,
    null $result
): void {

    if ($exception) {
        $this->expectException($exception);
        $this->expectExceptionMessage($exceptionMessage);
    }

    /** @var ExpenseService $expenseService */
    $expenseService = resolve(ExpenseService::class);

    /** @var ReflectionMethod $validateDescriptionMethod */
    $validateDescriptionMethod = new ReflectionMethod($expenseService, 'validateDescription');
    $validateDescriptionMethod->setAccessible(true);

    $output = $validateDescriptionMethod->invoke($expenseService, $input);

    expect($output)->toBe($result);
})->with('validateDescription provider');

dataset('validatePrice provider', fn (): array => [
    'should throw exception when price is lower than zero' => [
        'input' => -1,
        'exception' => BusinessException::class,
        'exceptionMessage' => ExpenseMessage::priceIsNotValid(),
        'result' => null,
    ],
    'should return null when price is valid' => [
        'input' => fake()->randomFloat(),
        'exception' => false,
        'exceptionMessage' => false,
        'result' => null,
    ],
]);

test('validatePrice method', function (
    float $price,
    mixed $exception,
    string|bool $exceptionMessage,
    null $result
): void {

    if ($exception) {
        $this->expectException($exception);
        $this->expectExceptionMessage($exceptionMessage);
    }

    /** @var ExpenseService $expenseService */
    $expenseService = resolve(ExpenseService::class);

    $validatePriceMethod = new ReflectionMethod($expenseService, 'validatePrice');
    $validatePriceMethod->setAccessible(true);

    $output = $validatePriceMethod->invoke($expenseService, $price);

    expect($output)->toBe($result);
})->with('validatePrice provider');

dataset('validateDate provider', fn (): array => [
    'should throw exception when date is in the future' => [
        'input' => Carbon::now()->addDay(),
        'exception' => BusinessException::class,
        'exceptionMessage' => ExpenseMessage::dateIsInvalid(),
        'result' => null,
    ],
    'should return null when date is valid' => [
        'input' => Carbon::now(),
        'exception' => false,
        'exceptionMessage' => false,
        'result' => null,
    ],
]);

test('validateDate method', function (
    Carbon $input,
    mixed $exception,
    string|bool $exceptionMessage,
    null $result
): void {

    if ($exceptionMessage) {
        $this->expectException($exception);
        $this->expectExceptionMessage($exceptionMessage);
    }

    /** @var ExpenseService $expenseService */
    $expenseService = resolve(ExpenseService::class);

    $validateDateMethod = new ReflectionMethod($expenseService, 'validateDate');
    $validateDateMethod->setAccessible(true);

    $output = $validateDateMethod->invoke($expenseService, $input);

    expect($output)->toBe($result);
})->with('validateDate provider');

dataset('validateExpense provider', fn (): array => [
    'should throw exception when description is higher than max length' => [
        'input' => StoreExpenseDTO::from([
            'user_id' => fake()->uuid(),
            'description' => str_repeat('#', (DescriptionEnum::MAX_LENGTH->value + 1)),
            'price' => fake()->randomFloat(),
            'date' => Carbon::now(),
        ]),
        'exception' => BusinessException::class,
        'exceptionMessage' => ExpenseMessage::descriptionMaxLength(),
        'result' => null,
    ],
    'should throw exception when price is lower than zero' => [
        'input' => StoreExpenseDTO::from([
            'user_id' => fake()->uuid(),
            'description' => str_repeat('#', DescriptionEnum::MAX_LENGTH->value),
            'price' => -1,
            'date' => Carbon::now(),
        ]),
        'exception' => BusinessException::class,
        'exceptionMessage' => ExpenseMessage::priceIsNotValid(),
        'result' => null,
    ],
    'should throw exception when date is in the future' => [
        'input' => StoreExpenseDTO::from([
            'user_id' => fake()->uuid(),
            'description' => str_repeat('#', DescriptionEnum::MAX_LENGTH->value),
            'price' => fake()->randomFloat(),
            'date' => Carbon::now()->addDay(),
        ]),
        'exception' => BusinessException::class,
        'exceptionMessage' => ExpenseMessage::dateIsInvalid(),
        'result' => null,
    ],
]);

test('validateExpense method', function (StoreExpenseDTO $input, mixed $exception, string|bool $exceptionMessage, null $result): void {

    $this->expectException($exception);
    $this->expectExceptionMessage($exceptionMessage);

    /** @var ExpenseService $expenseService */
    $expenseService = resolve(ExpenseService::class);

    $validateExpenseMethod = new ReflectionMethod($expenseService, 'validateExpense');
    $validateExpenseMethod->setAccessible(true);

    $validateExpenseMethod->invoke($expenseService, $input);
})->with('validateExpense provider');

test('create method should throw exception when user does not exists', function (): void {

    $expenseDTO = StoreExpenseDTO::from([
        'user_id' => fake()->uuid(),
        'description' => str_repeat('#', DescriptionEnum::MAX_LENGTH->value),
        'price' => fake()->randomFloat(),
        'date' => Carbon::now(),
    ]);

    $userServiceMock = Mockery::mock(UserService::class);

    $userServiceMock->shouldReceive('find')
        ->once()
        ->with($expenseDTO->user_id)
        ->andReturnFalse();

    /** @var ExpenseService $expenseService */
    $expenseService = resolve(ExpenseService::class, ['userService' => $userServiceMock]);

    $this->expectException(LogicalException::class);
    $this->expectExceptionMessage(SystemMessage::RESOURCE_NOT_FOUND);

    $expenseService->create($expenseDTO);
});

test('create method should create expense and return created expense data', function (): void {

    $userMock = new User(['id' => fake()->uuid(), 'name' => fake()->name(), 'email' => fake()->freeEmail()]);

    $expenseDTO = StoreExpenseDTO::from([
        'user_id' => $userMock->id,
        'description' => str_repeat('#', DescriptionEnum::MAX_LENGTH->value),
        'price' => fake()->randomFloat(),
        'date' => Carbon::now(),
    ]);

    $userServiceMock = Mockery::mock(UserService::class);

    $userServiceMock->shouldReceive('find')
        ->once()
        ->with($expenseDTO->user_id)
        ->andReturn($userMock);

    $expenseRepositoryMock = Mockery::mock(ExpenseContract::class);

    $expenseMock = new Expense([
        'id' => fake()->uuid(),
        'user_id' => $userMock->id,
        'description' => $expenseDTO->description,
        'price' => $expenseDTO->price,
        'date' => $expenseDTO->date,
    ]);

    $expenseRepositoryMock->shouldReceive('create')
        ->once()
        ->with($expenseDTO->toArray())
        ->andReturn($expenseMock);

    DB::shouldReceive('transaction')
        ->once()
        ->andReturnUsing(function (Closure $closure) {
            return $closure();
        });

    Event::fake([CreateExpenseEvent::class]);

    /** @var ExpenseService $expenseService */
    $expenseService = resolve(ExpenseService::class, [
        'userService' => $userServiceMock,
        'expenseRepository' => $expenseRepositoryMock,
    ]);

    /** @var Expense $expense */
    $expense = $expenseService->create($expenseDTO);

    expect($expense)
        ->not->toBeEmpty()
        ->toBeInstanceOf(Expense::class)
        ->toEqual($expenseMock);
});

test('update method should throw exception when user does not exists', function (): void {

    $expenseDTO = UpdateExpenseDTO::from([
        'id' => fake()->uuid(),
        'user_id' => fake()->uuid(),
        'description' => str_repeat('#', DescriptionEnum::MAX_LENGTH->value),
        'price' => fake()->randomFloat(),
        'date' => Carbon::now(),
    ]);

    $userServiceMock = Mockery::mock(UserService::class);

    $userServiceMock->shouldReceive('find')
        ->once()
        ->with($expenseDTO->user_id)
        ->andReturnFalse();

    /** @var ExpenseService $expenseService */
    $expenseService = resolve(ExpenseService::class, ['userService' => $userServiceMock]);

    $this->expectException(LogicalException::class);
    $this->expectExceptionMessage(SystemMessage::RESOURCE_NOT_FOUND);

    $expenseService->update($expenseDTO);
});

test('update method should throw exception when expense does not exists', function (): void {

    $expenseDTO = UpdateExpenseDTO::from([
        'id' => fake()->uuid(),
        'user_id' => fake()->uuid(),
        'description' => str_repeat('#', DescriptionEnum::MAX_LENGTH->value),
        'price' => fake()->randomFloat(),
        'date' => Carbon::now(),
    ]);

    $expenseRepositoryMock = Mockery::mock(ExpenseContract::class);

    $expenseRepositoryMock->shouldReceive('find')
        ->once()
        ->with($expenseDTO->id)
        ->andReturnFalse();

    $userServiceMock = Mockery::mock(UserService::class);

    $userServiceMock->shouldReceive('find')
        ->once()
        ->with($expenseDTO->user_id)
        ->andReturn(new User(['id' => $expenseDTO->user_id]));

    /** @var ExpenseService $expenseService */
    $expenseService = resolve(ExpenseService::class, [
        'userService' => $userServiceMock,
        'expenseRepository' => $expenseRepositoryMock,
    ]);

    $this->expectException(LogicalException::class);
    $this->expectExceptionMessage(SystemMessage::RESOURCE_NOT_FOUND);

    $expenseService->update($expenseDTO);
});

test('update method should update expense and return updated expense data', function (): void {

    $userMok = new User(['id' => fake()->uuid(), 'name' => fake()->name(), 'email' => fake()->freeEmail()]);

    $expenseMock = new Expense([
        'id' => fake()->uuid(),
        'user_id' => $userMok->id,
        'description' => str_repeat('#', DescriptionEnum::MAX_LENGTH->value),
        'price' => fake()->randomFloat(),
        'date' => Carbon::now(),
    ]);

    $updatedExpenseMock = new Expense([
        'id' => $expenseMock->id,
        'user_id' => $userMok->id,
        'description' => fake()->sentence(),
        'price' => fake()->randomFloat(),
    ]);

    $expenseDTO = UpdateExpenseDTO::from($updatedExpenseMock->toArray());

    $userServiceMock = Mockery::mock(UserService::class);

    $userServiceMock->shouldReceive('find')
        ->once()
        ->with($expenseDTO->user_id)
        ->andReturn($userMok);

    $expenseRepositoryMock = Mockery::mock(ExpenseContract::class);

    $expenseRepositoryMock->shouldReceive('find')
        ->twice()
        ->with($expenseDTO->id)
        ->andReturnUsing(fn (): Expense => $expenseMock, fn (): Expense => $updatedExpenseMock);

    $expenseRepositoryMock->shouldReceive('update')
        ->once()
        ->with($expenseDTO->id, removeEmpty($expenseDTO->toArray()))
        ->andReturnTrue();

    DB::shouldReceive('transaction')
        ->once()
        ->andReturnUsing(function (Closure $closure): Expense {
            return $closure();
        });

    $expenseService = resolve(ExpenseService::class, [
        'userService' => $userServiceMock,
        'expenseRepository' => $expenseRepositoryMock,
    ]);

    /** @var Expense $expense */
    $expense = $expenseService->update($expenseDTO);

    expect($expense)
        ->not->toBeEmpty()
        ->toBeInstanceOf(Expense::class)
        ->toEqual($updatedExpenseMock);
});

dataset('destroy provider', fn (): array => [
    'should throw exception when expense id does not exists' => [
        'input' => fake()->uuid(),
        'exception' => LogicalException::class,
        'exceptionMessage' => SystemMessage::RESOURCE_NOT_FOUND,
        'result' => false,
    ],
    'should destroy expense and return true' => [
        'input' => fake()->uuid(),
        'exception' => false,
        'exceptionMessage' => false,
        'result' => true,
    ],
]);

test('destroy method', function (string $input, mixed $exception, string|bool $exceptionMessage, bool $result): void {

    $expenseRepositoryMock = Mockery::mock(ExpenseContract::class);

    $expenseRepositoryMock->shouldReceive('find')
        ->once()
        ->with($input)
        ->andReturn($result);

    if ($exception) {
        $this->expectException($exception);
        $this->expectExceptionMessage($exceptionMessage);
    } else {

        $expenseRepositoryMock
            ->shouldReceive('destroy')
            ->once()
            ->with($input)
            ->andReturn($result);

        DB::shouldReceive('transaction')
            ->once()
            ->andReturnUsing(function (Closure $closure): bool {
                return $closure();
            });
    }

    /** @var ExpenseService $expenseService */
    $expenseService = resolve(ExpenseService::class, ['expenseRepository' => $expenseRepositoryMock]);

    $output = $expenseService->destroy($input);

    expect($output)
        ->toBeBool()
        ->toBe($result);
})->with('destroy provider');
