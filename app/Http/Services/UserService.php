<?php
namespace App\Http\Services;

use App\Http\Repositories\UserRepository;
use App\Models\User;

class UserService {

    public function register(array $user) : User {
        $user = array_merge($user,['admin' => 0,'balance' => 0]);

        return UserRepository::create($user);
    }
}
