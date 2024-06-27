<?php

namespace App\Repositories\Contracts;

interface BaseContract
{
    public function find(string $id): mixed;

    public function findBy(string $column, mixed $value, array $fields = ['*'], bool $all = false): mixed;

    public function create(array $params): mixed;

    public function update(string $id, array $params): bool;

    public function destroy(string $id): bool;
}
