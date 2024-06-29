<?php

namespace App\Repositories\ORM;

use App\Exceptions\LogicalException;
use App\Messages\System\SystemMessage;

abstract class BaseRepository
{
    protected $model;

    private function handle(): mixed
    {
        return app($this->model);
    }

    public function __construct()
    {
        $this->model = $this->handle();
    }

    public function find(string $id): mixed
    {
        $result = $this->model->find($id);

        return ! is_null($result) ? $result : false;
    }

    public function findBy(string $column, mixed $value, array $fields = ['*'], bool $all = false): mixed
    {
        if (! in_array($column, $this->model->getFillable())) {
            throw new LogicalException(SystemMessage::INVALID_PARAMETER);
        }

        $query = $this->model->select($fields)->where($column, $value);

        return ! $all ? $query->first() : $query->get();
    }

    public function create(array $params): mixed
    {
        return $this->model->create($params);
    }

    public function update(string $id, array $params): bool
    {
        return $this->model->find($id)?->update($params);
    }

    public function destroy(string $id): bool
    {
        return $this->model->destroy($id);
    }
}
