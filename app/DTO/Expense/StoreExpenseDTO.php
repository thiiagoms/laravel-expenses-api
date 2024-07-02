<?php

namespace App\DTO\Expense;

use App\DTO\DTO;
use Carbon\Carbon;

class StoreExpenseDTO extends DTO
{
    public function __construct(
        public readonly string $user_id,
        public readonly string $description,
        public readonly float $price,
        public readonly Carbon $date
    ) {}

    public static function from(array $params): self
    {
        $params = clean($params);

        $params['date'] = Carbon::parse($params['date']);

        return new self(...$params);
    }
}
