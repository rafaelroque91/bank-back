<?php
namespace App\Http\Services;

use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Http\Repositories\TransactionRepository;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Storage;

class TransactionService {

    public function depositCheck(array $check,object $checkFile, User $user) {
        $fileName = $checkFile->store('public/checks');
        $check = array_merge($check,['type' => TransactionType::Income,'status' => TransactionStatus::Pending]);

        return TransactionRepository::create($check, $user, $fileName);
    }

    public function listUserChecks(User $user,$filterDates = []) : array {
        $pending = [];
        $accepted = [];
        $rejected = [];

        $checks = TransactionRepository::listUserChecks($user, $filterDates);

        foreach($checks as $c) {
            switch ($c->status){
                case '0' :
                    $pending[] = $c;
                    break;
                case '1' :
                    $accepted[] = $c;
                    break;
                case '2' :
                    $rejected[] = $c;
                    break;
            }
        }

        return ["pending" => $pending, "accepted" => $accepted, "rejected" => $rejected];
    }

    public function approveCheck(Transaction $transaction) : bool {
        return TransactionRepository::approveCheck($transaction);
    }

    public function rejectCheck(Transaction $transaction) : bool {
        return TransactionRepository::rejectCheck($transaction);
    }

    public function listPendingChecks() : Collection  {
        $checks = TransactionRepository::listPendingChecks();
        foreach($checks as $c) {
            $c->file_url =  Storage::url($c->filename);
        }

        return $checks;
    }

    public function listIncomes(User $user,array $filterDates = []) : Collection  {
        return TransactionRepository::listIncomes($user,$filterDates);
    }

    public function listExpenses(User $user,array $filterDates = []) : Collection  {
        return TransactionRepository::listExpenses($user,$filterDates);
    }

    public function purchase(array $purchase, User $user) {
        $check = array_merge($purchase,['type' => TransactionType::Expense,'status' => TransactionStatus::Accepted]);
        $balance = $this->getCurrentBalance($user);
        if ($balance < $purchase["amount"]) {
            return ['success' => false, "message" => "not enough funds"];
        }
        return  ['success' => true, "data" => TransactionRepository::create($check,$user)];
    }

    public function listTransactions(User $user,array $filterDates = []) {

        $listTransactions = TransactionRepository::listTransactions($user, $filterDates);

        $transactions = $this->sumTransactions($listTransactions);

        if ($filterDates) {
            $balance = $this->getCurrentBalance($user);
        } else {
            $balance = $transactions['totalBalance'];
        }

        $transactions['currentBalance'] = round($balance,2);

        return $transactions;
    }

    public function getCurrentBalance(User $user) {
        $expenses = round(TransactionRepository::getTotalExpense($user),2);
        $incomes = round(TransactionRepository::getTotalIncome($user),2);

        return round($incomes - $expenses,2);
    }

    private function sumTransactions($collection) : array {
        $totalIncome = 0;
        $totalExpense = 0;

        foreach($collection as $c) {
            if ($c->type == TransactionType::Income) {
                $totalIncome += round($c->amount,2);
            } else {
                $totalExpense += round($c->amount,2);;
            }
        }
        $collection->total =$totalExpense;
        return ['totalIncome' => round($totalIncome,2),
                'totalExpense' => round($totalExpense,2),
                'totalBalance' => round($totalIncome,2) - round($totalExpense,2),
                'transactions' => $collection];
    }

    public function getValidMonths(User $user) {
        $min = Carbon::create(TransactionRepository::getMinDateValidMonths($user))->startofMonth();
        $current = Carbon::today();
        foreach (CarbonPeriod::create($min, '1 month', $current) as $month) {
            $months[$month->format('m-Y')] = $month->format('F, Y');
        }

        return $months;
    }

    /*public function getCheckDetail(Transaction $check){
        return TransactionRepository::getTransactionDetail($check);
    }*/
}
