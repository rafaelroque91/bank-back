<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::post('/register', [AuthController::class, 'register']);

Route::middleware(['auth:api'])->group(function () {
    Route::prefix('/user')->group(function () {
        Route::get('/', [AuthController::class, 'userData']);
        Route::get('/balance', [TransactionController::class, 'getCurrentbalance']);
        Route::get('/months', [TransactionController::class, 'getValidMonths']);
    });

    Route::get('/transactions', [TransactionController::class, 'listTransactions']);

    //Deposit routes...
    Route::controller(IncomeController::class)->group(function () {
        //list incomes
        Route::get('/income/list', 'list');
    });

    Route::get('/income/list', [IncomeController::class, 'list']);

    //Check routes...
    Route::prefix('/check')->group(function () {
        Route::post('/deposit', [TransactionController::class,'depositCheck']);
        Route::get('/list', [TransactionController::class,'listChecks']);
    });

    //Expenses routes...
    Route::prefix('/expense')->group(function () {
        Route::get('/list', [TransactionController::class,'listExpenses']);
        Route::post('/purchase', [TransactionController::class,'purchase']);
    });

    //Income routes...
    Route::get('/income/list', [TransactionController::class,'listIncomes']);

    Route::prefix('/admin')->group(function () {
        Route::prefix('/check')->group(function () {
            Route::get('/pending', [TransactionController::class,'pendingChecks']);
            // Route::get('/detail/{transaction}', [TransactionController::class,'checkDetails']);
            Route::post('/approve/{transaction}', [TransactionController::class,'approveCheck']);
            Route::post('/reject/{transaction}', [TransactionController::class,'rejectCheck']);
        });
    });
});

/* to generate storage link */
/*
Route::get('/storage', function (){
    Artisan::call("storage:link");
});
*/
