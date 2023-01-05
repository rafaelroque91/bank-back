<?php
namespace App\Http\Repositories;

use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

class TransactionRepository {

    public static function create(array $transactionData,User $user, string $filename = '') : Transaction {
        $transactionData = array_merge($transactionData,['filename' => $filename]);
        return $user->transactions()->create($transactionData);
    }

    public static function listUserChecks(User $user, array $filterDate) : Collection {
        $checks = $user->transactions()->where('type',TransactionType::Income);

        return self::getFilterTransaction($checks,$filterDate,'created_at');
    }

    public static function listPendingChecks() : Collection {
        $checks = Transaction::where('type',TransactionType::Income)
            ->where('status',TransactionStatus::Pending)->get();
        foreach($checks as $c) {
          $c->user->name;
        }
        return $checks;
    }

    public static function listExpenses(User $user, array $filterDate) : Collection {
        $expenses = $user->transactions()->where('type',TransactionType::Expense)->orderBy('id', 'desc');
        return self::getFilterTransaction($expenses,$filterDate);
    }

    public static function listIncomes(User $user, array $filterDate) : Collection {
        $incomes = $user->transactions()->where('type',TransactionType::Income)
            ->where('status',TransactionStatus::Accepted)->orderBy('id', 'desc');

        return self::getFilterTransaction($incomes,$filterDate);
    }

    public static function approveCheck(Transaction $transaction) : bool {
        $transaction->status = TransactionStatus::Accepted;
        $transaction->due_date = now();

        return $transaction->save();
    }

    public static function rejectCheck(Transaction $transaction) : bool {
        $transaction->status = TransactionStatus::Rejected;
        return $transaction->save();
    }

    public static function listTransactions(User $user, array $filterDate) : Collection {
        $transactions = $user->transactions()->where('status',TransactionStatus::Accepted)->orderBy('id', 'desc');
        return self::getFilterTransaction($transactions,$filterDate);
    }

    public static function getTotalExpense(User $user) : Float {
        return $user->transactions()->where('status',TransactionStatus::Accepted)
            ->where('type',TransactionType::Expense)->sum('amount');
    }

    public static function getTotalIncome(User $user) : Float {
        return $user->transactions()->where('status',TransactionStatus::Accepted)
            ->where('type',TransactionType::Income)->sum('amount');
    }

    private static function getFilterTransaction($obj,$filterDate,$field = 'due_date') : object {
        if ($filterDate){
            $obj->whereBetween($field,[$filterDate['firstDate'],$filterDate['lastDate']]);
        }
        return $obj->get();
    }

    public static function getMinDateValidMonths(User $user) {
        return $user->transactions->min('due_date');
    }
}
