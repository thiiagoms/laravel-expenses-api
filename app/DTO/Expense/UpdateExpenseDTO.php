<?php

namespace App\DTO\Expense;

use App\DTO\DTO;
use Carbon\Carbon;

class UpdateExpenseDTO extends DTO
{
    public function __construct(
        public readonly string $id,
        public readonly string $user_id,
        public readonly ?string $description = null,
        public readonly ?float $price = null,
        public readonly ?Carbon $date = null
    ) {}

    public static function from(array $params): self
    {
        $params = clean($params);

        if (isset($params['date'])) {
            $params['date'] = Carbon::parse($params['date']);
        }

        return new self(...$params);
    }
}
