<?php

namespace App\Http\Controllers;

use Carbon\Carbon;

class BaseController extends Controller
{
    public function responseSuccess($data,$message = ''){
        return response()->json(['success' => true,'data' => $data,'message' => $message], 200);
    }

    public function responseError($message) {
        return response()->json(['success' => false,'message' => $message], 400);
    }

    public function filterMonthToDates(string $filterMonth) {

        $firstDate = Carbon::createFromFormat('d-m-Y','01-'.$filterMonth)->startofMonth();

        $lastDate = clone $firstDate;

        $lastDate->endOfMonth();

        return ["firstDate" => $firstDate, "lastDate" => $lastDate];
    }

}
