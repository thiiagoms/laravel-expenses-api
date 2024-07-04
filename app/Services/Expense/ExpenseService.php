<?php

namespace App\Services\Expense;

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
use App\Services\User\UserService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ExpenseService
{
    public function __construct(
        private readonly ExpenseContract $expenseRepository,
        private readonly UserService $userService
    ) {}

    public function find(string $id): Expense|bool
    {
        if (! uuid_is_valid($id)) {
            throw new LogicalException(SystemMessage::INVALID_PARAMETER);
        }

        return $this->expenseRepository->find($id);
    }

    private function validateDescription(string $description): void
    {
        if (strlen($description) > DescriptionEnum::MAX_LENGTH->value) {
            throw new BusinessException(ExpenseMessage::descriptionMaxLength());
        }
    }

    private function validatePrice(float|int $price): void
    {
        if ($price <= 0) {
            throw new BusinessException(ExpenseMessage::priceIsNotValid());
        }
    }

    private function validateDate(Carbon $date): void
    {
        if ($date->isFuture()) {
            throw new BusinessException(ExpenseMessage::dateIsInvalid());
        }
    }

    private function validateExpense(StoreExpenseDTO|UpdateExpenseDTO $expenseDTO): void
    {
        if (! empty($expenseDTO->description)) {
            $this->validateDescription($expenseDTO->description);
        }

        if (! empty($expenseDTO->price)) {
            $this->validatePrice($expenseDTO->price);
        }

        if (! empty($expenseDTO->date)) {
            $this->validateDate($expenseDTO->date);
        }
    }

    private function userExists(string $userId): User|bool
    {
        return $this->userService->find($userId);
    }

    public function create(StoreExpenseDTO $expenseDTO): Expense
    {
        $this->validateExpense($expenseDTO);

        if (! $this->userExists($expenseDTO->user_id)) {
            throw new LogicalException(SystemMessage::RESOURCE_NOT_FOUND);
        }

        return DB::transaction(function () use ($expenseDTO): Expense {

            /** @var Expense $expense */
            $expense = $this->expenseRepository->create($expenseDTO->toArray());

            event(new CreateExpenseEvent($expense));

            return $expense;
        });
    }

    public function update(UpdateExpenseDTO $expenseDTO): Expense
    {
        $this->validateExpense($expenseDTO);

        if (! $this->userExists($expenseDTO->user_id) || ! $this->find($expenseDTO->id)) {
            throw new LogicalException(SystemMessage::RESOURCE_NOT_FOUND);
        }

        return DB::transaction(function () use ($expenseDTO): Expense {

            $dataToUpdate = removeEmpty($expenseDTO->toArray());

            if (! $this->expenseRepository->update($expenseDTO->id, $dataToUpdate)) {
                throw new LogicalException(SystemMessage::GENERIC_ERROR);
            }

            return $this->find($expenseDTO->id);
        });
    }

    public function destroy(string $id): bool
    {
        if (! $this->find($id)) {
            throw new LogicalException(SystemMessage::RESOURCE_NOT_FOUND);
        }

        return DB::transaction(fn (): bool => $this->expenseRepository->destroy($id));
    }
}
