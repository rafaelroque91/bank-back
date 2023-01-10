<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::factory([
            'username' => 'admin',
            'email' => 'admin@bnbbank.com',
            'admin' => true,
            'password' => 'password'
        ])->count(1)->create();
    }
}
