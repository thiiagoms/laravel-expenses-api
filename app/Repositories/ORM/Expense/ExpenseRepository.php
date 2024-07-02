<?php

namespace App\Repositories\ORM\Expense;

use App\Models\Expense;
use App\Repositories\Contracts\Expense\ExpenseContract;
use App\Repositories\ORM\BaseRepository;

class ExpenseRepository extends BaseRepository implements ExpenseContract
{
    protected $model = Expense::class;
}
