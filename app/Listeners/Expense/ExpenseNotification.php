<?php

namespace App\Listeners\Expense;

use App\Events\Expense\CreateExpenseEvent;
use App\Mail\Expense\CreateExpenseMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

class ExpenseNotification implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(CreateExpenseEvent $event): void
    {
        Mail::to($event->expense->user->email)->send(new CreateExpenseMail($event->expense));
    }
}
