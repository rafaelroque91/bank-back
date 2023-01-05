<?php

namespace App\Http\Controllers;

use App\Http\Requests\Users\RegisterUserRequest;
use App\Http\Services\UserService;
use Illuminate\Http\Request;

class AuthController extends BaseController
{
    public $userService;

    public function __construct()
    {
        $this->userService = new UserService();
    }

    public function register(RegisterUserRequest $request) {

        $request->validated();
        $userRequest = $request->only(['username','email','password']);

        $newUser = $this->userService->register($userRequest);

        if ($newUser) {
            return $this->responseSuccess($newUser,'Registered successfully');
        } else {
            return $this->responseError('Error when trying to register.');
        }
    }

    public function userData(Request $request) {
        return $request->user();
    }
}
