<?php

namespace App\Services\User;

use App\DTO\User\StoreUserDTO;
use App\DTO\User\UpdateUserDTO;
use App\Exceptions\BusinessException;
use App\Exceptions\LogicalException;
use App\Messages\System\SystemMessage;
use App\Messages\User\UserMessage;
use App\Models\User;
use App\Repositories\Contracts\User\UserContract;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class UserService
{
    /**
     * Init constructor
     */
    public function __construct(private readonly UserContract $userRepository) {}

    public function find(string $id): User|bool
    {
        if (! uuid_is_valid($id)) {
            throw new LogicalException(SystemMessage::INVALID_PARAMETER);
        }

        return $this->userRepository->find($id);
    }

    public function findBy(string $column, mixed $value, array $fields = ['*'], bool $all = false): Collection|User|bool
    {
        /** @var Collection|User|null $query */
        $query = $this->userRepository->findBy($column, $value, $fields, $all);

        return ! is_null($query) ? $query : false;
    }

    private function emailExists(string $email): bool
    {
        if (! isEmail($email)) {
            throw new LogicalException(SystemMessage::INVALID_PARAMETER);
        }

        /** @var User|bool $emailExists */
        $emailExists = $this->findBy(column: 'email', value: $email, fields: ['email']);

        return $emailExists instanceof User;
    }

    public function create(StoreUserDTO $userDTO): User
    {
        if ($this->emailExists($userDTO->email)) {
            throw new BusinessException(UserMessage::emailAlreadyExists());
        }

        return DB::transaction(fn (): User => $this->userRepository->create($userDTO->toArray()));
    }

    public function update(UpdateUserDTO $userDTO): User
    {
        if (! $user = $this->find($userDTO->id)) {
            throw new LogicalException(SystemMessage::RESOURCE_NOT_FOUND);
        }

        if (isset($userDTO->email) && $userDTO->email !== $user->email && $this->emailExists($userDTO->email)) {
            throw new BusinessException(UserMessage::emailAlreadyExists());
        }

        return DB::transaction(function () use ($userDTO): User {

            $dataToUpdate = removeEmpty($userDTO->toArray());

            if (! $this->userRepository->update($userDTO->id, $dataToUpdate)) {
                throw new LogicalException(SystemMessage::GENERIC_ERROR);
            }

            return $this->find($userDTO->id);
        });
    }

    public function destroy(string $id): bool
    {
        if (! $this->find($id)) {
            throw new LogicalException(SystemMessage::RESOURCE_NOT_FOUND);
        }

        return DB::transaction(fn (): bool => $this->userRepository->destroy($id));
    }
}
