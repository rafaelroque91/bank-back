<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;


class TransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'description' => $this->faker->text(50),
            'amount' => $this->faker->randomElement([2,200]),
            'type' => $this->faker->randomElement([0,1]),
            'filename' => 'file.jpg',
            'due_date' => $this->faker->date('Y-m-d'),
            'user_id' => User::factory()->create()->id,
            'status' =>$this->faker->randomElement([0,2])
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function unverified()
    {
        return $this->state(function (array $attributes) {
            return [
                'email_verified_at' => null,
            ];
        });
    }
}
