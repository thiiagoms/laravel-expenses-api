<?php

use App\DTO\Expense\StoreExpenseDTO;
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
