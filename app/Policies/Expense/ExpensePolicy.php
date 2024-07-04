<?php

namespace App\Policies\Expense;

use App\Exceptions\AuthorizationException;
use App\Messages\Auth\AuthMessage;
use App\Models\Expense;
use App\Models\User;

class ExpensePolicy
{
    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Expense $expense): bool
    {
        if ($user->id !== $expense->user_id) {
            throw new AuthorizationException(AuthMessage::unauthorized());
        }

        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Expense $expense): bool
    {
        if ($user->id !== $expense->user_id) {
            throw new AuthorizationException(AuthMessage::unauthorized());
        }

        return true;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Expense $expense): bool
    {
        return $user->id === $expense->user->id;
    }
}
