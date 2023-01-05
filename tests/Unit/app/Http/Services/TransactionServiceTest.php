<?php

namespace Tests\Unit;

use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Http\Services\TransactionService;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Tests\TestCase;

class TransactionServiceTest extends TestCase
{

    /**
     * A basic test example.
     *
     * @return void
     */

    public function depositCheckProvider()
    {
        return [
            'testValidationAmountError' => [
                'data' => [
                    'description' => 'test',
                    'amount' => 25.50,
                ],
                'check' => \Illuminate\Http\UploadedFile::fake()->image('check.jpg')
            ],
        ];
    }

    /**
     * @dataProvider depositCheckProvider
     */
    public function testDepositCheck($data,$check) {
        $user = User::factory()->create();

        $service = new TransactionService();

        $response = $service->depositCheck($data,$check,$user);

        $this->assertDatabaseHas('transactions', [
            'id' => $response->id,
            'user_id' => $user->id,
            'amount' => $data["amount"]
        ]);
    }

    public function listUserChecksProvider()
    {
        return [
            'testPending' => [
                'status' => TransactionStatus::Pending,
                'nodeResponse' => 'pending'
            ],
            'testRejected' => [
                'status' => TransactionStatus::Rejected,
                'nodeResponse' => 'rejected'
            ],
            'testAccepted' => [
                'status' => TransactionStatus::Accepted,
                'nodeResponse' => 'accepted'
            ],
        ];
    }

    /**
     * @dataProvider listUserChecksProvider
     */
    public function testListUserChecks($status,$nodeResponse){
        $transaction = Transaction::factory()->create(
            ['type' => TransactionType::Income,'status' => $status ]);

        $service = new TransactionService();

        $return = $service->listUserChecks($transaction->user);

        $this->AssertSame($return[$nodeResponse][0]['id'],$transaction->id);
        $this->AssertSame($return[$nodeResponse][0]['description'],$transaction->description);
        $this->AssertSame(round($return[$nodeResponse][0]['amount'],2),round($transaction->amount),2);
        $this->AssertSame($return[$nodeResponse][0]['user_id'],$transaction->user_id);
        $this->AssertSame($return[$nodeResponse][0]['status'],$transaction->status);
    }

