<?php
namespace App\Http\Repositories;

use App\Models\User;

class UserRepository {

    public static function create(array $userData) : User {
        $newUser = User::create($userData);
        return $newUser;
    }
}
