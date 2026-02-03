<?php

namespace Database\Factories;

use App\Models\Applications;
use Illuminate\Database\Eloquent\Factories\Factory;

class ApplicationsFactory extends Factory
{
    protected $model = Applications::class;

    public function definition(): array
    {
        $deadline = $this->faker->dateTimeBetween('now', '+2 weeks'); // generate once to split for both

        return [
            'user_id' => $this->faker->unique()->numberBetween(1, 20),
            'vacancy_id' => 'DEV-001',
            'status' => $this->faker->randomElement(['Pending', 'Complete', 'Incomplete', 'Closed']),

            // ✅ Split deadline into date and time
            'deadline_date' => $deadline->format('Y-m-d'),
            'deadline_time' => $deadline->format('H:i:s'),

            'result' => $this->faker->numberBetween(50, 100),
            'answers' => json_encode([
                '1' => $this->faker->randomElement(['A', 'B', 'C', 'D']),
                '2' => $this->faker->randomElement(['A', 'B', 'C', 'D']),
                '3' => $this->faker->randomElement(['A', 'B', 'C', 'D']),
            ]),
            'scores' => json_encode([
                '1' => $this->faker->numberBetween(0, 3),
                '2' => $this->faker->numberBetween(0, 3),
                '3' => $this->faker->numberBetween(0, 3),
            ]),
            'is_valid' => $this->faker->boolean(),
        ];
    }
}
