<?php

namespace App\Http\Controllers;

use App\Http\Requests\Transactions\DepositCheckRequest;
use App\Http\Requests\Transactions\PurchaseRequest;
use App\Http\Services\TransactionService;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class TransactionController extends BaseController
{
    public $transactionService;

    public function __construct()
    {
        $this->transactionService = new TransactionService();
    }

    public function depositCheck(DepositCheckRequest $request){
        $request->validated();
        $checkFile = $request->file('check');
        $checkInputs = $request->only(['description','amount']);

        $return = $this->transactionService->depositCheck($checkInputs,$checkFile,auth()->user());

        if ($return) {
            return $this->responseSuccess($return,'Check deposited successfully');
        } else {
            return $this->responseError('Error when trying to deposit the checks.');
        }
    }

    public function listChecks(Request $request){

        $filterDates = $request->query('filter-month') ?
        $this->filterMonthToDates($request->query('filter-month')) : [];

        $return = $this->transactionService->listUserChecks(auth()->user(),$filterDates);

        if ($return) {
            return $this->responseSuccess($return);
        } else {
            return $this->responseError('Error when trying to get the checks.');
        }
    }

    public function pendingChecks() {
        $return = $this->transactionService->listPendingChecks();

        if ($return) {
            return $this->responseSuccess($return);
        } else {
            return $this->responseError('Error when trying to get pending checks.');
        }
    }

    /*public function checkDetails(Transaction $transaction) {
        $return = $this->transactionService->getCheckDetail($transaction);

        if ($return) {
            return $this->responseSuccess($return);
        } else {
            return $this->responseError('Error when trying to get pending checks.');
        }
    }*/

    public function approveCheck(Transaction $transaction) {
        if (!Gate::allows('admin-check')) {
            return $this->responseError('Only allowed for admin');
        }

        if ($this->transactionService->approveCheck($transaction)) {
            return $this->responseSuccess('','Check approved');
        } else {
            return $this->responseError('Error when trying to approve the check.');
        }
    }

    public function rejectCheck(Transaction $transaction) {

        if (!Gate::allows('admin-check')) {
            return $this->responseError('only allowed for admin');
        }

        if ($this->transactionService->rejectCheck($transaction)) {
            return $this->responseSuccess('','Check rejected');
        }  else {
            return $this->responseError('Error when trying to reject the check.');
        }
    }

    public function listIncomes(Request $request) {

        $filterDates = $request->query('filter-month') ?
        $this->filterMonthToDates($request->query('filter-month')) : [];

        $return = $this->transactionService->listIncomes(auth()->user(),$filterDates);

        if ($return) {
            return $this->responseSuccess($return);
        } else {
            return $this->responseError('Error when trying to get incomes list.');
        }
    }

    public function listExpenses(Request $request) {

        $filterDates = $request->query('filter-month') ?
        $this->filterMonthToDates($request->query('filter-month')) : [];

        $return = $this->transactionService->listExpenses(auth()->user(),$filterDates);

        if ($return) {
            return $this->responseSuccess($return);
        } else {
            return $this->responseError('Error when trying to get expenses list.');
        }
    }

    public function purchase(PurchaseRequest $request){
        $request->validated();

        $purchaseInputs = $request->only(['description','amount','due_date']);

        $return = $this->transactionService->purchase($purchaseInputs,auth()->user());

        if ($return["success"]) {
            return $this->responseSuccess($return["data"],'Purchase successfully');
        } else {
            return $this->responseError($return["message"]);
        }
    }

    public function listTransactions(Request $request) {
        $filterDates = $request->query('filter-month') ?
            $this->filterMonthToDates($request->query('filter-month')) : [];

        $return = $this->transactionService->listTransactions(auth()->user(),$filterDates);

        if ($return) {
            return $this->responseSuccess($return);
        } else {
            return $this->responseError('Error when trying to get the transactions.');
        }
    }

    public function getCurrentbalance() {

        $return = $this->transactionService->getCurrentBalance(auth()->user());

        if ($return) {
            return $this->responseSuccess($return);
        } else {
            return $this->responseError('Error when trying to get the transactions.');
        }
    }

    public function getValidMonths() {
        $return = $this->transactionService->getValidMonths(auth()->user());

        if ($return) {
            return $this->responseSuccess($return);
        } else {
            return $this->responseError('Error when trying to get the valid months.');
        }

    }

}