    public function testApproveCheck(){
        $transaction = Transaction::factory()->create(
            ['type' => TransactionType::Income,'status' => TransactionStatus::Pending]);

        $service = new TransactionService();

        $service->approveCheck($transaction);

        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'status' => TransactionStatus::Accepted
        ]);
    }

    public function testRejectCheck(){
        $transaction = Transaction::factory()->create(
            ['type' => TransactionType::Income,'status' => TransactionStatus::Pending]);

        $service = new TransactionService();

        $service->rejectCheck($transaction);

        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'status' => TransactionStatus::Rejected
        ]);
    }

    public function testListPendingChecks() {
        $checkPending = Transaction::factory()->create(
            ['type' => TransactionType::Income,'status' => TransactionStatus::Pending]);

        $checkAccepted = Transaction::factory()->create(
            ['type' => TransactionType::Income,'status' => TransactionStatus::Accepted]);

        $service = new TransactionService();

        $return = $service->listPendingChecks()->toArray();

       $pendingFound = array_search($checkPending->id, array_column($return, 'id'));
       $acceptedFound = array_search($checkAccepted->id, array_column($return, 'id'));
       $this->assertTrue($pendingFound>=0);
       $this->assertFalse($acceptedFound);
    }

    public function testListIncomes(){
        $service = new TransactionService();

        $transactionAccepted = Transaction::factory()->create(
            ['type' => TransactionType::Income,'status' => TransactionStatus::Accepted]);

        $return = $service->listIncomes($transactionAccepted->user)->toArray();

        $incomeFound = array_search($transactionAccepted->id, array_column($return, 'id'));

        $this->assertTrue($incomeFound>=0);
    }

    public function testListExpenses(){
        $service = new TransactionService();

        $transactionAccepted = Transaction::factory()->create(
            ['type' => TransactionType::Expense,'status' => TransactionStatus::Accepted]);

        $return = $service->listExpenses($transactionAccepted->user)->toArray();

        $incomeFound = array_search($transactionAccepted->id, array_column($return, 'id'));

        $this->assertTrue($incomeFound>=0);
    }

    public function testExpenses(){
        $service = new TransactionService();

        $transactionAccepted = Transaction::factory()->create(
            ['type' => TransactionType::Expense,'status' => TransactionStatus::Accepted]);

        $return = $service->listIncomes($transactionAccepted->user)->toArray();

        $incomeFound = array_search($transactionAccepted->id, array_column($return, 'id'));

        $this->assertTrue($incomeFound>=0);
    }

    public function testPurchase() {
        $user = User::factory()->create();

        $service = new TransactionService();

        Transaction::factory()->create(
            ['amount' => 1000, 'type' => TransactionType::Income,'status' => TransactionStatus::Accepted]);

        $balance = $service->getCurrentBalance($user);

        $purchase = [
            'description' => 'purchase test',
            'amount' => $balance,
            'due_date' => now()
        ];

        $return = $service->purchase($purchase,$user);

        $this->assertTrue($return["success"]);

        $purchase = [
            'description' => 'purchase test 2',
            'amount' => 1,
            'due_date' => now()
        ];

        $return = $service->purchase($purchase,$user);
        $this->assertFalse($return["success"]);
    }

    public function testListTransactions() {
        $user = User::factory()->create();

        $firstDate = Carbon::now()->startofMonth();

        $lastDate = clone $firstDate;
        $lastDate->endOfMonth();

        $income = Transaction::factory()->create(['user_id' => $user->id, 'due_date' => Carbon::now(),
            'type' => TransactionType::Income,'status' => TransactionStatus::Accepted]);

        $expense = Transaction::factory()->create(['user_id' => $user->id, 'due_date' => Carbon::now(),
            'type' => TransactionType::Expense,'status' => TransactionStatus::Accepted]);

        $service = new TransactionService();
        $returnFiltered = $service->listTransactions($user,['firstDate' => $firstDate, 'lastDate' => $lastDate]);

        $incomeFound = array_search($income->id, array_column($returnFiltered, 'id'));
        $expenseFound = array_search($expense->id, array_column($returnFiltered, 'id'));

        $this->assertTrue($incomeFound>=0);
        $this->assertTrue($expenseFound>=0);

        $returnDefault = $service->listTransactions($user,);

        $incomeFound = array_search($income->id, array_column($returnDefault, 'id'));
        $expenseFound = array_search($expense->id, array_column($returnDefault, 'id'));

        $this->assertTrue($incomeFound>=0);
        $this->assertTrue($expenseFound>=0);
    }

    public function testGetCurrentBalance(){
        $user = User::factory()->create();

        Transaction::factory()->create(['user_id' => $user->id,
        'amount' => 129.90, 'type' => TransactionType::Income,'status' => TransactionStatus::Accepted]);

        Transaction::factory()->create(['user_id' => $user->id,
        'amount' => 25.72, 'type' => TransactionType::Expense,'status' => TransactionStatus::Accepted]);

        $service = new TransactionService();
        $balance = $service->getCurrentBalance($user);

        $this->AssertSame($balance,round(104.18,2));
    }

    public function testGetValidMonths(){

        $user = User::factory()->create();

        Transaction::factory()->create(['user_id' => $user->id, 'due_date' => Carbon::create('2022-09-05'),
            'type' => TransactionType::Income,'status' => TransactionStatus::Accepted]);

        $expense = Transaction::factory()->create(['user_id' => $user->id, 'due_date' => Carbon::create('2023-01-05'),
            'type' => TransactionType::Expense,'status' => TransactionStatus::Accepted]);

        $service = new TransactionService();
        $validMonths = $service->getValidMonths($user);

        $this->AssertSame($validMonths["09-2022"],"September, 2022");
        $this->AssertSame($validMonths["10-2022"],"October, 2022");
        $this->AssertSame($validMonths["11-2022"],"November, 2022");
        $this->AssertSame($validMonths["12-2022"],"December, 2022");
        $this->AssertSame($validMonths["01-2023"],"January, 2023");
    }

}
